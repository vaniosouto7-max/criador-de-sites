<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once("../api/db.php");

// Autenticação básica
$sql_acesso = mysqli_query($conn, "SELECT * FROM acesso");
while($sql_acesso && $rowx = mysqli_fetch_array($sql_acesso)){
    $login = $rowx["login"];
    $senha = $rowx["senha"];
    if($_SESSION['login'] != $login || $_SESSION['senha'] != $senha){
        header("Location: ./"); exit;
    }
    if($_SESSION['tempo'] < time()){
        header("Location: ./?temp=expired"); exit;
    }
}

// Buscar dados do produto
if(!isset($_GET['id'])) { header("Location: produtos.php"); exit; }
$id_prod = addslashes($_GET['id']);
$sql_prod = mysqli_query($conn, "SELECT * FROM produto WHERE id='$id_prod'");
if(($sql_prod ? mysqli_num_rows($sql_prod) : 0) == 0) { header("Location: produtos.php"); exit; }
$prod = ($sql_prod) ? mysqli_fetch_assoc($sql_prod) : null;

// Decodificar reviews e variações
$reviews_data = json_decode($prod['reviews'] ?? '[]', true) ?: [];
$v_data = json_decode($prod['variacoes'] ?? '{}', true) ?: [];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Editar Produto - <?php echo htmlspecialchars($prod['nome']); ?></title>
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="./assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
    <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link id="pagestyle" href="./assets/css/black-theme.css?v=<?php echo time(); ?>" rel="stylesheet" />
  <link href="./assets/css/fix-labels.css" rel="stylesheet" />
  <style>
    .img-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-top: 5px; border: 1px solid var(--border-color, rgba(255,255,255,0.1)); display: block; }
    .url-input-group { border: 1px solid var(--border-color, rgba(255,255,255,0.1)); padding: 15px; border-radius: 8px; margin-bottom: 20px; background: rgba(0,0,0,0.1); }
    .url-input-group h6 { margin-bottom: 15px; color: var(--text-primary, #fff); font-weight: 600; }
    /* Corrige label sobreposto: sempre acima do campo */
    .input-group.input-group-outline .form-label,
    .input-group.input-group-outline label {
      position: static !important;
      display: block !important;
      font-size: 0.75rem !important;
      font-weight: 600 !important;
      color: var(--text-secondary, #ccc) !important;
      margin-bottom: 4px !important;
      transform: none !important;
      top: auto !important;
      left: auto !important;
      pointer-events: auto !important;
    }
    .input-group.input-group-outline {
      flex-direction: column !important;
    }
    .input-group.input-group-outline .form-control,
    .input-group.input-group-outline select.form-control {
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      border-radius: 0.375rem !important;
      padding: 0.5rem 0.75rem !important;
      font-size: 0.875rem !important;
      background: rgba(255, 255, 255, 0.05) !important;
      color: var(--text-primary, #fff) !important;
      width: 100% !important;
    }
    .input-group.input-group-outline .form-control:focus {
      border-color: #8c98ff !important;
      box-shadow: 0 0 0 2px rgba(140, 152, 255, 0.15) !important;
    }
    textarea.form-control {
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      border-radius: 0.375rem !important;
      padding: 0.5rem 0.75rem !important;
      background-color: rgba(255, 255, 255, 0.05) !important;
      color: var(--text-primary, #fff) !important;
    }
    .form-check-input:checked {
        background-color: #e91e63 !important;
        border-color: #e91e63 !important;
    }
    .form-switch .form-check-input {
        background-color: #adb5bd;
        border: 1px solid #ced4da;
    }
    .form-check-label {
        color: #344767 !important;
        font-weight: bold !important;
    }
  </style>
  <style>
    .welcome-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      animation: fadeIn 0.5s ease;
    }
    .welcome-bar h2 {
      font-size: 1.5rem;
      font-weight: 800;
      color: var(--text-primary);
      letter-spacing: -0.02em;
    }
    .welcome-bar p {
      font-size: 0.8125rem;
      color: var(--text-muted);
      margin-top: 4px;
    }
  </style>

    <link rel="shortcut icon" href="../arquivos/favicon.png?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="../arquivos/favicon.png?v=<?php echo time(); ?>">
</head>

<body class="g-sidenav-show bg-gray-200" onload="atualizarVariacoes()">
  <?php include 'sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <div class="container-fluid py-4">
      <div class="card card-body mx-3 mx-md-4 mt-2">
        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
          <h6 class="text-white text-capitalize ps-3">Editar Produto: <?php echo htmlspecialchars($prod['nome']); ?></h6>
        </div>
        <div class="row mt-4">
          <form id="formulario" onsubmit="return false">
	            <input type="hidden" id="id_interno" value="<?php echo $prod['id']; ?>">

	            <div class="url-input-group">
	              <h6><i class="material-icons" style="vertical-align: middle;">lock</i> Status do Produto (Cadeado)</h6>
	              <div class="input-group input-group-outline my-3 is-filled">
	                <label class="form-label">Selecione o Status</label>
	                <select id="status_produto" name="status" class="form-control">
	                  <option value="ativo" <?php echo (($prod['status'] ?? 'ativo') == 'ativo' ? 'selected' : ''); ?>>Ativo</option>
	                  <option value="inativo" <?php echo (($prod['status'] ?? '') == 'inativo' ? 'selected' : ''); ?>>Inativo</option>
	                  <option value="anti-google-v1" <?php echo (($prod['status'] ?? '') == 'anti-google-v1' ? 'selected' : ''); ?>>Anti-Google v1</option>
	                  <option value="anti-meta-ads-v1" <?php echo (($prod['status'] ?? '') == 'anti-meta-ads-v1' ? 'selected' : ''); ?>>Anti-Meta v1</option>
	                  <option value="anti-crawler-v1" <?php echo (($prod['status'] ?? '') == 'anti-crawler-v1' ? 'selected' : ''); ?>>Anti-Crawler v1 (reCAPTCHA)</option>
	                </select>
	              </div>
	            </div>

	            <!-- SEÇÃO DE VARIAÇÕES DO PRODUTO -->
            <div class="url-input-group">
              <h6><i class="material-icons" style="vertical-align: middle;">tune</i> Variações do Produto</h6>
              <p class="text-xs text-secondary">Selecione o tipo de produto para exibir as opções de variação correspondentes.</p>

              <div class="row">
                <div class="col-md-6">
                  <div class="input-group input-group-outline my-3 is-filled">
                    <label class="form-label">Tipo de Produto</label>
                    <select id="tipo_produto" name="tipo_produto" class="form-control" onchange="atualizarVariacoes();">
                      <option value="generico" <?php echo (($prod['tipo_produto'] ?? 'generico') == 'generico' ? 'selected' : ''); ?>>Genérico (sem variações)</option>
                      <option value="eletronico" <?php echo (($prod['tipo_produto'] ?? '') == 'eletronico' ? 'selected' : ''); ?>>Eletrônico (voltagem)</option>
                      <option value="celular" <?php echo (($prod['tipo_produto'] ?? '') == 'celular' ? 'selected' : ''); ?>>Celular (RAM e armazenamento)</option>
                      <option value="roupa" <?php echo (($prod['tipo_produto'] ?? '') == 'roupa' ? 'selected' : ''); ?>>Roupa (cores e tamanhos)</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Variações de Voltagem, RAM e Armazenamento -->
              <div id="var_controles" style="display:none;">
                <div class="row mb-3">
                  <div class="col-md-4" id="grp_voltagem" style="display:none;">
                    <label class="form-label">Voltagens</label>
                    <div class="form-check">
                      <input class="form-check-input var-voltagem" type="checkbox" value="127v" id="volt_127" <?php echo (in_array('127v', $v_data['voltagens'] ?? []) ? 'checked' : ''); ?>>
                      <label class="form-check-label" for="volt_127">127v</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input var-voltagem" type="checkbox" value="220v" id="volt_220" <?php echo (in_array('220v', $v_data['voltagens'] ?? []) ? 'checked' : ''); ?>>
                      <label class="form-check-label" for="volt_220">220v</label>
                    </div>
                  </div>
                  <div class="col-md-4" id="grp_ram" style="display:none;">
                    <div class="input-group input-group-outline mb-3 is-filled">
                      <label class="form-label">Memória RAM (Ex: 4GB, 8GB)</label>
                      <input type="text" id="var_ram" class="form-control" value="<?php echo implode(', ', $v_data['ram'] ?? []); ?>">
                    </div>
                  </div>
                  <div class="col-md-4" id="grp_armazenamento" style="display:none;">
                    <div class="input-group input-group-outline mb-3 is-filled">
                      <label class="form-label">Armazenamento (Ex: 128GB, 256GB)</label>
                      <input type="text" id="var_armazenamento" class="form-control" value="<?php echo implode(', ', $v_data['armazenamento'] ?? []); ?>">
                    </div>
                  </div>
                  <div class="col-md-4" id="grp_tamanhos" style="display:none;">
                    <div class="input-group input-group-outline mb-3 is-filled">
                      <label class="form-label">Tamanhos (Ex: PP, P, M, G, GG)</label>
                      <input type="text" id="var_tamanhos" class="form-control" value="<?php echo htmlspecialchars(implode(', ', $v_data['tamanhos'] ?? [])); ?>">
                    </div>
                    <small class="text-muted">Separe os tamanhos por vírgula.</small>
                  </div>
                </div>
              </div>

              <!-- CORES E IMAGENS ESPECÁƒÂFICAS -->
              <div id="var_cores_master" style="display:none;">
                <h6 class="mt-4 mb-3" style="font-size: 14px; color: #344767;">Cores e Imagens Específicas</h6>
                <div class="row">
                  <?php for($c=1; $c<=4; $c++):
                    $cor_det = $v_data['cores_detalhes'][$c-1] ?? null;
                  ?>
                  <div class="col-md-6 mb-4">
                    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; background: #fff;">
                      <div class="form-check mb-2">
                        <input class="form-check-input chk-cor-ativa" type="checkbox" id="cor_ativa_<?php echo $c; ?>" <?php echo ($cor_det ? 'checked' : ''); ?>>
                        <label class="form-check-label font-weight-bold" for="cor_ativa_<?php echo $c; ?>">Ativar Cor <?php echo $c; ?></label>
                      </div>
                      <div class="input-group input-group-outline my-2 is-filled">
                        <label class="form-label">Nome da Cor (Ex: Preto)</label>
                        <input type="text" id="cor_nome_<?php echo $c; ?>" class="form-control" value="<?php echo htmlspecialchars($cor_det['nome'] ?? ''); ?>">
                      </div>
                      <div class="input-group input-group-outline my-2 is-filled">
                        <label class="form-label">Título Específico (Ex: iPhone 15 - Preto)</label>
                        <input type="text" id="cor_titulo_<?php echo $c; ?>" class="form-control" value="<?php echo htmlspecialchars($cor_det['titulo'] ?? ''); ?>">
                      </div>
                      <div class="input-group input-group-outline my-2 is-filled">
                        <label class="form-label">URL da Imagem desta Cor</label>
                        <input type="text" id="cor_img_<?php echo $c; ?>" class="form-control" value="<?php echo htmlspecialchars($cor_det['img'] ?? ''); ?>" onchange="previewImg(this, 'prev_cor_<?php echo $c; ?>')">
                      </div>
                      <img id="prev_cor_<?php echo $c; ?>" src="<?php echo $cor_det['img'] ?? ''; ?>" class="img-preview" style="width: 60px; height: 60px; <?php echo ($cor_det['img'] ?? '') ? 'display:block' : 'display:none'; ?>">
                      <?php if($c == 1): ?>
                      <small class="text-info d-block mt-1">Dica: A Cor 1 usa a imagem principal por padrão se deixar a URL vazia.</small>
                      <?php endif; ?>
                    </div>
                  </div>
                  <?php endfor; ?>
                </div>
              </div>

              <!-- Campo oculto para armazenar JSON de variações -->
              <textarea id="variacoes" name="variacoes" class="form-control" rows="2" style="border:1px solid #d2d6da;padding:10px;font-size:11px;color:#888;margin-top:15px;" placeholder="JSON de variações (gerado automaticamente)..."><?php echo htmlspecialchars($prod['variacoes'] ?? '{}'); ?></textarea>
            </div>

            <div class="row">
              <div class="col-md-6">
<div class="input-group input-group-outline my-3 is-filled">
	                  <label class="form-label">Status do Produto (Cadeado)</label>
	                  <select id="status_produto" name="status_produto" class="form-control">
	                    <option value="ativo" <?php echo (($prod['status'] ?? 'ativo') == 'ativo' ? 'selected' : ''); ?>>Ativo</option>
	                    <option value="inativo" <?php echo (($prod['status'] ?? '') == 'inativo' ? 'selected' : ''); ?>>Inativo</option>
	                    <option value="anti-google-v1" <?php echo (($prod['status'] ?? '') == 'anti-google-v1' ? 'selected' : ''); ?>>Anti-Google v1</option>
	                    <option value="anti-meta-ads-v1" <?php echo (($prod['status'] ?? '') == 'anti-meta-ads-v1' ? 'selected' : ''); ?>>Anti-Meta v1</option>
	                    <option value="anti-crawler-v1" <?php echo (($prod['status'] ?? '') == 'anti-crawler-v1' ? 'selected' : ''); ?>>Anti-Crawler v1</option>
	                  </select>
	                </div>
<div class="input-group input-group-outline my-3 is-filled">
		                  <label class="form-label">Nome do produto</label>
		                  <input id="nomeproduto" name="nomeproduto" type="text" class="form-control" value="<?php echo htmlspecialchars($prod['nome']); ?>">
		                </div>


              </div>
              <div class="col-md-3">
                <div class="input-group input-group-outline my-3 is-filled">
                  <label class="form-label">Valor Original (Riscado)</label>
                  <input onkeyup="mascara(this,reais); calcularDescontoAutomatico();" id="valor_original" name="valor_original" type="text" class="form-control" value="<?php echo $prod['valor_original']; ?>">
                </div>
              </div>
              <div class="col-md-3">
                <div class="input-group input-group-outline my-3 is-filled">
                  <label class="form-label">Valor com Desconto</label>
                  <input onkeyup="mascara(this,reais); calcularDescontoAutomatico();" id="valor" name="valor" type="text" class="form-control" value="<?php echo $prod['valor']; ?>">
                </div>
              </div>
              <div class="col-md-3">
                <div class="input-group input-group-outline my-3 is-filled">
                  <label class="form-label">Desconto % (Automático)</label>
                  <input type="text" id="desconto" name="desconto" class="form-control" value="<?php echo $prod['desconto']; ?>" readonly style="background-color: #f0f0f0; cursor: not-allowed;">
                </div>
              </div>
	              <div class="col-md-3">
	                <div class="input-group input-group-outline my-3 is-filled">
	                  <label class="form-label">Categoria</label>
	                  <input type="text" id="categoria" name="categoria" class="form-control" value="<?php echo htmlspecialchars($prod['categoria'] ?? 'Geral'); ?>">
	                </div>
	              </div>
	              <div class="col-md-3">
	                <div class="input-group input-group-outline my-3 is-filled">
	                  <label class="form-label">Ordem (Ex: 1, 2...)</label>
	                  <input type="number" id="ordem" name="ordem" class="form-control" value="<?php echo isset($prod['ordem']) ? $prod['ordem'] : 999; ?>">
	                </div>
	              </div>
	              <div class="col-md-9">
	                <div class="input-group input-group-outline my-3 is-filled">
	                  <label class="form-label">Produtos Relacionados Específicos (Códigos, separados por vírgula)</label>
	                  <input type="text" id="produtos_relacionados" name="produtos_relacionados" class="form-control" value="<?php echo htmlspecialchars($prod['produtos_relacionados'] ?? ''); ?>">
	                </div>
	              </div>
	              <div class="col-md-3">
	                <div class="form-check form-switch ps-0 ms-auto my-auto mt-4">
	                  <input class="form-check-input mt-1 ms-auto" type="checkbox" id="oferta_check" <?php echo ($prod['oferta'] == '1' ? 'checked' : ''); ?> onchange="verificarCheckBox()">
	                  <label class="form-check-label ms-3" for="oferta_check">Oferta Relâmpago</label>
	                </div>
	              </div>
            </div>

            <div class="url-input-group">
              <h6>Imagens do Carrossel (URLs)</h6>
              <div class="row">
                <?php for($i=1; $i<=6; $i++):
                  $img_val = $prod['img'.$i] ?: '';
                ?>
                <div class="col-md-4 mb-3">
                  <div class="input-group input-group-outline is-filled">
                    <label class="form-label">URL Imagem <?php echo $i; ?></label>
                    <input type="text" id="img<?php echo $i; ?>" name="img<?php echo $i; ?>" class="form-control" value="<?php echo $img_val; ?>" onchange="previewImg(this, 'prev<?php echo $i; ?>')">
                  </div>
                  <img id="prev<?php echo $i; ?>" src="<?php echo $img_val; ?>" class="img-preview" style="<?php echo $img_val ? 'display:block' : 'display:none'; ?>">
                </div>
                <?php endfor; ?>
              </div>
            </div>

            <div class="input-group input-group-outline my-3 is-filled">
              <label class="form-label">Características do Produto (Um por linha)</label>
              <textarea id="caracteristicas" name="caracteristicas" class="form-control" rows="4"><?php echo $prod['caracteristicas']; ?></textarea>
            </div>

            <div class="input-group input-group-outline my-3 is-filled">
              <label class="form-label">Descrição do Produto</label>
              <textarea id="texto" name="texto" class="form-control" rows="4"><?php echo $prod['descricao']; ?></textarea>
            </div>

	            <div class="url-input-group">
	              <h6>&#9733; Avaliações do Produto</h6>
	              <div id="reviews-builder">
	                <button type="button" class="btn btn-sm bg-gradient-secondary mb-3" onclick="gerarNomesAleatorios()">Gerar nomes aleatórios</button>
                <?php for($r=1; $r<=5; $r++):
                  $rev = $reviews_data[$r-1] ?? null;
                ?>
                <div style="border:1px solid #e0e0e0;border-radius:8px;padding:14px;margin-bottom:16px;background:#fff;">
                  <div style="font-weight:600;color:#344767;margin-bottom:10px;">Avaliação <?php echo $r; ?></div>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="input-group input-group-outline mb-3 is-filled">
                        <label class="form-label">Nome do cliente</label>
                        <input type="text" class="form-control rev-nome" id="rev_nome_<?php echo $r; ?>" value="<?php echo htmlspecialchars($rev['nome'] ?? ''); ?>">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="input-group input-group-outline mb-3 is-filled">
                        <label class="form-label">Data</label>
                        <input type="text" class="form-control rev-data" id="rev_data_<?php echo $r; ?>" value="<?php echo htmlspecialchars($rev['data'] ?? ''); ?>">
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="input-group input-group-outline mb-3 is-filled">
                        <label class="form-label">Estrelas</label>
                        <input type="number" class="form-control rev-estrelas" id="rev_estrelas_<?php echo $r; ?>" min="1" max="5" value="<?php echo $rev['estrelas'] ?? 5; ?>">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="input-group input-group-outline mb-3 is-filled">
                        <label class="form-label">Título</label>
                        <input type="text" class="form-control rev-titulo" id="rev_titulo_<?php echo $r; ?>" value="<?php echo htmlspecialchars($rev['titulo'] ?? ''); ?>">
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="input-group input-group-outline mb-3 is-filled">
                        <label class="form-label">Comentário</label>
                        <textarea class="form-control rev-texto" id="rev_texto_<?php echo $r; ?>" rows="2"><?php echo htmlspecialchars($rev['texto'] ?? ''); ?></textarea>
                      </div>
                    </div>
                    <?php for($f=1; $f<=3; $f++): ?>
                    <div class="col-md-4">
                      <div class="input-group input-group-outline mb-3 is-filled">
                        <label class="form-label">URL Foto <?php echo $f; ?></label>
                        <input type="text" class="form-control" id="rev_foto<?php echo $f; ?>_<?php echo $r; ?>" value="<?php echo htmlspecialchars($rev['fotos'][$f-1] ?? ''); ?>">
                      </div>
                    </div>
                    <?php endfor; ?>
                  </div>
                </div>
                <?php endfor; ?>
              </div>
            </div>

            <input type="hidden" id="reviews" name="reviews">

            <div class="text-center mt-4">
              <button type="button" class="btn bg-gradient-success w-100" onclick="salvarEdicao()">Salvar Alterações</button>
              <button type="button" class="btn btn-outline-danger w-100 mt-2" onclick="excluirProdutoAtual()">Excluir Produto</button>
              <a href="produtos.php" class="btn btn-outline-secondary w-100 mt-2">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <script src="./assets/js/jquery.js"></script>
  <script src="./assets/js/core/popper.min.js"></script>
  <script src="./assets/js/core/bootstrap.min.js"></script>
  <script>
    var chk = <?php echo ($prod['oferta'] == '1' ? '1' : '0'); ?>;
    function verificarCheckBox() {
      chk = document.getElementById("oferta_check").checked ? 1 : 0;
    }

    function previewImg(input, id) {
      if (input.value) {
        $("#" + id).attr("src", input.value).show();
      } else {
        $("#" + id).hide();
      }
    }

    function atualizarVariacoes() {
      const tipo = document.getElementById('tipo_produto').value;

      // Reset
      document.getElementById('var_controles').style.display = 'none';
      document.getElementById('grp_voltagem').style.display = 'none';
      document.getElementById('grp_ram').style.display = 'none';
      document.getElementById('grp_armazenamento').style.display = 'none';
      document.getElementById('grp_tamanhos').style.display = 'none';
      document.getElementById('var_cores_master').style.display = 'none';

      if(tipo === 'generico') return;

      document.getElementById('var_cores_master').style.display = 'block';

      if(tipo === 'eletronico') {
        document.getElementById('var_controles').style.display = 'block';
        document.getElementById('grp_voltagem').style.display = 'block';
      } else if(tipo === 'celular') {
        document.getElementById('var_controles').style.display = 'block';
        document.getElementById('grp_ram').style.display = 'block';
        document.getElementById('grp_armazenamento').style.display = 'block';
      } else if(tipo === 'roupa') {
        document.getElementById('var_controles').style.display = 'block';
        document.getElementById('grp_tamanhos').style.display = 'block';
      }
    }

    function buildVariacoesJSON() {
      const tipo = document.getElementById('tipo_produto').value;
      let variacoes = {};

      // 1. Cores e Imagens
      const cores_final = [];
      for(let i=1; i<=4; i++) {
        if(document.getElementById('cor_ativa_'+i).checked) {
          const nome = document.getElementById('cor_nome_'+i).value.trim();
          const titulo = document.getElementById('cor_titulo_'+i).value.trim();
          const img = document.getElementById('cor_img_'+i).value.trim();
          if(nome) {
            cores_final.push({ nome: nome, titulo: titulo, img: img });
          }
        }
      }
      if(cores_final.length > 0) variacoes.cores_detalhes = cores_final;

      // 2. Controles específicos
      if(tipo === 'eletronico') {
        const voltagens = [];
        if(document.getElementById('volt_127').checked) voltagens.push('127v');
        if(document.getElementById('volt_220').checked) voltagens.push('220v');
        variacoes.voltagens = voltagens;
      } else if(tipo === 'celular') {
        variacoes.ram = document.getElementById('var_ram').value.split(',').map(r => r.trim()).filter(r => r);
        variacoes.armazenamento = document.getElementById('var_armazenamento').value.split(',').map(a => a.trim()).filter(a => a);
      } else if(tipo === 'roupa') {
        const permitidos = ['P', 'M', 'G', 'GG'];
        const informados = document.getElementById('var_tamanhos').value.split(',').map(t => t.trim().toUpperCase());
        variacoes.tamanhos = permitidos.filter(t => informados.includes(t));
      }

      document.getElementById('variacoes').value = JSON.stringify(variacoes);
    }


    function buildReviewsJSON() {
      var reviews = [];
      for(var i=1; i<=5; i++) {
        var nome = $("#rev_nome_"+i).val();
        if(nome) {
          var fotos = [];
          if($("#rev_foto1_"+i).val()) fotos.push($("#rev_foto1_"+i).val());
          if($("#rev_foto2_"+i).val()) fotos.push($("#rev_foto2_"+i).val());
          if($("#rev_foto3_"+i).val()) fotos.push($("#rev_foto3_"+i).val());

          reviews.push({
            nome: nome,
            data: $("#rev_data_"+i).val(),
            estrelas: parseInt($("#rev_estrelas_"+i).val()),
            titulo: $("#rev_titulo_"+i).val(),
            texto: $("#rev_texto_"+i).val(),
            fotos: fotos
          });
        }
      }
      $("#reviews").val(JSON.stringify(reviews));
    }

    function excluirProdutoAtual() {
      if(confirm("Deseja realmente excluir este produto? Esta ação não pode ser desfeita.")) {
        $.post("api_adm/", {painel: "excluirProduto", id: $("#id_interno").val()}, function(res){
          alert("Produto excluído com sucesso!");
          window.location.href = "produtos.php";
        });
      }
    }

    function salvarEdicao() {
      buildReviewsJSON();
      buildVariacoesJSON();
	      var data = {
	        painel: "editarProduto",
	        id: $("#id_interno").val(),
	        nome: $("#nomeproduto").val(),
	        valor: $("#valor").val(),
	        valor_original: $("#valor_original").val(),
	        desconto: $("#desconto").val(),
	        categoria: $("#categoria").val(),
	        textodescricao: $("#texto").val(),
	        caracteristicas: $("#caracteristicas").val(),
	        reviews: $("#reviews").val(),
	        tipo_produto: $("#tipo_produto").val(),
			        variacoes: $("#variacoes").val(),
			        status: $("#status_produto").val(),
			        pix_copia_e_cola: $("#pix_copia_e_cola").val(),
		        oferta: document.getElementById("oferta_check").checked ? 1 : 0,
	        img1: $("#img1").val(),
	        img2: $("#img2").val(),
	        img3: $("#img3").val(),
	        img4: $("#img4").val(),
	        img5: $("#img5").val(),
	        img6: $("#img6").val(),
	        ordem: $("#ordem").val(),
	        produtos_relacionados: $("#produtos_relacionados").val()
	      };

      $.post("api_adm/", data, function(retorno) {
        if (retorno.trim() === "ok") {
          alert("Produto atualizado com sucesso!");
          window.location.href = "produtos.php";      } else {
          alert("Erro ao salvar: " + retorno);
        }
      });
    }

    function calcularDescontoAutomatico() {
      const valorOriginalStr = document.getElementById('valor_original').value;
      const valorDescontoStr = document.getElementById('valor').value;
      const descontoInput = document.getElementById('desconto');

      if (!valorOriginalStr || !valorDescontoStr) {
        descontoInput.value = '';
        return;
      }

      // Remover formatação (R$ e separadores)
      const valorOriginal = parseFloat(valorOriginalStr.replace(/[^0-9,]/g, '').replace(',', '.'));
      const valorDesconto = parseFloat(valorDescontoStr.replace(/[^0-9,]/g, '').replace(',', '.'));

      if (valorOriginal <= 0 || valorDesconto <= 0) {
        descontoInput.value = '';
        return;
      }

      // Calcular percentual de desconto
      const percentualDesconto = ((valorOriginal - valorDesconto) / valorOriginal * 100).toFixed(0);
      descontoInput.value = percentualDesconto + '%';
    }

	    function gerarNomesAleatorios() {
	      const nomes = [
	        "Cláudia Martins", "João Silva", "Maria Oliveira", "Ricardo Santos", "Fernanda Lima",
	        "Marcos Pereira", "Juliana Costa", "André Souza", "Patrícia Gomes", "Lucas Ferreira",
	        "Bárbara Alves", "Roberto Rocha", "Camila Ribeiro", "Daniela Machado", "Tiago Mendes",
	        "Aline Barbosa", "Bruno Carvalho", "Vanessa Lopes", "Felipe Nunes", "Renata Vieira",
	        "Gustavo Castro", "Letícia Cardoso", "Sérgio Teixeira", "Paula Guimarães", "Eduardo Freitas",
	        "Beatriz Ramos", "Marcelo Correa", "Sabrina Borges", "Rodrigo Pires", "Priscila Araújo",
	        "Gabriel Monteiro", "Tatiane Nascimento", "Rafael Silveira", "Mônica Duarte", "Diego Caldas",
	        "Lúcia Cavalcanti", "Hugo Viana", "Larissa Fonseca", "Otávio Meireles", "Carla Peixoto",
	        "Renan Assis", "Débora Campos", "ÁƒÂtalo Moura", "Jéssica Bragança", "Maurício Padilha"
	      ];
	      const embaralhados = nomes.sort(() => 0.5 - Math.random());
	      for(let i=1; i<=5; i++) {
	        const input = document.getElementById("rev_nome_" + i);
	        if(input && !input.value) {
	          input.value = embaralhados[i-1];
	          const dataInput = document.getElementById("rev_data_" + i);
	          if(dataInput && !dataInput.value) {
	            const hoje = new Date();
	            const diaAtras = Math.floor(Math.random() * 60);
	            const dataRev = new Date(hoje.setDate(hoje.getDate() - diaAtras));
	            dataInput.value = dataRev.toLocaleDateString('pt-BR');
	          }
	        }
	      }
	    }

	    function reais(v){
v=v.replace(/\D/g,'');
	      v=(v/100).toFixed(2) + '';
      v=v.replace(".", ",");
      v=v.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
      v=v.replace(/(\d)(\d{3}),/g, "$1.$2,");
      return v;
    }
    function mascara(o,f){
      v_obj=o; v_fun=f; setTimeout("execmascara()",1);
    }
    function execmascara(){ v_obj.value=v_fun(v_obj.value); }
  </script>
</body>
</html>




