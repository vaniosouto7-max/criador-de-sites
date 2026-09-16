<?php
session_start();
require_once("api/db.php");

// Pegar configurações da loja
$sql_config = mysqli_query($conn, "SELECT * from config");
$cor = "#ffe600"; // Default ML Yellow
$cor_botao = "#3483fa"; // Default ML Blue
$nome = "Minha Loja";
$numerozap = "";
$zap_cotacao = "";
$endereco = "";
$cnpj = "";

if ($sql_config && $row_config = mysqli_fetch_array($sql_config)) {
    $cor = $row_config["cor"];
    $cor_botao = isset($row_config["cor_botao"]) ? $row_config["cor_botao"] : "#3483fa";
    $cor_icones = isset($row_config["cor_icones"]) ? $row_config["cor_icones"] : "#ffffff";
    $nome = $row_config["nome"];
    $numerozap = $row_config["zap"];
    $zap_cotacao = (isset($row_config["zap_cotacao"]) && !empty($row_config["zap_cotacao"])) ? $row_config["zap_cotacao"] : $row_config["zap"];
    $endereco = isset($row_config["endereco"]) ? $row_config["endereco"] : "";
    $cnpj = isset($row_config["cnpj"]) ? $row_config["cnpj"] : "";
}

$logo_files = glob("arquivos/logo/*.png");
$logo_loja = !empty($logo_files) ? $logo_files[0] : "";

// Pegar banners do catálogo
$banners = [];
$sql_banners = @mysqli_query($conn, "SELECT * FROM catalogo_banners ORDER BY ordem ASC, id DESC");
if($sql_banners) {
    while($b = mysqli_fetch_array($sql_banners)){
        $banners[] = $b;
    }
}

