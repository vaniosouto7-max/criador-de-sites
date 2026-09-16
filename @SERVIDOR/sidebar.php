<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-dark-sidebar" id="sidenav-main" style="z-index: 1050; display: flex; flex-direction: column;">

  <div class="navbar-collapse w-auto" id="sidenav-collapse-main" style="flex: 1; overflow-y: auto; padding-top: 10px;">
    <ul class="navbar-nav">

      <li class="nav-item">
        <a class="nav-link text-white <?php echo ($currentPage == 'dashboard.php' || $currentPage == 'index.php') ? 'active bg-blue-accent' : ''; ?>" href="dashboard.php">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">dashboard</i></div>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link text-white <?php echo ($currentPage == 'cadastros.php') ? 'active bg-blue-accent' : ''; ?>" href="cadastros.php">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">assignment_ind</i></div>
          <span class="nav-link-text ms-1">Cadastros</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link text-white <?php echo ($currentPage == 'produtos.php') ? 'active bg-blue-accent' : ''; ?>" href="produtos.php">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">local_grocery_store</i></div>
          <span class="nav-link-text ms-1">Produtos</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link text-white <?php echo ($currentPage == 'add_produto.php' || $currentPage == 'edit_produto.php') ? 'active bg-blue-accent' : ''; ?>" href="add_produto.php">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">add_circle</i></div>
          <span class="nav-link-text ms-1">Adicionar Produto</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link text-white <?php echo ($currentPage == 'estatisticas.php') ? 'active bg-blue-accent' : ''; ?>" href="estatisticas.php">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">insert_chart</i></div>
          <span class="nav-link-text ms-1">Estatísticas</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link text-white <?php echo ($currentPage == 'bloqueados.php') ? 'active bg-blue-accent' : ''; ?>" href="bloqueados.php">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">key</i></div>
          <span class="nav-link-text ms-1">Bloqueados</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link text-white <?php echo ($currentPage == 'administrador.php') ? 'active bg-blue-accent' : ''; ?>" href="administrador.php">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">person</i></div>
          <span class="nav-link-text ms-1">Administrador</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link text-white <?php echo ($currentPage == 'pix.php') ? 'active bg-blue-accent' : ''; ?>" href="pix.php">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">paid</i></div>
          <span class="nav-link-text ms-1">Config Pix</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link text-white <?php echo ($currentPage == 'config.php') ? 'active bg-blue-accent' : ''; ?>" href="config.php">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">storefront</i></div>
          <span class="nav-link-text ms-1">Config Loja</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link text-white <?php echo ($currentPage == 'apis.php') ? 'active bg-blue-accent' : ''; ?>" href="apis.php">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">notification_important</i></div>
          <span class="nav-link-text ms-1">Config Apis</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link text-white <?php echo ($currentPage == 'pixel.php') ? 'active bg-blue-accent' : ''; ?>" href="pixel.php">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">track_changes</i></div>
          <span class="nav-link-text ms-1">Pixel Facebook</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link text-white <?php echo ($currentPage == 'config_catalogo.php') ? 'active bg-blue-accent' : ''; ?>" href="config_catalogo.php">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">view_carousel</i></div>
          <span class="nav-link-text ms-1">Configurar Catálogo</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link text-white" href="../catalogo" target="_blank">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">storefront</i></div>
          <span class="nav-link-text ms-1">Ver Catálogo</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link text-white" href="sair.php">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">login</i></div>
          <span class="nav-link-text ms-1">Sair</span>
        </a>
      </li>

    </ul>
  </div>

  <div class="sidenav-footer w-100 mt-3" style="flex-shrink: 0; padding-bottom: 20px;">
    <div class="mx-3">
      <a class="btn bg-blue-accent mt-4 w-100" href="https://api.whatsapp.com/send?phone=5513996514973&text=Oi%20SKIP-DSN" target="_blank" type="button" style="border: none; color: white; background: var(--accent-primary); border-radius: 8px;">Falar com SKIP-DSN</a>
    </div>
  </div>
</aside>
