<?php
/**
 * Inicialização Global e Captura de Erros
 */

// Habilitar exibição de erros para o usuário ver o que está acontecendo (pode desativar depois)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definir um arquivo de log local para o usuário baixar e ver os erros
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/../meus_erros.log');

// Função para capturar erros fatais antes do Erro 500
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "<div style='background:#fee; border:1px solid #f00; padding:20px; font-family:sans-serif;'>";
        echo "<h2>Ocorreu um erro no servidor (PHP)</h2>";
        echo "<p><b>Mensagem:</b> " . $error['message'] . "</p>";
        echo "<p><b>Arquivo:</b> " . $error['file'] . "</p>";
        echo "<p><b>Linha:</b> " . $error['line'] . "</p>";
        echo "<p>Verifique o arquivo <b>meus_erros.log</b> na raiz para mais detalhes.</p>";
        echo "</div>";
    }
});
?>
