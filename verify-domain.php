<?php
require_once __DIR__ . '/saas/bootstrap.php';
$user = require_auth(); $id = (int)($_GET['id'] ?? 0); $db = control_db();
$stmt = $db->prepare('SELECT d.*, t.user_id FROM domains d JOIN tenants t ON t.id=d.tenant_id WHERE d.id=? AND t.user_id=? LIMIT 1'); $stmt->bind_param('ii', $id, $user['id']); $stmt->execute(); $domain = $stmt->get_result()->fetch_assoc();
if (!$domain) { http_response_code(404); exit('Domínio não encontrado.'); }
$target = strtolower(trim((string)envv('RAILWAY_PUBLIC_DOMAIN', ''))); $records = @dns_get_record($domain['hostname'], DNS_CNAME | DNS_A); $verified = false;
foreach ($records ?: [] as $record) { $value = strtolower(rtrim((string)($record['target'] ?? $record['ip'] ?? ''), '.')); if ($target && ($value === rtrim($target, '.') || str_ends_with($value, rtrim($target, '.')))) $verified = true; }
if ($verified) { $status = 'active'; $stmt = $db->prepare('UPDATE domains SET status=? WHERE id=?'); $stmt->bind_param('si', $status, $id); $stmt->execute(); header('Location: /dashboard.php'); exit; }
page_start('Verificar domínio'); ?><section class="auth card"><span class="eyebrow">VERIFICAÇÃO DNS</span><h1><?= e($domain['hostname']) ?></h1><div class="notice error">Ainda não encontramos o apontamento DNS.</div><p>Crie um registro <strong>CNAME</strong> para <strong><?= e($domain['hostname']) ?></strong> apontando para <strong><?= e($target ?: 'o domínio público do serviço no Railway') ?></strong>. A propagação pode levar alguns minutos.</p><p><a class="button" href="/verify-domain.php?id=<?= $id ?>">Verificar novamente</a> <a class="button ghost" href="/dashboard.php">Voltar</a></p></section><?php page_end(); ?>
