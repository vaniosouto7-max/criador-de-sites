<?php
require_once __DIR__ . '/saas/bootstrap.php';
if (!is_platform_host()) { require __DIR__ . '/legacy_storefront.php'; exit; }
if (isset($_SESSION['user_id'])) { header('Location: /dashboard.php'); exit; }
page_start('Criador de Sites');
?><section class="hero"><div><span class="eyebrow">CRIADOR DE SITES</span><h1>Crie sua loja, conecte seu domínio e publique.</h1><p>Um painel independente para cada cliente, com produtos, checkout e configurações separados desde a origem.</p><div class="actions"><a class="button" href="/register.php">Criar minha conta</a><a class="button ghost" href="/login.php">Entrar</a></div></div><div class="hero-card"><strong>Multi-tenant por padrão</strong><span>Domínio → loja → banco isolado</span><span>Checkout e pagamentos preservados</span><span>Pronto para crescer no Railway</span></div></section><?php page_end(); ?>
