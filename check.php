<?php
/**
 * Script de Diagnóstico do Sistema
 * Este script ajuda a identificar por que a página está dando Erro 500.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico do Sistema</h1>";

// 1. Versão do PHP
echo "<h2>1. Ambiente</h2>";
echo "Versão do PHP: " . phpversion() . "<br>";
echo "Sistema Operacional: " . PHP_OS . "<br>";

// 2. Extensões Necessárias
echo "<h2>2. Extensões</h2>";
$exts = ['mysqli', 'curl', 'mbstring', 'json', 'gd'];
foreach ($exts as $ext) {
    echo "Extensão '$ext': " . (extension_loaded($ext) ? "<span style='color:green'>INSTALADA</span>" : "<span style='color:red'>NÃO ENCONTRADA</span>") . "<br>";
}

// 3. Conexão com Banco de Dados
echo "<h2>3. Banco de Dados</h2>";
if (file_exists("api/db.php")) {
    include "api/db.php";
    if (isset($conn) && $conn) {
        echo "<span style='color:green'>Conexão com o banco de dados OK!</span><br>";

        // Verificar tabelas
        $tabelas = ['config', 'pix', 'produto', 'clientes', 'online', 'pixgerado'];
        foreach ($tabelas as $t) {
            $res = mysqli_query($conn, "SHOW TABLES LIKE '$t'");
            echo "Tabela '$t': " . (mysqli_num_rows($res) > 0 ? "<span style='color:green'>OK</span>" : "<span style='color:red'>FALTANDO</span>") . "<br>";
        }
    } else {
        echo "<span style='color:red'>Falha na conexão. Verifique api/db.php</span><br>";
    }
} else {
    echo "<span style='color:red'>Arquivo api/db.php não encontrado!</span><br>";
}

// 4. Teste de Sessão
echo "<h2>4. Sessão</h2>";
session_start();
$_SESSION['teste_diagnostico'] = true;
if (isset($_SESSION['teste_diagnostico'])) {
    echo "<span style='color:green'>Sessões funcionando corretamente.</span><br>";
} else {
    echo "<span style='color:red'>Erro nas sessões. Verifique as permissões da pasta de temporários.</span><br>";
}

echo "<br><hr><p>Se houver erros acima, corrija-os para que o site funcione.</p>";
?>
