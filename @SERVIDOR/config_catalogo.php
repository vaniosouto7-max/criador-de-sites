<?php
session_start();
require_once('../api/db.php');
if(!isset($_SESSION['login'], $_SESSION['senha'], $_SESSION['tempo']) || $_SESSION['tempo'] < time()){
    header('Location: index.php?access=fail&id='.time());
    exit;
}

// 1. Processar Delete de Banner
if(isset($_GET['del_banner'])) {
    $id_del = intval($_GET['del_banner']);
    $sqlImg = mysqli_query($conn, "SELECT imagem FROM catalogo_banners WHERE id = '$id_del'");
    if($sqlImg && mysqli_num_rows($sqlImg) > 0) {
        $rowImg = mysqli_fetch_array($sqlImg);
        $filepath = "../" . $rowImg['imagem'];
        if(file_exists($filepath)) {
            unlink($filepath);
        }
    }
    mysqli_query($conn, "DELETE FROM catalogo_banners WHERE id = '$id_del'");
    header("Location: config_catalogo.php?msg=Banner deletado");
    exit;
}

// 2. Processar Upload de Banner
if(isset($_POST['add_banner'])) {
    $link = mysqli_real_escape_string($conn, $_POST['link']);
    $ordem = intval($_POST['ordem']);

    if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $novo_nome = time() . "_" . rand(100,999) . "." . $ext;
        $dir = "../arquivos/banners/";
        if(!is_dir($dir)) mkdir($dir, 0777, true);
        $destino = $dir . $novo_nome;

        if(move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
            $db_path = "arquivos/banners/" . $novo_nome;
            mysqli_query($conn, "INSERT INTO catalogo_banners (imagem, link, ordem) VALUES ('$db_path', '$link', '$ordem')");
            header("Location: config_catalogo.php?msg=Banner adicionado");
            exit;
        }
    }
}

// 3. Processar Atualização de Produtos Destaque
if(isset($_POST['update_destaques'])) {
    mysqli_query($conn, "UPDATE produto SET destaque_catalogo = 0");
    if(isset($_POST['destaques']) && is_array($_POST['destaques'])) {
        foreach($_POST['destaques'] as $id_prod) {
            $id_prod = intval($id_prod);
            mysqli_query($conn, "UPDATE produto SET destaque_catalogo = 1 WHERE id = '$id_prod'");
        }
    }
    header("Location: config_catalogo.php?msg=Destaques atualizados");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Configurar Catálogo</title>
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="./assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link id="pagestyle" href="./assets/css/black-theme.css?v=<?php echo time(); ?>" rel="stylesheet" />
  <link href="./assets/css/fix-labels.css" rel="stylesheet" />
  <style>
    .welcome-bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
    .welcome-bar h2 { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em; }
    .welcome-bar p { font-size: 0.8125rem; color: var(--text-muted); margin-top: 4px; }
    .banner-img-preview { width: 100%; max-height: 120px; object-fit: cover; border-radius: 8px; }
  </style>
</head>
<body class="g-sidenav-show bg-gray-200">
  <?php include 'sidebar.php'; ?>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <div class="container-fluid py-4">
      <div class="welcome-bar">
        <div>
          <h2>Configurar Catálogo</h2>
          <p>Personalize os banners e as ofertas em destaque do seu catálogo.</p>
        </div>
      </div>

      <?php if(isset($_GET['msg'])): ?>
      <div class="alert alert-success text-white">
          <?php echo htmlspecialchars($_GET['msg']); ?>
      </div>
      <?php endif; ?>

      <!-- Secao Banners -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card bg-card">
            <div class="card-header pb-0 bg-transparent border-bottom-0">
              <h6 class="mb-0 text-white">Banners do Topo</h6>
            </div>
            <div class="card-body">
              <form method="post" enctype="multipart/form-data" class="row align-items-end mb-4">
                <div class="col-md-4">
                    <div class="input-group input-group-static mb-3">
                        <label class="text-white">Imagem (Recomendado: 1200x300px)</label>
                        <input type="file" name="imagem" class="form-control text-white" accept="image/*" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-outline mb-3">
                        <label class="form-label">Link (opcional)</label>
                        <input type="text" name="link" class="form-control text-white">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group input-group-outline mb-3">
                        <label class="form-label">Ordem</label>
                        <input type="number" name="ordem" value="0" class="form-control text-white">
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" name="add_banner" class="btn btn-primary w-100 m-0">Adicionar</button>
                </div>
              </form>

              <div class="row">
                <?php
                $sqlB = mysqli_query($conn, "SELECT * FROM catalogo_banners ORDER BY ordem ASC, id DESC");
                if(mysqli_num_rows($sqlB) == 0): ?>
                    <div class="col-12"><p class="text-white opacity-6">Nenhum banner cadastrado.</p></div>
                <?php else:
                    while($b = mysqli_fetch_array($sqlB)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card bg-dark">
                            <img src="../<?php echo $b['imagem']; ?>" class="banner-img-preview m-2" alt="Banner">
                            <div class="card-body p-3">
                                <p class="text-white text-sm mb-1">Ordem: <?php echo $b['ordem']; ?></p>
                                <p class="text-white text-sm mb-3 text-truncate">Link: <?php echo $b['link'] ?: 'Nenhum'; ?></p>
                                <a href="?del_banner=<?php echo $b['id']; ?>" class="btn btn-danger btn-sm w-100 m-0" onclick="return confirm('Excluir banner?')">Excluir</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Secao Produtos Destaque -->
      <div class="row">
        <div class="col-12">
          <div class="card bg-card">
            <div class="card-header pb-0 bg-transparent border-bottom-0">
              <h6 class="mb-0 text-white">Produtos em Destaque (Carrossel Melhores Ofertas)</h6>
              <p class="text-sm text-white opacity-6">Selecione os produtos que aparecerão logo abaixo do banner principal.</p>
            </div>
            <div class="card-body">
              <form method="post">
                <div class="table-responsive">
                  <table class="table align-items-center mb-0">
                    <thead>
                      <tr>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Produto</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Valor</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Destaque</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $sqlP = mysqli_query($conn, "SELECT id, nome, img, valor, destaque_catalogo FROM produto ORDER BY nome ASC");
                      while($p = mysqli_fetch_array($sqlP)):
                      ?>
                      <tr>
                        <td>
                          <div class="d-flex px-2 py-1">
                            <div>
                              <img src="../<?php echo $p['img']; ?>" class="avatar avatar-sm me-3" alt="produto">
                            </div>
                            <div class="d-flex flex-column justify-content-center">
                              <h6 class="mb-0 text-sm text-white"><?php echo htmlspecialchars($p['nome']); ?></h6>
                            </div>
                          </div>
                        </td>
                        <td>
                          <p class="text-xs font-weight-bold mb-0 text-white">R$ <?php echo $p['valor']; ?></p>
                        </td>
                        <td class="align-middle text-center">
                          <div class="form-check d-flex justify-content-center">
                            <input class="form-check-input" type="checkbox" name="destaques[]" value="<?php echo $p['id']; ?>" <?php echo ($p['destaque_catalogo'] == 1) ? 'checked' : ''; ?>>
                          </div>
                        </td>
                      </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
                <div class="mt-4 text-end">
                    <button type="submit" name="update_destaques" class="btn btn-primary">Salvar Destaques</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>

  <script src="./assets/js/core/popper.min.js"></script>
  <script src="./assets/js/core/bootstrap.min.js"></script>
  <script src="./assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="./assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="./assets/js/material-dashboard.min.js?v=3.0.0"></script>
</body>
</html>
