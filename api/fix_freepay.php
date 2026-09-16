<?php
/**
 * Script de Correção e Diagnóstico - FreePay Brasil
 *
 * Este script:
 * 1. Garante que as colunas necessárias existam no banco de dados.
 * 2. Verifica se as chaves de API estão configuradas.
 * 3. Testa a comunicação com a API.
 */

require_once("db.php");

echo "<pre style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>";
echo "=== CORREÇÃO E DIAGNÓSTICO FREEPAY ===\n\n";

// 1. Corrigir Estrutura do Banco
echo "1. Verificando/Corrigindo estrutura do banco...\n";

// Tabela pix
$cols_pix = [];
$res = mysqli_query($conn, "SHOW COLUMNS FROM pix");
while($res && $r = mysqli_fetch_assoc($res)) $cols_pix[] = $r['Field'];

if (!in_array('freepay_public_key', $cols_pix)) {
    mysqli_query($conn, "ALTER TABLE pix ADD COLUMN freepay_public_key TEXT NOT NULL DEFAULT ''");
    echo "   [+] Coluna freepay_public_key adicionada em 'pix'\n";
}
if (!in_array('freepay_secret_key', $cols_pix)) {
    mysqli_query($conn, "ALTER TABLE pix ADD COLUMN freepay_secret_key TEXT NOT NULL DEFAULT ''");
    echo "   [+] Coluna freepay_secret_key adicionada em 'pix'\n";
}
if (!in_array('use_freepay', $cols_pix)) {
    mysqli_query($conn, "ALTER TABLE pix ADD COLUMN use_freepay tinyint(1) NOT NULL DEFAULT 0");
    echo "   [+] Coluna use_freepay adicionada em 'pix'\n";
}

// Tabela pixgerado
$cols_pg = [];
$res = mysqli_query($conn, "SHOW COLUMNS FROM pixgerado");
while($res && $r = mysqli_fetch_assoc($res)) $cols_pg[] = $r['Field'];

if (!in_array('freepay_transaction_id', $cols_pg)) {
    mysqli_query($conn, "ALTER TABLE pixgerado ADD COLUMN freepay_transaction_id TEXT NOT NULL DEFAULT ''");
    echo "   [+] Coluna freepay_transaction_id adicionada em 'pixgerado'\n";
}
if (!in_array('freepay_status', $cols_pg)) {
    mysqli_query($conn, "ALTER TABLE pixgerado ADD COLUMN freepay_status TEXT NOT NULL DEFAULT 'PENDING'");
    echo "   [+] Coluna freepay_status adicionada em 'pixgerado'\n";
}

echo "   ✓ Estrutura verificada.\n\n";

// 2. Verificar Configurações
echo "2. Verificando configurações...\n";
$sql = mysqli_query($conn, "SELECT * FROM pix WHERE id='1'");
$cfg = ($sql) ? mysqli_fetch_assoc($sql) : null;

if (!$cfg) {
    echo "   [!] Registro ID 1 não encontrado na tabela 'pix'. Criando...\n";
    mysqli_query($conn, "INSERT INTO pix (id, chave, cidade, descricao, beneficiario) VALUES (1, '', '', '', '')");
    $sql = mysqli_query($conn, "SELECT * FROM pix WHERE id='1'");
    $cfg = ($sql) ? mysqli_fetch_assoc($sql) : null;
}

$use_fp = (int)($cfg['use_freepay'] ?? 0);
$pub = trim($cfg['freepay_public_key'] ?? '');
$sec = trim($cfg['freepay_secret_key'] ?? '');

echo "   Gateway Ativo: " . ($use_fp ? "SIM" : "NÃO (Ative no painel admin)") . "\n";
echo "   Chave Pública: " . ($pub ? "OK" : "VAZIA") . "\n";
echo "   Chave Secreta: " . ($sec ? "OK" : "VAZIA") . "\n";

if (!$use_fp || !$pub || !$sec) {
    echo "\n   [!] ATENÇÃO: O FreePay não funcionará sem as chaves e sem estar ativo.\n";
}

// 3. Teste de Conectividade
if ($pub && $sec) {
    echo "\n3. Testando conexão com API FreePay...\n";
    $auth = base64_encode($pub . ':' . $sec);
    $ch = curl_init('https://api.freepaybrasil.com/v1/payment-transaction/info/test');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $auth],
        CURLOPT_TIMEOUT        => 10
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "   Resposta HTTP: $code\n";
    if ($code == 401) {
        echo "   [X] ERRO: Chaves de API inválidas (401 Unauthorized).\n";
    } elseif ($code == 200 || $code == 404) {
        echo "   [✓] SUCESSO: Comunicação com a API está funcionando.\n";
    } else {
        echo "   [?] Resposta inesperada: $code. Verifique se o servidor tem acesso à internet.\n";
    }
}

echo "\n=== FIM DO PROCESSO ===\n";
echo "Se tudo estiver OK acima e o erro persistir, verifique se os dados do cliente (CPF/Email) estão sendo preenchidos corretamente no checkout.\n";
echo "</pre>";
?>
