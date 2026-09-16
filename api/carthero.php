<?php
/**
 * CartHero API Integration - RECONSTRUÍDO DO ZERO
 * Versão: 2.0.0
 */

function createCartheroPayment($amount, $customer_data = [], $product_data = []) {
    global $conn;

    // 1. Configurações
    $sql = mysqli_query($conn, "SELECT * FROM pix WHERE id='1'");
    $config = $sql ? mysqli_fetch_assoc($sql) : null;
    if (!$config || empty($config['carthero_private_key'])) {
        return ['success' => false, 'error' => 'Chave Privada CartHero não configurada'];
    }
    $api_token = trim($config['carthero_private_key']);

    // 2. Tratamento do Valor (Centavos)
    $amount_float = is_numeric($amount) ? (float)$amount : (float)str_replace(',', '.', str_replace('.', '', $amount));
    $total_cents = (int)round($amount_float * 100);

    // 3. Preparação dos Dados do Pagador
    $payer = [
        "name"     => trim($customer_data['nome'] ?? 'Cliente'),
        "cpf_cnpj" => preg_replace('/\D/', '', $customer_data['cpf'] ?? '12345678909'),
        "email"    => trim($customer_data['email'] ?? 'cliente@email.com'),
        "phone"    => preg_replace('/\D/', '', $customer_data['telefone'] ?? ($customer_data['celular'] ?? '11999999999'))
    ];

    // Formatação de telefone para padrão internacional se necessário (11 dígitos -> 55 + 11 dígitos)
    if (strlen($payer['phone']) === 11) $payer['phone'] = '55' . $payer['phone'];

    // Campos de Endereço (Obrigatórios para evitar rejeição em algumas contas)
    $payer["address_zip"]          = preg_replace('/\D/', '', $customer_data['cep'] ?? '01001000');
    $payer["address_line"]         = $customer_data['endereco'] ?? 'Rua Nao Informada';
    $payer["address_number"]       = $customer_data['numero'] ?? 'SN';
    $payer["address_neighborhood"] = $customer_data['bairro'] ?? 'Centro';
    $payer["address_city"]         = $customer_data['cidade'] ?? 'Sao Paulo';
    $payer["address_state"]        = $customer_data['estado'] ?? 'SP';
    $payer["address_complement"]   = $customer_data['complemento'] ?? '';

    // 4. Preparação dos Itens
    $qty = (int)($product_data['quantidade'] ?? 1);
    if ($qty <= 0) $qty = 1;

    // Garantir que a soma dos itens bata exatamente com o total
    $unit_price = (int)floor($total_cents / $qty);
    $items = [
        [
            "name"        => mb_strimwidth($product_data['nome'] ?? 'Produto', 0, 50, "..."),
            "description" => mb_strimwidth($product_data['nome'] ?? 'Produto', 0, 100, "..."),
            "quantity"    => $qty,
            "unit_price"  => $unit_price
        ]
    ];

    // 5. Payload Final
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $postback = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/api/webhook_carthero.php';

    $payload = [
        "method"             => "pix",
        "product_fisical"    => "digital",
        "ip_payer"           => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        "total_price_cents"  => $total_cents,
        "postback_url"       => $postback,
        "payer"              => $payer,
        "items"              => $items
    ];

    // 6. Execução via cURL
    $ch = curl_init('https://api.carthero.com.br/api/v2/invoices');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_token
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false // Evita erros de certificado em servidores variados
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $res = json_decode($response, true);

    // 7. Processamento do Retorno
    if (($http_code === 200 || $http_code === 201) && isset($res['data'])) {
        return [
            'success'       => true,
            'payment_id'    => $res['data']['invoice_id'] ?? $res['data']['id'],
            'pix_code'      => $res['data']['qr_code_pix'] ?? $res['data']['pix_code'],
            'pix_qr_base64' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($res['data']['qr_code_pix'] ?? $res['data']['pix_code'])
        ];
    }

    // Caso falhe, logar erro e retornar mensagem
    $error_msg = $res['message'] ?? ($res['error'] ?? $response);
    error_log("CartHero Fail: " . $error_msg);
    return ['success' => false, 'error' => $error_msg];
}
?>
