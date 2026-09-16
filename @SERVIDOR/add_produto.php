<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once('../api/db.php');
if(!isset($_SESSION['login'], $_SESSION['senha'], $_SESSION['tempo']) || $_SESSION['tempo'] < time()){
    header('Location: index.php?access=fail&id='.time());
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Add produto - Moderno</title>
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
    .img-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-top: 5px; border: 1px solid var(--border-color, rgba(255,255,255,0.1)); display: none; }
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

<body class="g-sidenav-show">
  <?php include 'sidebar.php'; ?>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Página</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Adicionar produto</li>
          </ol>
          <h6 class="font-weight-bolder mb-0">Loja V2.0 - Novo Layout</h6>
        </nav>
      </div>
    </nav>
    <div class="container-fluid py-4">
      <div class="card card-body mx-3 mx-md-4 mt-n6">
        <div class="bg-blue-accent shadow-primary border-radius-lg pt-4 pb-3">
          <h6 class="text-white text-capitalize ps-3">Novo produto (Importador de produto)</h6>
        </div>
        <div class="row mt-4">
          <!-- Seção do Clonador -->
          <div class="col-12 mb-4">
            <div class="url-input-group" style="background: #f0f2f5; border: 2px dashed #3483fa;">
              <h6><i class="material-icons" style="vertical-align: middle;">content_copy</i> Importar dados do produto</h6>
              <p class="text-xs text-secondary">Abra a página de origem do produto, pressione <code>CTRL + U</code>, copie todo o código fonte e cole abaixo.</p>
              <textarea id="produto_source" class="form-control mb-3" rows="4" placeholder="Cole o código fonte (HTML) aqui..."></textarea>
              <button type="button" class="btn bg-gradient-info btn-sm" onclick="importarProduto();">Extrair Dados</button>
            </div>
          </div>

          <form id="formulario" onsubmit="return false">
            <div class="row">
              <div class="col-md-6">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Nome do produto</label>
                  <input id="nomeproduto" name="nomeproduto" type="text" class="form-control">
                </div>
              </div>
		              <div class="col-md-3">
		                <div class="input-group input-group-outline my-3">
		                  <label class="form-label">Valor Original (Riscado)</label>
			                  <input onkeyup="mascara(this,reais); calcularDescontoAutomatico();" id="valor_original" name="valor_original" type="text" class="form-control">
			                </div>
			              </div>
		              <div class="col-md-3">
		                <div class="input-group input-group-outline my-3">
		                  <label class="form-label">Valor com Desconto</label>
		                  <input onkeyup="mascara(this,reais); calcularDescontoAutomatico();" id="valor" name="valor" type="text" class="form-control">
		                </div>
		              </div>
              <div class="col-md-3">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Desconto % (Automático)</label>
                  <input type="text" id="desconto" name="desconto" class="form-control" readonly style="background-color: #f0f0f0; cursor: not-allowed;">
                </div>
              </div>
              <div class="col-md-3">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Ordem (Ex: 1, 2...)</label>
                  <input type="number" id="ordem" name="ordem" class="form-control" value="999">
                </div>
              </div>
              <div class="col-md-4">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Categoria (Ex: Ferramentas)</label>
                  <input type="text" id="categoria" name="categoria" class="form-control" placeholder="Categoria principal do produto">
                </div>
              </div>
              <div class="col-md-8">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Produtos Relacionados Específicos (Opcional)</label>
                  <input type="text" id="produtos_relacionados" name="produtos_relacionados" class="form-control" placeholder="Códigos separados por vírgula. Ex: 12345, 67890">
                </div>
              </div>
            </div>

            <div class="url-input-group">
              <h6>Imagens do Carrossel (URLs)</h6>
              <div class="row">
                <?php for($i=1; $i<=6; $i++): ?>
                <div class="col-md-4 mb-3">
                  <div class="input-group input-group-outline">
                    <label class="form-label">URL Imagem <?php echo $i; ?></label>
                    <input type="text" id="img<?php echo $i; ?>" name="img<?php echo $i; ?>" class="form-control" onchange="previewImg(this, 'prev<?php echo $i; ?>')">
                  </div>
                  <img id="prev<?php echo $i; ?>" class="img-preview">
                </div>
                <?php endfor; ?>
              </div>
            </div>

            <div class="input-group input-group-outline my-3">
              <label class="form-label">Características do Produto (Um por linha)</label>
              <textarea id="caracteristicas" name="caracteristicas" class="form-control" rows="4"></textarea>
            </div>

            <div class="input-group input-group-outline my-3">
	              <label class="form-label">Descrição do Produto</label>
	              <textarea id="texto" name="texto" class="form-control" rows="4"></textarea>
	            </div>


	            <div class="url-input-group">
	              <h6>&#9733; Avaliações do Produto (5 comentários com 3 fotos cada)</h6>
	              <p class="text-xs text-secondary mb-3">Preencha os campos abaixo. Ao salvar, as avaliações serão convertidas automaticamente para JSON.</p>

	              <div id="reviews-builder">
	                <button type="button" class="btn btn-sm bg-gradient-secondary mb-3" onclick="gerarNomesAleatorios()">Gerar nomes aleatórios</button>
                <?php for($r=1; $r<=5; $r++): ?>
                <div style="border:1px solid #e0e0e0;border-radius:8px;padding:14px;margin-bottom:16px;background:#fff;">
                  <div style="font-weight:600;color:#344767;margin-bottom:10px;">Avaliação <?php echo $r; ?></div>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="input-group input-group-outline mb-3">
                        <label class="form-label">Nome do cliente</label>
                        <input type="text" class="form-control rev-nome" id="rev_nome_<?php echo $r; ?>" placeholder="Ex: Cláudia Martins">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="input-group input-group-outline mb-3">
                        <label class="form-label">Data (dd/mm/aaaa)</label>
                        <input type="text" class="form-control rev-data" id="rev_data_<?php echo $r; ?>" placeholder="Ex: 15/04/2025">
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="input-group input-group-outline mb-3">
                        <label class="form-label">Estrelas (1-5)</label>
                        <input type="number" class="form-control rev-estrelas" id="rev_estrelas_<?php echo $r; ?>" min="1" max="5" value="5">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="input-group input-group-outline mb-3">
                        <label class="form-label">Título da avaliação</label>
                        <input type="text" class="form-control rev-titulo" id="rev_titulo_<?php echo $r; ?>" placeholder="Ex: Produto excelente!">
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="input-group input-group-outline mb-3">
                        <label class="form-label">Texto do comentário</label>
                        <textarea class="form-control rev-texto" id="rev_texto_<?php echo $r; ?>" rows="2" style="border:1px solid #d2d6da;padding:8px;"></textarea>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="input-group input-group-outline mb-2">
                        <label class="form-label">URL Foto 1</label>
                        <input type="text" class="form-control rev-foto" id="rev_foto1_<?php echo $r; ?>" placeholder="https://...">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="input-group input-group-outline mb-2">
                        <label class="form-label">URL Foto 2</label>
                        <input type="text" class="form-control rev-foto" id="rev_foto2_<?php echo $r; ?>" placeholder="https://...">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="input-group input-group-outline mb-2">
                        <label class="form-label">URL Foto 3</label>
                        <input type="text" class="form-control rev-foto" id="rev_foto3_<?php echo $r; ?>" placeholder="https://...">
                      </div>
                    </div>
                  </div>
                </div>
                <?php endfor; ?>
              </div>

              <!-- Campo oculto que recebe o JSON gerado -->
              <textarea id="reviews" name="reviews" class="form-control" rows="3" style="border:1px solid #d2d6da;padding:10px;font-size:11px;color:#888;" placeholder="JSON gerado automaticamente ao salvar..."></textarea>
              <small class="text-secondary">Você também pode colar um JSON diretamente no campo acima (substitui os campos acima).</small>
            </div>

            <!-- SEÇÃO DE VARIAÇÕES DINÂMICAS -->
            <div class="url-input-group" style="background: #f9f9f9; border: 1px solid #e0e0e0;">
              <h6><i class="material-icons" style="vertical-align: middle;">tune</i> Variações do Produto</h6>
              <p class="text-xs text-secondary mb-3">Selecione o tipo de produto para exibir as opções de variação correspondentes.</p>

              <div class="row">
                <div class="col-md-6">
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Tipo de Produto</label>
                    <select id="tipo_produto" name="tipo_produto" class="form-control" onchange="atualizarVariacoes();">
                      <option value="generico">Genérico (sem variações)</option>
                      <option value="eletronico">Eletrônico (voltagem)</option>
                      <option value="celular">Celular (RAM e armazenamento)</option>
                      <option value="roupa">Roupa (cores e tamanhos)</option>
                      <option value="outros">Outros</option>
                    </select>
                  </div>
                </div>
              </div>

	              <!-- Variações Compartilhadas (Voltagem, RAM, Armazenamento) -->
	                      <div id="var_controles" style="display:none;">
	                <div class="row mb-3">
	                  <div class="col-md-4" id="grp_voltagem" style="display:none;">
	                    <label class="form-label">Voltagens</label>
	                    <div class="form-check">
	                      <input class="form-check-input var-voltagem" type="checkbox" value="127v" id="volt_127">
	                      <label class="form-check-label" for="volt_127">127v</label>
	                    </div>
	                    <div class="form-check">
	                      <input class="form-check-input var-voltagem" type="checkbox" value="220v" id="volt_220">
	                      <label class="form-check-label" for="volt_220">220v</label>
	                    </div>
	                  </div>
	                  <div class="col-md-4" id="grp_ram" style="display:none;">
	                    <label class="form-label">Memória RAM</label>
	                    <input type="text" id="var_ram" class="form-control border p-2" placeholder="Ex: 4GB, 8GB">
	                  </div>
	                  <div class="col-md-4" id="grp_armazenamento" style="display:none;">
	                    <label class="form-label">Armazenamento</label>
	                    <input type="text" id="var_armazenamento" class="form-control border p-2" placeholder="Ex: 128GB, 256GB">
	                  </div>
	                  <div class="col-md-4" id="grp_tamanhos" style="display:none;">
	                    <label class="form-label">Tamanhos</label>
	                    <input type="text" id="var_tamanhos" class="form-control border p-2" placeholder="Ex: PP, P, M, G, GG">
	                    <small class="text-muted">Separe os tamanhos por vírgula. O código importado pode preencher automaticamente.</small>
	                  </div>
	                </div>
	              </div>

	              <!-- SEÇÃO DE CORES COM IMAGENS (Até 4 cores) -->
	              <div id="var_cores_master" style="display:none;">
	                <h6 class="mt-4 mb-3" style="font-size: 14px; color: #344767;">Cores e Imagens Específicas</h6>
	                <div class="row">
	                  <?php for($c=1; $c<=4; $c++): ?>
	                  <div class="col-md-6 mb-4">
	                    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; background: #fff;">
	                      <div class="form-check mb-2">
	                        <input class="form-check-input chk-cor-ativa" type="checkbox" id="cor_ativa_<?php echo $c; ?>">
	                        <label class="form-check-label font-weight-bold" for="cor_ativa_<?php echo $c; ?>">Ativar Cor <?php echo $c; ?></label>
	                      </div>
	                      <div class="input-group input-group-outline my-2">
	                        <label class="form-label">Nome da Cor (Ex: Preto)</label>
	                        <input type="text" id="cor_nome_<?php echo $c; ?>" class="form-control">
	                      </div>
	                      <div class="input-group input-group-outline my-2">
	                        <label class="form-label">Título Específico (Ex: iPhone 15 - Preto)</label>
	                        <input type="text" id="cor_titulo_<?php echo $c; ?>" class="form-control">
	                      </div>
	                      <div class="input-group input-group-outline my-2">
	                        <label class="form-label">URL da Imagem desta Cor</label>
	                        <input type="text" id="cor_img_<?php echo $c; ?>" class="form-control" onchange="previewImg(this, 'prev_cor_<?php echo $c; ?>')">
	                      </div>
	                      <img id="prev_cor_<?php echo $c; ?>" class="img-preview" style="width: 60px; height: 60px;">
	                      <?php if($c == 1): ?>
	                      <small class="text-info d-block mt-1">Dica: A Cor 1 usa a imagem principal por padrÁƒÂ£o se deixar a URL vazia.</small>
	                      <?php endif; ?>
	                    </div>
	                  </div>
	                  <?php endfor; ?>
	                </div>
	              </div>

              <!-- Campo oculto para armazenar JSON de variações -->
              <textarea id="variacoes" name="variacoes" class="form-control" rows="2" style="border:1px solid #d2d6da;padding:10px;font-size:11px;color:#888;margin-top:15px;" placeholder="JSON de variações (gerado automaticamente)..."></textarea>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="mt-3 d-flex align-items-center">
                  <h6 class="mb-0">Oferta relampago</h6>
                  <div class="form-check form-switch ps-0 ms-auto my-auto">
                    <input class="form-check-input mt-1 ms-auto" type="checkbox" id="oferta_check" onclick="verificarCheckBox();">
                  </div>
                </div>
              </div>
            </div>

            <input type="hidden" id="idproduto" name="idproduto">
            <button id="salvar" class="btn bg-gradient-success w-100 mt-4" onclick="addprox();">Adicionar Produto</button>
          </form>
        </div>
      </div>
    </div>
  </main>

  <script src="./assets/js/core/popper.min.js"></script>
  <script src="./assets/js/core/bootstrap.min.js"></script>
  <script src="./assets/js/jquery.js"></script>
  <script src="./assets/js/material-dashboard.min.js?v=3.0.4"></script>
  <script src="./js_variacoes_extrator.js"></script>

  <script>
    function previewImg(input, previewId) {
      var preview = document.getElementById(previewId);
      if (input.value) {
        preview.src = input.value;
        preview.style.display = 'block';
      } else {
        preview.style.display = 'none';
      }
    }

	    function importarProduto() {
	      const raw = document.getElementById('produto_source').value;
	      if (!raw) { alert("Cole o código fonte primeiro!"); return; }

	      const doc = new DOMParser().parseFromString(raw, "text/html");

	      // 1. Título e PreÁƒÂ§o
	      const title = doc.querySelector('.ui-pdp-title')?.innerText || '';
	      const price = doc.querySelector('.andes-money-amount__fraction')?.innerText || '';

	      if (title) {
	        $("#nomeproduto").val(title).parent().addClass("is-filled");
	      }
	      if (price) {
	        $("#valor").val(price + ",00").parent().addClass("is-filled");
	      }

	      // 2. Imagens
	      const galleryImgs = Array.from(doc.querySelectorAll('.ui-pdp-gallery__figure__image, .ui-pdp-gallery__thumbnail__img, .ui-pdp-gallery__figure img'));
	      const srcs = [...new Set(galleryImgs.map(i => i.src || i.dataset.src || i.getAttribute('data-zoom')).filter(s => s && !s.includes('pixel') && s.startsWith('http')))];

	      srcs.slice(0, 6).forEach((src, i) => {
	        const input = document.getElementById('img' + (i + 1));
	        if (input) {
	          input.value = src;
	          $(input).parent().addClass("is-filled");
	          previewImg(input, 'prev' + (i + 1));
	        }
	      });

	      // 3. Características
	      let charText = '';
	      doc.querySelectorAll('.ui-pdp-features__list-item, .ui-vpp-striped-specs__row').forEach(p => {
	        const text = p.innerText.trim();
	        if (text.length > 1) {
	          // Tenta formatar como Nome: Valor se houver estrutura de tabela
	          const label = p.querySelector('.ui-vpp-striped-specs__row__label')?.innerText.trim();
	          const value = p.querySelector('.ui-vpp-striped-specs__row__value')?.innerText.trim();
	          if (label && value) {
	            charText += label + ": " + value + "\n";
	          } else {
	            charText += text + "\n";
	          }
	        }
	      });
	      if (charText) {
	        $("#caracteristicas").val(charText.trim()).parent().addClass("is-filled");
	      }

	      // 4. Descrição
	      const desc = doc.querySelector('.ui-pdp-description__content')?.innerText || '';
	      if (desc) {
	        $("#texto").val(desc.trim()).parent().addClass("is-filled");
	      }

	      // 5. Avaliações
	      const reviews = [];
	      doc.querySelectorAll('.ui-review-capability-comments__comment').forEach((rev, idx) => {
	        if (idx >= 5) return; // Limite de 5
	        const text = rev.querySelector('.ui-review-capability-comments__comment__content')?.innerText || '';
	        const pics = Array.from(rev.querySelectorAll('img')).map(i => i.src || i.dataset.src).filter(s => s && s.startsWith('http'));

	        reviews.push({
	          nome: "Cliente " + (document.getElementById('nome_loja_referencia')?.value || "Loja Ester"),
	          data: new Date().toLocaleDateString('pt-BR'),
	          estrelas: 5,
	          titulo: "Excelente produto",
	          texto: text,
	          fotos: pics.slice(0, 3)
	        });
	      });

	      if (reviews.length > 0) {
	        $("#reviews").val(JSON.stringify(reviews));
	        reviews.forEach((r, i) => {
	          const idx = i + 1;
	          $("#rev_nome_" + idx).val(r.nome).parent().addClass("is-filled");
	          $("#rev_data_" + idx).val(r.data).parent().addClass("is-filled");
	          $("#rev_estrelas_" + idx).val(r.estrelas).parent().addClass("is-filled");
	          $("#rev_titulo_" + idx).val(r.titulo).parent().addClass("is-filled");
	          $("#rev_texto_" + idx).val(r.texto).parent().addClass("is-filled");
	          r.fotos.forEach((f, fi) => {
	            $("#rev_foto" + (fi + 1) + "_" + idx).val(f).parent().addClass("is-filled");
	          });
	        });
	      }

	      // 6. Extrair variações automaticamente
	      const { variacoes, tipoDetectado } = extrairVariacoes(doc.body.innerText);
	      if (Object.keys(variacoes).length > 0) {
	        preencherVariacoes(variacoes, tipoDetectado);
	        alert("Dados extraídos com sucesso!\n\nVariações detectadas: " + Object.keys(variacoes).join(', '));
	      } else {
	        alert("Dados extraídos com sucesso! Nenhuma variação detectada.");
	      }
	    }

    var chk = 0;
    function verificarCheckBox() {
      chk = document.getElementById("oferta_check").checked ? 1 : 0;
    }

    // Funções para gerenciar variações dinâmicas
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
	      if(reviews.length > 0) {
	        $("#reviews").val(JSON.stringify(reviews));
	      }
	    }

	    function addprox() {
	      buildReviewsJSON();
	      buildVariacoesJSON();
		      var data = {
		        painel: "addproduto_v2",
		        nome: $("#nomeproduto").val(),
		        valor: $("#valor").val(),
		        valor_original: $("#valor_original").val(),
		        desconto: $("#desconto").val(),
		        categoria: $("#categoria").val(),
		        produtos_relacionados: $("#produtos_relacionados").val(),
		        textodescricao: $("#texto").val(),
		        caracteristicas: $("#caracteristicas").val(),
		        reviews: $("#reviews").val(),
	        tipo_produto: $("#tipo_produto").val(),
			        variacoes: $("#variacoes").val(),
			        pix_copia_e_cola: $("#pix_copia_e_cola").val(),
			        oferta: document.getElementById("oferta_check").checked ? 1 : 0,
			        img1: $("#img1").val(),
		        img2: $("#img2").val(),
		        img3: $("#img3").val(),
		        img4: $("#img4").val(),
		        img5: $("#img5").val(),
		        img6: $("#img6").val(),
		        ordem: $("#ordem").val()
		      };

      if(!data.nome || !data.valor) {
        alert("Preencha o nome e o valor!");
        return;
      }

	      $.post("api_adm/", data, function(retorno) {
	        if (retorno.trim().includes("ok")) {
	          var partes = retorno.split("|");
	          var idGerado = partes[1];
	          var urlBase = window.location.href.split('/@SERVIDOR/')[0];
		          var linkProduto = urlBase + "/index.php?id=" + idGerado;

	          // Criar um modal ou alerta com o link
	          var msg = "Produto adicionado com sucesso!\n\nLink para divulgação:\n" + linkProduto;
		          if(confirm(msg + "\n\nDeseja copiar o link agora?")) {
		            copyToClipboard(linkProduto);
		            alert("Link copiado para a ÁƒÂ¡rea de transferÁƒÂªncia!");
		            window.location.href = "produtos.php";
		          } else {
		            window.location.href = "produtos.php";
		          }
	        } else {
	          alert("Erro: " + retorno);
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

    function reais(v){
      v=v.replace(/\D/g,'');
      v=(v/100).toFixed(2) + '';
      v=v.replace(".", ",");
      v=v.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
      v=v.replace(/(\d)(\d{3}),/g, "$1.$2,");
      return v;
    }
    function mascara(o,f){
      v_obj=o; v_fun=f;
      setTimeout("execmascara()",1);
    }
    function execmascara(){ v_obj.value=v_fun(v_obj.value); }

	    function gerarNomesAleatorios() {
	      const nomes = [
	        "ClÁƒÂ¡udia Martins", "JoÁƒÂ£o Silva", "Maria Oliveira", "Ricardo Santos", "Fernanda Lima",
	        "Marcos Pereira", "Juliana Costa", "AndrÁƒÂ© Souza", "Patrícia Gomes", "Lucas Ferreira",
	        "BÁƒÂ¡rbara Alves", "Roberto Rocha", "Camila Ribeiro", "Daniela Machado", "Tiago Mendes",
	        "Aline Barbosa", "Bruno Carvalho", "Vanessa Lopes", "Felipe Nunes", "Renata Vieira",
	        "Gustavo Castro", "Letícia Cardoso", "SÁƒÂ©rgio Teixeira", "Paula GuimarÁƒÂ£es", "Eduardo Freitas",
	        "Beatriz Ramos", "Marcelo Correa", "Sabrina Borges", "Rodrigo Pires", "Priscila Araújo",
	        "Gabriel Monteiro", "Tatiane Nascimento", "Rafael Silveira", "MÁƒÂ´nica Duarte", "Diego Caldas",
	        "Lúcia Cavalcanti", "Hugo Viana", "Larissa Fonseca", "OtÁƒÂ¡vio Meireles", "Carla Peixoto",
	        "Renan Assis", "DÁƒÂ©bora Campos", "ÁƒÂtalo Moura", "JÁƒÂ©ssica BraganÁƒÂ§a", "Maurício Padilha"
	      ];

	      // Embaralhar nomes
	      const embaralhados = nomes.sort(() => 0.5 - Math.random());

	      // Preencher campos de avaliação
	      for(let i=1; i<=5; i++) {
	        const input = document.getElementById("rev_nome_" + i);
	        if(input && !input.value) {
	          input.value = embaralhados[i-1];
	        }

	        // Preencher data aleatÁƒÂ³ria recente se estiver vazio
	        const dataInput = document.getElementById("rev_data_" + i);
	        if(dataInput && !dataInput.value) {
	          const hoje = new Date();
	          const diaAtras = Math.floor(Math.random() * 60); // atÁƒÂ© 60 dias atrÁƒÂ¡s
	          const dataRev = new Date(hoje.setDate(hoje.getDate() - diaAtras));
	          dataInput.value = dataRev.toLocaleDateString('pt-BR');
	        }
	      }
	    }

	    // Chamar ao carregar a pÁƒÂ¡gina para jÁƒÂ¡ ter nomes prontos
	    window.addEventListener('load', gerarNomesAleatorios);

	    function copyToClipboard(text) {
      var textArea = document.createElement("textarea");
      textArea.value = text;
      document.body.appendChild(textArea);
      textArea.select();
      try {
        document.execCommand('copy');
      } catch (err) {
        console.error('Erro ao copiar', err);
      }
      document.body.removeChild(textArea);
    }
  </script>
</body>
</html>




