<?php
/**
 * FreePay Brasil - Integração de Pagamento (Versão Ultra-Robusta)
 *
 * CORREÇÕES:
 * 1. Tratamento flexível da resposta da API (data[0] ou data direto)
 * 2. Busca profunda pelo campo qr_code
 * 3. Log completo da resposta em caso de falha na extração do código
 */

require_once("db.php");

function createFreePayPix($amount, $customer_data, $product_data = []) {
    global $conn;

    // 1. Buscar credenciais
    $sql = mysqli_query($conn, "SELECT * FROM pix WHERE id='1'");
    $config = $sql ? mysqli_fetch_assoc($sql) : null;

    if (!$config || (int)$config['use_freepay'] !== 1) {
        return ['success' => false, 'error' => 'Gateway FreePay desativado ou não configurado.'];
    }

    $public_key = trim($config['freepay_public_key']);
    $secret_key = trim($config['freepay_secret_key']);

    // 2. Converter valor para CENTAVOS (Integer)
    $clean = preg_replace('/[^\d,.]/', '', (string)$amount);
    if (strpos($clean, ',') !== false) {
        $clean = str_replace('.', '', $clean);
        $clean = str_replace(',', '.', $clean);
    }
    $amount_cents = (int)round((float)$clean * 100);

    // 3. Dados do cliente e item
    $customer_name  = !empty($customer_data['nome'])     ? trim($customer_data['nome'])     : 'Cliente';
    $customer_email = !empty($customer_data['email'])    ? trim($customer_data['email'])    : 'cliente@email.com';
    $customer_phone = !empty($customer_data['telefone']) ? preg_replace('/\D/', '', $customer_data['telefone']) : '11999999999';
    $customer_cpf   = !empty($customer_data['cpf'])      ? preg_replace('/\D/', '', $customer_data['cpf'])      : '00000000000';

    if (strlen($customer_phone) <= 11) $customer_phone = '+55' . $customer_phone;

    $item_title = !empty($product_data['nome']) ? $product_data['nome'] : 'Produto';
    $item_ref   = !empty($product_data['codigo']) ? $product_data['codigo'] : 'prod_' . time();
    $item_qty   = !empty($product_data['quantidade']) ? (int)$product_data['quantidade'] : 1;

    // 4. Montar Payload
    $payload = [
        "request"        => $item_ref,
        "payment_method" => "pix",
        "amount"         => $amount_cents,
        "postback_url"   => ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/api/webhook_freepay.php',
        "customer"       => [
            "name"     => $customer_name,
            "email"    => $customer_email,
            "phone"    => $customer_phone,
            "document" => [
                "type"   => "cpf",
                "number" => $customer_cpf
            ]
        ],
        "items" => [
            [
                "title"      => $item_title,
                "unit_price" => (int)round($amount_cents / $item_qty),
                "quantity"   => $item_qty,
                "tangible"   => true
            ]
        ],
        "metadata" => [
            "origin" => "Lojinha V8",
            "reference_id" => $item_ref
        ]
    ];

    $auth = base64_encode($public_key . ':' . $secret_key);
    $ch = curl_init('https://api.freepaybrasil.com/v1/payment-transaction/create');

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . $auth
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if (($http_code === 200 || $http_code === 201) && !empty($data)) {
        // A FreePay pode retornar os dados em 'data' ou 'data[0]'
        $transaction = null;
        if (isset($data['data'])) {
            $transaction = is_array($data['data']) && isset($data['data'][0]) ? $data['data'][0] : $data['data'];
        } else {
            $transaction = $data;
        }

        // Tentar encontrar o objeto PIX
        $pix_obj = null;
        if (isset($transaction['pix'])) {
            $pix_obj = is_array($transaction['pix']) && isset($transaction['pix'][0]) ? $transaction['pix'][0] : $transaction['pix'];
        }

        // Extrair campos finais
        $pix_code = $pix_obj['qr_code'] ?? $transaction['qr_code'] ?? '';
        $pix_qr   = $pix_obj['url'] ?? $transaction['url'] ?? '';
        $tid      = $transaction['id'] ?? '';

        if (!empty($pix_code)) {
            return [
                'success'        => true,
                'pix_code'       => $pix_code,
                'pix_qr'         => $pix_qr,
                'transaction_id' => $tid,
                'status'         => $transaction['status'] ?? 'PENDING'
            ];
        }
    }

    // Se chegou aqui, falhou em extrair o Pix
    $error_msg = "Falha ao extrair Pix da resposta. HTTP: $http_code. Resposta: " . $response;
    error_log("FreePay Error: " . $error_msg);

    return ['success' => false, 'error' => $error_msg];
}

function getFreePayTransaction($transaction_id) {
    global $conn;
    $sql = mysqli_query($conn, "SELECT * FROM pix WHERE id='1'");
    $config = $sql ? mysqli_fetch_assoc($sql) : null;
    $auth = base64_encode(trim($config['freepay_public_key']) . ':' . trim($config['freepay_secret_key']));

    $ch = curl_init('https://api.freepaybrasil.com/v1/payment-transaction/info/' . $transaction_id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Authorization: Basic ' . $auth
        ]
    ]);
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);

    if ($data && isset($data['data'])) {
        $t = is_array($data['data']) ? $data['data'][0] : $data['data'];
        return ['success' => true, 'status' => $t['status'] ?? 'UNKNOWN'];
    }
    return ['success' => false];
}
?>
