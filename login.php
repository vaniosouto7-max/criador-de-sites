<?php
require_once __DIR__ . '/saas/bootstrap.php';
if (isset($_SESSION['user_id'])) { header('Location: /dashboard.php'); exit; }
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string)($_POST['email'] ?? ''))); $password = (string)($_POST['password'] ?? ''); $db = control_db();
    $stmt = $db->prepare('SELECT * FROM users WHERE email=? LIMIT 1'); $stmt->bind_param('s', $email); $stmt->execute(); $user = $stmt->get_result()->fetch_assoc();
    if (!$user || !password_verify($password, $user['password_hash'])) $error = 'E-mail ou senha inválidos.';
    else { session_regenerate_id(true); $_SESSION['user_id'] = (int)$user['id']; $_SESSION['login'] = $email; $_SESSION['senha'] = $user['password_hash']; $_SESSION['tempo'] = time() + 86400; $s = $db->prepare('SELECT id FROM tenants WHERE user_id=? ORDER BY id LIMIT 1'); $s->bind_param('i', $user['id']); $s->execute(); $tenant = $s->get_result()->fetch_assoc(); $_SESSION['tenant_id'] = (int)($tenant['id'] ?? 0); header('Location: /dashboard.php'); exit; }
}
page_start('Entrar'); ?><section class="auth card"><span class="eyebrow">BEM-VINDO DE VOLTA</span><h1>Entrar no painel</h1><?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><form method="post"><label class="field">E-mail<input required type="email" name="email"></label><label class="field">Senha<input required type="password" name="password"></label><button class="button" type="submit">Entrar</button></form><p class="muted">Ainda não tem conta? <a href="/register.php">Criar uma</a></p></section><?php page_end(); ?>
