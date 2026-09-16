<?php
require_once __DIR__ . '/saas/bootstrap.php';
if (isset($_SESSION['user_id'])) { header('Location: /dashboard.php'); exit; }
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $store = trim((string)($_POST['store'] ?? ''));
    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || !$store) $error = 'Preencha os campos e use uma senha com pelo menos 8 caracteres.';
    else {
        $db = control_db(); $slug = slugify($store); $root = root_domain(); $host = $slug . '.' . $root; $database = 'tenant_' . substr(hash('sha256', $slug . microtime(true)), 0, 18);
        try {
            $db->begin_transaction();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO users (name,email,password_hash) VALUES (?,?,?)'); $stmt->bind_param('sss', $name, $email, $hash); $stmt->execute(); $uid = $db->insert_id;
            $stmt = $db->prepare('INSERT INTO tenants (user_id,name,slug,database_name,status) VALUES (?,?,?,?,"provisioning")'); $stmt->bind_param('isss', $uid, $store, $slug, $database); $stmt->execute(); $tid = $db->insert_id;
            $token = bin2hex(random_bytes(20)); $type = 'subdomain'; $status = 'active'; $stmt = $db->prepare('INSERT INTO domains (tenant_id,hostname,type,status,verification_token) VALUES (?,?,?,?,?)'); $stmt->bind_param('issss', $tid, $host, $type, $status, $token); $stmt->execute();
            provision_tenant_database($database);
            $stmt = $db->prepare('UPDATE tenants SET status="active" WHERE id=?'); $stmt->bind_param('i', $tid); $stmt->execute(); $db->commit();
            session_regenerate_id(true); $_SESSION['user_id'] = $uid; $_SESSION['tenant_id'] = $tid; $_SESSION['login'] = $email; $_SESSION['senha'] = $hash; $_SESSION['tempo'] = time() + 86400; header('Location: /dashboard.php'); exit;
        } catch (Throwable $e) { $db->rollback(); $error = $e->getMessage(); }
    }
}
page_start('Criar conta'); ?><section class="auth card"><span class="eyebrow">PRIMEIRO PASSO</span><h1>Crie sua conta</h1><p class="muted">Sua loja será provisionada em um banco separado.</p><?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><form method="post"><label class="field">Seu nome<input required name="name" value="<?= e($_POST['name'] ?? '') ?>"></label><label class="field">E-mail<input required type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>"></label><label class="field">Senha<input required type="password" name="password" minlength="8"></label><label class="field">Nome da loja<input required name="store" placeholder="Minha Loja" value="<?= e($_POST['store'] ?? '') ?>"></label><button class="button" type="submit">Criar conta e loja</button></form><p class="muted">Já tem acesso? <a href="/login.php">Entrar</a></p></section><?php page_end(); ?>
