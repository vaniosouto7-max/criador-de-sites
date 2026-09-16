<?php
session_start();
require_once('../api/db.php');
if(!isset($_SESSION['login'], $_SESSION['senha'], $_SESSION['tempo']) || $_SESSION['tempo'] < time()){
    header('Location: index?access=fail&id='.time());
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Painel Administrativo</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">

  <!-- Font Awesome -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

  <!-- Theme CSS -->
  <link id="pagestyle" href="./assets/css/black-theme.css?v=<?php echo time(); ?>" rel="stylesheet" />
  <link href="./assets/css/fix-labels.css" rel="stylesheet" />

  <style>
    /* Dashboard-specific layout refinements */
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 24px;
    }

    @media (max-width: 1200px) {
      .dashboard-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 480px) {
      .dashboard-grid { grid-template-columns: 1fr; }
    }

    .bottom-section {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px;
    }

    @media (max-width: 992px) {
      .bottom-section { grid-template-columns: 1fr; }
    }

    /* Monitoring card */
    .monitor-card {
      background: var(--bg-card);
      backdrop-filter: blur(10px);
      border: 1px solid var(--border-subtle);
      border-radius: var(--border-radius);
      overflow: hidden;
      transition: all var(--transition-normal);
    }

    .monitor-card:hover {
      border-color: var(--border-hover);
    }

    .monitor-header {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 16px 20px;
      background: var(--gradient-primary);
    }

    .monitor-header i {
      font-size: 20px;
      color: rgba(255, 255, 255, 0.9);
    }

    .monitor-header h6 {
      font-size: 0.875rem;
      font-weight: 600;
      color: #fff;
      margin: 0;
    }

    /* PIX orders card */
    .pix-card {
      background: var(--bg-card);
      backdrop-filter: blur(10px);
      border: 1px solid var(--border-subtle);
      border-radius: var(--border-radius);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: all var(--transition-normal);
    }

    .pix-card:hover {
      border-color: var(--border-hover);
    }

    .pix-header {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 16px 20px;
      background: var(--gradient-emerald);
    }

    .pix-header i {
      font-size: 20px;
      color: rgba(255, 255, 255, 0.9);
    }

    .pix-header h6 {
      font-size: 0.875rem;
      font-weight: 600;
      color: #fff;
      margin: 0;
    }

    .pix-body {
      padding: 16px 20px;
      flex: 1;
      overflow-y: auto;
      max-height: 500px;
    }

    /* Empty state */
    .empty-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
      color: var(--text-muted);
      text-align: center;
    }

    .empty-state i {
      font-size: 40px;
      margin-bottom: 12px;
      opacity: 0.3;
    }

    /* Dashboard welcome */
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

    .live-indicator {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      background: rgba(16, 185, 129, 0.1);
      border: 1px solid rgba(16, 185, 129, 0.2);
      border-radius: 100px;
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--accent-emerald);
    }

    .live-dot {
      width: 8px;
      height: 8px;
      background: var(--accent-emerald);
      border-radius: 50%;
      animation: pulse 2s ease-in-out infinite;
    }
  </style>

    <link rel="shortcut icon" href="../arquivos/favicon.png?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="../arquivos/favicon.png?v=<?php echo time(); ?>">
</head>

