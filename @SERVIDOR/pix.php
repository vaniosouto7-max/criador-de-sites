<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once('../api/db.php');
if(!isset($_SESSION['login'], $_SESSION['senha'], $_SESSION['tempo']) || $_SESSION['tempo'] < time()){
    header('Location: index.php?access=fail&id='.time());
    exit;
}

$sql_config = mysqli_query($conn, "SELECT * FROM config");
$row_config = mysqli_fetch_array($sql_config);
$lojinha = $row_config["nome"] ?? "Loja";

$sql_pix = mysqli_query($conn, "SELECT * FROM pix WHERE id=1");
$pix = mysqli_fetch_array($sql_pix);

// Garantir que as tabelas pix_tabelas e pix_tabela_codigos existam
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `pix_tabelas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL DEFAULT 'Tabela PIX',
  `ativa` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Migração caso a coluna 'status' exista em vez de 'ativa'
$chk_status = mysqli_query($conn, "SHOW COLUMNS FROM pix_tabelas LIKE 'status'");
if ($chk_status && mysqli_num_rows($chk_status) > 0) {
    mysqli_query($conn, "ALTER TABLE pix_tabelas DROP COLUMN `status`");
    mysqli_query($conn, "ALTER TABLE pix_tabelas ADD COLUMN `ativa` tinyint(1) NOT NULL DEFAULT 0");
}
// Caso a coluna ativa não exista e também não tinha status (garantia extra)
$chk_ativa = mysqli_query($conn, "SHOW COLUMNS FROM pix_tabelas LIKE 'ativa'");
if (!$chk_ativa || mysqli_num_rows($chk_ativa) === 0) {
    mysqli_query($conn, "ALTER TABLE pix_tabelas ADD COLUMN `ativa` tinyint(1) NOT NULL DEFAULT 0");
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `pix_tabela_codigos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tabela_id` int(11) NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `codigo` TEXT NOT NULL,
  `status_pagamento` varchar(20) NOT NULL DEFAULT 'DISPONIVEL',
  `reservado_em` DATETIME DEFAULT NULL,
  `reservado_pedido_ref` varchar(100) DEFAULT NULL,
  `pago_em` DATETIME DEFAULT NULL,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tabela_id` (`tabela_id`),
  INDEX `idx_status` (`status_pagamento`),
  FOREIGN KEY (`tabela_id`) REFERENCES `pix_tabelas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Garantir coluna pix_modo na tabela pix
$chk_modo = mysqli_query($conn, "SHOW COLUMNS FROM pix LIKE 'pix_modo'");
if (!$chk_modo || mysqli_num_rows($chk_modo) === 0) {
    mysqli_query($conn, "ALTER TABLE pix ADD COLUMN pix_modo varchar(20) NOT NULL DEFAULT 'manual'");
    mysqli_query($conn, "ALTER TABLE pix ADD COLUMN pix_max_itens int(11) NOT NULL DEFAULT 4");
}

// Carregar tabelas PIX
$sql_tabelas = mysqli_query($conn, "SELECT t.*,
    COUNT(c.id) as total_codigos,
    SUM(CASE WHEN c.status_pagamento='DISPONIVEL' THEN 1 ELSE 0 END) as disponiveis,
    SUM(CASE WHEN c.status_pagamento='PAGO' THEN 1 ELSE 0 END) as pagos,
    SUM(CASE WHEN c.status_pagamento='PAGO' THEN c.valor ELSE 0 END) as valor_pago,
    SUM(c.valor) as valor_total
    FROM pix_tabelas t
    LEFT JOIN pix_tabela_codigos c ON c.tabela_id = t.id
    GROUP BY t.id
    ORDER BY t.ativa DESC, t.id ASC");
if (!$sql_tabelas) {
    die("Erro MySQL na consulta sql_tabelas: " . mysqli_error($conn));
}
$tabelas_pix = [];
while ($row_t = mysqli_fetch_assoc($sql_tabelas)) {
    $tabelas_pix[] = $row_t;
}

$pix_modo_atual = $pix['pix_modo'] ?? 'manual';
$pix_max_itens = $pix['pix_max_itens'] ?? 4;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="./assets/img/favicon.png">
  <title>Configuração Pix - <?php echo $lojinha; ?></title>
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <!-- Nucleo Icons Removidos -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
  <link id="pagestyle" href="./assets/css/black-theme.css?v=3.0.4" rel="stylesheet" />
  <link href="./assets/css/fix-labels.css" rel="stylesheet" />
  <style>
    .form-section {
      background: var(--bg-tertiary);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      border-left: 5px solid var(--accent-pink);
      border: 1px solid var(--border-subtle);
      border-left-width: 5px;
    }
    .form-section h6 {
      color: var(--text-primary);
      font-weight: 700;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
    }
    .form-section h6 i {
      margin-right: 10px;
      color: var(--accent-pink);
    }
    .webhook-box {
      background: rgba(59, 130, 246, 0.1);
      border-radius: 8px;
      padding: 15px;
      margin-top: 10px;
      font-size: 0.85rem;
      border: 1px solid rgba(59, 130, 246, 0.3);
      color: var(--text-secondary);
    }
    .webhook-box code {
      display: block;
      background: rgba(0,0,0,0.3);
      padding: 8px;
      border-radius: 4px;
      margin: 8px 0;
      word-break: break-all;
      color: var(--text-accent);
    }

    /* ===== TABELAS PIX COPIA E COLA ===== */
    .pix-tabelas-wrap {
      display: grid;
      gap: 12px;
      margin-top: 12px;
    }
    .pix-tabela-card {
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      padding: 16px;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .pix-tabela-card:hover {
      box-shadow: var(--shadow-sm);
    }
    .pix-tabela-card.ativa {
      border-color: var(--accent-emerald);
      background: rgba(16, 185, 129, 0.05);
      box-shadow: 0 0 10px rgba(16, 185, 129, 0.1);
    }
    .pix-tabela-card-header {
      display: flex;
      gap: 10px;
      align-items: center;
      flex-wrap: wrap;
    }
    .pix-tabela-nome {
      font-weight: 900;
      color: var(--text-primary);
      font-size: 15px;
    }
    .badge-ativa {
      background: rgba(22,163,74,0.15);
      color: #16a34a;
      border: 1px solid #22c55e;
      font-weight: 900;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 11px;
    }
    .badge-inativa {
      background: rgba(100,116,139,0.12);
      color: #64748b;
      border: 1px solid rgba(100,116,139,0.3);
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 11px;
    }
    .pix-tabela-stats {
      margin-top: 8px;
      font-size: 12px;
      color: var(--text-secondary);
    }
    .pix-tabela-actions {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
      margin-top: 10px;
      align-items: center;
    }
    .pix-codigos-wrap {
      display: none;
      margin-top: 12px;
      border: 1px solid var(--border-subtle);
      border-radius: 8px;
      overflow-x: auto; /* IMPORTANTE: Garante que tabelas de PIX rolem no celular */
    }
    .pix-codigos-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
      white-space: nowrap;
    }
    .pix-codigos-table th {
      background: rgba(255,255,255,0.02);
      padding: 8px 10px;
      text-align: left;
      font-weight: 700;
      color: var(--text-secondary);
      border-bottom: 1px solid var(--border-subtle);
    }
    .pix-codigos-table td {
      padding: 8px 10px;
      border-bottom: 1px solid rgba(255,255,255,0.02);
      vertical-align: middle;
      color: var(--text-primary);
    }
    .pix-codigos-table tr:last-child td { border-bottom: none; }
    .badge-pago {
      background: rgba(22,163,74,0.15);
      color: #16a34a;
      border: 1px solid #22c55e;
      font-weight: 900;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 10px;
    }
    .badge-disponivel {
      background: rgba(148,163,184,0.12);
      color: #475569;
      border: 1px solid rgba(148,163,184,0.4);
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 10px;
    }
    .badge-reservado {
      background: rgba(245,158,11,0.15);
      color: #d97706;
      border: 1px solid #f59e0b;
      font-weight: 800;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 10px;
    }
    .pix-modo-selector {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }
    .pix-modo-btn {
      flex: 1;
      min-width: 140px;
      padding: 12px 16px;
      border: 2px solid #dee2e6;
      border-radius: 10px;
      background: #fff;
      cursor: pointer;
      text-align: center;
      transition: all 0.2s;
      font-size: 13px;
      font-weight: 600;
      color: #64748b;
    }
    .pix-modo-btn:hover { border-color: #e91e63; color: #e91e63; }
    .pix-modo-btn.selected { border-color: #e91e63; background: #fdf2f8; color: #e91e63; }
    .pix-modo-btn i { display: block; font-size: 22px; margin-bottom: 6px; }

    /* Modal */
    .pix-modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 9999;
      background: rgba(0,0,0,0.5);
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .pix-modal-overlay.show { display: flex; }
    .pix-modal-box {
      background: #fff;
      border-radius: 12px;
      padding: 28px;
      max-width: 520px;
      width: 100%;
      box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    }
    .pix-modal-title {
      font-size: 18px;
      font-weight: 800;
      color: #344767;
      margin-bottom: 20px;
    }
    .pix-modal-footer {
      display: flex;
      gap: 10px;
      margin-top: 20px;
      justify-content: flex-end;
    }
    .pix-modal-box input, .pix-modal-box textarea {
      color: #000 !important;
      background: #fff !important;
      border: 1px solid #d2d6da !important;
      padding: 10px 12px !important;
      margin-bottom: 10px;
      font-weight: bold;
    }
    .pix-modal-box label {
      color: #e91e63 !important; /* Cor rosa de destaque */
      font-weight: bold !important;
    }
  </style>
</head>

<body class="g-sidenav-show bg-gray-200">
  <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="dashboard.php">
        <span class="ms-1 font-weight-bold text-white">Loja - <?php echo $lojinha; ?></span>
      </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="navbar-collapse w-auto" style="height: auto !important; overflow-y: auto;" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link text-white" href="dashboard.php"><div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">dashboard</i></div><span class="nav-link-text ms-1">Dashboard</span></a></li>
        <li class="nav-item"><a class="nav-link text-white" href="cadastros.php"><div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">assignment_ind</i></div><span class="nav-link-text ms-1">Cadastros</span></a></li>
        <li class="nav-item"><a class="nav-link text-white" href="produtos.php"><div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">local_grocery_store</i></div><span class="nav-link-text ms-1">Produtos</span></a></li>
        <li class="nav-item"><a class="nav-link text-white" href="add_produto.php"><div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">add_circle</i></div><span class="nav-link-text ms-1">Adicionar Produto</span></a></li>
        <li class="nav-item"><a class="nav-link text-white" href="estatisticas.php"><div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">insert_chart</i></div><span class="nav-link-text ms-1">Estatísticas</span></a></li>
        <li class="nav-item"><a class="nav-link text-white" href="bloqueados.php"><div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">key</i></div><span class="nav-link-text ms-1">Bloqueados</span></a></li>
        <li class="nav-item"><a class="nav-link text-white" href="administrador.php"><div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">person</i></div><span class="nav-link-text ms-1">Administrador</span></a></li>
        <li class="nav-item"><a class="nav-link active text-white bg-gradient-primary" href="pix.php"><div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">paid</i></div><span class="nav-link-text ms-1">Config Pix</span></a></li>
        <li class="nav-item"><a class="nav-link text-white" href="config.php"><div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">storefront</i></div><span class="nav-link-text ms-1">Config Loja</span></a></li>
        <li class="nav-item"><a class="nav-link text-white" href="apis.php"><div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">notification_important</i></div><span class="nav-link-text ms-1">Config Apis</span></a></li>
        <li class="nav-item"><a class="nav-link text-white" href="pixel.php"><div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">track_changes</i></div><span class="nav-link-text ms-1">Pixel Facebook</span></a></li>
        <li class="nav-item"><a class="nav-link text-white" href="sair.php"><div class="text-white text-center me-2 d-flex align-items-center justify-content-center"><i class="material-icons opacity-10">login</i></div><span class="nav-link-text ms-1">Sair</span></a></li>
      </ul>
    </div>
  </aside>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Página</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Configuração Pix</li>
          </ol>
          <h6 class="font-weight-bolder mb-0">Gerenciamento de Pagamentos</h6>
        </nav>
        <ul class="navbar-nav justify-content-end">
          <li class="nav-item d-flex align-items-center">
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
    </nav>

    <div class="container-fluid py-4">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-11 col-xl-10">

          <!-- ===== CARD MODO PIX ===== -->
          <div class="card my-4">
            <div class="card-header p-3 position-relative z-index-2">
              <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                <h6 class="text-white text-capitalize ps-3"><i class="material-icons me-2" style="vertical-align:middle">paid</i> Configuração do Pix da Loja</h6>
              </div>
            </div>
            <div class="card-body px-4 pb-4">

              <!-- SELETOR DE MODO -->
              <div class="form-section" style="border-left-color: #6366f1;">
                <h6><i class="material-icons">tune</i> Modo do PIX</h6>
                <p class="text-sm text-muted mb-3">Escolha como o PIX será gerado para os clientes:</p>
                <div class="pix-modo-selector">
                  <div class="pix-modo-btn <?php echo $pix_modo_atual === 'manual' ? 'selected' : ''; ?>" onclick="selecionarModo('manual')">
                    <i class="material-icons">qr_code</i>
                    Chave PIX Manual
                  </div>
                  <div class="pix-modo-btn <?php echo $pix_modo_atual === 'copia_cola' ? 'selected' : ''; ?>" onclick="selecionarModo('copia_cola')">
                    <i class="material-icons">content_copy</i>
                    Copia e Cola (Tabelas)
                  </div>
                  <div class="pix-modo-btn <?php echo $pix_modo_atual === 'gateway' ? 'selected' : ''; ?>" onclick="selecionarModo('gateway')">
                    <i class="material-icons">bolt</i>
                    Gateway Automático
                  </div>
                </div>
                <input type="hidden" id="pix_modo" value="<?php echo htmlspecialchars($pix_modo_atual); ?>">
                <div style="margin-top:8px; font-size:12px; color:#64748b;" id="pix_modo_info">
                  <?php if ($pix_modo_atual === 'manual'): ?>
                    <i class="material-icons" style="font-size:14px;vertical-align:middle">info</i> O PIX será gerado a partir da chave PIX configurada abaixo.
                  <?php elseif ($pix_modo_atual === 'copia_cola'): ?>
                    <i class="material-icons" style="font-size:14px;vertical-align:middle">info</i> O PIX será retirado da tabela ativa de códigos Copia e Cola.
                  <?php else: ?>
                    <i class="material-icons" style="font-size:14px;vertical-align:middle">info</i> O PIX será gerado automaticamente pelo gateway ativado abaixo.
                  <?php endif; ?>
                </div>
              </div>

              <form id="formPix">

                <!-- ===== SEÇÃO PIX ESTÁTICO ===== -->
                <div class="form-section" id="secao_manual" style="<?php echo $pix_modo_atual !== 'manual' ? 'display:none;' : ''; ?>">
                  <h6><i class="material-icons">qr_code</i> Pix Estático (Chave Manual)</h6>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Chave Pix</label>
                        <input type="text" class="form-control" id="chave" value="<?php echo htmlspecialchars($pix['chave'] ?? ''); ?>">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Cidade</label>
                        <input type="text" class="form-control" id="cidade" value="<?php echo htmlspecialchars($pix['cidade'] ?? ''); ?>">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Beneficiário</label>
                        <input type="text" class="form-control" id="beneficiario" value="<?php echo htmlspecialchars($pix['beneficiario'] ?? ''); ?>">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Identificador</label>
                        <input type="text" class="form-control" id="identificador" value="<?php echo htmlspecialchars($pix['identificador'] ?? ''); ?>">
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Descrição do Pagamento</label>
                        <input type="text" class="form-control" id="descricao" value="<?php echo htmlspecialchars($pix['descricao'] ?? ''); ?>">
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ===== SEÇÃO COPIA E COLA (TABELAS) ===== -->
                <div class="form-section" id="secao_copia_cola" style="border-left-color: #0ea5e9; <?php echo $pix_modo_atual !== 'copia_cola' ? 'display:none;' : ''; ?>">
                  <h6><i class="material-icons" style="color:#0ea5e9">content_copy</i> PIX Copia e Cola — Múltiplas Tabelas</h6>
                  <p class="text-sm text-muted mb-3">Gerencie tabelas de códigos PIX. A tabela <strong>ATIVA</strong> será usada para gerar o PIX na loja.</p>

                  <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:12px;">
                    <button type="button" class="btn btn-sm bg-gradient-primary" onclick="pixTabelaNovaModal()">
                      <i class="material-icons text-sm">add</i> Criar Tabela
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                      <i class="material-icons text-sm">refresh</i> Atualizar
                    </button>
                    <div class="ms-auto" style="font-size:12px; color:#64748b;">
                      <i class="material-icons" style="font-size:14px;vertical-align:middle">info</i>
                      A tabela ativa é usada para gerar PIX na loja
                    </div>
                  </div>

                  <div class="pix-tabelas-wrap" id="pixTabelasWrap">
                    <?php if (empty($tabelas_pix)): ?>
                      <div style="text-align:center; padding:30px; color:#94a3b8; font-size:14px;">
                        <i class="material-icons" style="font-size:40px; display:block; margin-bottom:8px;">table_chart</i>
                        Nenhuma tabela criada ainda. Clique em <strong>Criar Tabela</strong> para começar.
                      </div>
                    <?php else: ?>
                      <?php foreach ($tabelas_pix as $tab): ?>
                        <div class="pix-tabela-card <?php echo $tab['ativa'] ? 'ativa' : ''; ?>" id="tabela_card_<?php echo $tab['id']; ?>">
                          <div class="pix-tabela-card-header">
                            <span class="pix-tabela-nome"><?php echo htmlspecialchars($tab['nome']); ?></span>
                            <?php if ($tab['ativa']): ?>
                              <span class="badge-ativa">✓ ATIVA</span>
                            <?php else: ?>
                              <span class="badge-inativa">INATIVA</span>
                            <?php endif; ?>
                            <div style="flex:1"></div>
                            <?php if (!$tab['ativa']): ?>
                              <button type="button" class="btn btn-sm bg-gradient-success" onclick="pixTabelaAtivar(<?php echo $tab['id']; ?>)">
                                <i class="material-icons text-sm">check_circle</i> Usar esta
                              </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="pixTabelaRenomearModal(<?php echo $tab['id']; ?>, '<?php echo addslashes(htmlspecialchars($tab['nome'])); ?>')">
                              <i class="material-icons text-sm">edit</i> Renomear
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="pixTabelaDeletar(<?php echo $tab['id']; ?>, <?php echo (int)$tab['total_codigos']; ?>)">
                              <i class="material-icons text-sm">delete</i> Deletar
                            </button>
                          </div>
                          <div class="pix-tabela-stats">
                            <strong><?php echo (int)$tab['total_codigos']; ?></strong> total &nbsp;|&nbsp;
                            <strong><?php echo (int)$tab['disponiveis']; ?></strong> disponíveis &nbsp;|&nbsp;
                            <strong><?php echo (int)$tab['pagos']; ?></strong> pagos &nbsp;|&nbsp;
                            <strong>R$ <?php echo number_format((float)$tab['valor_pago'], 2, ',', '.'); ?></strong> pagos &nbsp;|&nbsp;
                            <strong>R$ <?php echo number_format((float)$tab['valor_total'], 2, ',', '.'); ?></strong> total
                          </div>
                          <div class="pix-tabela-actions">
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="pixTabelaAdicionarCodigosModal(<?php echo $tab['id']; ?>)">
                              <i class="material-icons text-sm">add</i> Adicionar códigos
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="pixTabelaToggle(<?php echo $tab['id']; ?>)">
                              <i class="material-icons text-sm">list</i> Ver códigos
                            </button>
                          </div>
                          <!-- Área de códigos (carregada via AJAX) -->
                          <div class="pix-codigos-wrap" id="pixTabCodes_<?php echo $tab['id']; ?>">
                            <div style="padding:12px; color:#94a3b8; text-align:center;">
                              <i class="fas fa-spinner fa-spin"></i> Carregando...
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- ===== SEÇÃO GATEWAYS AUTOMÁTICOS ===== -->
                <div id="secao_gateway" style="<?php echo $pix_modo_atual !== 'gateway' ? 'display:none;' : ''; ?>">

                  <!-- PIXGO -->
                  <div class="form-section" style="border-left-color: #00bcd4;">
                    <h6><i class="material-icons" style="color:#00bcd4">bolt</i> Integração PixGo (Automático)</h6>
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="use_pixgo" onchange="exclusivoGateway('pixgo')" <?php echo ($pix['use_pixgo'] ?? 0) == 1 ? 'checked' : ''; ?>>
                      <label class="form-check-label mb-0 ms-3" for="use_pixgo">Ativar Gateway PixGo</label>
                    </div>
                    <div id="pixgo_fields" style="<?php echo ($pix['use_pixgo'] ?? 0) == 1 ? '' : 'display:none;'; ?>">
                      <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">API Key (pk_...)</label>
                        <input type="text" class="form-control" id="pixgo_api_key" value="<?php echo htmlspecialchars($pix['pixgo_api_key'] ?? ''); ?>">
                      </div>
                      <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Webhook Secret (whsec_...)</label>
                        <input type="text" class="form-control" id="pixgo_webhook_secret" value="<?php echo htmlspecialchars($pix['pixgo_webhook_secret'] ?? ''); ?>">
                      </div>
                      <div class="webhook-box">
                        <strong>URL do Webhook para PixGo:</strong>
                        <code id="pixgo_webhook_url"></code>
                        <small class="text-muted">Configure esta URL no painel da PixGo para receber notificações de pagamento.</small>
                      </div>
                    </div>
                  </div>

                  <!-- MERCADO PAGO -->
                  <div class="form-section" style="border-left-color: #4caf50;">
                    <h6><i class="material-icons" style="color:#4caf50">payments</i> Mercado Pago (Automático)</h6>
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="use_mercadopago" onchange="exclusivoGateway('mercadopago')" <?php echo ($pix['use_mercadopago'] ?? 0) == 1 ? 'checked' : ''; ?>>
                      <label class="form-check-label mb-0 ms-3" for="use_mercadopago">Ativar Gateway Mercado Pago</label>
                    </div>
                    <div id="mp_fields" style="<?php echo ($pix['use_mercadopago'] ?? 0) == 1 ? '' : 'display:none;'; ?>">
                      <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Access Token (APP_USR-...)</label>
                        <input type="text" class="form-control" id="mp_access_token" value="<?php echo htmlspecialchars($pix['mp_access_token'] ?? ''); ?>">
                      </div>
                      <div class="webhook-box">
                        <strong>URL do Webhook para Mercado Pago:</strong>
                        <code id="mp_webhook_url"></code>
                        <small class="text-muted">Configure no Painel MP → Webhooks → Evento: Pagamentos.</small>
                      </div>
                    </div>
                  </div>

                  <!-- FREEPAY -->
                  <div class="form-section" style="border-left-color: #fb8c00;">
                    <h6><i class="material-icons" style="color:#fb8c00">account_balance_wallet</i> FreePay (Automático)</h6>
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="use_freepay" onchange="exclusivoGateway('freepay')" <?php echo ($pix['use_freepay'] ?? 0) == 1 ? 'checked' : ''; ?>>
                      <label class="form-check-label mb-0 ms-3" for="use_freepay">Ativar Gateway FreePay</label>
                    </div>
                    <div id="freepay_fields" style="<?php echo ($pix['use_freepay'] ?? 0) == 1 ? '' : 'display:none;'; ?>">
                      <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Public Key</label>
                        <input type="text" class="form-control" id="freepay_public" value="<?php echo htmlspecialchars($pix['freepay_public_key'] ?? ''); ?>">
                      </div>
                      <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Secret Key</label>
                        <input type="text" class="form-control" id="freepay_secret" value="<?php echo htmlspecialchars($pix['freepay_secret_key'] ?? ''); ?>">
                      </div>
                    </div>
                  </div>

                  <!-- CARTHERO -->
                  <div class="form-section" style="border-left-color: #7c3aed;">
                    <h6><i class="material-icons" style="color:#7c3aed">bolt</i> CartHero (Automático)</h6>
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="use_carthero" onchange="exclusivoGateway('carthero')" <?php echo ($pix['use_carthero'] ?? 0) == 1 ? 'checked' : ''; ?>>
                      <label class="form-check-label mb-0 ms-3" for="use_carthero">Ativar Gateway CartHero</label>
                    </div>
                    <div id="carthero_fields" style="<?php echo ($pix['use_carthero'] ?? 0) == 1 ? '' : 'display:none;'; ?>">
                      <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Chave Privada</label>
                        <input type="text" class="form-control" id="carthero_private_key" value="<?php echo htmlspecialchars($pix['carthero_private_key'] ?? ''); ?>">
                      </div>
                      <div class="input-group input-group-outline my-3 is-filled">
                        <label class="form-label">Chave Pública</label>
                        <input type="text" class="form-control" id="carthero_public_key" value="<?php echo htmlspecialchars($pix['carthero_public_key'] ?? ''); ?>">
                      </div>
                      <div class="webhook-box">
                        <strong>URL do Webhook para CartHero:</strong>
                        <code id="carthero_webhook_url"></code>
                        <small class="text-muted">Configure esta URL no painel CartHero → Webhooks → Adicionar Endpoint.</small>
                      </div>
                    </div>
                  </div>

                </div><!-- /secao_gateway -->

                <!-- BOTÃO SALVAR -->
                <div class="mt-4">
                  <button type="button" class="btn bg-gradient-primary w-100 py-3" onclick="atualizar()">
                    <i class="material-icons text-sm">save</i> Salvar Configurações
                  </button>
                </div>
              </form>
            </div>
          </div>

        </div>
      </div>

      <footer class="footer py-4">
        <div class="container-fluid">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6 mb-lg-0 mb-4">
              <div class="copyright text-center text-sm text-muted text-lg-start">
                © <script>document.write(new Date().getFullYear())</script> Loja V1.0
              </div>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </main>

  <!-- ===== MODAIS ===== -->

  <!-- Modal Criar/Renomear Tabela -->
  <div class="pix-modal-overlay" id="modalTabela">
    <div class="pix-modal-box">
      <div class="pix-modal-title" id="modalTabelaTitulo">Criar Tabela PIX</div>
      <div>
        <label style="font-size:13px; font-weight:600; color:#344767; display:block; margin-bottom:6px;">Nome da tabela</label>
        <input type="text" id="pix_tab_nome" class="form-control" placeholder="Ex: Tabela Principal">
        <input type="hidden" id="pix_tab_id" value="0">
      </div>
      <div class="pix-modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="fecharModalTabela()">Cancelar</button>
        <button type="button" class="btn bg-gradient-primary btn-sm" id="btnModalTabelaConfirm" onclick="pixTabelaSalvar()">
          <i class="material-icons text-sm">save</i> Salvar
        </button>
      </div>
    </div>
  </div>

  <!-- Modal Adicionar Códigos -->
  <div class="pix-modal-overlay" id="modalAdicionarCodigos">
    <div class="pix-modal-box">
      <div class="pix-modal-title">Adicionar Códigos PIX</div>
      <input type="hidden" id="pix_add_tabela_id" value="0">
      <div style="margin-bottom:12px;">
        <label style="font-size:13px; font-weight:600; color:#344767; display:block; margin-bottom:6px;">Valor (R$)</label>
        <input type="text" id="pix_add_valor" class="form-control" placeholder="Ex: 199.90">
      </div>
      <div>
        <label style="font-size:13px; font-weight:600; color:#344767; display:block; margin-bottom:6px;">Códigos PIX (um por linha)</label>
        <textarea id="pix_add_codigos" class="form-control" style="height:150px; font-family:monospace; font-size:12px;" placeholder="Cole aqui os códigos PIX, um por linha..."></textarea>
        <small class="text-muted">Cada linha será um código PIX independente.</small>
      </div>
      <div class="pix-modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="fecharModalCodigos()">Cancelar</button>
        <button type="button" class="btn bg-gradient-warning btn-sm" onclick="pixTabelaAdicionarCodigos()">
          <i class="material-icons text-sm">add</i> Adicionar
        </button>
      </div>
    </div>
  </div>

  <!-- TOASTS -->
  <div class="pix-modal-overlay" id="modalQrCode">
    <div class="pix-modal-box" style="max-width:420px;text-align:center;">
      <div class="pix-modal-title">QR Code PIX</div>
      <div id="pixQrCodeCanvas" style="display:flex;justify-content:center;padding:12px;background:#fff;border-radius:8px;"></div>
      <div id="pixQrCodeTexto" style="font-family:monospace;font-size:11px;word-break:break-all;color:#475569;margin:10px 0;"></div>
      <div id="pixQrCodeStatus" style="font-size:12px;color:#64748b;margin-bottom:12px;"></div>
      <div class="pix-modal-footer" style="justify-content:center;">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="fecharModalQrCode()">Fechar</button>
      </div>
    </div>
  </div>

  <div class="position-fixed bottom-1 end-1 z-index-3">
    <div class="toast fade hide p-2 bg-white" role="alert" aria-live="assertive" id="successToast" aria-atomic="true">
      <div class="toast-header border-0">
        <i class="material-icons text-success me-2">check_circle</i>
        <span class="me-auto font-weight-bold">Sucesso</span>
        <i class="fas fa-times text-md ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
      </div>
      <hr class="horizontal dark m-0">
      <div class="toast-body" id="successToastMsg">Configurações salvas com sucesso!</div>
    </div>
    <div class="toast fade hide p-2 bg-white" role="alert" aria-live="assertive" id="errorToast" aria-atomic="true">
      <div class="toast-header border-0">
        <i class="material-icons text-danger me-2">error</i>
        <span class="me-auto font-weight-bold">Erro</span>
        <i class="fas fa-times text-md ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
      </div>
      <hr class="horizontal dark m-0">
      <div class="toast-body" id="errorToastMsg">Ocorreu um erro.</div>
    </div>
  </div>

  <script src="./assets/js/core/popper.min.js"></script>
  <script src="./assets/js/core/bootstrap.min.js"></script>
  <script src="./assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="./assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="./assets/js/jquery.js"></script>
  <script src="./assets/js/material-dashboard.min.js?v=3.0.4"></script>

  <script>
  $(document).ready(function() {
    var currentPath = window.location.pathname;
    var storePath = currentPath.substring(0, currentPath.lastIndexOf('/@SERVIDOR'));
    var baseUrl = window.location.protocol + '//' + window.location.host + storePath;
    $('#pixgo_webhook_url').text(baseUrl + '/api/webhook_pixgo.php');
    $('#mp_webhook_url').text(baseUrl + '/api/webhook_mercadopago.php');
    $('#carthero_webhook_url').text(baseUrl + '/api/webhook_carthero.php');
  });

  // ===== TOAST HELPERS =====
  function showSuccess(msg) {
    $('#successToastMsg').text(msg || 'Operação realizada com sucesso!');
    var t = new bootstrap.Toast(document.getElementById('successToast'));
    t.show();
  }
  function showError(msg) {
    $('#errorToastMsg').text(msg || 'Ocorreu um erro.');
    var t = new bootstrap.Toast(document.getElementById('errorToast'));
    t.show();
  }

  // ===== MODO PIX =====
  function selecionarModo(modo) {
    $('#pix_modo').val(modo);
    $('.pix-modo-btn').removeClass('selected');
    event.currentTarget.classList.add('selected');

    var infos = {
      manual: '<i class="material-icons" style="font-size:14px;vertical-align:middle">info</i> O PIX será gerado a partir da chave PIX configurada abaixo.',
      copia_cola: '<i class="material-icons" style="font-size:14px;vertical-align:middle">info</i> O PIX será retirado da tabela ativa de códigos Copia e Cola.',
      gateway: '<i class="material-icons" style="font-size:14px;vertical-align:middle">info</i> O PIX será gerado automaticamente pelo gateway ativado abaixo.'
    };
    $('#pix_modo_info').html(infos[modo] || '');

    $('#secao_manual').toggle(modo === 'manual');
    $('#secao_copia_cola').toggle(modo === 'copia_cola');
    $('#secao_gateway').toggle(modo === 'gateway');
  }

  // ===== GATEWAY EXCLUSIVO =====
  function exclusivoGateway(ativado) {
    if (ativado === 'pixgo' && $('#use_pixgo').is(':checked')) {
      $('#use_mercadopago, #use_freepay, #use_carthero').prop('checked', false);
    }
    if (ativado === 'mercadopago' && $('#use_mercadopago').is(':checked')) {
      $('#use_pixgo, #use_freepay, #use_carthero').prop('checked', false);
    }
    if (ativado === 'freepay' && $('#use_freepay').is(':checked')) {
      $('#use_pixgo, #use_mercadopago, #use_carthero').prop('checked', false);
    }
    if (ativado === 'carthero' && $('#use_carthero').is(':checked')) {
      $('#use_pixgo, #use_mercadopago, #use_freepay').prop('checked', false);
    }
    $('#pixgo_fields').toggle($('#use_pixgo').is(':checked'));
    $('#mp_fields').toggle($('#use_mercadopago').is(':checked'));
    $('#freepay_fields').toggle($('#use_freepay').is(':checked'));
    $('#carthero_fields').toggle($('#use_carthero').is(':checked'));
  }

  // ===== SALVAR CONFIG GERAL =====
  function atualizar() {
    var dados = {
      painel: "trocapix",
      pix_modo: $("#pix_modo").val(),
      chave: $("#chave").val(),
      cidade: $("#cidade").val(),
      identificador: $("#identificador").val(),
      beneficiario: $("#beneficiario").val(),
      descricao: $("#descricao").val(),
      pixgo_api_key: $("#pixgo_api_key").val(),
      pixgo_webhook_secret: $("#pixgo_webhook_secret").val(),
      use_pixgo: $("#use_pixgo").is(':checked') ? 1 : 0,
      mp_access_token: $("#mp_access_token").val(),
      use_mercadopago: $("#use_mercadopago").is(':checked') ? 1 : 0,
      freepay_public: $("#freepay_public").val(),
      freepay_secret: $("#freepay_secret").val(),
      use_freepay: $("#use_freepay").is(':checked') ? 1 : 0,
      carthero_private_key: $("#carthero_private_key").val(),
      carthero_public_key: $("#carthero_public_key").val(),
      use_carthero: $("#use_carthero").is(':checked') ? 1 : 0,
      use_pix_produto: 0
    };
    var payload = btoa(unescape(encodeURIComponent(JSON.stringify(dados))));
    payload = payload.split("").reverse().join("");
    $.post("api_adm/", { p: payload }, function(res) {
      if (res.trim() == "ok") {
        showSuccess('Configurações salvas com sucesso!');
        setTimeout(function() { window.location.reload(); }, 1500);
      } else {
        showError('Erro ao salvar: ' + res);
        console.error(res);
      }
    });
  }

  // ===== TABELAS PIX COPIA E COLA =====

  function apiAdm(dados, callback) {
    var payload = btoa(unescape(encodeURIComponent(JSON.stringify(dados))));
    payload = payload.split("").reverse().join("");
    $.post("api_adm/", { p: payload }, function(res) {
      try {
        var r = (typeof res === 'object') ? res : JSON.parse(res);
        callback(r);
      } catch(e) {
        if(res.trim() === "ok") callback({ ok: true });
        else callback({ ok: false, error: res.toString() });
      }
    });
  }

  // Abrir modal criar tabela
  function pixTabelaNovaModal() {
    $('#pix_tab_id').val(0);
    $('#pix_tab_nome').val('');
    $('#modalTabelaTitulo').text('Criar Tabela PIX');
    $('#btnModalTabelaConfirm').html('<i class="material-icons text-sm">add</i> Criar');
    $('#modalTabela').addClass('show');
    setTimeout(function(){ $('#pix_tab_nome').focus(); }, 100);
  }

  // Abrir modal renomear tabela
  function pixTabelaRenomearModal(id, nome) {
    $('#pix_tab_id').val(id);
    $('#pix_tab_nome').val(nome);
    $('#modalTabelaTitulo').text('Renomear Tabela');
    $('#btnModalTabelaConfirm').html('<i class="material-icons text-sm">save</i> Salvar');
    $('#modalTabela').addClass('show');
    setTimeout(function(){ $('#pix_tab_nome').focus(); }, 100);
  }

  function fecharModalTabela() {
    $('#modalTabela').removeClass('show');
  }

  // Criar ou renomear tabela
  function pixTabelaSalvar() {
    var nome = $.trim($('#pix_tab_nome').val());
    var id = parseInt($('#pix_tab_id').val()) || 0;
    if (!nome) { showError('Informe o nome da tabela.'); return; }

    var acao = id > 0 ? 'pix_tabela_renomear' : 'pix_tabela_criar';
    apiAdm({ painel: acao, id: id, nome: nome }, function(r) {
      if (r.ok) {
        showSuccess(id > 0 ? 'Tabela renomeada!' : 'Tabela criada!');
        fecharModalTabela();
        setTimeout(function(){ window.location.reload(); }, 1000);
      } else {
        showError(r.error || 'Erro ao salvar tabela.');
      }
    });
  }

  // Ativar tabela
  function pixTabelaAtivar(id) {
    apiAdm({ painel: 'pix_tabela_ativar', id: id }, function(r) {
      if (r.ok) {
        showSuccess('Tabela ativada!');
        setTimeout(function(){ window.location.reload(); }, 800);
      } else {
        showError(r.error || 'Erro ao ativar tabela.');
      }
    });
  }

  // Deletar tabela
  function pixTabelaDeletar(id, total) {
    var msg = 'Deletar esta tabela?';
    if (total > 0) msg = 'Deletar esta tabela e apagar também os ' + total + ' códigos dentro?';
    if (!confirm(msg)) return;
    apiAdm({ painel: 'pix_tabela_deletar', id: id }, function(r) {
      if (r.ok) {
        showSuccess('Tabela deletada!');
        setTimeout(function(){ window.location.reload(); }, 800);
      } else {
        showError(r.error || 'Erro ao deletar tabela.');
      }
    });
  }

  // Abrir modal adicionar códigos
  function pixTabelaAdicionarCodigosModal(tabelaId) {
    $('#pix_add_tabela_id').val(tabelaId);
    $('#pix_add_valor').val('');
    $('#pix_add_codigos').val('');
    $('#modalAdicionarCodigos').addClass('show');
    setTimeout(function(){ $('#pix_add_valor').focus(); }, 100);
  }

  function fecharModalCodigos() {
    $('#modalAdicionarCodigos').removeClass('show');
  }

  // Adicionar códigos à tabela
  function pixTabelaAdicionarCodigos() {
    var tabelaId = parseInt($('#pix_add_tabela_id').val()) || 0;
    var valor = $.trim($('#pix_add_valor').val());
    var codigos = $.trim($('#pix_add_codigos').val());
    if (!valor || !codigos) { showError('Preencha o valor e os códigos.'); return; }
    if (!tabelaId) { showError('Tabela inválida.'); return; }

    apiAdm({ painel: 'pix_tabela_adicionar_codigos', tabela_id: tabelaId, valor: valor, codigos: codigos }, function(r) {
      if (r.ok) {
        showSuccess('Códigos adicionados: ' + (r.total || '') + '!');
        fecharModalCodigos();
        setTimeout(function(){ window.location.reload(); }, 1000);
      } else {
        showError(r.error || 'Erro ao adicionar códigos.');
      }
    });
  }

  // Toggle ver/ocultar códigos
  function pixTabelaToggle(tabelaId) {
    var el = document.getElementById('pixTabCodes_' + tabelaId);
    if (!el) return;
    var visible = el.style.display !== 'none' && el.style.display !== '';
    if (visible) {
      el.style.display = 'none';
      return;
    }
    el.style.display = 'block';
    if (el.dataset.loaded === '1') return;
    pixTabelaCarregarCodigos(tabelaId);
  }

  // Carregar códigos via AJAX
  function pixTabelaCarregarCodigos(tabelaId) {
    var el = document.getElementById('pixTabCodes_' + tabelaId);
    if (!el) return;
    el.innerHTML = '<div style="padding:12px; color:#94a3b8; text-align:center;"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>';

    apiAdm({ painel: 'pix_tabela_listar_codigos', tabela_id: tabelaId, limit: 100, offset: 0 }, function(r) {
      if (!r || !r.ok) {
        el.innerHTML = '<div style="padding:12px; color:#ef4444; text-align:center;">Erro ao carregar códigos.</div>';
        return;
      }
      el.dataset.loaded = '1';
      var items = r.items || [];
      if (items.length === 0) {
        el.innerHTML = '<div style="padding:16px; color:#94a3b8; text-align:center;">Nenhum código cadastrado.</div>';
        return;
      }

      var rows = items.map(function(p) {
        var st = String(p.status_pagamento || '').toUpperCase();
        var codigoShort = String(p.codigo || '');
        var codigoDisplay = codigoShort.length > 60 ? codigoShort.substring(0, 60) + '...' : codigoShort;
        var badge = '<span class="badge-disponivel">' + st + '</span>';
        if (st === 'PAGO') badge = '<span class="badge-pago">PAGO</span>';
        else if (st === 'RESERVADO') badge = '<span class="badge-reservado">RESERVADO</span>';

        var btnQr = '<button type="button" class="btn btn-xs btn-outline-primary" style="font-size:10px;padding:2px 8px;" onclick="pixCodigoMostrarQr(' + p.id + ',' + tabelaId + ',\'' + encodeURIComponent(codigoShort) + '\')"><i class="material-icons" style="font-size:12px">qr_code_2</i> QR</button>';
        var btnConsultar = '<button type="button" class="btn btn-xs btn-outline-info" style="font-size:10px;padding:2px 8px;" onclick="pixCodigoConsultar(' + p.id + ',' + tabelaId + ')"><i class="material-icons" style="font-size:12px">sync</i> Consultar</button>';
        var btnPago = (st !== 'PAGO') ? '<button type="button" class="btn btn-xs btn-outline-success" style="font-size:10px;padding:2px 8px;" onclick="pixCodigoMarcarPago(' + p.id + ',' + tabelaId + ')"><i class="material-icons" style="font-size:12px">check</i> Pago</button>' : '';
        var btnDisponivel = (st === 'RESERVADO') ? '<button type="button" class="btn btn-xs btn-outline-warning" style="font-size:10px;padding:2px 8px;" onclick="pixCodigoTornarDisponivel(' + p.id + ',' + tabelaId + ')"><i class="material-icons" style="font-size:12px">lock_open</i> Tornar disponível</button>' : '';
        var btnDel = '<button type="button" class="btn btn-xs btn-outline-danger" style="font-size:10px;padding:2px 8px;" onclick="pixCodigoDeletar(' + p.id + ',' + tabelaId + ')"><i class="material-icons" style="font-size:12px">delete</i></button>';

        return '<tr>' +
          '<td><strong>R$ ' + parseFloat(p.valor || 0).toFixed(2).replace('.', ',') + '</strong></td>' +
          '<td>' + badge + '</td>' +
          '<td style="font-family:monospace;font-size:11px;color:#475569;word-break:break-all;" title="' + escHtml(codigoShort) + '">' + escHtml(codigoDisplay) + '</td>' +
          '<td style="font-size:11px;color:#94a3b8;white-space:nowrap;">' + escHtml(p.criado_em || '') + '</td>' +
          '<td style="white-space:nowrap;">' + btnQr + ' ' + btnConsultar + ' ' + btnPago + ' ' + btnDisponivel + ' ' + btnDel + '</td>' +
          '</tr>';
      }).join('');

      el.innerHTML = '<table class="pix-codigos-table">' +
        '<thead><tr><th>Valor</th><th>Status</th><th>Código</th><th>Adicionado em</th><th>Ações</th></tr></thead>' +
        '<tbody>' + rows + '</tbody>' +
        '</table>';
    });
  }

  // Liberar manualmente um código reservado antes dos 5 minutos.
  function pixCodigoTornarDisponivel(codigoId, tabelaId) {
    if (!confirm('Tornar este código disponível agora?')) return;
    apiAdm({ painel: 'pix_codigo_tornar_disponivel', codigo_id: codigoId }, function(r) {
      if (r.ok) {
        showSuccess('Código tornado disponível!');
        pixTabelaCarregarCodigos(tabelaId);
        setTimeout(function(){ window.location.reload(); }, 800);
      } else {
        showError(r.error || 'Erro ao tornar o código disponível.');
      }
    });
  }

  // Marcar código como pago
  function pixCodigoMarcarPago(codigoId, tabelaId) {
    if (!confirm('Marcar este código como PAGO?')) return;
    apiAdm({ painel: 'pix_codigo_marcar_pago', codigo_id: codigoId }, function(r) {
      if (r.ok) {
        showSuccess('Código marcado como pago!');
        pixTabelaCarregarCodigos(tabelaId);
        setTimeout(function(){ window.location.reload(); }, 1200);
      } else {
        showError(r.error || 'Erro ao marcar como pago.');
      }
    });
  }

  // Gerar QR Code localmente a partir do código PIX copia e cola.
  function pixCodigoMostrarQr(codigoId, tabelaId, codigo) {
    codigo = decodeURIComponent(codigo || '');
    $('#pixQrCodeCanvas').empty();
    $('#pixQrCodeTexto').text(codigo || '');
    $('#pixQrCodeStatus').text('QR Code gerado localmente a partir deste código copia e cola.');
    $('#modalQrCode').addClass('show');
    if (!codigo) {
      $('#pixQrCodeStatus').text('Este código PIX está vazio.');
      return;
    }
    if (typeof QRCode === 'undefined') {
      $('#pixQrCodeStatus').text('Biblioteca de QR Code não carregada. Verifique a conexão do painel.');
      return;
    }
    new QRCode(document.getElementById('pixQrCodeCanvas'), {
      text: codigo,
      width: 240,
      height: 240,
      correctLevel: QRCode.CorrectLevel.M
    });
  }

  function fecharModalQrCode() {
    $('#modalQrCode').removeClass('show');
  }

  // Consulta o status gravado pelo webhook/gateway ou o status manual interno.
  function pixCodigoConsultar(codigoId, tabelaId) {
    apiAdm({ painel: 'pix_codigo_consultar_status', codigo_id: codigoId }, function(r) {
      if (!r || !r.ok) {
        showError((r && r.error) || 'Não foi possível consultar o status.');
        return;
      }
      var status = String(r.status || 'DISPONIVEL').toUpperCase();
      if (status === 'PAGO' || status === 'PAID' || status === 'COMPLETED' || status === 'APPROVED') {
        showSuccess('Pagamento confirmado. Origem: ' + (r.origem || 'gateway'));
      } else {
        showError('Pagamento ainda não confirmado. Status: ' + status + '. Origem: ' + (r.origem || 'controle interno'));
      }
      pixTabelaCarregarCodigos(tabelaId);
    });
  }

  // Deletar código
  function pixCodigoDeletar(codigoId, tabelaId) {
    if (!confirm('Apagar este código?')) return;
    apiAdm({ painel: 'pix_codigo_deletar', codigo_id: codigoId }, function(r) {
      if (r.ok) {
        showSuccess('Código apagado!');
        pixTabelaCarregarCodigos(tabelaId);
        setTimeout(function(){ window.location.reload(); }, 1200);
      } else {
        showError(r.error || 'Erro ao apagar código.');
      }
    });
  }

  // Fechar modal clicando fora
  $('.pix-modal-overlay').on('click', function(e) {
    if (e.target === this) {
      $(this).removeClass('show');
    }
  });

  // Helper escape HTML
  function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  </script>
</body>
</html>
