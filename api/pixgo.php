<?php
/**
 * PixGo API Integration
 * Integração com a API PixGo para gerar cobranças PIX
 *
 * Documentação: https://pixgo.org/api/v1/docs
 */

/**
 * Criar uma cobrança PIX via PixGo
 *
 * @param float $amount Valor em reais
 * @param array $customer_data Dados do cliente (nome, email, telefone, cpf)
 * @param array $product_data Dados do produto (nome, codigo, quantidade)
 * @return array ['success' => bool, 'pix_code' => string, 'pix_qr' => string, 'payment_id' => string, ...]
 */
function createPixGoPayment($amount, $customer_data = [], $product_data = []) {
    global $conn;

    // 1. Recuperar configuração da PixGo do banco
    $sql = mysqli_query($conn, "SELECT * FROM pix WHERE id='1'");
    $config = $sql ? mysqli_fetch_assoc($sql) : null;

    if (!$config || empty($config['pixgo_api_key'])) {
        return ['success' => false, 'error' => 'Chave de API PixGo não configurada'];
    }

    $api_key = trim($config['pixgo_api_key']);

    // 2. Validar e formatar valores
    if (is_numeric($amount)) {
        $amount_float = (float)$amount;
    } else {
        // Se vier como "599,90" ou "1.599,90"
        $clean = str_replace('.', '', (string)$amount);
        $clean = str_replace(',', '.', $clean);
        $amount_float = (float)$clean;
    }

    if ($amount_float < 1.00) {
        return ['success' => false, 'error' => 'Valor mínimo de R$ 1,00 não atingido'];
    }

    if ($amount_float > 5000.00) {
        return ['success' => false, 'error' => 'O valor máximo permitido pela PixGo é de R$ 5.000,00. Por favor, reduza o valor ou entre em contato com o suporte.'];
    }

    // 3. Preparar dados do cliente
    $customer_name  = !empty($customer_data['nome'])     ? trim($customer_data['nome'])     : 'Cliente';
    $customer_email = !empty($customer_data['email'])    ? trim($customer_data['email'])    : 'cliente@email.com';
    $customer_phone = !empty($customer_data['telefone']) ? preg_replace('/\D/', '', $customer_data['telefone']) : '11999999999';
    $customer_cpf   = !empty($customer_data['cpf'])      ? preg_replace('/\D/', '', $customer_data['cpf'])      : '00000000000';

    // 4. Preparar dados do produto
    $product_name = !empty($product_data['nome']) ? $product_data['nome'] : 'Produto';
    $product_code = !empty($product_data['codigo']) ? $product_data['codigo'] : 'prod_' . time();

    // 5. Gerar external_id único para rastreamento
    $external_id = 'order_' . time() . '_' . rand(1000, 9999);

    // 6. Montar payload para PixGo
    $payload = [
        "amount"            => $amount_float,
        "description"       => $product_name,
        "customer_name"     => $customer_name,
        "customer_cpf"      => $customer_cpf,
        "customer_email"    => $customer_email,
        "customer_phone"    => $customer_phone,
        "customer_address"  => $customer_data['endereco'] ?? '',
        "external_id"       => $external_id,
        "webhook_url"       => 'https://' . $_SERVER['HTTP_HOST'] . '/api/webhook_pixgo.php'
    ];

    // 7. Fazer requisição POST para PixGo
    $ch = curl_init('https://pixgo.org/api/v1/payment/create');

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-API-Key: ' . $api_key
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // 8. Processar resposta
    if (!empty($curl_error)) {
        error_log("PixGo cURL Error: " . $curl_error);
        return ['success' => false, 'error' => 'Erro de conexão com PixGo: ' . $curl_error];
    }

    $data = json_decode($response, true);

    if (($http_code === 200 || $http_code === 201) && !empty($data) && $data['success'] === true) {
        $payment_data = $data['data'] ?? [];

        $payment_id = $payment_data['payment_id'] ?? '';
        $qr_code    = $payment_data['qr_code'] ?? '';
        $qr_image   = $payment_data['qr_image_url'] ?? '';

        if (!empty($payment_id) && !empty($qr_code)) {
            return [
                'success'        => true,
                'pix_code'       => $qr_code,
                'pix_qr'         => $qr_image,
                'payment_id'     => $payment_id,
                'external_id'    => $external_id,
                'status'         => $payment_data['status'] ?? 'pending',
                'expires_at'     => $payment_data['expires_at'] ?? ''
            ];
        }
    }

    // Erro na resposta
    $error_msg = "Falha ao criar cobrança PixGo. HTTP: $http_code. Resposta: " . substr($response, 0, 500);
    error_log("PixGo Error: " . $error_msg);

    return ['success' => false, 'error' => $error_msg];
}

/**
 * Consultar status de um pagamento PixGo
 *
 * @param string $payment_id ID do pagamento retornado pela PixGo
 * @return array ['success' => bool, 'status' => string, ...]
 */
function getPixGoPaymentStatus($payment_id) {
    global $conn;

    if (empty($payment_id)) {
        return ['success' => false, 'error' => 'ID do pagamento não informado'];
    }

    // Recuperar configuração da PixGo
    $sql = mysqli_query($conn, "SELECT * FROM pix WHERE id='1'");
    $config = $sql ? mysqli_fetch_assoc($sql) : null;

    if (!$config || empty($config['pixgo_api_key'])) {
        return ['success' => false, 'error' => 'Chave de API PixGo não configurada'];
    }

    $api_key = trim($config['pixgo_api_key']);

    // Fazer requisição GET para PixGo
    $ch = curl_init('https://pixgo.org/api/v1/payment/' . urlencode($payment_id) . '/status');

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'X-API-Key: ' . $api_key
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($http_code === 200 && !empty($data) && $data['success'] === true) {
        $payment_data = $data['data'] ?? [];
        return [
            'success' => true,
            'status'  => $payment_data['status'] ?? 'unknown',
            'amount'  => $payment_data['amount'] ?? 0,
            'created_at' => $payment_data['created_at'] ?? '',
            'updated_at' => $payment_data['updated_at'] ?? ''
        ];
    }

    return ['success' => false, 'status' => 'unknown', 'error' => 'Falha ao consultar status'];
}

/**
 * Consultar detalhes completos de um pagamento PixGo
 *
 * @param string $payment_id ID do pagamento
 * @return array Dados completos do pagamento
 */
function getPixGoPaymentDetails($payment_id) {
    global $conn;

    if (empty($payment_id)) {
        return ['success' => false, 'error' => 'ID do pagamento não informado'];
    }

    $sql = mysqli_query($conn, "SELECT * FROM pix WHERE id='1'");
    $config = $sql ? mysqli_fetch_assoc($sql) : null;

    if (!$config || empty($config['pixgo_api_key'])) {
        return ['success' => false, 'error' => 'Chave de API PixGo não configurada'];
    }

    $api_key = trim($config['pixgo_api_key']);

    $ch = curl_init('https://pixgo.org/api/v1/payment/' . urlencode($payment_id));

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'X-API-Key: ' . $api_key
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($http_code === 200 && !empty($data) && $data['success'] === true) {
        return ['success' => true, 'data' => $data['data'] ?? []];
    }

    return ['success' => false, 'error' => 'Falha ao obter detalhes do pagamento'];
}
?>
