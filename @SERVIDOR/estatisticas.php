<?php
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
  <title>
    Estatísticas
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

<body class="g-sidenav-show  bg-gray-200">
  <?php include 'sidebar.php'; ?>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
      <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Página</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Estatísticas</li>
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
		  <div class="col-lg-4" style="width:100% !important;">
          <div class="card h-100">
            <div class="card-header pb-0 p-3">
              <div class="row">
                <div class="col-6 d-flex align-items-center">
                  <h6 class="mb-0">Estatísticas geral</h6>
                </div>
				<!--
                <div class="col-6 text-end">
                  <button class="btn btn-outline-primary btn-sm mb-0 toast-btn" data-target="infoToast">Zerar tudo</button>
                </div>-->
              </div>
            </div>
            <div class="card-body p-3 pb-0">
              <ul class="list-group">
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex flex-column">
                    <h6 class="mb-1 text-dark font-weight-bold text-sm">Cliques</h6>
                    <span class="text-xs" id="cliques"><div class="spinner-grow" role="status"><span class="sr-only"></span></div></span>
                  </div>
                  <div class="d-flex align-items-center text-sm">
                     <span onclick="zerar('cliques')" style="cursor:pointer;" class="badge badge-sm bg-gradient-dark toast-btn" data-target="infoToast">Zerar</span>
                  </div>
                </li>

				<li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex flex-column">
                    <h6 class="mb-1 text-dark font-weight-bold text-sm">Cadastros</h6>
                    <span class="text-xs" id="cadastro"><div class="spinner-grow" role="status"><span class="sr-only"></span></div></span>
                  </div>
                  <div class="d-flex align-items-center text-sm">
                     <span onclick="zerar('cadastro')" style="cursor:pointer;" class="badge badge-sm bg-gradient-dark toast-btn" data-target="infoToast">Zerar</span>
                  </div>
                </li>

				<li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex flex-column">
                    <h6 class="mb-1 text-dark font-weight-bold text-sm">Pix gerado</h6>
                    <span class="text-xs" id="estimativa"><div class="spinner-grow" role="status"><span class="sr-only"></span></div></span>
                  </div>
                  <div class="d-flex align-items-center text-sm">
                     <span onclick="zerar('estimativa')" style="cursor:pointer;" class="badge badge-sm bg-gradient-dark toast-btn" data-target="infoToast">Zerar</span>
                  </div>
                </li>

				<li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex flex-column">
                    <h6 class="mb-1 text-dark font-weight-bold text-sm">Celular</h6>
                    <span class="text-xs" id="celular"><div class="spinner-grow" role="status"><span class="sr-only"></span></div></span>
                  </div>
                  <div class="d-flex align-items-center text-sm">
                     <span onclick="zerar('mobile')" style="cursor:pointer;" class="badge badge-sm bg-gradient-dark toast-btn" data-target="infoToast">Zerar</span>
                  </div>
                </li>

				<li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex flex-column">
                    <h6 class="mb-1 text-dark font-weight-bold text-sm">Computador</h6>
                    <span class="text-xs" id="computador"><div class="spinner-grow" role="status"><span class="sr-only"></span></div></span>
                  </div>
                  <div class="d-flex align-items-center text-sm">
                     <span onclick="zerar('desktop')" style="cursor:pointer;" class="badge badge-sm bg-gradient-dark toast-btn" data-target="infoToast">Zerar</span>
                  </div>
                </li>

				<li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex flex-column">
                    <h6 class="mb-1 text-dark font-weight-bold text-sm">Bots</h6>
                    <span class="text-xs" id="bot"><div class="spinner-grow" role="status"><span class="sr-only"></span></div></span>
                  </div>
                  <div class="d-flex align-items-center text-sm">
                    <span onclick="zerar('bot')" style="cursor:pointer;" class="badge badge-sm bg-gradient-dark toast-btn" data-target="infoToast">Zerar</span>
                  </div>
                </li>

				<li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex flex-column">
                    <h6 class="mb-1 text-dark font-weight-bold text-sm">Bloqueados</h6>
                    <span class="text-xs" id="bloqueado"><div class="spinner-grow" role="status"><span class="sr-only"></span></div></span>
                  </div>
                  <div class="d-flex align-items-center text-sm">
                    <span onclick="zerar('bloqueado')" style="cursor:pointer;" class="badge badge-sm bg-gradient-dark toast-btn" data-target="infoToast">Zerar</span>
                  </div>
                </li>

              </ul>
            </div>
          </div>
        </div>
				</div>

	 <div class="position-fixed bottom-1 end-1 z-index-2">
        <div class="toast fade hide p-2 mt-2 bg-gradient-success" role="alert" aria-live="assertive" id="infoToast" aria-atomic="true">
          <div class="toast-header bg-transparent border-0">
            <i class="material-icons text-white me-2">check</i>
            <span class="me-auto text-white font-weight-bold">Limpeza feita com sucesso!</span>
            <i class="fas fa-times text-md text-white ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
          </div>
        </div>
      </div>




      <footer class="footer py-4  ">
        <div class="container-fluid">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6 mb-lg-0 mb-4">
              <div class="copyright text-center text-sm text-muted text-lg-start">
                Â© <script>
                  document.write(new Date().getFullYear())
                </script>,
                Desenvolvido com <i class="fa fa-heart"></i> por
                <a href="https://www.creative-tim.com" class="font-weight-bold" target="_blank">SKIP-DSN</a>
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
  setTimeout(()=>{
  $.post("api_adm/", {painel:"online"},function(resumo){
  var info = resumo.split("|");
  document.getElementById("cliques").innerHTML=info[1];
  document.getElementById("cadastro").innerHTML=info[5];
  document.getElementById("estimativa").innerHTML=info[7];
  document.getElementById("celular").innerHTML=info[3];
  document.getElementById("computador").innerHTML=info[2];
  document.getElementById("bot").innerHTML=info[4];
  document.getElementById("bloqueado").innerHTML=info[6];
  });

  }, 1000);

  function zerar(id){
  $.post("api_adm/", {painel:"zerar", comando:id},function(zeragem){
  window.location.reload();
  });
  }

  </script>



  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="./assets/js/material-dashboard.min.js?v=3.0.4"></script>
</body>

</html>


