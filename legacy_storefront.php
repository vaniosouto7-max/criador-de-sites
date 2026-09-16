<?php
/**
 * =================================================================
 * PÁGINA INICIAL - REDIRECIONAMENTO PARA PRODUTO
 * =================================================================
 */

session_start();
require_once("api/db.php");

function get_device_consolidated() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $mobile_patterns = '/iPhone|iPad|Android|webOS|BlackBerry|iPod|Symbian|Windows Phone/i';
    return preg_match($mobile_patterns, $ua) ? "mobile" : "desktop";
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// Registrar visita (apenas uma vez por sessão)
if (!isset($_SESSION['visit_registered'])) {
    $dispositivo = get_device_consolidated();
    if (isset($conn)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO $dispositivo (ip, useragent) VALUES (?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $ip, $ua);
            mysqli_stmt_execute($stmt);
        }
    }
    $_SESSION['visit_registered'] = true;
}

$id = isset($_GET["id"]) ? addslashes($_GET["id"]) : null;

if ($id) {
    $index = time() + 1000;
    $_SESSION['session_index'] = $index;

    // Se o usuário está vindo pelo link principal, podemos resetar o captcha
    // para garantir que a proteção seja reativada se o admin mudou o status.
    // Isso resolve o problema de "funcionar apenas uma vez".
    unset($_SESSION['captcha_solved_' . $id]);

    header("Location: ./produto.php?produto=$id");
    exit();
} else {
    // Sem ID: exibir página inicial da loja
    $sql = isset($conn) ? mysqli_query($conn, "SELECT * from config") : false;
    $nome_loja = 'Nossa Loja';
    $logo_loja = '';
    while ($sql && $row = mysqli_fetch_array($sql)) {
        $nome_loja = $row["nome"] ?? $nome_loja;
    }
    $logo_files = glob("arquivos/logo/*.png");
    $logo_loja = !empty($logo_files) ? $logo_files[0] : "";
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($nome_loja); ?></title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 900px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
            header { text-align: center; padding: 30px 0 20px; border-bottom: 2px solid #eee; margin-bottom: 30px; }
            header img { max-height: 60px; }
            h1 { color: #333; font-size: 24px; }
            .mensagem { text-align: center; padding: 40px 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); }
            footer { text-align: center; font-size: 0.8em; color: #aaa; margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; }
        </style>

    <link rel="shortcut icon" href="arquivos/favicon.png?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="arquivos/favicon.png?v=<?php echo time(); ?>">
</head>
    <body>
        <header>
            <?php if ($logo_loja): ?>
                <img src="<?php echo htmlspecialchars($logo_loja); ?>" alt="<?php echo htmlspecialchars($nome_loja); ?>">
            <?php else: ?>
                <h1><?php echo htmlspecialchars($nome_loja); ?></h1>
            <?php endif; ?>
        </header>
        <div class="mensagem">
            <p>Bem-vindo à nossa loja! Acesse o link do produto para ver nossas ofertas.</p>
        </div>
        <footer>
            <div style="margin-bottom: 10px;">
                <a href="politica-de-privacidade" style="margin: 0 10px; color: #666; text-decoration: none;">Privacidade</a>
                <a href="termos-de-uso" style="margin: 0 10px; color: #666; text-decoration: none;">Termos</a>
                <a href="trocas-e-devolucoes" style="margin: 0 10px; color: #666; text-decoration: none;">Devoluções</a>
            </div>
            &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($nome_loja); ?>. Todos os direitos reservados.
            <br><small><?php echo htmlspecialchars($nome_loja); ?> - CNPJ 31.141.746/0001-49</small>
        </footer>
    <script>
    function sendOnline(etapa) {
        const ua = navigator.userAgent;
        const isMobile = /iPhone|iPad|Android|webOS|BlackBerry|iPod|Symbian|Windows Phone/i.test(ua);
        const dispositivo = isMobile ? "mobile" : "desktop";

        const payload = btoa(unescape(encodeURIComponent(JSON.stringify({
            api: "online",
            etapa: etapa,
            dispositivo: dispositivo
        }))));

        $.post("api/index.php", { p: payload });
    }

    $(document).ready(function() {
        sendOnline("produto");
        setInterval(() => sendOnline("produto"), 15000);
    });
    </script>
    </body>
    </html>
    <?php
    exit();
}
?>


