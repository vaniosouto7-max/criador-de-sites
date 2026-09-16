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
    Administrador
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
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">PÃ¡gina</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Administrador</li>
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
	<br><br><br>
	<div class="card card-body mx-3 mx-md-4 mt-n6">

        <div class="row">
          <div class="row">


            <div class="col-12 col-xl-4">
              <div class="card card-plain h-100">
                <div class="card-header pb-0 p-3">
                  <h6 class="mb-0">Administrador</h6>
                </div>
                <div class="card-body p-3">
                  <ul class="list-group" id="lista">
                   <?php
				   $sql = mysqli_query($conn, "SELECT * from acesso");
					 if(($sql ? mysqli_num_rows($sql) : 0) > 0){


						$sql = mysqli_query($conn, "SELECT * FROM acesso");
						 while($sql && $rowx = mysqli_fetch_array($sql)){

						 $id = $rowx["id"];
						 $login = $rowx["login"];
						 $senha = $rowx["senha"];
						 $acesso = $rowx["acesso"];


						$primeira = substr($senha, 0, 1);
						$ultima = substr($senha, -1);

						echo '<li class="list-group-item border-0 d-flex align-items-center px-0 mb-2 pt-0">
							  <div class="avatar me-3">
								<img src="./assets/img/the.png" alt="kal" class="border-radius-lg shadow">
							  </div>
							  <div class="d-flex align-items-start flex-column justify-content-center">

								<h6 class="mb-0 text-sm">UsuÃ¡rio: '.$login.'</h6>
								<h6 class="mb-0 text-sm">Senha: '.$primeira.'******'.$ultima.'</h6>

							  </div>
							  <a class="btn btn-link pe-3 ps-0 mb-0 ms-auto w-25 w-md-auto" href="javascript:;"><span style="cursor:pointer;" class="badge badge-sm bg-dark-sidebar fixed-plugin-button">Editar</span></a>
							</li>  ';
						}

					}else{
					 echo "";
					}
				   ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

	  <div class="position-fixed bottom-1 end-1 z-index-2" style="z-index:9999999 !important;">
        <div class="toast fade hide p-2 mt-2 bg-gradient-danger" role="alert" aria-live="assertive" id="infoToast" aria-atomic="true">
          <div class="toast-header bg-transparent border-0">
            <i class="material-icons text-white me-2">notifications</i>
            <span class="me-auto text-white font-weight-bold">Erro! Senha invÃ¡lida.</span>
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
                </script>, Copyright Â© 2026 Marketplace. Todos os direitos reservados.
              </div>
            </div>

          </div>
        </div>
      </footer>
    </div>
  </main>
    <div class="fixed-plugin">
    <div class="card shadow-lg">
      <div class="card-header pb-0 pt-3">
        <div class="float-start">
          <h5 class="mt-3 mb-0">Editar Administrador</h5>
        </div>
        <div class="float-end mt-4">
          <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
            <i class="material-icons">clear</i>
          </button>
        </div>

        <!-- End Toggle Button -->
      </div>
      <hr class="horizontal dark my-1">
      <div class="card-body pt-sm-3 pt-0">

        <form>
		<div class="input-group input-group-outline my-3">
		  <label class="form-label">Novo Login</label>
		  <input id="newlogin" name="newlogin" type="text" class="form-control">
		</div>
		<div class="input-group input-group-outline my-3">
		  <label class="form-label">Nova Senha</label>
		  <input id="newsenha" name="newsenha" type="text" class="form-control">
		</div>
		<div class="input-group input-group-outline my-3">
		  <label class="form-label">Senha antiga</label>
		  <input id="oldsenha" name="oldsenha" type="text" class="form-control">
		</div>
		</form>

		<hr class="horizontal dark my-3">
		   <div class="mt-3 d-flex">

       <a class="btn bg-dark-sidebar w-100" href="#" onclick="atualizar()">Salvar</a>
	   </div>
      </div>
    </div>
  </div>


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
  function atualizar(){
  var l = document.getElementById("newlogin").value;
  var s = document.getElementById("newsenha").value;
  var o = document.getElementById("oldsenha").value;
  $.post("api_adm/", {painel:"atualizarAdm", newlogin:l, newsenha:s, oldsenha:o},function(zeragem){

  if(zeragem.trim()=="erro"){
   document.getElementById("infoToast").setAttribute("class","toast fade hide p-2 mt-2 bg-gradient-danger show");

	setTimeout(()=>{
	  document.getElementById("infoToast").setAttribute("class","toast fade hide p-2 mt-2 bg-gradient-danger hide");
	  }, 3000);

  }else{
  window.location.reload();
  }
  });
  }
</script>

  <script src="./assets/js/material-dashboard.min.js?v=3.0.4"></script>
</body>

</html>


