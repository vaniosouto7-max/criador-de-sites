<?php
/**
 * PixGo Webhook Handler
 * Processa notificações de pagamento da PixGo em tempo real
 *
 * Documentação: https://pixgo.org/api/v1/docs#webhooks
 *
 * Eventos suportados:
 *   payment.completed  → Pagamento confirmado
 *   payment.expired    → Pagamento expirou (20 minutos)
 *   payment.refunded   → Pagamento reembolsado
 */

require_once("db.php");

// Registrar log de entrada
error_log("[PixGo Webhook] Recebido em " . date('Y-m-d H:i:s'));

// Ler o corpo da requisição (JSON)
$raw_body = file_get_contents('php://input');
error_log("[PixGo Webhook] Payload: " . substr($raw_body, 0, 500));

if (empty($raw_body)) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload vazio']);
    exit;
}

$payload = json_decode($raw_body, true);

if (!$payload || !isset($payload['event'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload inválido ou campo event ausente']);
    exit;
}

// -------------------------------------------------------
// Verificar assinatura do webhook (HMAC-SHA256)
// -------------------------------------------------------
$event = $payload['event'] ?? '';
$timestamp = $_SERVER['HTTP_X_WEBHOOK_TIMESTAMP'] ?? '';
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

// Recuperar Webhook Secret da configuração
$sql_config = mysqli_query($conn, "SELECT pixgo_webhook_secret FROM pix WHERE id='1'");
$config_row = $sql_config ? mysqli_fetch_assoc($sql_config) : null;
$webhook_secret = $config_row['pixgo_webhook_secret'] ?? '';

// Se houver secret configurado, verificar assinatura
if (!empty($webhook_secret) && (!empty($timestamp) && !empty($signature))) {
    $signature_payload = $timestamp . '.' . $raw_body;
    $expected_signature = hash_hmac('sha256', $signature_payload, $webhook_secret);

    // Comparação timing-safe
    if (!hash_equals($expected_signature, $signature)) {
        error_log("[PixGo Webhook] Assinatura inválida! Esperado: $expected_signature, Recebido: $signature");
        http_response_code(401);
        echo json_encode(['error' => 'Assinatura inválida']);
        exit;
    }

    // Proteção contra replay attack (5 minutos)
    if (abs(time() - intval($timestamp)) > 300) {
        error_log("[PixGo Webhook] Timestamp expirado!");
        http_response_code(401);
        echo json_encode(['error' => 'Timestamp expirado']);
        exit;
    }

    error_log("[PixGo Webhook] Assinatura verificada com sucesso");
} else {
    error_log("[PixGo Webhook] Aviso: Webhook Secret não configurado ou headers ausentes. Processando sem verificação.");
}

// -------------------------------------------------------
// Extrair dados do webhook
// -------------------------------------------------------
$data = $payload['data'] ?? [];
$payment_id = $data['payment_id'] ?? '';
$external_id = $data['external_id'] ?? '';
$status = strtoupper($data['status'] ?? '');
$amount = $data['amount'] ?? 0;
$customer = $data['customer'] ?? [];
$payer = $data['payer'] ?? [];
$amounts = $data['amounts'] ?? [];

error_log("[PixGo Webhook] Event: $event | Payment ID: $payment_id | Status: $status | Amount: $amount");

// -------------------------------------------------------
// Verificar se a tabela pixgerado possui as colunas PixGo
// -------------------------------------------------------
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM pixgerado LIKE 'pixgo_payment_id'");
if (($check_col ? mysqli_num_rows($check_col) : 0) === 0) {
    mysqli_query($conn, "ALTER TABLE pixgerado ADD COLUMN pixgo_payment_id TEXT NOT NULL DEFAULT ''");
    mysqli_query($conn, "ALTER TABLE pixgerado ADD COLUMN pixgo_status TEXT NOT NULL DEFAULT 'PENDING'");
    error_log("[PixGo Webhook] Colunas pixgo_payment_id e pixgo_status adicionadas à tabela pixgerado.");
}

// -------------------------------------------------------
// Processar evento de pagamento
// -------------------------------------------------------
if (!empty($payment_id)) {
    $pid_safe = mysqli_real_escape_string($conn, $payment_id);
    $status_safe = mysqli_real_escape_string($conn, $status);

    // Buscar registro de pagamento pelo external_id ou payment_id
    $check = mysqli_query($conn, "SELECT id, produto, ip FROM pixgerado WHERE pixgo_payment_id='$pid_safe' LIMIT 1");

    if (($check ? mysqli_num_rows($check) : 0) > 0) {
        $row_pix = mysqli_fetch_assoc($check);
        $pix_id = $row_pix['id'];
        $produto_codigo = $row_pix['produto'];
        $cliente_ip = $row_pix['ip'];

        // Atualizar status do PIX
        $update = mysqli_query($conn, "UPDATE pixgerado SET pixgo_status='$status_safe', data_atualizacao=NOW() WHERE pixgo_payment_id='$pid_safe'");
        error_log("[PixGo Webhook] Status atualizado para '$status' no pagamento '$payment_id'");

        // Processar conforme o evento
        switch ($event) {
            case 'payment.completed':
                // Marcar cliente como pago
                $cliente_ip_b64 = base64_encode($cliente_ip);
                $update_cliente = mysqli_query($conn, "UPDATE clientes SET pagamento_confirmado='1', data_pagamento=NOW() WHERE ip='$cliente_ip_b64' ORDER BY id DESC LIMIT 1");

                // Registrar venda como confirmada
                $valor_net = $amounts['net'] ?? $amount;
                $sql_venda = "INSERT INTO vendas_confirmadas (produto_codigo, cliente_ip, transaction_id, valor, data_venda, status, gateway)
                             VALUES ('$produto_codigo', '$cliente_ip', '$pid_safe', '$valor_net', NOW(), 'COMPLETED', 'pixgo')
                             ON DUPLICATE KEY UPDATE status='COMPLETED', data_venda=NOW()";
	                mysqli_query($conn, $sql_venda);

	                // BAIXA AUTOMÁTICA: Marcar código Pix como pago se for um código múltiplo
	                mysqli_query($conn, "UPDATE produto_pix_codigos SET status='pago' WHERE cliente_ip='$cliente_ip' AND produto_codigo='$produto_codigo' AND status='reservado'");

	                error_log("[PixGo Webhook] Pagamento confirmado para o produto: $produto_codigo | Valor líquido: R$ $valor_net");
                break;

            case 'payment.expired':
                error_log("[PixGo Webhook] Pagamento expirou para: $payment_id");
                break;

            case 'payment.refunded':
                // Marcar como reembolsado
                $cliente_ip_b64 = base64_encode($cliente_ip);
                $update_cliente = mysqli_query($conn, "UPDATE clientes SET pagamento_confirmado='0' WHERE ip='$cliente_ip_b64' ORDER BY id DESC LIMIT 1");
                error_log("[PixGo Webhook] Pagamento reembolsado para: $payment_id");
                break;
        }
    } else {
        // Se não encontrar pelo payment_id, tentar registrar novo
        error_log("[PixGo Webhook] Pagamento '$payment_id' não encontrado no banco. Pode ser um pagamento novo.");

        // Opcionalmente, criar um novo registro se necessário
        // Isso depende da lógica do seu negócio
    }
}

// -------------------------------------------------------
// Responder com 200 OK para confirmar recebimento
// -------------------------------------------------------
http_response_code(200);
echo json_encode(['received' => true, 'event' => $event, 'payment_id' => $payment_id]);
exit;
?>
