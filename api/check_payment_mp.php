<?php
/**
 * Mercado Pago - Verificação de Status de Pagamento
 * Chamado via AJAX pelo frontend para checar se o PIX foi pago.
 *
 * POST params:
 *   transaction_id  string  ID da transação Mercado Pago
 */

session_start();
require_once("db.php");
require_once("mercadopago.php");

header('Content-Type: application/json; charset=utf-8');

// Validar sessão de pagamento
if (!isset($_SESSION['session_payment']) || $_SESSION['session_payment'] <= time()) {
    echo json_encode(['success' => false, 'error' => 'Sessão expirada', 'status' => 'EXPIRED']);
    exit;
}

$transaction_id = isset($_POST['transaction_id']) ? trim($_POST['transaction_id']) : '';

if (empty($transaction_id)) {
    echo json_encode(['success' => false, 'error' => 'ID da transação não informado', 'status' => 'UNKNOWN']);
    exit;
}

// 1. Verificar primeiro no banco local (atualizado pelo webhook)
$tid_safe  = mysqli_real_escape_string($conn, $transaction_id);
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM pixgerado LIKE 'mp_transaction_id'");

if (($check_col ? mysqli_num_rows($check_col) : 0) > 0) {
    $local = mysqli_query($conn, "SELECT mp_status FROM pixgerado WHERE mp_transaction_id='$tid_safe' LIMIT 1");
    $local_row = $local ? mysqli_fetch_assoc($local) : null;

    if ($local_row && !empty($local_row['mp_status'])) {
        $local_status = strtoupper($local_row['mp_status']);

        // Se o webhook já confirmou o pagamento, retornar imediatamente
        if ($local_status === 'APPROVED') {
            echo json_encode(['success' => true, 'status' => 'PAID', 'source' => 'webhook']);
            exit;
        }

        // Se foi rejeitado/cancelado, informar
        if (in_array($local_status, ['REJECTED', 'CANCELLED', 'REFUNDED', 'CHARGED_BACK'])) {
            echo json_encode(['success' => true, 'status' => $local_status, 'source' => 'webhook']);
            exit;
        }
    }
}

// 2. Se não confirmado pelo webhook, consultar a API diretamente
$result = getMercadoPagoPayment($transaction_id);

if ($result['success']) {
    $api_status = strtoupper($result['status']);

    // Mapear status do MP para padrão interno
    $mapped_status = $api_status;
    if ($api_status === 'APPROVED') {
        $mapped_status = 'PAID';
    }

    // Atualizar status no banco se mudou
    if (!empty($api_status)) {
        $status_safe = mysqli_real_escape_string($conn, strtolower($api_status));
        $check_col2  = mysqli_query($conn, "SHOW COLUMNS FROM pixgerado LIKE 'mp_transaction_id'");
        if (($check_col2 ? mysqli_num_rows($check_col2) : 0) > 0) {
            mysqli_query($conn, "UPDATE pixgerado SET mp_status='$status_safe' WHERE mp_transaction_id='$tid_safe'");
        }

        // Se aprovado, marcar cliente como pago
        if ($api_status === 'APPROVED') {
            $check_pix = mysqli_query($conn, "SELECT ip FROM pixgerado WHERE mp_transaction_id='$tid_safe' LIMIT 1");
            $pix_row   = $check_pix ? mysqli_fetch_assoc($check_pix) : null;
            if ($pix_row && !empty($pix_row['ip'])) {
                $ip_b64 = base64_encode($pix_row['ip']);
                mysqli_query($conn, "UPDATE clientes SET pagamento_confirmado='1', data_pagamento=NOW() WHERE ip='$ip_b64' ORDER BY id DESC LIMIT 1");
            }
        }
    }

    echo json_encode([
        'success' => true,
        'status'  => $mapped_status,
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
?>
