<?php
session_start();
require_once("../api/db.php");

if(!isset($_SESSION['login'], $_SESSION['senha'], $_SESSION['tempo']) || $_SESSION['tempo'] < time()){
    header('Location: index.php?access=fail&id='.time());
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Painel Administrativo - Produtos</title>
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="./assets/css/nucleo-svg.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
    <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link id="pagestyle" href="./assets/css/black-theme.css?v=<?php echo time(); ?>" rel="stylesheet" />
  <link href="./assets/css/fix-labels.css" rel="stylesheet" />
  <style>
    .status-badge {
      font-size: 10px;
      padding: 4px 8px;
      border-radius: 4px;
      font-weight: bold;
      text-transform: uppercase;
    }
    .status-active { background: rgba(76, 175, 80, 0.2); color: #4caf50; }
    .status-inactive { background: rgba(244, 67, 54, 0.2); color: #f44336; }
    .status-google { background: rgba(33, 150, 243, 0.2); color: #2196f3; }
    .status-meta { background: rgba(156, 39, 176, 0.2); color: #9c27b0; }
    .status-crawler { background: rgba(255, 152, 0, 0.2); color: #ff9800; }

    .cadeado-btn {
      cursor: pointer;
      transition: all 0.3s;
    }
    .cadeado-btn:hover {
      transform: scale(1.1);
    }
    .card {
      transition: transform 0.2s;
    }
    .card:hover {
      transform: translateY(-5px);
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

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Página</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Produtos</li>
          </ol>
          <h6 class="font-weight-bolder mb-0">Produtos</h6>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center"></div>
          <ul class="navbar-nav  justify-content-end">
            <li class="nav-item d-flex align-items-center">
              <div class="avatar me-3">
                <img src="./assets/img/the.png" alt="kal" class="border-radius-lg shadow">
              </div>
              <a href="api_adm/logout.php" class="nav-link text-body font-weight-bold px-0">
                <i class="fa fa-user me-sm-1"></i>
                <span class="d-sm-inline d-none">Sair</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-dark">Lista de Produtos</h4>
            <div class="d-flex flex-wrap gap-2 justify-content-end">
              <a href="produtos_json.php?acao=baixar" class="btn btn-outline-success mb-0"><i class="material-icons align-middle me-1">download</i>Baixar lista JSON</a>
              <button type="button" class="btn btn-outline-info mb-0" onclick="document.getElementById('arquivo_json').click()"><i class="material-icons align-middle me-1">upload</i>Carregar lista JSON</button>
              <a href="add_produto.php" class="btn bg-blue-accent mb-0">Novo Produto</a>
            </div>
          </div>
          <form id="form_importacao_json" action="produtos_json.php" method="post" enctype="multipart/form-data" class="d-none">
            <input type="file" id="arquivo_json" name="arquivo_json" accept="application/json,.json" onchange="carregarListaJson(this.files[0])">
          </form>
          <div id="import_status" class="alert alert-info text-white d-none" role="status" aria-live="polite">
            <div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2" role="status"></span><span id="import_status_text">Carregando lista de produtos...</span></div>
            <div class="progress mt-2" style="height: 7px;"><div id="import_progress" class="progress-bar bg-white" style="width: 0%;"></div></div>
          </div>
          <?php if (isset($_GET['importado'])): ?>
            <?php if ($_GET['importado'] === '1'): ?>
              <div class="alert alert-success text-white">Lista carregada. Novos: <?php echo (int)($_GET['inseridos'] ?? 0); ?>; atualizados: <?php echo (int)($_GET['atualizados'] ?? 0); ?>; ignorados: <?php echo (int)($_GET['ignorados'] ?? 0); ?>.</div>
            <?php else: ?>
              <div class="alert alert-danger text-white">Não foi possível carregar a lista: <?php echo htmlspecialchars($_GET['erro'] ?? 'erro desconhecido', ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
          <?php endif; ?>

          <div class="row">
            <?php
            $sql = mysqli_query($conn, "SELECT * from produto ORDER BY id DESC");
            if($sql && mysqli_num_rows($sql) > 0){
              while($row = mysqli_fetch_array($sql)){
                $id = $row["id"];
                $codigo = $row["codigo"];
                $nome = $row["nome"];
                $valor = $row["valor"];
                $img = $row["img"];
                $status = isset($row["status"]) ? $row["status"] : 'ativo';

                $status_label = "Ativo";
                $status_class = "status-active";
                if($status == 'inativo') { $status_label = "Inativo"; $status_class = "status-inactive"; }
                if($status == 'anti-google-v1') { $status_label = "Anti-Google v1"; $status_class = "status-google"; }
                if($status == 'anti-meta-ads-v1') { $status_label = "Anti-Meta v1"; $status_class = "status-meta"; }
                if($status == 'anti-crawler-v1') { $status_label = "Anti-Crawler v1"; $status_class = "status-crawler"; }
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card bg-white border-radius-xl shadow-sm">
                <div class="card-header p-3 position-relative z-index-2">
                  <div class="bg-blue-accent shadow-primary border-radius-lg pt-3 pb-3 px-2 text-center">
                    <h6 class="text-white text-sm mb-0" style="display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; white-space: normal;" title="<?php echo htmlspecialchars($nome);?>"><?php echo htmlspecialchars($nome);?></h6>
                  </div>
                </div>
                <div class="card-body p-3">
                  <div class="text-center mb-3">
                    <?php
                      $img_src = (strpos($img, 'http') === 0) ? $img : "../arquivos/produtos/$codigo/$img";
                    ?>
                    <img src="<?php echo $img_src; ?>" class="img-fluid border-radius-lg shadow" style="height: 150px; width: 100%; object-fit: cover;">
                  </div>

                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-xs text-secondary">Status:</span>
                    <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                  </div>

                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-xs text-secondary">Preço:</span>
                    <span class="text-dark font-weight-bold">R$ <?php echo $valor; ?></span>
                  </div>

                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-xs text-secondary">Ordem (Seq):</span>
                    <input type="number" class="form-control text-center form-control-sm border" style="width: 70px; height: 30px; font-size: 12px; padding: 0;" value="<?php echo isset($row['ordem']) ? $row['ordem'] : 999; ?>" onchange="mudarOrdem('<?php echo $id; ?>', this.value)">
                  </div>

                  <div class="d-flex justify-content-around mb-3">
                    <div class="dropdown">
                      <i class="material-icons text-success cadeado-btn" id="statusDrop<?php echo $id; ?>" data-bs-toggle="dropdown" aria-expanded="false">lock_open</i>
                      <ul class="dropdown-menu bg-white border-secondary shadow-lg" aria-labelledby="statusDrop<?php echo $id; ?>">
                        <li><a class="dropdown-item text-dark text-xs" href="javascript:void(0)" onclick="mudarStatus('<?php echo $id; ?>', 'ativo')"><i class="material-icons text-success text-xs me-2">check_circle</i> Ativo</a></li>
                        <li><a class="dropdown-item text-dark text-xs" href="javascript:void(0)" onclick="mudarStatus('<?php echo $id; ?>', 'inativo')"><i class="material-icons text-danger text-xs me-2">cancel</i> Inativo</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-dark text-xs" href="javascript:void(0)" onclick="mudarStatus('<?php echo $id; ?>', 'anti-google-v1')"><i class="material-icons text-info text-xs me-2">security</i> Anti-Google v1</a></li>
                        <li><a class="dropdown-item text-dark text-xs" href="javascript:void(0)" onclick="mudarStatus('<?php echo $id; ?>', 'anti-meta-ads-v1')"><i class="material-icons text-info text-xs me-2">security</i> Anti-Meta v1</a></li>
                        <li><a class="dropdown-item text-dark text-xs" href="javascript:void(0)" onclick="mudarStatus('<?php echo $id; ?>', 'anti-crawler-v1')"><i class="material-icons text-warning text-xs me-2">security</i> Anti-Crawler v1</a></li>
                      </ul>
                    </div>

                    <a href="../index.php?id=<?php echo $codigo; ?>" target="_blank" title="Ver Produto">
                      <i class="material-icons text-dark cadeado-btn">open_in_new</i>
                    </a>
                    <a href="edit_produto.php?id=<?php echo $id; ?>" title="Editar">
                      <i class="material-icons text-dark cadeado-btn">edit</i>
                    </a>
                    <i class="material-icons text-danger cadeado-btn" onclick="excluirProduto('<?php echo $id; ?>')" title="Excluir">delete</i>
                  </div>

                  <button class="btn btn-outline-primary btn-sm w-100 mb-0" onclick="copiarLink('<?php echo $codigo; ?>', '<?php echo $status; ?>')">Copiar Link</button>
                </div>
              </div>
            </div>
            <?php } } else { ?>
              <div class="col-12 text-center py-5">
                <h5 class="text-secondary">Nenhum produto cadastrado.</h5>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="./assets/js/jquery.js"></script>
  <script src="./assets/js/core/popper.min.js"></script>
  <script src="./assets/js/core/bootstrap.min.js"></script>
  <script src="./assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="./assets/js/material-dashboard.min.js?v=3.0.4"></script>

  <script>
    function carregarListaJson(arquivo) {
      if (!arquivo) return;
      if (!arquivo.name.toLowerCase().endsWith('.json')) {
        alert('Selecione um arquivo com extensão .json.');
        document.getElementById('arquivo_json').value = '';
        return;
      }
      var leitor = new FileReader();
      leitor.onload = function() {
        var dados;
        try { dados = JSON.parse(leitor.result); } catch (e) {
          alert('O arquivo selecionado não é um JSON válido. Baixe novamente pelo botão Baixar lista JSON.');
          return;
        }
        var produtos = Array.isArray(dados) ? dados : (Array.isArray(dados.produtos) ? dados.produtos : [dados]);
        var indice = 0;
        var statusBox = document.getElementById('import_status');
        var statusText = document.getElementById('import_status_text');
        var progressBar = document.getElementById('import_progress');
        statusBox.classList.remove('d-none');
        statusText.textContent = 'Carregando lista de produtos: 0 de ' + produtos.length + '...';
        progressBar.style.width = '0%';
        function enviarProximo() {
          if (indice >= produtos.length) { statusText.textContent = 'Lista carregada com sucesso: ' + produtos.length + ' produto(s).'; progressBar.style.width = '100%'; alert('Lista carregada com sucesso: ' + produtos.length + ' produto(s).'); window.location.reload(); return; }
          var conteudo = JSON.stringify({produtos: [produtos[indice]]});
          var base64 = btoa(unescape(encodeURIComponent(conteudo)));
          var id = Date.now().toString(36) + Math.random().toString(36).slice(2);
          var tamanho = 2400, partes = [];
          for (var pos = 0; pos < base64.length; pos += tamanho) partes.push(base64.slice(pos, pos + tamanho));
          statusText.textContent = 'Carregando produto ' + (indice + 1) + ' de ' + produtos.length + ' (' + partes.length + ' bloco(s))...';
          progressBar.style.width = Math.round((indice / produtos.length) * 100) + '%';
          function enviarParte(n) {
            if (n >= partes.length) return fetch('produtos_json.php?acao=importar_finalizar&id=' + encodeURIComponent(id), {credentials: 'same-origin'});
            return fetch('produtos_json.php?acao=importar_chunk&id=' + encodeURIComponent(id) + '&parte=' + n + '&total=' + partes.length + '&dados=' + encodeURIComponent(partes[n]), {credentials: 'same-origin'}).then(function(res) { if (!res.ok) throw new Error('Falha ao enviar o bloco ' + (n + 1)); return enviarParte(n + 1); });
          }
          enviarParte(0).then(function(res) { return res.text(); }).then(function(texto) {
              var resultado;
              try { resultado = JSON.parse(texto); } catch (e) {
                if (texto.indexOf('<!DOCTYPE') === 0 || texto.indexOf('<html') >= 0) throw new Error('O servidor devolveu HTML. Atualize a página e tente novamente.');
                throw new Error(texto.substring(0, 180) || 'Resposta inválida do servidor.');
              }
              return resultado;
            })
            .then(function(resultado) { if (!resultado.ok) throw new Error(resultado.erro || 'Falha ao importar o produto ' + (indice + 1)); indice++; progressBar.style.width = Math.round((indice / produtos.length) * 100) + '%'; enviarProximo(); })
            .catch(function(erro) { statusText.textContent = 'Erro ao carregar a lista: ' + (erro.message || 'falha desconhecida.'); statusBox.classList.remove('alert-info'); statusBox.classList.add('alert-danger'); progressBar.classList.add('bg-danger'); alert(erro.message || 'Falha ao importar o arquivo.'); document.getElementById('arquivo_json').value = ''; });
        }
        enviarProximo();
      };
      leitor.onerror = function() { alert('Não foi possível ler o arquivo no navegador.'); };
      leitor.readAsText(arquivo, 'UTF-8');
    }

    function mudarStatus(id, status) {
      $.post("api_adm/", {painel: "mudarStatusProduto", id: id, status: status}, function(res){
        if(res == "ok") {
          window.location.reload();
        } else {
          alert("Erro ao mudar status.");
        }
      });
    }

    function mudarOrdem(id, ordem) {
      $.post("api_adm/", {painel: "mudarOrdemProduto", id: id, ordem: ordem}, function(res){
        if(res !== "ok") {
          alert("Erro ao alterar ordem.");
        }
      });
    }

    function excluirProduto(id) {
      if(confirm("Deseja realmente excluir este produto?")) {
        $.post("api_adm/", {painel: "excluirProduto", id: id}, function(res){
          window.location.reload();
        });
      }
    }

    function copiarLink(codigo, status) {
      var urlBase = window.location.href.split('/@SERVIDOR/')[0];

      // Padronizando todos os links para passar pelo index.php
      // Isso garante que a sessÃ£o seja iniciada corretamente e o Anti-Crawler funcione sempre
      var link = urlBase + "/index.php?id=" + codigo;

      var textArea = document.createElement("textarea");
      textArea.value = link;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);
      alert("Link seguro copiado!");
    }
  </script>
</body>
</html>



