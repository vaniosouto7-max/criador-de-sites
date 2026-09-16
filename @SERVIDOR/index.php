<?php

			require_once("../api/db.php");
            $sql = mysqli_query($conn, "SELECT * from acesso");
	         if(($sql ? mysqli_num_rows($sql) : 0) > 0){
            }else{
			$tempo= rand(9,9999) . time();
			header("Location: cadastrar.php?cadId=$tempo");
			}

?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="./assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="./assets/img/favicon.png?v=<?php echo time(); ?>">
  <title>
    Painel
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
  <link id="pagestyle" href="./assets/css/black-theme.css?v=3.0.4" rel="stylesheet" />
  <link href="./assets/css/fix-labels.css" rel="stylesheet" />
  <script>
  function verificar(){

  var l = document.getElementById("login").value;
  var s = document.getElementById("senha").value;

	$.post("api_adm/", {painel:"usuario", login:l, senha:s},function(retorno){
	if(retorno.trim()=="sucesso"){
	window.location.href="dashboard.php?the=<?php echo time();?>";
	}else{
       document.getElementById("infoToastok2").setAttribute("class", "toast fade hide p-2 mt-2 bg-gradient-danger show");
      setTimeout(()=>{
	  document.getElementById("infoToastok2").setAttribute("class", "toast fade hide p-2 mt-2 bg-gradient-danger hide");
	  }, 4000);
	}
	 });
	}
  </script>
</head>

<body class="bg-dark-sidebar">

  <main class="main-content  mt-0">
    <div class="page-header align-items-start min-vh-100" style="background-position: 35% !important; background-size: auto !important; background-image: none;">
      <span class="mask bg-dark-sidebar opacity-6"></span>
      <div class="container my-auto">
        <div class="row">
          <div class="col-lg-4 col-md-8 col-12 mx-auto">
            <div class="card z-index-0 fadeIn3 fadeInBottom">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">

				<div class="bg-blue-accent shadow-primary border-radius-lg py-3 pe-1">
                  <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Painel de Login</h4>

                </div>
              </div>
              <div class="card-body">
                <form role="form" class="text-start">
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Login</label>
                    <input type="text" class="form-control" id="login" name="login" autocomplete="off">
                  </div>
                  <div class="input-group input-group-outline mb-3">
                    <label class="form-label">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" autocomplete="off">
                  </div>

                  <div class="text-center">
                    <button onclick="verificar();" type="button" class="btn bg-blue-accent w-100 my-4 mb-2">Entrar</button>
                  </div>

                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

	  <div class="position-fixed bottom-1 end-1 z-index-2">
        <div class="toast fade hide p-2 mt-2 bg-gradient-danger" role="alert" aria-live="assertive" id="infoToastok2" aria-atomic="true">
          <div class="toast-header bg-transparent border-0">
            <i class="material-icons text-white me-2">notifications</i>
            <span class="me-auto text-white font-weight-bold">Erro! Login ou senha inválidos!</span>
            <i class="fas fa-times text-md text-white ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
          </div>
        </div>
      </div>

      <footer class="footer position-absolute bottom-2 py-2 w-100">
        <div class="container">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-12 col-md-6 my-auto">
              <div class="copyright text-center text-sm text-white text-lg-start">
                © <script>
                  document.write(new Date().getFullYear())
                </script>, Copyright © 2026 Marketplace. Todos os direitos reservados.
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
  <!-- Github buttons -->
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="./assets/js/material-dashboard.min.js?v=3.0.4"></script>
</body>

</html>
