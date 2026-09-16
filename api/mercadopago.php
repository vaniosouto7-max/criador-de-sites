<?php
/**
 * Mercado Pago - Integração de Pagamento PIX
 *
 * Utiliza a API v1/payments do Mercado Pago para gerar pagamentos via PIX.
 * Documentação: https://www.mercadopago.com.br/developers/pt/reference
 *
 * Campos retornados pela API:
 *   $response["id"]                                                        → ID da transação
 *   $response["point_of_interaction"]["transaction_data"]["qr_code"]       → Código PIX copia e cola
 *   $response["point_of_interaction"]["transaction_data"]["qr_code_base64"]→ QR Code em base64
 *   $response["point_of_interaction"]["transaction_data"]["ticket_url"]    → URL do ticket
 *   $response["status"]                                                    → pending / approved / rejected
 */

require_once("db.php");

/**
 * Cria um pagamento PIX via Mercado Pago.
 *
 * @param float  $amount         Valor em reais (ex: 99.90)
 * @param array  $customer_data  ['nome', 'email', 'telefone', 'cpf']
 * @param array  $product_data   ['nome', 'codigo', 'quantidade']
 * @return array ['success', 'pix_code', 'pix_qr', 'pix_qr_base64', 'transaction_id', 'status'] ou ['success'=>false, 'error'=>'...']
 */
function createMercadoPagoPix($amount, $customer_data, $product_data = []) {
    global $conn;

    // 1. Buscar credenciais do banco
    $sql = mysqli_query($conn, "SELECT * FROM pix WHERE id='1'");
    $config = $sql ? mysqli_fetch_assoc($sql) : null;

    if (!$config || (int)($config['use_mercadopago'] ?? 0) !== 1) {
        return ['success' => false, 'error' => 'Gateway Mercado Pago desativado ou não configurado.'];
    }

    $access_token = trim($config['mp_access_token'] ?? '');

    if (empty($access_token)) {
        return ['success' => false, 'error' => 'Access Token do Mercado Pago não configurado.'];
    }

    // 2. Normalizar valor para float com 2 casas decimais
    $clean = preg_replace('/[^\d,.]/', '', (string)$amount);
    if (strpos($clean, ',') !== false) {
        $clean = str_replace('.', '', $clean);
        $clean = str_replace(',', '.', $clean);
    }
    $amount_float = round((float)$clean, 2);

    if ($amount_float <= 0) {
        return ['success' => false, 'error' => 'Valor inválido para pagamento.'];
    }

    // 3. Dados do pagador
    $customer_name  = !empty($customer_data['nome'])     ? trim($customer_data['nome'])     : 'Cliente';
    $customer_email = !empty($customer_data['email'])    ? trim($customer_data['email'])    : 'cliente@email.com';
    $customer_cpf   = !empty($customer_data['cpf'])      ? preg_replace('/\D/', '', $customer_data['cpf']) : '00000000000';

    // Separar primeiro e último nome
    $name_parts  = explode(' ', $customer_name, 2);
    $first_name  = $name_parts[0];
    $last_name   = isset($name_parts[1]) ? $name_parts[1] : $first_name;

    // 4. Referência externa única
    $item_ref = !empty($product_data['codigo']) ? $product_data['codigo'] . '_' . time() : 'prod_' . time();

    // 5. Montar payload
    $payload = [
        'transaction_amount' => $amount_float,
        'payment_method_id'  => 'pix',
        'external_reference' => $item_ref,
        'notification_url'   => _mp_base_url() . '/api/webhook_mercadopago.php',
        'description'        => !empty($product_data['nome']) ? $product_data['nome'] : 'Produto',
        'payer'              => [
            'first_name'     => $first_name,
            'last_name'      => $last_name,
            'email'          => $customer_email,
            'identification' => [
                'type'   => 'CPF',
                'number' => $customer_cpf
            ]
        ]
    ];

    // 6. Requisição cURL
    $ch = curl_init('https://api.mercadopago.com/v1/payments');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $access_token,
            'X-Idempotency-Key: ' . md5($item_ref . $amount_float)
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if (!empty($curl_err)) {
        error_log("[MercadoPago] cURL Error: " . $curl_err);
        return ['success' => false, 'error' => 'Erro de conexão: ' . $curl_err];
    }

    $data = json_decode($response, true);

    if (($http_code === 200 || $http_code === 201) && !empty($data['id'])) {
        $pix_code    = $data['point_of_interaction']['transaction_data']['qr_code']        ?? '';
        $pix_b64     = $data['point_of_interaction']['transaction_data']['qr_code_base64'] ?? '';
        $ticket_url  = $data['point_of_interaction']['transaction_data']['ticket_url']     ?? '';
        $tid         = (string)($data['id'] ?? '');
        $status      = strtoupper($data['status'] ?? 'pending');

        if (!empty($pix_code)) {
            // Montar URL do QR Code via serviço externo (fallback se base64 não vier)
            $pix_qr = !empty($pix_b64)
                ? 'data:image/png;base64,' . $pix_b64
                : 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($pix_code);

            return [
                'success'        => true,
                'pix_code'       => $pix_code,
                'pix_qr'         => $pix_qr,
                'pix_qr_base64'  => $pix_b64,
                'ticket_url'     => $ticket_url,
                'transaction_id' => $tid,
                'status'         => $status
            ];
        }
    }

    // Falha
    $error_msg = isset($data['message']) ? $data['message'] : "HTTP $http_code: $response";
    error_log("[MercadoPago] Erro ao criar pagamento: " . $error_msg);

    return ['success' => false, 'error' => $error_msg];
}

/**
 * Consulta o status de um pagamento pelo ID da transação.
 *
 * @param string $transaction_id  ID retornado na criação do pagamento
 * @return array ['success', 'status'] ou ['success'=>false]
 */
function getMercadoPagoPayment($transaction_id) {
    global $conn;

    $sql = mysqli_query($conn, "SELECT mp_access_token FROM pix WHERE id='1'");
    $config = $sql ? mysqli_fetch_assoc($sql) : null;
    $access_token = trim($config['mp_access_token'] ?? '');

    if (empty($access_token) || empty($transaction_id)) {
        return ['success' => false, 'error' => 'Configuração incompleta.'];
    }

    $ch = curl_init('https://api.mercadopago.com/v1/payments/' . $transaction_id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Authorization: Bearer ' . $access_token
        ],
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($http_code === 200 && !empty($data['id'])) {
        return [
            'success' => true,
            'status'  => strtoupper($data['status'] ?? 'pending'),
            'data'    => $data
        ];
    }

    return ['success' => false, 'error' => "HTTP $http_code"];
}

/**
 * Retorna a URL base do servidor atual.
 */
function _mp_base_url() {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}
?>
