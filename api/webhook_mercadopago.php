<?php
/**
 * Mercado Pago - Webhook de Notificações
 * Documentação: https://www.mercadopago.com.br/developers/pt/docs/your-integrations/notifications/webhooks
 *
 * Este endpoint recebe notificações de atualização de pagamentos PIX.
 *
 * O Mercado Pago envia um POST com o seguinte payload JSON:
 * {
 *   "id": 12345,
 *   "live_mode": true,
 *   "type": "payment",
 *   "action": "payment.updated",
 *   "data": { "id": "999999999" }
 * }
 *
 * Após receber a notificação, consultamos a API para obter o status real do pagamento.
 *
 * Status possíveis do Mercado Pago:
 *   pending    → Aguardando pagamento
 *   approved   → Pagamento aprovado
 *   authorized → Autorizado (captura pendente)
 *   in_process → Em análise
 *   in_mediation → Em disputa
 *   rejected   → Rejeitado
 *   cancelled  → Cancelado
 *   refunded   → Reembolsado
 *   charged_back → Chargeback
 */

require_once("db.php");

// Log de entrada
error_log("[MP Webhook] Recebido em " . date('Y-m-d H:i:s'));

// Ler corpo da requisição
$raw_body = file_get_contents('php://input');
error_log("[MP Webhook] Payload: " . $raw_body);

// Responder imediatamente com 200 para evitar retentativas desnecessárias
// (o processamento continua abaixo)
http_response_code(200);
header('Content-Type: application/json');

if (empty($raw_body)) {
    echo json_encode(['received' => false, 'error' => 'Payload vazio']);
    exit;
}

$payload = json_decode($raw_body, true);

// Aceitar também notificações via query string (método antigo IPN)
if (empty($payload) || !isset($payload['type'])) {
    // Tentar via GET params (IPN legado)
    $topic = $_GET['topic'] ?? $_GET['type'] ?? '';
    $mp_id = $_GET['id'] ?? '';

    if (!empty($topic) && !empty($mp_id) && $topic === 'payment') {
        $payload = ['type' => 'payment', 'data' => ['id' => $mp_id]];
    } else {
        echo json_encode(['received' => false, 'error' => 'Payload inválido']);
        exit;
    }
}

$type    = $payload['type']       ?? '';
$action  = $payload['action']     ?? '';
$data_id = $payload['data']['id'] ?? '';

error_log("[MP Webhook] Type: $type | Action: $action | Data ID: $data_id");

// Processar apenas notificações de pagamento
if ($type !== 'payment' || empty($data_id)) {
    echo json_encode(['received' => true, 'processed' => false, 'reason' => 'Tipo não é payment ou ID ausente']);
    exit;
}

// -------------------------------------------------------
// Garantir colunas do Mercado Pago na tabela pixgerado
// -------------------------------------------------------
$check_mp = mysqli_query($conn, "SHOW COLUMNS FROM pixgerado LIKE 'mp_transaction_id'");
if (($check_mp ? mysqli_num_rows($check_mp) : 0) === 0) {
    mysqli_query($conn, "ALTER TABLE pixgerado ADD COLUMN mp_transaction_id VARCHAR(64) NOT NULL DEFAULT ''");
    mysqli_query($conn, "ALTER TABLE pixgerado ADD COLUMN mp_status VARCHAR(32) NOT NULL DEFAULT 'pending'");
    error_log("[MP Webhook] Colunas mp_transaction_id e mp_status adicionadas à tabela pixgerado.");
}

// -------------------------------------------------------
// Consultar a API do Mercado Pago para obter status real
// -------------------------------------------------------
$sql_cfg = mysqli_query($conn, "SELECT mp_access_token FROM pix WHERE id='1'");
$cfg     = $sql_cfg ? mysqli_fetch_assoc($sql_cfg) : null;
$access_token = trim($cfg['mp_access_token'] ?? '');

if (empty($access_token)) {
    error_log("[MP Webhook] Access Token não configurado.");
    echo json_encode(['received' => true, 'processed' => false, 'error' => 'Access Token não configurado']);
    exit;
}

$ch = curl_init('https://api.mercadopago.com/v1/payments/' . $data_id);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Accept: application/json',
        'Authorization: Bearer ' . $access_token
    ],
    CURLOPT_TIMEOUT => 15,
]);
$api_response  = curl_exec($ch);
$api_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($api_http_code !== 200) {
    error_log("[MP Webhook] Falha ao consultar pagamento $data_id. HTTP: $api_http_code");
    echo json_encode(['received' => true, 'processed' => false, 'error' => "HTTP $api_http_code ao consultar API"]);
    exit;
}

$payment = json_decode($api_response, true);
$mp_status       = strtolower($payment['status']           ?? 'unknown');
$mp_status_detail = $payment['status_detail']              ?? '';
$mp_amount       = $payment['transaction_amount']          ?? 0;
$mp_ext_ref      = $payment['external_reference']          ?? '';

error_log("[MP Webhook] Pagamento $data_id | Status: $mp_status | Detalhe: $mp_status_detail | Valor: $mp_amount");

// -------------------------------------------------------
// Atualizar status no banco de dados
// -------------------------------------------------------
$tid_safe    = mysqli_real_escape_string($conn, (string)$data_id);
$status_safe = mysqli_real_escape_string($conn, $mp_status);

