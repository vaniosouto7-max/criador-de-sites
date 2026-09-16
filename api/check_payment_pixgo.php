<?php
/**
 * PixGo Payment Status Checker
 * Chamado via AJAX pelo frontend para checar se o PIX foi pago
 *
 * POST params:
 *   payment_id  string  ID do pagamento PixGo (ex: "dep_abc123")
 */

session_start();
require_once("db.php");
require_once("pixgo.php");

header('Content-Type: application/json; charset=utf-8');

// Validar sessão de pagamento
if (!isset($_SESSION['session_payment']) || $_SESSION['session_payment'] <= time()) {
    echo json_encode(['success' => false, 'error' => 'Sessão expirada', 'status' => 'EXPIRED']);
    exit;
}

$payment_id = isset($_POST['payment_id']) ? trim($_POST['payment_id']) : '';

if (empty($payment_id)) {
    echo json_encode(['success' => false, 'error' => 'ID do pagamento não informado', 'status' => 'UNKNOWN']);
    exit;
}

// Primeiro, verificar no banco local (atualizado pelo webhook)
$pid_safe = mysqli_real_escape_string($conn, $payment_id);
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM pixgerado LIKE 'pixgo_payment_id'");

if (($check_col ? mysqli_num_rows($check_col) : 0) > 0) {
    $local = mysqli_query($conn, "SELECT pixgo_status FROM pixgerado WHERE pixgo_payment_id='$pid_safe' LIMIT 1");
    $local_row = ($local) ? mysqli_fetch_assoc($local) : null;

    if ($local_row && !empty($local_row['pixgo_status'])) {
        $local_status = strtoupper($local_row['pixgo_status']);

        // Se o webhook já atualizou para COMPLETED, retornar imediatamente
        if ($local_status === 'COMPLETED') {
            echo json_encode(['success' => true, 'status' => 'COMPLETED', 'source' => 'webhook']);
            exit;
        }
    }
}

// Se não confirmado pelo webhook, consultar a API diretamente
$result = getPixGoPaymentStatus($payment_id);

if ($result['success']) {
    $api_status = strtoupper($result['status']);

    // Normalizar status para compatibilidade com o frontend
    // PixGo retorna: pending, completed, expired, cancelled
    // Esperado pelo frontend: PENDING, COMPLETED, EXPIRED, CANCELLED
    $normalized_status = $api_status;

    // Atualizar status no banco se mudou
    if (!empty($api_status)) {
        $status_safe = mysqli_real_escape_string($conn, $api_status);
        $check_col2 = mysqli_query($conn, "SHOW COLUMNS FROM pixgerado LIKE 'pixgo_payment_id'");

        if (($check_col2 ? mysqli_num_rows($check_col2) : 0) > 0) {
            mysqli_query($conn, "UPDATE pixgerado SET pixgo_status='$status_safe', data_atualizacao=NOW() WHERE pixgo_payment_id='$pid_safe'");
        }
    }

    echo json_encode([
        'success' => true,
        'status'  => $normalized_status,
        'source'  => 'api',
        'amount'  => $result['amount'] ?? 0,
        'created_at' => $result['created_at'] ?? '',
        'updated_at' => $result['updated_at'] ?? ''
    ]);
} else {
    echo json_encode([
        'success' => false,
        'status'  => 'UNKNOWN',
        'error'   => $result['error'] ?? 'Erro ao consultar API'
    ]);
}

exit;
?>
