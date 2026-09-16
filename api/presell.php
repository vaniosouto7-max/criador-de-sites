<?php
// Página de pouso para Anti-Google e Anti-Meta. O produto abre após um clique.
$id_produto = isset($_GET['produto']) ? trim((string)$_GET['produto']) : '';
$id_produto_sql = mysqli_real_escape_string($conn, $id_produto);
$sql_p = mysqli_query($conn, "SELECT * FROM produto WHERE codigo='$id_produto_sql' LIMIT 1");
$prod = $sql_p ? mysqli_fetch_assoc($sql_p) : null;

if (!$prod) {
    header('Location: ./index');
    exit();
}

$nome = (string)($prod['nome'] ?? 'Produto');
$valor = (string)($prod['valor'] ?? '0,00');
$img = (string)($prod['img'] ?? '');
$img_src = (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0)
    ? $img
    : 'arquivos/produtos/' . rawurlencode($id_produto) . '/' . rawurlencode(basename($img));
$url_produto = '?produto=' . rawurlencode($id_produto) . '&bypass=true';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title><?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        :root { --blue: #3483fa; --blue-dark: #2968c8; }
        *, *::before, *::after { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body { margin: 0; min-height: 100vh; min-height: 100svh; display: grid; place-items: center; padding: max(18px, env(safe-area-inset-top)) 16px max(18px, env(safe-area-inset-bottom)); overflow-x: hidden; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #202124; background: #f4f7fb; position: relative; isolation: isolate; }
        /* Fundo branco-gelo com a imagem enviada em camada independente, desfocada e a 75%. */
        .background-image { position: fixed; inset: -24px; z-index: 0; pointer-events: none; background-image: url('./arquivos/anti-landing-bg.png'); background-position: center; background-size: cover; background-repeat: no-repeat; filter: blur(10px); opacity: .75; transform: scale(1.04); }
        body::after { content: ""; position: fixed; inset: 0; z-index: 1; pointer-events: none; background: rgba(244, 247, 251, .60); }
        .container { position: relative; z-index: 2; width: min(100%, 440px); text-align: center; padding: clamp(22px, 6vw, 34px) clamp(18px, 6vw, 32px); border: 1px solid rgba(255,255,255,.72); border-radius: 22px; background: rgba(255,255,255,.94); box-shadow: 0 16px 50px rgba(0,0,0,.22); backdrop-filter: blur(5px); }
        .produto-img { display: block; width: min(100%, 260px); height: clamp(170px, 48vw, 250px); object-fit: contain; margin: 0 auto 18px; border-radius: 14px; background: #fff; }
        h1 { margin: 0 0 12px; font-size: clamp(21px, 6vw, 28px); line-height: 1.2; overflow-wrap: anywhere; }
        .price { margin-bottom: 22px; color: #168a45; font-size: clamp(23px, 7vw, 30px); font-weight: 800; }
        .btn { display: flex; align-items: center; justify-content: center; width: 100%; min-height: 54px; padding: 14px 18px; border: 0; border-radius: 10px; background: var(--blue); color: #fff; text-decoration: none; font-size: clamp(16px, 4.5vw, 19px); font-weight: 800; cursor: pointer; transition: transform .15s ease, background .15s ease, box-shadow .15s ease; -webkit-tap-highlight-color: transparent; }
        .btn:hover, .btn:focus-visible { background: var(--blue-dark); box-shadow: 0 6px 18px rgba(52,131,250,.35); }
        .btn:active { transform: scale(.98); }
        @media (max-width: 360px) { body { padding-left: 10px; padding-right: 10px; } .container { border-radius: 16px; padding: 20px 15px; } }
    </style>
</head>
<body>
    <div class="background-image" aria-hidden="true"></div>
    <main class="container" aria-labelledby="produto-titulo">
        <?php if ($img !== ''): ?>
            <img class="produto-img" src="<?php echo htmlspecialchars($img_src, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>">
        <?php endif; ?>
        <h1 id="produto-titulo"><?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?></h1>
        <div class="price">R$ <?php echo htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?></div>
        <a class="btn" href="<?php echo htmlspecialchars($url_produto, ENT_QUOTES, 'UTF-8'); ?>">CONTINUAR PARA O PRODUTO</a>
    </main>
</body>
</html>
