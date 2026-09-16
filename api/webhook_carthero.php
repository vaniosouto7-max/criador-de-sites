<?php
/**
 * CartHero Webhook Handler
 * Recebe notificações de pagamento do CartHero e atualiza o banco de dados
 *
 * Documentação: https://doc.carthero.com.br/webhooks-1645223m0
 *
 * Payload esperado:
 * {
 *   "id": "invoice_123456",
 *   "status": "paid",
 *   "total": 1990,
 *   "method": "pix",
 *   "event": "invoice.paid"
 * }
 */

require_once("db.php");

// Apenas aceitar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Ler corpo da requisição
$raw_body = file_get_contents('php://input');
$data     = json_decode($raw_body, true);

if (empty($data)) {
    http_response_code(400);
    exit('Invalid payload');
}

// Registrar log para diagnóstico
error_log("CartHero Webhook recebido: " . $raw_body);

// Extrair campos do payload
$event      = $data['event']  ?? '';
$invoice_id = $data['id']     ?? '';
$status     = $data['status'] ?? '';
$total      = $data['total']  ?? 0;   // em centavos
$method     = $data['method'] ?? '';

if (empty($invoice_id)) {
    http_response_code(400);
    exit('Missing invoice id');
}

// Garantir que as colunas do CartHero existem em pixgerado
$chk = mysqli_query($conn, "SHOW COLUMNS FROM pixgerado LIKE 'carthero_payment_id'");
if (($chk ? mysqli_num_rows($chk) : 0) === 0) {
    mysqli_query($conn, "ALTER TABLE pixgerado ADD COLUMN carthero_payment_id VARCHAR(255) DEFAULT NULL");
    mysqli_query($conn, "ALTER TABLE pixgerado ADD COLUMN carthero_status VARCHAR(50) DEFAULT NULL");
}

// Buscar o registro de pixgerado correspondente
$invoice_safe = mysqli_real_escape_string($conn, $invoice_id);
$sql_pix = mysqli_query($conn, "SELECT * FROM pixgerado WHERE carthero_payment_id='$invoice_safe' ORDER BY id DESC LIMIT 1");
$pix_row = $sql_pix ? mysqli_fetch_assoc($sql_pix) : null;

if (!$pix_row) {
    // Responder 200 mesmo sem encontrar (evita reenvios desnecessários)
    http_response_code(200);
    exit('Invoice not found locally');
}

$pix_id    = $pix_row['id'];
$ip_payer  = $pix_row['ip'];
$produto   = $pix_row['produto'];
$valor_str = $pix_row['valor'];

// Atualizar status no pixgerado
$status_safe = mysqli_real_escape_string($conn, $status);
mysqli_query($conn, "UPDATE pixgerado SET carthero_status='$status_safe', data_atualizacao=NOW() WHERE id='$pix_id'");

// Processar evento de pagamento confirmado
if ($event === 'invoice.paid' || $status === 'paid') {

    date_default_timezone_set('America/Sao_Paulo');
    $hora_now = date('H:i:s');

    // Garantir que a tabela vendas_confirmadas existe
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS vendas_confirmadas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip VARCHAR(50),
        produto VARCHAR(100),
        valor VARCHAR(20),
        gateway VARCHAR(50),
        transaction_id VARCHAR(255),
        hora VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Registrar venda confirmada
    $ip_safe      = mysqli_real_escape_string($conn, $ip_payer);
    $produto_safe = mysqli_real_escape_string($conn, $produto);
    $valor_safe   = mysqli_real_escape_string($conn, $valor_str);

    mysqli_query($conn, "INSERT INTO vendas_confirmadas (ip, produto, valor, gateway, transaction_id, hora)
        VALUES ('$ip_safe', '$produto_safe', '$valor_safe', 'carthero', '$invoice_safe', '$hora_now')");

    // Marcar cliente como pago (campo pago na tabela clientes, se existir)
    $chk_pago = mysqli_query($conn, "SHOW COLUMNS FROM clientes LIKE 'pago'");
    if ($chk_pago && mysqli_num_rows($chk_pago) > 0) {
        mysqli_query($conn, "UPDATE clientes SET pago='1' WHERE ip='" . mysqli_real_escape_string($conn, base64_encode($ip_payer)) . "' ORDER BY id DESC LIMIT 1");
    }

    // Dar baixa no código PIX de produto (produto_pix_codigos), se aplicável
    $sql_cod = mysqli_query($conn, "SELECT * FROM produto_pix_codigos WHERE produto_codigo='$produto_safe' AND cliente_ip='$ip_safe' AND status='reservado' LIMIT 1");
    if ($sql_cod && $row_cod = mysqli_fetch_assoc($sql_cod)) {
        mysqli_query($conn, "UPDATE produto_pix_codigos SET status='usado' WHERE id='" . (int)$row_cod['id'] . "'");
    }

    error_log("CartHero: Pagamento confirmado para invoice $invoice_id, produto $produto, valor $valor_str");
}

http_response_code(200);
echo 'OK';
?>
