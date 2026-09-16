<?php
/**
 * FreePay Brasil - Verificação de Status de Pagamento
 * Chamado via AJAX pelo frontend para checar se o PIX foi pago
 *
 * POST params:
 *   transaction_id  string  ID da transação FreePay (ex: "pay_abc123")
 */

session_start();
require_once("db.php");
require_once("freepay.php");

header('Content-Type: application/json; charset=utf-8');

// Validar sessão de pagamento
if (!isset($_SESSION['session_checkout']) && !isset($_SESSION['session_payment'])) {
    echo json_encode(['success' => false, 'error' => 'Sessão não encontrada', 'status' => 'EXPIRED']);
    exit;
}

$transaction_id = isset($_POST['transaction_id']) ? trim($_POST['transaction_id']) : '';

if (empty($transaction_id)) {
    echo json_encode(['success' => false, 'error' => 'ID da transação não informado', 'status' => 'UNKNOWN']);
    exit;
}

// Primeiro, verificar no banco local (atualizado pelo webhook)
$tid_safe = mysqli_real_escape_string($conn, $transaction_id);
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM pixgerado LIKE 'freepay_transaction_id'");

if (($check_col ? mysqli_num_rows($check_col) : 0) > 0) {
    $local = mysqli_query($conn, "SELECT freepay_status FROM pixgerado WHERE freepay_transaction_id='$tid_safe' LIMIT 1");
    $local_row = ($local) ? mysqli_fetch_assoc($local) : null;

    if ($local_row && !empty($local_row['freepay_status'])) {
        $local_status = strtoupper($local_row['freepay_status']);

        // Se o webhook já atualizou para PAID, retornar imediatamente
        if ($local_status === 'PAID') {
            echo json_encode(['success' => true, 'status' => 'PAID', 'source' => 'webhook']);
            exit;
        }
    }
}

// Se não confirmado pelo webhook, consultar a API diretamente
$result = getFreePayTransaction($transaction_id);

if ($result['success']) {
    $api_status = strtoupper($result['status']);

    // Atualizar status no banco se mudou
    if (!empty($api_status)) {
        $status_safe = mysqli_real_escape_string($conn, $api_status);
        $check_col2 = mysqli_query($conn, "SHOW COLUMNS FROM pixgerado LIKE 'freepay_transaction_id'");
        if (($check_col2 ? mysqli_num_rows($check_col2) : 0) > 0) {
            mysqli_query($conn, "UPDATE pixgerado SET freepay_status='$status_safe' WHERE freepay_transaction_id='$tid_safe'");
        }
    }

    echo json_encode([
        'success' => true,
        'status'  => $api_status,
        'source'  => 'api'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'status'  => 'UNKNOWN',
        'error'   => $result['error'] ?? 'Erro ao consultar API'
    ]);
}
exit;
