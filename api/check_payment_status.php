<?php
/**
 * Consulta o status local do pagamento para o frontend.
 * Os webhooks dos gateways persistem o status em pixgerado.
 */
session_start();
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['session_payment']) && !isset($_SESSION['session_checkout'])) {
    echo json_encode(['success' => false, 'status' => 'UNAUTHORIZED']);
    exit;
}

$gateway = strtolower(trim((string)($_POST['gateway'] ?? '')));
$transaction_id = trim((string)($_POST['transaction_id'] ?? ''));
$columns = [
    'mercadopago' => ['mp_transaction_id', 'mp_status'],
    'freepay' => ['freepay_transaction_id', 'freepay_status'],
    'pixgo' => ['pixgo_payment_id', 'pixgo_status'],
    'carthero' => ['carthero_payment_id', 'carthero_status'],
];

if ($gateway === 'copia_cola' && $transaction_id !== '') {
    $code_safe = mysqli_real_escape_string($conn, $transaction_id);
    $manual = mysqli_query($conn, "SELECT status_pagamento FROM pix_tabela_codigos WHERE codigo='$code_safe' ORDER BY id DESC LIMIT 1");
    $manual_row = $manual ? mysqli_fetch_assoc($manual) : null;
    $manual_status = strtoupper(trim((string)($manual_row['status_pagamento'] ?? '')));
    echo json_encode([
        'success' => true,
        'paid' => $manual_status === 'PAGO',
        'status' => $manual_status !== '' ? $manual_status : 'PENDING',
        'source' => 'admin'
    ]);
    exit;
}

if (!isset($columns[$gateway]) || $transaction_id === '') {
    echo json_encode(['success' => false, 'status' => 'UNKNOWN', 'error' => 'Pagamento inválido']);
    exit;
}

$id_column = $columns[$gateway][0];
$status_column = $columns[$gateway][1];
$id_safe = mysqli_real_escape_string($conn, $transaction_id);
$sql = "SELECT `$status_column` AS gateway_status, status FROM pixgerado WHERE `$id_column`='$id_safe' ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $sql);
$row = $result ? mysqli_fetch_assoc($result) : null;
if (!$row) {
    echo json_encode(['success' => true, 'paid' => false, 'status' => 'PENDING']);
    exit;
}

$status = strtoupper(trim((string)($row['gateway_status'] ?? '')));
$paid_statuses = [
    'APPROVED', 'PAID', 'PAGO', 'COMPLETED', 'RECEIVED', 'CONFIRMED', 'SUCCEEDED', 'SETTLED'
];
$paid = in_array($status, $paid_statuses, true);
if (!$paid && strtoupper(trim((string)($row['status'] ?? ''))) === 'PAGO') {
    $paid = true;
}

echo json_encode([
    'success' => true,
    'paid' => $paid,
    'status' => $status !== '' ? $status : 'PENDING'
]);
exit;
?>