// Pegar produtos em destaque
$destaques = [];
$sql_destaques = @mysqli_query($conn, "SELECT * FROM produto WHERE destaque_catalogo = 1 AND status = 'ativo' ORDER BY id DESC LIMIT 15");
if ($sql_destaques && mysqli_num_rows($sql_destaques) > 0) {
    while($row = mysqli_fetch_array($sql_destaques)){
        $destaques[] = $row;
    }
} else {
    // Se falhar a coluna ou não tiver nenhum produto marcado como destaque, puxa os últimos produtos normais
    $sql_destaques_fallback = @mysqli_query($conn, "SELECT * FROM produto WHERE status = 'ativo' ORDER BY id DESC LIMIT 15");
    if($sql_destaques_fallback) {
        while($row = mysqli_fetch_array($sql_destaques_fallback)){
            $destaques[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($nome); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <style>
        :root {
            --cor-primaria: <?php echo $cor; ?>;
            --cor-botao: <?php echo $cor_botao; ?>;
            --cor-icones: <?php echo $cor_icones; ?>;
            --cor-fundo: #ebebeb;
            --cor-texto: #333;
            --cor-verde: #00a650;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Roboto', sans-serif; background-color: var(--cor-fundo); color: var(--cor-texto); overflow-x: hidden; }

        /* Header */
        header { background-color: var(--cor-primaria); padding: 10px 0; }
        .header-container { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; padding: 0 15px; }
        .logo img { height: 40px; object-fit: contain; }
        .search-bar { flex: 1; max-width: 600px; margin: 0 20px; display: flex; align-items: center; background: #fff; border-radius: 2px; padding: 8px 15px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .search-bar input { border: none; outline: none; width: 100%; font-size: 15px; color: #333; }
        .search-bar i { color: #666; cursor: pointer; }
        .header-promo img { height: 40px; }

        .nav-bar { background-color: var(--cor-primaria); padding: 5px 0; border-top: 1px solid rgba(0,0,0,0.05); }
        .nav-container { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; padding: 0 15px; font-size: 13px; color: var(--cor-icones); }
        .nav-links { display: flex; gap: 15px; }
        .nav-links a { color: var(--cor-icones); text-decoration: none; transition: color 0.2s; }
        .nav-links a:hover { color: #666; }
        .location-info { display: flex; align-items: center; gap: 5px; opacity: 0.8; }

        /* Top Banner Slider */
        .swiper-banner { width: 100%; height: auto; max-height: 400px; }
        .swiper-banner .swiper-slide img { width: 100%; height: 100%; object-fit: cover; }
        .swiper-button-next, .swiper-button-prev { color: var(--cor-botao); background: #fff; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .swiper-button-next:after, .swiper-button-prev:after { font-size: 18px; font-weight: bold; }

        /* Container Principal */
        .main-container { max-width: 1200px; margin: 40px auto; padding: 0 15px; }

        /* Seção Ofertas */
        .section-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 20px; }
        .section-header h2 { font-size: 24px; font-weight: 300; color: #666; display: flex; align-items: center; gap: 10px; }
        .section-header a { font-size: 14px; color: var(--cor-botao); text-decoration: none; font-weight: 500; }

        .produto-card { background: #fff; border-radius: 4px; padding: 15px; text-decoration: none; color: inherit; display: flex; flex-direction: column; transition: box-shadow 0.2s; border-bottom: 1px solid #eee; }
        .produto-card:hover { box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .produto-img { width: 100%; height: 200px; object-fit: contain; margin-bottom: 15px; }

        .tag-vendido { background: #ff7733; color: white; font-size: 11px; font-weight: bold; padding: 2px 6px; border-radius: 3px; align-self: flex-start; margin-bottom: 8px; text-transform: uppercase; }

        .produto-preco-antigo { font-size: 14px; color: #999; text-decoration: line-through; margin-bottom: 2px; height: 16px; }
        .produto-preco-atual { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
        .preco-valor { font-size: 24px; font-weight: 400; color: #333; }
        .preco-desconto { font-size: 14px; color: var(--cor-verde); font-weight: 500; }

        .produto-parcela { font-size: 14px; color: var(--cor-verde); margin-bottom: 8px; }
        .produto-frete { font-size: 13px; color: var(--cor-verde); font-weight: 500; display: flex; align-items: center; gap: 4px; margin-top: auto; }
        .produto-frete i { font-style: italic; font-weight: 900; }

        /* Banners Promocionais Meio */
        .promo-banners { display: flex; gap: 20px; margin: 40px 0; }
        .promo-banner { flex: 1; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .promo-banner img { width: 100%; height: auto; display: block; }

        /* Categorias Grade */
        .categories-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 50px; }
        .category-card { position: relative; border-radius: 4px; overflow: hidden; aspect-ratio: 16/9; display: flex; align-items: flex-end; padding: 15px; text-decoration: none; transition: transform 0.2s; }
        .category-card:hover { transform: translateY(-3px); }
        .category-card img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; mix-blend-mode: multiply; }
        .category-title { position: relative; z-index: 2; width: 100%; text-align: center; font-size: 14px; font-weight: 500; color: #333; background: rgba(255,255,255,0.9); padding: 8px; border-radius: 2px; }

        .bg-cat-1 { background-color: #d8f3dc; }
        .bg-cat-2 { background-color: #fcefb4; }
        .bg-cat-3 { background-color: #e2e8f0; }
        .bg-cat-4 { background-color: #fef08a; }

        /* Footer */
        footer { background: #fff; padding: 40px 20px; text-align: center; border-top: 1px solid #ddd; }
        .footer-links { display: flex; justify-content: center; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
        .footer-links a { color: #666; text-decoration: none; font-size: 13px; }
        .footer-links a:hover { color: #333; }
        .footer-info { font-size: 12px; color: #999; line-height: 1.6; }

        @media (max-width: 768px) {
            .search-bar { margin: 0 10px; }
            .header-promo, .nav-links { display: none; }
            .categories-grid { grid-template-columns: repeat(2, 1fr); }
            .promo-banners { flex-direction: column; }
        }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <a href="catalogo.php" class="logo">
            <?php if(!empty($logo_loja)): ?>
                <img src="<?php echo $logo_loja; ?>" alt="Logo">
            <?php else: ?>
                <h1 style="font-size: 20px; font-weight:bold; color: #333;"><?php echo $nome; ?></h1>
            <?php endif; ?>
        </a>
        <div class="search-bar">
            <input type="text" placeholder="Buscar produtos, marcas e muito mais...">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <div class="header-promo">
            <!-- Promoção Estática Topo Direita (opcional) -->
            <div style="font-size:18px; font-weight:bold; color:#333;">
                <i class="fa-brands fa-discourse"></i> <?php echo $nome; ?>
            </div>
        </div>
    </div>
</header>
<div class="nav-bar">
    <div class="nav-container">
        <div class="location-info">
            <i class="fa-solid fa-location-dot"></i> Informar CEP
        </div>
        <div class="nav-links">
            <a href="#">Categorias</a>
            <a href="#">Ofertas</a>
            <a href="#">Cupons</a>
            <a href="#">Contato</a>
        </div>
        <div>
            <a href="#" style="color:#333; text-decoration:none; font-weight:500;">Crie sua conta / Entre</a>
            <i class="fa-solid fa-cart-shopping" style="margin-left: 15px; font-size:16px;"></i>
        </div>
    </div>
</div>

<!-- Banners Topo (Swiper) -->
<?php if(count($banners) > 0): ?>
<div class="swiper swiper-banner">
    <div class="swiper-wrapper">
        <?php foreach($banners as $b): ?>
        <div class="swiper-slide">
            <?php if(!empty($b['link'])): ?><a href="<?php echo $b['link']; ?>"><?php endif; ?>
                <img src="<?php echo $b['imagem']; ?>" alt="Banner">
            <?php if(!empty($b['link'])): ?></a><?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
</div>
<?php else: ?>
<div class="swiper swiper-banner">
    <div class="swiper-wrapper">
        <div class="swiper-slide">
            <div style="width:100%; height:300px; background:linear-gradient(90deg, #1d4ed8, #3b82f6); display:flex; align-items:center; justify-content:center; color:white;">
                <h1 style="font-size:3rem; font-weight:800; text-align:center;">
                    OFERTAS INCRÍVEIS<br>
                    <span style="color:#fef08a;">ATÉ 40% OFF</span>
                </h1>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="main-container">

    <!-- Carrossel de Ofertas Destaque -->
    <?php if(count($destaques) > 0): ?>
    <div class="section-header">
        <h2>MELHORES OFERTAS</h2>
        <a href="#todos">Ver mais</a>
    </div>

    <div class="swiper swiper-produtos">
        <div class="swiper-wrapper" style="padding-bottom:10px;">
            <?php foreach($destaques as $prod):
                $valor = (float)str_replace(',', '.', str_replace('.', '', $prod['valor']));
                $valor_original = (float)str_replace(',', '.', str_replace('.', '', $prod['valor_original'] ?? $prod['valor']));

                // Calcular desconto
                $desconto_pct = 0;
                if($valor_original > $valor && $valor_original > 0) {
                    $desconto_pct = round((($valor_original - $valor) / $valor_original) * 100);
                }

                $parcela = number_format($valor / 12, 2, ',', '.');
            ?>
            <div class="swiper-slide">
                <a href="produto.php?produto=<?php echo $prod['codigo']; ?>" class="produto-card">
                    <img src="<?php echo $prod['img']; ?>" class="produto-img" alt="<?php echo htmlspecialchars($prod['nome']); ?>">

                    <div class="tag-vendido">MAIS VENDIDO</div>

                    <?php if($desconto_pct > 0): ?>
                        <div class="produto-preco-antigo">R$ <?php echo number_format($valor_original, 2, ',', '.'); ?></div>
                    <?php else: ?>
                        <div class="produto-preco-antigo"></div>
                    <?php endif; ?>

                    <div class="produto-preco-atual">
                        <span class="preco-valor">R$ <?php echo number_format($valor, 2, ',', '.'); ?></span>
                        <?php if($desconto_pct > 0): ?>
                            <span class="preco-desconto"><?php echo $desconto_pct; ?>% OFF</span>
                        <?php endif; ?>
                    </div>

                    <div class="produto-parcela">em 12x R$ <?php echo $parcela; ?> sem juros</div>

                    <div class="produto-frete">
                        Chegará grátis amanhã<br>
                        <span style="font-weight:900; font-style:italic; font-size:14px; margin-top:4px; display:inline-block;">⚡ FULL</span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-button-next" style="right:0;"></div>
        <div class="swiper-button-prev" style="left:0;"></div>
    </div>
    <?php endif; ?>

    <!-- Banners Promocionais Fixos (Conforme pedido, estáticos por enquanto) -->
    <div class="promo-banners">
        <a href="#" class="promo-banner" style="background: linear-gradient(135deg, #a7f3d0, #34d399); padding:30px; display:flex; align-items:center; justify-content:space-between; text-decoration:none;">
            <div>
                <h3 style="color:#064e3b; font-size:24px; font-weight:800;">+ PAGUE COM <br><span style="font-size:32px;">PIX</span></h3>
                <p style="color:#047857; margin-top:10px; font-weight:600;">GANHE ATÉ 15% OFF</p>
            </div>
            <i class="fa-brands fa-pix" style="font-size:80px; color:rgba(0,0,0,0.1);"></i>
        </a>
        <a href="#" class="promo-banner" style="background: linear-gradient(135deg, #fef08a, #eab308); padding:30px; display:flex; align-items:center; justify-content:space-between; text-decoration:none;">
            <div>
                <h3 style="color:#713f12; font-size:24px; font-weight:800;">APROVEITE OS<br><span style="font-size:32px;">CUPONS EXCLUSIVOS</span></h3>
                <span style="display:inline-block; margin-top:15px; background:#1e3a8a; color:white; padding:8px 20px; border-radius:20px; font-weight:bold; font-size:14px;">ATIVE AGORA ></span>
            </div>
        </a>
    </div>

    <!-- Categorias Grade Fixa -->
    <div class="section-header" style="margin-top:60px;">
        <h2>MELHORES CATEGORIAS PARA A SUA COMPRA</h2>
    </div>
    <div class="categories-grid">
        <a href="#" class="category-card bg-cat-1">
            <!-- Imagem ilustrativa ou icone. Como não temos as imagens do cliente, deixamos fundo com cor -->
            <div style="position:absolute; top:20%; left:50%; transform:translateX(-50%); font-size:60px; color:rgba(0,0,0,0.1);"><i class="fa-solid fa-plug"></i></div>
            <div class="category-title">FERRAMENTAS ELÉTRICAS</div>
        </a>
        <a href="#" class="category-card bg-cat-2">
            <div style="position:absolute; top:20%; left:50%; transform:translateX(-50%); font-size:60px; color:rgba(0,0,0,0.1);"><i class="fa-solid fa-wrench"></i></div>
            <div class="category-title">FERRAMENTAS MANUAIS</div>
        </a>
        <a href="#" class="category-card bg-cat-3">
            <div style="position:absolute; top:20%; left:50%; transform:translateX(-50%); font-size:60px; color:rgba(0,0,0,0.1);"><i class="fa-solid fa-ruler-combined"></i></div>
            <div class="category-title">MEDIÇÃO E INSTRUMENTAÇÃO</div>
        </a>
        <a href="#" class="category-card bg-cat-4">
            <div style="position:absolute; top:20%; left:50%; transform:translateX(-50%); font-size:60px; color:rgba(0,0,0,0.1);"><i class="fa-solid fa-toolbox"></i></div>
            <div class="category-title">CAIXAS ORGANIZADORAS</div>
        </a>
    </div>

</div>

<footer>
    <div class="footer-links">
        <a href="politica-de-privacidade.php">Política de Privacidade</a>
        <a href="termos-de-uso.php">Termos de Uso</a>
        <a href="trocas-e-devolucoes.php">Trocas e Devoluções</a>
        <a href="#">Contato</a>
    </div>
    <div class="footer-info">
        Copyright © <?php echo date('Y'); ?> <?php echo $nome; ?>. Todos os direitos reservados.<br>
        CNPJ: <?php echo !empty($cnpj) ? htmlspecialchars($cnpj) : "00.000.000/0001-00"; ?> | Endereço: <?php echo !empty($endereco) ? htmlspecialchars($endereco) : "Av. Paulista, 1000 - São Paulo, SP"; ?>
    </div>
</footer>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
    // Iniciar Banner Topo
    var swiperBanner = new Swiper(".swiper-banner", {
        loop: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: ".swiper-banner .swiper-button-next",
            prevEl: ".swiper-banner .swiper-button-prev",
        },
    });

    // Iniciar Carrossel Produtos
    var swiperProdutos = new Swiper(".swiper-produtos", {
        slidesPerView: 2,
        spaceBetween: 10,
        navigation: {
            nextEl: ".swiper-produtos .swiper-button-next",
            prevEl: ".swiper-produtos .swiper-button-prev",
        },
        breakpoints: {
            640: { slidesPerView: 3, spaceBetween: 15 },
            768: { slidesPerView: 4, spaceBetween: 15 },
            1024: { slidesPerView: 5, spaceBetween: 15 },
        }
    });
</script>

</body>
</html>
