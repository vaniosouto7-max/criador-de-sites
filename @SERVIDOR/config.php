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
    ConfiguraÃ§Ã£o
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
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Configuração</li>
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
                  <h6 class="mb-0">Configuração da loja</h6>
                </div>
                <div class="card-body p-3">
                  <ul class="list-group" id="lista">
                   <?php


				   $sql = mysqli_query($conn, "SELECT * from config");


					 if(($sql ? mysqli_num_rows($sql) : 0) > 0){

					 $f = "../arquivos/logo/";
					 $i = glob($f . "*.png");

					 foreach($i as $im){
					 $imgFile = str_replace("../arquivos/logo/", "", $im); //remove o caminho deixando sà¸£à¸“ nome
					 $thefile = $imgFile;
					 }


							$sql = mysqli_query($conn, "SELECT * FROM config");
							 while($sql && $rowx = mysqli_fetch_array($sql)){

							 $id = $rowx["id"];
							 $nome = $rowx["nome"];
							 $cor = $rowx["cor"];
							 $cor_botao = isset($rowx["cor_botao"]) ? $rowx["cor_botao"] : "#3483fa";
							 $cor_icones = isset($rowx["cor_icones"]) ? $rowx["cor_icones"] : "#ffffff";
							 $img = $rowx["img"];
							 $numero = $rowx["numero"];
							 $zap_cotacao = isset($rowx["zap_cotacao"]) ? $rowx["zap_cotacao"] : "";
							 $zap_flutuante_ativo = isset($rowx["zap_flutuante_ativo"]) ? $rowx["zap_flutuante_ativo"] : "1";
							 $texto = $rowx["texto"];
							 $endereco = isset($rowx["endereco"]) ? $rowx["endereco"] : "";
							 $cnpj = isset($rowx["cnpj"]) ? $rowx["cnpj"] : "";

							echo '<li class="list-group-item border-0 d-flex align-items-center px-0 mb-2 pt-0">
							  <div class="avatar me-3">
								<img style="width:70px;" src="../arquivos/logo/'.$thefile.'" class="border-radius-lg shadow">
							  </div>
							  <div class="d-flex align-items-start flex-column justify-content-center">
								<h6 class="mb-0 text-sm">Nome: '.$nome.'</h6>
								<h6 class="mb-0 text-sm">Cor Loja: <span style="display:inline-block;width:12px;height:12px;background:'.$cor.';border-radius:2px;margin-right:5px;"></span>'.$cor.'</h6>
								<h6 class="mb-0 text-sm">Cor Botão: <span style="display:inline-block;width:12px;height:12px;background:'.$cor_botao.';border-radius:2px;margin-right:5px;"></span>'.$cor_botao.'</h6>
								<h6 class="mb-0 text-sm">Cor Ícones e CEP: <span style="display:inline-block;width:12px;height:12px;background:'.$cor_icones.';border-radius:2px;margin-right:5px;border:1px solid #ccc;"></span>'.$cor_icones.'</h6>
								<h6 class="mb-0 text-sm">WhatsApp Suporte: '.$numero.'</b></h6>
								<h6 class="mb-0 text-sm">WhatsApp Cotação: '.$zap_cotacao.' (Botão '.($zap_flutuante_ativo == "1" ? "Ativo" : "Inativo").')</h6>
								<h6 class="mb-0 text-sm">Texto: '.$texto.'</b></h6>
								<h6 class="mb-0 text-sm">CNPJ: '.$cnpj.'</b></h6>
								<h6 class="mb-0 text-sm">Endereço: '.$endereco.'</b></h6>

							  </div>
							  <a class="btn btn-link pe-3 ps-0 mb-0 ms-auto w-25 w-md-auto" href="javascript:;"><span style="cursor:pointer;" class="badge badge-sm bg-gradient-dark fixed-plugin-button">Editar</span></a>
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
            <span class="me-auto text-white font-weight-bold">Erro! Senha inválida.</span>
            <i class="fas fa-times text-md text-white ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
          </div>
        </div>

		<div class="toast fade hide p-2 mt-2 bg-gradient-danger" role="alert" aria-live="assertive" id="infoToast1" aria-atomic="true">
          <div class="toast-header bg-transparent border-0">
            <i class="material-icons text-white me-2">notifications</i>
            <span class="me-auto text-white font-weight-bold">Atenção! Preencha todos os campos!</span>
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
                <a href="#" class="font-weight-bold" target="_blank">SKIP-DSN</a>
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
          <h5 class="mt-3 mb-0">Editar Loja</h5>
        </div>
        <div class="float-end mt-4">
          <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
            <i class="material-icons">clear</i>
          </button>
        </div>


      </div>
      <hr class="horizontal dark my-1">
      <div class="card-body pt-sm-3 pt-0">

        <form id="formqr" onsubmit="return false" >
		<div class="input-group input-group-outline my-3">
		 <!-- <label class="form-label">Logo da loja</label>
		  <input id="logoloja" name="logoloja" type="text" class="form-control">-->

		 <!--<p>Logo da loja</p>-->
		   <label for='pic' style="text-align:center;background-color: transparent; border: 1px solid #344767; cursor:pointer;justify-content: center;width: 100%;background-image: none; color: #344767; padding: 4px 12px;font-size: 15px;font-weight: 400;border-radius: 4px;text-decoration: none;transition: all 0.3s ease;">Buscar logo (click aqui)</label>
		  <input style="display:none; width:100%;text-align:center;color:transparent;" accept="image/*" type="file" id="pic" name="pic">

		   <label for='favicon' style="margin-top:15px;text-align:center;background-color: transparent; border: 1px solid #344767; cursor:pointer;justify-content: center;width: 100%;background-image: none; color: #344767; padding: 4px 12px;font-size: 15px;font-weight: 400;border-radius: 4px;text-decoration: none;transition: all 0.3s ease;">Buscar Favicon (click aqui)</label>
		  <input style="display:none; width:100%;text-align:center;color:transparent;" accept="image/*" type="file" id="favicon" name="favicon">
		</div>
			<div class="input-group input-group-outline my-3 is-filled">
			  <label class="form-label">Nome da loja</label>
			  <input id="nomeloja" name="nomeloja" type="text" class="form-control" value="<?php echo $nome; ?>">
			</div>

			<div class="input-group input-group-outline my-3 is-filled">
			  <label class="form-label">WhatsApp Suporte da loja</label>
			  <input id="numerozap" name="numerozap" type="text" class="form-control" value="<?php echo $numero; ?>">
			</div>			<div class="input-group input-group-outline my-3 is-filled">
			  <label class="form-label" style="position: static; margin-bottom: 5px; width: 100%;">WhatsApp para Cotação (Flutuante)<br><small style="color: #888; font-size: 11px; font-weight: normal;">Deixe em branco para usar o número principal.</small></label>
			  <input id="zap_cotacao" name="zap_cotacao" type="text" class="form-control" value="<?php echo $zap_cotacao; ?>" placeholder="Ex: 5511999999999">
			</div>
			<div class="input-group input-group-outline my-3 is-filled" style="flex-direction: column;">
			  <label class="form-label" style="position: static; margin-bottom: 5px; width: 100%;">Exibir botão de WhatsApp flutuante?</label>
			  <select id="zap_flutuante_ativo" class="form-control" style="padding: 10px; border: 1px solid #ccc; appearance: auto;">
			      <option value="1" <?php if($zap_flutuante_ativo == "1") echo "selected"; ?>>Sim</option>
			      <option value="0" <?php if($zap_flutuante_ativo == "0") echo "selected"; ?>>Não</option>
			  </select>
			</div>
			<div class="input-group input-group-outline my-3 is-filled">
			  <label class="form-label">Texto do WhatsApp</label>
			  <input id="textozap" name="textozap" type="text" class="form-control" value="<?php echo $texto; ?>">
			</div><div class="input-group input-group-outline my-3 is-filled">
			  <label class="form-label">CNPJ</label>
			  <input id="cnpj" name="cnpj" type="text" class="form-control" value="<?php echo $cnpj; ?>">
			</div><div class="input-group input-group-outline my-3 is-filled">
			  <label class="form-label">Endereço Completo</label>
			  <input id="endereco" name="endereco" type="text" class="form-control" value="<?php echo $endereco; ?>">
			</div>
				<div class="input-group input-group-outline my-3 is-filled" style="flex-direction: column;">
				  <label class="form-label" style="position: static; margin-bottom: 5px;">Cor da loja (RGB)</label>
				  <input id="corloja" name="corloja" type="color" class="form-control" style="height: 45px; padding: 2px;" value="<?php echo $cor; ?>">
				</div>
				<div class="input-group input-group-outline my-3 is-filled" style="flex-direction: column;">
				  <label class="form-label" style="position: static; margin-bottom: 5px;">Cor dos botões de compra (RGB)</label>
				  <input id="corbotao" name="corbotao" type="color" class="form-control" style="height: 45px; padding: 2px;" value="<?php echo $cor_botao; ?>">
				</div>
				<div class="input-group input-group-outline my-3 is-filled" style="flex-direction: column;">
				  <label class="form-label" style="position: static; margin-bottom: 5px;">Cor dos ícones do Menu e CEP (RGB)</label>
				  <input id="coricones" name="coricones" type="color" class="form-control" style="height: 45px; padding: 2px;" value="<?php echo $cor_icones; ?>">
				</div>
		</form>

		<hr class="horizontal dark my-3">
		   <div class="mt-3 d-flex">

       <a class="btn bg-gradient-dark w-100" href="#" onclick="atualizar()">Salvar</a>
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
	     var cordaloja = document.getElementById("corloja").value;
	     var nomedaloja = document.getElementById("nomeloja").value;

	 var numerozap = document.getElementById("numerozap").value;
	 var zap_cotacao = document.getElementById("zap_cotacao").value;
	 var zap_flutuante_ativo = document.getElementById("zap_flutuante_ativo").value;
     var textozap = document.getElementById("textozap").value;
     var endereco = document.getElementById("endereco").value;
     var cnpj = document.getElementById("cnpj").value;
     var picpic = document.getElementById("pic").value;
     var fav = document.getElementById("favicon").value;

	 if(!cordaloja || !nomedaloja || !numerozap || !textozap){
	  document.getElementById("infoToast1").setAttribute("class","toast fade hide p-2 mt-2 bg-gradient-danger show");

	  setTimeout(()=>{
	  document.getElementById("infoToast1").setAttribute("class","toast fade hide p-2 mt-2 bg-gradient-danger hide");
	  }, 3000);

		 }else{
		  if(picpic || fav){
			  var form_data = new FormData(document.getElementById("formqr"));

			 fetch("api_adm/img.php",{
			 method: "POST",
			 body: form_data
			 });

			setTimeout(()=>{
			newnew();
			}, 2000);
		  } else {
			newnew();
		  }
		 }




  }

	  function newnew(){
	     var cordaloja = document.getElementById("corloja").value;
	     var corbotao = document.getElementById("corbotao").value;
	     var coricones = document.getElementById("coricones").value;
	     var nomedaloja = document.getElementById("nomeloja").value;

		 var numerozap = document.getElementById("numerozap").value;
		 var zap_cotacao = document.getElementById("zap_cotacao").value;
		 var zap_flutuante_ativo = document.getElementById("zap_flutuante_ativo").value;
	     var textozap = document.getElementById("textozap").value;
	     var endereco = document.getElementById("endereco").value;
	     var cnpj = document.getElementById("cnpj").value;

		 if(!cordaloja || !nomedaloja || !numerozap || !textozap){

	  document.getElementById("infoToast1").setAttribute("class","toast fade hide p-2 mt-2 bg-gradient-danger show");

	  setTimeout(()=>{
	  document.getElementById("infoToast1").setAttribute("class","toast fade hide p-2 mt-2 bg-gradient-danger hide");
	  }, 3000);

	 }else{

		 $.post("api_adm/", {painel:"atualizarLoja", cor:cordaloja, cor_botao:corbotao, cor_icones:coricones, nome:nomedaloja, zap:numerozap, zap_cotacao:zap_cotacao, zap_flutuante_ativo:zap_flutuante_ativo, texto:textozap, endereco:endereco, cnpj:cnpj},function(zeragem){
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

  }
</script>

  <script src="./assets/js/material-dashboard.min.js?v=3.0.4"></script>
</body>

</html>