// Verificar se existe registro com esse transaction_id
$check = mysqli_query($conn, "SELECT id, produto, ip FROM pixgerado WHERE mp_transaction_id='$tid_safe' LIMIT 1");

if (($check ? mysqli_num_rows($check) : 0) > 0) {
    $row_pix        = mysqli_fetch_assoc($check);
    $pix_id         = $row_pix['id'];
    $produto_codigo = $row_pix['produto'];
    $cliente_ip     = $row_pix['ip'];

    // Atualizar status
    mysqli_query($conn, "UPDATE pixgerado SET mp_status='$status_safe' WHERE mp_transaction_id='$tid_safe'");
    error_log("[MP Webhook] Status atualizado para '$mp_status' na transação $data_id");

    if ($mp_status === 'approved') {
        // Marcar cliente como pago
        $cliente_ip_b64 = base64_encode($cliente_ip);
        mysqli_query($conn, "UPDATE clientes SET pagamento_confirmado='1', data_pagamento=NOW() WHERE ip='$cliente_ip_b64' ORDER BY id DESC LIMIT 1");
        error_log("[MP Webhook] Cliente marcado como pago: $cliente_ip");

        // Registrar venda confirmada
        $amount_safe = mysqli_real_escape_string($conn, (string)$mp_amount);
	        mysqli_query($conn, "INSERT INTO vendas_confirmadas (produto_codigo, cliente_ip, transaction_id, valor, data_venda, status, gateway)
	                      VALUES ('$produto_codigo', '$cliente_ip', '$tid_safe', '$amount_safe', NOW(), 'PAID', 'mercadopago')
	                      ON DUPLICATE KEY UPDATE status='PAID', data_venda=NOW()");

	        // BAIXA AUTOMÁTICA: Marcar código Pix como pago se for um código múltiplo
	        mysqli_query($conn, "UPDATE produto_pix_codigos SET status='pago' WHERE cliente_ip='$cliente_ip' AND produto_codigo='$produto_codigo' AND status='reservado'");

		        error_log("[MP Webhook] Venda confirmada para produto: $produto_codigo");
	    } elseif (in_array($mp_status, ['rejected', 'cancelled', 'refunded', 'charged_back'])) {
        $cliente_ip_b64 = base64_encode($cliente_ip);
        mysqli_query($conn, "UPDATE clientes SET pagamento_confirmado='0' WHERE ip='$cliente_ip_b64' ORDER BY id DESC LIMIT 1");
        error_log("[MP Webhook] Pagamento $mp_status para: $cliente_ip");
    }

} else {
    // Transação não encontrada — pode ter sido gerada antes da coluna existir ou via outro fluxo
    error_log("[MP Webhook] Transação $data_id não encontrada no banco local. Tentando por external_reference...");

    if (!empty($mp_ext_ref)) {
        // Tentar localizar pelo external_reference (código do produto + timestamp)
        $ext_ref_produto = explode('_', $mp_ext_ref)[0];
        $ext_safe = mysqli_real_escape_string($conn, $ext_ref_produto);
        $check2 = mysqli_query($conn, "SELECT id, produto, ip FROM pixgerado WHERE produto='$ext_safe' ORDER BY id DESC LIMIT 1");

        if (($check2 ? mysqli_num_rows($check2) : 0) > 0) {
            $row2 = mysqli_fetch_assoc($check2);
            $pix_id2 = $row2['id'];
            $cliente_ip2 = $row2['ip'];

            // Atualizar com o transaction_id do MP e novo status
            mysqli_query($conn, "UPDATE pixgerado SET mp_transaction_id='$tid_safe', mp_status='$status_safe' WHERE id='$pix_id2'");

            if ($mp_status === 'approved') {
                $cliente_ip_b64 = base64_encode($cliente_ip2);
                mysqli_query($conn, "UPDATE clientes SET pagamento_confirmado='1', data_pagamento=NOW() WHERE ip='$cliente_ip_b64' ORDER BY id DESC LIMIT 1");
                $amount_safe = mysqli_real_escape_string($conn, (string)$mp_amount);
                $prod_safe2  = mysqli_real_escape_string($conn, $row2['produto']);
	                mysqli_query($conn, "INSERT INTO vendas_confirmadas (produto_codigo, cliente_ip, transaction_id, valor, data_venda, status, gateway)
	                                     VALUES ('$prod_safe2', '$cliente_ip2', '$tid_safe', '$amount_safe', NOW(), 'PAID', 'mercadopago')
	                                     ON DUPLICATE KEY UPDATE status='PAID', data_venda=NOW()");

	                // BAIXA AUTOMÁTICA: Marcar código Pix como pago se for um código múltiplo
	                mysqli_query($conn, "UPDATE produto_pix_codigos SET status='pago' WHERE cliente_ip='$cliente_ip2' AND produto_codigo='$prod_safe2' AND status='reservado'");

	                error_log("[MP Webhook] Venda confirmada via external_reference para produto: " . $row2['produto']);
            }
        } else {
            error_log("[MP Webhook] Transação $data_id não encontrada nem por external_reference.");
        }
    }
}

echo json_encode([
    'received'       => true,
    'processed'      => true,
    'transaction_id' => $data_id,
    'status'         => $mp_status
]);
exit;
?>
