<?php
session_start();
require_once("api/db.php");
require_once("api/facebook_pixel.php");

if (!isset($_GET["produto"])) {
    header("Location: ./index");
    exit();
}
$id = addslashes($_GET["produto"]);
$sqlx = mysqli_query($conn, "SELECT * FROM produto WHERE codigo='$id'");
$row_prod = $sqlx ? mysqli_fetch_assoc($sqlx) : null;
if (!$row_prod) {
    header("Location: ./index");
    exit();
}
$codigo = $row_prod["codigo"];
$nome_prod = $row_prod["nome"];
$valor_unit = $row_prod["valor"];

$sql_conf = mysqli_query($conn, "SELECT * FROM config LIMIT 1");
$row_conf = $sql_conf ? mysqli_fetch_assoc($sql_conf) : null;
$nome_loja = $row_conf['nome'] ?? 'Minha Loja';
$cor = $row_conf['cor'] ?? '#ffe600';
$cor_botao = isset($row_conf['cor_botao']) ? $row_conf['cor_botao'] : '#3483fa';
$endereco = isset($row_conf['endereco']) ? $row_conf['endereco'] : '';
$cnpj = isset($row_conf['cnpj']) ? $row_conf['cnpj'] : '';
$logo_files = glob("arquivos/logo/*.png");
$logo_loja = !empty($logo_files) ? $logo_files[0] : "";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pedido Confirmado - <?php echo htmlspecialchars($nome_loja); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --ml-yellow: #ffe600;
            --ml-blue: #3483fa;
            --ml-blue-dark: #2968c8;
            --ink: #333333;
            --muted: #666666;
            --surface: #ffffff;
            --canvas: #f5f5f5;
            --success: #00a650;
        }
        *, *::before, *::after { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body { margin: 0; background: var(--canvas); color: var(--ink); font-family: Arial, Helvetica, sans-serif; }
        button { font: inherit; }
        .ml-header { background: var(--ml-yellow); border-bottom: 1px solid rgba(0,0,0,.08); }
        .ml-header-inner { max-width: 1180px; min-height: 64px; margin: 0 auto; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .brand-logo { max-width: 180px; max-height: 42px; object-fit: contain; display: block; }
        .brand-name { color: #222; font-size: 20px; font-weight: 600; }
        .secure-label { color: #555; font-size: 13px; display: flex; align-items: center; gap: 7px; white-space: nowrap; }
        .secure-label i { color: #555; }
        .success-main { min-height: calc(100vh - 190px); padding: 48px 16px 64px; }
        .success-card { width: min(100%, 640px); margin: 0 auto; background: var(--surface); border-radius: 8px; padding: 40px 48px 44px; box-shadow: 0 1px 3px rgba(0,0,0,.16); text-align: center; }
        .state-icon { width: 76px; height: 76px; margin: 0 auto 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #e8f5e9; color: var(--success); font-size: 40px; }
        .loading-icon { background: #eaf2ff; color: var(--ml-blue); }
        .state-title { margin: 0 0 12px; color: #333; font-size: 26px; line-height: 1.2; font-weight: 600; }
        .state-text { margin: 0 auto 24px; max-width: 480px; color: var(--muted); font-size: 16px; line-height: 1.5; }
        .pix-box { margin-top: 28px; padding: 24px; border: 1px solid #e5e5e5; border-radius: 8px; background: #fafafa; }
        .pix-label { margin-bottom: 15px; color: #555; font-size: 14px; font-weight: 600; }
        .qr-frame { width: 220px; height: 220px; margin: 0 auto 20px; padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 6px; display: flex; align-items: center; justify-content: center; }
        .qr-frame img { width: 100%; height: 100%; object-fit: contain; image-rendering: pixelated; }
        .pix-code { padding: 13px; min-height: 70px; background: #fff; border: 1px solid #ddd; border-radius: 5px; color: #555; font-size: 12px; line-height: 1.45; word-break: break-all; text-align: left; }
        .copy-button, .track-button { width: 100%; margin-top: 14px; padding: 14px 18px; border: 0; border-radius: 6px; background: var(--ml-blue); color: #fff; font-weight: 600; cursor: pointer; transition: background .2s, transform .1s; }
        .copy-button:hover, .track-button:hover { background: var(--ml-blue-dark); }
        .copy-button:active, .track-button:active { transform: scale(.99); }
        .payment-hint { margin: 18px 0 0; color: #777; font-size: 13px; line-height: 1.45; }
        .confirmed-state { padding-top: 8px; }
        .confirmed-state .state-icon { background: #e8f5e9; color: var(--success); }
        .confirmed-state .state-title { color: var(--success); }
        .footer { padding: 24px 16px; border-top: 1px solid #ddd; background: #fff; color: #777; text-align: center; font-size: 12px; line-height: 1.5; }
        .footer-links { display: flex; flex-wrap: wrap; justify-content: center; gap: 18px; margin-bottom: 12px; }
        .footer a { color: #666; text-decoration: none; }
        .footer a:hover { color: var(--ml-blue); }
        @media (max-width: 600px) {
            .ml-header-inner { min-height: 56px; padding: 10px 16px; }
            .brand-logo { max-width: 145px; max-height: 34px; }
            .brand-name { font-size: 17px; }
            .secure-label { font-size: 11px; }
            .success-main { padding: 24px 10px 40px; }
            .success-card { padding: 28px 16px 30px; }
            .state-icon { width: 66px; height: 66px; font-size: 34px; margin-bottom: 18px; }
            .state-title { font-size: 21px; }
            .state-text { font-size: 14px; }
            .pix-box { padding: 16px 12px; }
            .qr-frame { width: 190px; height: 190px; }
        }
    </style>
    <?php echo fb_pixel_base_code(); ?>

    <link rel="shortcut icon" href="arquivos/favicon.png?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="arquivos/favicon.png?v=<?php echo time(); ?>">
</head>
<body>
    <header class="ml-header">
        <div class="ml-header-inner">
            <div>
                <?php if (!empty($logo_loja)): ?>
                    <img src="<?php echo htmlspecialchars($logo_loja); ?>" alt="<?php echo htmlspecialchars($nome_loja); ?>" class="brand-logo">
                <?php else: ?>
                    <span class="brand-name"><?php echo htmlspecialchars($nome_loja); ?></span>
                <?php endif; ?>
            </div>
            <div class="secure-label"><i class="fa-solid fa-shield-halved"></i> Compra segura</div>
        </div>
    </header>

    <main class="success-main">
        <section class="success-card" aria-live="polite">
            <div id="loadingBox">
                <div class="state-icon loading-icon"><i class="fa-solid fa-spinner fa-spin"></i></div>
                <h1 class="state-title">Processando seu pagamento...</h1>
                <p class="state-text">Aguarde um instante, estamos gerando as instruções para finalizar sua compra.</p>
            </div>

            <div id="pixContent" style="display:none;">
                <div class="state-icon"><i class="fa-brands fa-pix"></i></div>
                <h1 class="state-title">Quase lá!</h1>
                <p class="state-text">Para finalizar sua compra, realize o pagamento via Pix abaixo.</p>
                <div class="pix-box" id="pix-container">
                    <div class="pix-label">Escaneie o QR Code com o app do seu banco</div>
                    <div class="qr-frame" id="qrImage"><i class="fa-solid fa-spinner fa-spin" style="font-size:36px;color:#3483fa"></i></div>
                    <div class="pix-code" id="pixCode">...</div>
                    <button class="copy-button" id="btnCopy" type="button" onclick="copyPix()">Copiar código Pix</button>
                    <p class="payment-hint">Após o pagamento, a confirmação será feita automaticamente. Não feche esta página.</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <nav class="footer-links" aria-label="Links institucionais">
            <a href="politica-de-privacidade">Política de Privacidade</a>
            <a href="termos-de-uso">Termos de Uso</a>
            <a href="trocas-e-devolucoes">Trocas e Devoluções</a>
            <a href="https://api.whatsapp.com/send?phone=<?php echo isset($numerozap) ? $numerozap : ''; ?>&text=<?php echo isset($textozap) ? urlencode($textozap) : ''; ?>">Contato</a>
        </nav>
        <div>Copyright © <?php echo date('Y'); ?> <?php echo htmlspecialchars($nome_loja); ?>. Todos os direitos reservados.</div>
        <?php if (!empty($cnpj) || !empty($endereco)): ?><div><?php if (!empty($cnpj)): ?>CNPJ: <?php echo htmlspecialchars($cnpj); ?><?php endif; ?><?php if (!empty($cnpj) && !empty($endereco)): ?> | <?php endif; ?><?php if (!empty($endereco)): ?>Endereço: <?php echo htmlspecialchars($endereco); ?><?php endif; ?></div><?php endif; ?>
    </footer>

    <script>
        let currentGateway = '';

        $(document).ready(function(){
            const data = JSON.parse(localStorage.getItem('lojavirtual') || '{}');
            const pFinal = data.precoFinal || '<?php echo $valor_unit; ?>';

            const payload = btoa(unescape(encodeURIComponent(JSON.stringify({
                api: "gerarpix",
                pFinal: pFinal,
                ptotal: data.quantos || '1',
                codigo: "<?php echo $codigo; ?>",
                nome_produto: "<?php echo addslashes($nome_prod); ?>",
                variacoes: localStorage.getItem('variacoes_selecionadas') || '{}'
            }))));

            // Feedback visual dinâmico caso a API (ex: FreePay) demore um pouco
            let loadingTexts = [
                "Conectando com o banco...",
                "Registrando a transação Pix...",
                "Gerando QR Code seguro...",
                "Só mais um momento, não feche a página..."
            ];
            let textIndex = 0;
            let loadingInterval = setInterval(() => {
                if ($('#loadingBox').is(':visible')) {
                    $('#loadingBox .success-text').fadeOut(300, function() {
                        $(this).text(loadingTexts[textIndex]).fadeIn(300);
                        textIndex = (textIndex + 1) % loadingTexts.length;
                    });
                } else {
                    clearInterval(loadingInterval);
                }
            }, 3000);

		            $.post("api/index.php", { p: payload }, function(res){
                        clearInterval(loadingInterval);
		                const parts = res.split('|');

		                // Verificar se o retorno é um erro do gateway
		                if(parts[0].includes("ERRO:") || (parts.length >= 4 && parts[3] == "error")) {
		                    const msgErro = parts[0].replace("ERRO:", "").trim();
		                    $('#loadingBox').html('<div style="color:#e00; padding:20px;"><h4>Erro no Gateway</h4><p>'+msgErro+'</p><p>Por favor, tente novamente ou escolha outro método.</p></div>');
		                    return;
		                }

		                if(parts.length >= 2) {
		                    const pixCode = parts[0];
		                    $('#pixCode').text(pixCode);

		                    const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(pixCode);
		                    const qrHtml = '<img src="' + qrUrl + '" style="width:100%; height:100%; object-fit:contain; image-rendering: pixelated;">';

		                    $('#qrImage').html(qrHtml);
		                    $('#loadingBox').hide();
		                    $('#pixContent').show();

                    // Verificação automática para Mercado Pago, FreePay, PixGo e CartHero.
                    // O Purchase só é disparado depois que o webhook/API confirmar o pagamento.
                    const gateway = parts.length >= 3 ? parts[2] : '';
                    const tid = parts.length >= 4 ? parts[3] : '';
                    currentGateway = gateway;
                    if (gateway && tid && gateway !== 'estatico') {
                        let purchaseConfirmed = false;
                        let checkInterval = setInterval(function() {
                            $.post('api/check_payment_status.php', { gateway: gateway, transaction_id: tid }, function(resp) {
                                if (resp && resp.success && resp.paid && !purchaseConfirmed) {
                                    purchaseConfirmed = true;
                                    clearInterval(checkInterval);
                                    $('#pixContent').html(`
                                        <div class="confirmed-state">
                                            <div class="state-icon"><i class="fa-solid fa-circle-check"></i></div>
                                            <h1 class="state-title">Pagamento confirmado!</h1>
                                            <p class="state-text">Obrigado pela sua compra. Seu pedido já está sendo preparado para o envio.</p>
                                            <p class="payment-hint">Você receberá as próximas atualizações pelos canais informados no checkout.</p>
                                        </div>
                                    `);
                                    dispararPurchase();
                                    setTimeout(goToTracking, 2500);
                                }
                            }, 'json');
                        }, 4000);
                    }
		                } else {
		                    $('#loadingBox').html('<div style="color:#e00; padding:20px;"><h4>Erro Inesperado</h4><p>Não foi possível gerar o código PIX. Tente novamente.</p></div>');
		                }
		            });

            sendOnline('success');
            setInterval(function(){ sendOnline('success'); }, 15000);
        });

	        function copyPix() {
	            const code = $('#pixCode').text();
	            navigator.clipboard.writeText(code).then(() => {
	                $('#btnCopy').text('Copiado!').css('background', '#00a650');

                // Avisar o servidor que o Pix foi copiado
                sendOnline('pix_copiado');

                // No copia e cola, o painel precisa marcar o código como PAGO.
                // O polling acima detectará essa alteração e abrirá o rastreio.
                if (currentGateway === 'copia_cola') {
                    $('.state-title').text('Aguardando confirmação do pagamento');
                    $('.payment-hint').text('Código copiado. Assim que o pagamento for confirmado no painel, o rastreio será liberado automaticamente.');
                }
                if (currentGateway === 'estatico') {
                    dispararPurchase();
                    setTimeout(goToTracking, 1200);
                }

                setTimeout(() => { $('#btnCopy').text('Copiar código Pix').css('background', '#3483fa'); }, 2000);
            });
        }

        function goToTracking() {
            window.location.href = 'rastreio.php?produto=<?php echo rawurlencode($codigo); ?>&confirmado=1';
        }

        // Dispara uma única conversão, somente após confirmação do pagamento.
        function dispararPurchase() {
            if (localStorage.getItem('fb_purchase_disparado_<?php echo addslashes($codigo); ?>')) return;
            const data = JSON.parse(localStorage.getItem('lojavirtual') || '{}');
            const bruto = data.precoFinal ? String(data.precoFinal).replace(/\./g, '').replace(',', '.') : '<?php echo number_format((float)str_replace(',', '.', str_replace('.', '', $valor_unit)), 2, '.', ''); ?>';
            const pFinal = parseFloat(bruto) || 0;
            const qty = parseInt(data.quantos || 1, 10);
            if (typeof fbq === 'function') {
                fbq('track', 'Purchase', {
                    content_ids: ['<?php echo addslashes($codigo); ?>'],
                    contents: [{id: '<?php echo addslashes($codigo); ?>', quantity: qty, item_price: pFinal}],
                    content_name: '<?php echo addslashes($nome_prod); ?>',
                    content_type: 'product',
                    value: pFinal,
                    currency: 'BRL'
                });
                localStorage.setItem('fb_purchase_disparado_<?php echo addslashes($codigo); ?>', '1');
            }
        }

        function sendOnline(etapa) {
            const payload = btoa(unescape(encodeURIComponent(JSON.stringify({
                api: 'online',
                etapa: etapa,
                dispositivo: /Android|iPhone/i.test(navigator.userAgent) ? 'mobile' : 'desktop'
            }))));
            $.post('api/', { p: payload });
        }
    </script>
</body>
</html>
