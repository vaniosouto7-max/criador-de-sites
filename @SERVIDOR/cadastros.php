<?php
session_start();
require_once(__DIR__ . '/../api/db.php');
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
  <title>
    Cadastros
  </title>
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <!-- Nucleo Icons -->
  <link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="./assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
  <!-- CSS Files -->
    <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link id="pagestyle" href="./assets/css/black-theme.css?v=<?php echo time(); ?>" rel="stylesheet" />
  <link href="./assets/css/fix-labels.css" rel="stylesheet" />
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
    <!-- Navbar -->
     <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Página</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Cadastros</li>
          </ol>
          <h6 class="font-weight-bolder mb-0">Loja V1.0</h6>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div style="visibility:hidden;" class="ms-md-auto pe-md-3 d-flex align-items-center">
            <div class="input-group input-group-outline">
              <label class="form-label">Buscar...</label>
              <input type="text" class="form-control">
            </div>
          </div>
          <ul class="navbar-nav  justify-content-end">
            <li class="nav-item d-flex align-items-center">

			<div class="avatar me-3">
             <img src="./assets/img/the.png" alt="kal" class="border-radius-lg shadow">
            </div>

              <a href="sair.php" class="nav-link text-body font-weight-bold px-0">
                <i class="fa fa-user me-sm-1"></i>
                <span class="d-sm-inline d-none">Sair</span>
              </a>
            </li>

		   <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </a>
            </li>



          </ul>
        </div>
      </div>
    </nav>
    <!-- End Navbar -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card my-4">
              <div class="card-header p-3 position-relative z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg pt-3 pb-3 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                  <h6 class="text-white text-capitalize mb-0"><i class="material-icons text-sm me-1">people_alt</i> Cadastros de Clientes & Remarketing (<b id="totaldecadastros">0</b>)</h6>
                  <p class="text-white text-xs mb-0 opacity-8">Lista de contatos e leads para recuperação de vendas</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <form method="POST" action="api_adm/" style="margin:0;">
                    <input type="hidden" name="painel" value="exportar_csv">
                    <button type="submit" class="btn btn-sm bg-gradient-success mb-0 d-flex align-items-center gap-1">
                      <i class="material-icons text-sm">download</i> Exportar CSV
                    </button>
                  </form>
                  <button type="button" onclick="carregarCadastros();" class="btn btn-sm bg-gradient-dark mb-0 d-flex align-items-center gap-1">
                    <i class="material-icons text-sm">refresh</i> Atualizar
                  </button>
                </div>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cliente / Data</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Email / CPF</th>
					  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">WhatsApp / Telefone</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Endereço de Entrega</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Produto & Variações</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quantidade / Total</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ações</th>
                    </tr>
                  </thead>
                  <tbody id="lista_de_cadastro">
                    <tr><td colspan="7" class="text-center py-4 text-secondary text-sm"><i class="fa fa-spinner fa-spin me-2"></i>Carregando cadastros...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

	<div class="position-fixed bottom-1 end-1 z-index-2">
        <div class="toast fade hide p-2 mt-2 bg-gradient-secondary" role="alert" aria-live="assertive" id="infoToast" aria-atomic="true">
          <div class="toast-header bg-transparent border-0">
            <i class="material-icons text-white me-2">check</i>
            <span class="me-auto text-white font-weight-bold">Cliente foi deletado!</span>
            <i class="fas fa-times text-md text-white ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
          </div>
        </div>
      </div>

	  <div class="position-fixed bottom-1 end-1 z-index-2">
        <div class="toast fade hide p-2 mt-2 bg-gradient-danger" role="alert" aria-live="assertive" id="infoToastBlock" aria-atomic="true">
          <div class="toast-header bg-transparent border-0">
            <i class="material-icons text-white me-2">check</i>
            <span class="me-auto text-white font-weight-bold">Cliente foi bloqueado!</span>
            <i class="fas fa-times text-md text-white ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
          </div>
        </div>
      </div>

    <footer class="footer py-4">
        <div class="container-fluid">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6 mb-lg-0 mb-4">
              <div class="copyright text-center text-sm text-muted text-lg-start">
                © <script>
                  document.write(new Date().getFullYear())
                </script>,
                Desenvolvido com <i class="fa fa-heart"></i> por
                <a href="#" class="font-weight-bold">SKIP-DSN</a>
              </div>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </main>

  <!--   Core JS Files   -->
  <script src="./assets/js/core/popper.min.js"></script>
  <script src="./assets/js/core/bootstrap.min.js"></script>
  <script src="./assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="./assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="./assets/js/jquery.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>

  <script>
    function carregarCadastros() {
      $.post('api_adm/', {painel:"cadastros"}, function(r){
        document.getElementById("lista_de_cadastro").innerHTML = r;
      });
      $.post('api_adm/', {painel:"totalcadastros"}, function(z){
        document.getElementById("totaldecadastros").innerHTML = z;
      });
    }

    $(document).ready(function() {
      carregarCadastros();
      // Atualizar a cada 10 segundos
      setInterval(carregarCadastros, 10000);
    });

	function excluir(id){
      if(!confirm("Deseja realmente excluir este cadastro?")) return;
	  $.post("api_adm/", {painel:"excluir", user:id}, function(show){
	    document.getElementById("infoToast").setAttribute("class","toast fade hide p-2 mt-2 bg-gradient-danger show");
	    setTimeout(()=>{
	      document.getElementById("infoToast").setAttribute("class","toast fade hide p-2 mt-2 bg-gradient-danger hide");
	      carregarCadastros();
	    }, 1500);
	  });
	}

	function sendBlock(id){
      if(!confirm("Deseja bloquear o acesso deste cliente?")) return;
	  $.post("api_adm/", {painel:"blockUser", user:id}, function(show){
	    document.getElementById("infoToastBlock").setAttribute("class","toast fade hide p-2 mt-2 bg-gradient-danger show");
	    setTimeout(()=>{
	      document.getElementById("infoToastBlock").setAttribute("class","toast fade hide p-2 mt-2 bg-gradient-danger hide");
	      carregarCadastros();
	    }, 1500);
	  });
	}
	</script>

  <script src="./assets/js/material-dashboard.min.js?v=3.0.4"></script>
</body>
</html>