<body class="g-sidenav-show">
  <!-- Sidebar Overlay for Mobile -->
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main Content -->
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Página</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Dashboard</li>
          </ol>
          <h6 class="font-weight-bolder mb-0">Painel Administrativo</h6>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div style="visibility:hidden;" class="ms-md-auto pe-md-3 d-flex align-items-center">
            <div class="input-group input-group-outline">
              <label class="form-label">Buscar...</label>
              <input type="text" class="form-control">
            </div>
          </div>
          <ul class="navbar-nav justify-content-end">
            <li class="nav-item d-flex align-items-center">
              <div class="avatar me-3">
                <img src="./assets/img/the.png" alt="admin" class="border-radius-lg shadow">
              </div>
              <a href="sair" class="nav-link text-body font-weight-bold px-0">
                <i class="fa fa-user me-sm-1"></i>
                <span class="d-sm-inline d-none">Sair</span>
              </a>
            </li>
            <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav" onclick="toggleSidebar()">
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

      <!-- Welcome Bar -->
      <div class="welcome-bar">
        <div>
          <h2>Dashboard</h2>
          <p>Monitoramento em tempo real da sua loja</p>
        </div>
        <div class="live-indicator">
          <span class="live-dot"></span>
          AO VIVO
        </div>
      </div>

      <!-- KPI Cards - Row 1 -->
      <div class="dashboard-grid">

        <!-- Online -->
        <div class="kpi-card kpi-indigo">
          <div class="kpi-icon">
            <i class="material-icons">language</i>
          </div>
          <div class="kpi-label">Online Agora</div>
          <div class="kpi-value" id="totalOnline">
            <div class="spinner-grow" role="status"><span class="sr-only"></span></div>
          </div>
        </div>

        <!-- Cliques -->
        <div class="kpi-card kpi-cyan">
          <div class="kpi-icon">
            <i class="material-icons">touch_app</i>
          </div>
          <div class="kpi-label">Cliques</div>
          <div class="kpi-value" id="cliques">
            <div class="spinner-grow" role="status"><span class="sr-only"></span></div>
          </div>
        </div>

        <!-- Cadastros -->
        <div class="kpi-card kpi-emerald">
          <div class="kpi-icon">
            <i class="material-icons">assignment_ind</i>
          </div>
          <div class="kpi-label">Cadastros</div>
          <div class="kpi-value" id="cadastro">
            <div class="spinner-grow" role="status"><span class="sr-only"></span></div>
          </div>
        </div>

        <!-- Pix Gerado -->
        <div class="kpi-card kpi-amber">
          <div class="kpi-icon">
            <i class="material-icons">attach_money</i>
          </div>
          <div class="kpi-label">Pix Gerado</div>
          <div class="kpi-value" id="estimativa">
            <div class="spinner-grow" role="status"><span class="sr-only"></span></div>
          </div>
        </div>

        <!-- Celular -->
        <div class="kpi-card kpi-blue">
          <div class="kpi-icon">
            <i class="material-icons">phone_android</i>
          </div>
          <div class="kpi-label">Celular</div>
          <div class="kpi-value" id="celular">
            <div class="spinner-grow" role="status"><span class="sr-only"></span></div>
          </div>
        </div>

        <!-- Desktop -->
        <div class="kpi-card kpi-purple">
          <div class="kpi-icon">
            <i class="material-icons">desktop_windows</i>
          </div>
          <div class="kpi-label">Desktop</div>
          <div class="kpi-value" id="computador">
            <div class="spinner-grow" role="status"><span class="sr-only"></span></div>
          </div>
        </div>

        <!-- Bot -->
        <div class="kpi-card kpi-rose">
          <div class="kpi-icon">
            <i class="material-icons">bug_report</i>
          </div>
          <div class="kpi-label">Bot</div>
          <div class="kpi-value" id="bot">
            <div class="spinner-grow" role="status"><span class="sr-only"></span></div>
          </div>
        </div>

        <!-- Bloqueados -->
        <div class="kpi-card kpi-pink">
          <div class="kpi-icon">
            <i class="material-icons">lock_outline</i>
          </div>
          <div class="kpi-label">Bloqueados</div>
          <div class="kpi-value" id="bloqueado">
            <div class="spinner-grow" role="status"><span class="sr-only"></span></div>
          </div>
        </div>

      </div>

      <!-- Bottom Section: Monitor Table + PIX Orders -->
      <div class="bottom-section">

        <!-- Monitoring Table -->
        <div class="monitor-card">
          <div class="monitor-header">
            <i class="material-icons">monitoring</i>
            <h6>Monitoramento</h6>
          </div>
          <div style="padding: 0;">
            <div class="table-responsive">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Área do site</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Dispositivo</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cidade / Estado</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Entrada</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Opção</th>
                  </tr>
                </thead>
                <tbody id="listaUsuariosOnline">
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- PIX Orders Timeline -->
        <div class="pix-card">
          <div class="pix-header">
            <i class="material-icons">paid</i>
            <h6>Ordens de Pagamento</h6>
          </div>
          <div class="pix-body">
            <div class="timeline timeline-one-side" id="pixgerados">
            </div>
          </div>
        </div>

      </div>

      <!-- Webhook Finance Section -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="monitor-card">
            <div class="monitor-header bg-gradient-success">
              <i class="material-icons">account_balance_wallet</i>
              <h6>Painel Financeiro (Webhook) - Vendas Confirmadas</h6>
            </div>
            <div style="padding: 0; max-height: 400px; overflow-y: auto;">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead class="sticky-top bg-dark">
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Produto / TxID</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Valor</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Data</th>
                    </tr>
                  </thead>
                  <tbody id="webhook-body">
                     <!-- Injetado via AJAX -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Toast Notification -->
      <div class="position-fixed bottom-1 end-1 z-index-2">
        <div class="toast fade hide p-2 mt-2 bg-gradient-warning" role="alert" aria-live="assertive" id="infoToast" aria-atomic="true">
          <div class="toast-header bg-transparent border-0">
            <i class="material-icons text-white me-2">check</i>
            <span class="me-auto text-white font-weight-bold">Cliente foi bloqueado!</span>
            <i class="fas fa-times text-md text-white ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <footer class="footer py-4">
        <div class="container-fluid">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6 mb-lg-0 mb-4">
              <div class="copyright text-center text-sm text-muted text-lg-start">
                © <script>document.write(new Date().getFullYear())</script>,
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
  <script src="./assets/js/plugins/chartjs.min.js"></script>
  <script src="./assets/js/jquery.js"></script>

  <script>
  // Sidebar toggle for mobile
  function toggleSidebar() {
    var sidebar = document.getElementById('sidenav-main');
    var overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('show');
    overlay.classList.toggle('active');
  }

  // Close sidebar when pressing Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      var sidebar = document.getElementById('sidenav-main');
      var overlay = document.getElementById('sidebarOverlay');
      sidebar.classList.remove('show');
      overlay.classList.remove('active');
    }
  });
  </script>

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
  var chegouInfo = new Audio('assets/mp3/pix.mp3');

  function atualizarDashboard() {
	    $.post("api_adm/", {painel:"online"}, function(resumo) {
	      var info = resumo.split("|");
	      if (info.length >= 8) {
	        var elOnline = document.getElementById("totalOnline");
	        var elCliques = document.getElementById("cliques");
	        var elCadastro = document.getElementById("cadastro");
	        var elEstimativa = document.getElementById("estimativa");
	        var elCelular = document.getElementById("celular");
	        var elComputador = document.getElementById("computador");
	        var elBot = document.getElementById("bot");
	        var elBloqueado = document.getElementById("bloqueado");

	        if (elOnline) elOnline.innerHTML = info[0];
	        if (elCliques) elCliques.innerHTML = info[1];
	        if (elCadastro) elCadastro.innerHTML = info[5];
	        if (elEstimativa) elEstimativa.innerHTML = info[7] + ' <small class="text-xs">(' + (info[8] || 'R$ 0,00') + ')</small>';
	        if (elCelular) elCelular.innerHTML = info[3];
	        if (elComputador) elComputador.innerHTML = info[2];
	        if (elBot) elBot.innerHTML = info[4];
	        if (elBloqueado) elBloqueado.innerHTML = info[6];
	      }
	    });

	    $.post("api_adm/", {painel:"lista_online"}, function(resumo2) {
	      var containerOnline = document.getElementById("listaUsuariosOnline");
	      if (containerOnline) {
	        containerOnline.innerHTML = resumo2;
	      }
	    });

	    $.post("api_adm/", {painel:"lista_pix"}, function(resumo3) {
	      var containerPix = document.getElementById("pixgerados");
	      if (containerPix) {
	        if (resumo3.includes("attach_money") || resumo3.includes("timeline-block")) {
	          if (containerPix.innerHTML !== resumo3) {
	            containerPix.innerHTML = resumo3;
	            try { chegouInfo.play().catch(function(e){}); } catch(e){}
	          }
	        } else {
	          containerPix.innerHTML = resumo3;
	        }
	      }
	    });

	    $.post("api_adm/", {painel:"lista_webhook"}, function(resumo4) {
	      var containerWebhook = document.getElementById("webhook-body");
	      if (containerWebhook) {
	          containerWebhook.innerHTML = resumo4;
	      }
	    });
	  }

	  // Executa imediatamente e depois a cada 3 segundos
	  $(document).ready(function(){
	    atualizarDashboard();
	    setInterval(atualizarDashboard, 3000);
	  });


  </script>

<script>
	function sendBlock(id){
	 $.post("api_adm/", {painel:"blockUser", user:id},function(show){
	 document.getElementById("infoToast").setAttribute("class","toast fade hide p-2 mt-2 bg-gradient-danger show");

	 setTimeout(()=>{
	  document.getElementById("infoToast").setAttribute("class","toast fade hide p-2 mt-2 bg-gradient-danger hide");
	 },3000);

	 });
	}
</script>

  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="./assets/js/material-dashboard.min.js?v=3.0.4"></script>
</body>

</html>


