<?php
declare(strict_types=1);

function envv(string $key, ?string $default = null): ?string {
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

function control_db(): mysqli {
    static $db = null;
    if ($db instanceof mysqli) return $db;
    $db = @new mysqli(
        envv('MYSQLHOST', envv('DB_HOST', '127.0.0.1')),
        envv('MYSQLUSER', envv('DB_USER', 'root')),
        envv('MYSQLPASSWORD', envv('DB_PASS', '')),
        envv('MYSQLDATABASE', envv('DB_NAME', 'railway')),
        (int) envv('MYSQLPORT', envv('DB_PORT', '3306'))
    );
    if ($db->connect_errno) {
        http_response_code(500);
        exit('Banco de dados indisponível. Configure MYSQLHOST, MYSQLUSER, MYSQLPASSWORD e MYSQLDATABASE.');
    }
    $db->set_charset('utf8mb4');
    return $db;
}

function ensure_control_schema(): void {
    static $done = false;
    if ($done) return;
    $db = control_db();
    $schema = file_get_contents(__DIR__ . '/control_schema.sql');
    foreach (array_filter(array_map('trim', explode(';', (string) $schema))) as $statement) {
        if (!$db->query($statement)) {
            http_response_code(500);
            exit('Falha ao inicializar o banco de controle.');
        }
    }
    $done = true;
}

function host_name(): string {
    $host = strtolower(trim(explode(':', $_SERVER['HTTP_HOST'] ?? '', 2)[0]));
    return preg_replace('/[^a-z0-9.\-]/', '', $host) ?: 'localhost';
}

function root_domain(): string {
    return strtolower(trim((string) envv('APP_ROOT_DOMAIN', 'localhost')));
}

function is_platform_host(): bool {
    $host = host_name();
    $root = root_domain();
    return $host === 'localhost' || $host === '127.0.0.1' || $host === $root || $host === 'www.' . $root || str_ends_with($host, '.railway.app');
}

function current_tenant(): ?array {
    ensure_control_schema();
    $host = host_name();
    $db = control_db();
    $stmt = $db->prepare('SELECT t.*, d.hostname FROM domains d JOIN tenants t ON t.id=d.tenant_id WHERE d.hostname=? AND d.status="active" LIMIT 1');
    $stmt->bind_param('s', $host);
    $stmt->execute();
    $tenant = $stmt->get_result()->fetch_assoc() ?: null;
    if (!$tenant && isset($_SESSION['tenant_id'])) {
        $id = (int) $_SESSION['tenant_id'];
        $stmt = $db->prepare('SELECT * FROM tenants WHERE id=? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $tenant = $stmt->get_result()->fetch_assoc() ?: null;
    }
    return $tenant;
}

function tenant_db(?array $tenant = null): mysqli {
    $tenant ??= current_tenant();
    if (!$tenant) {
        http_response_code(404);
        exit('Loja não encontrada para este domínio.');
    }
    static $dbs = [];
    $name = $tenant['database_name'];
    if (isset($dbs[$name]) && $dbs[$name] instanceof mysqli) return $dbs[$name];
    $db = @new mysqli(
        envv('MYSQLHOST', envv('DB_HOST', '127.0.0.1')),
        envv('MYSQLUSER', envv('DB_USER', 'root')),
        envv('MYSQLPASSWORD', envv('DB_PASS', '')),
        $name,
        (int) envv('MYSQLPORT', envv('DB_PORT', '3306'))
    );
    if ($db->connect_errno) {
        http_response_code(503);
        exit('A loja ainda está sendo provisionada. Tente novamente em alguns segundos.');
    }
    $db->set_charset('utf8mb4');
    $dbs[$name] = $db;
    return $db;
}

function require_auth(): array {
    ensure_control_schema();
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    if (!$userId) { header('Location: /login.php'); exit; }
    $db = control_db();
    $stmt = $db->prepare('SELECT * FROM users WHERE id=? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user) { session_destroy(); header('Location: /login.php'); exit; }
    return $user;
}

function csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(24));
}

function verify_csrf(): void {
    if (!hash_equals((string) ($_SESSION['csrf'] ?? ''), (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(419); exit('Sessão expirada. Recarregue a página.');
    }
}

function e(?string $value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

function slugify(string $value): string {
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $value));
    return trim($value, '-') ?: 'loja';
}

function provision_tenant_database(string $databaseName): void {
    $db = control_db();
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $databaseName)) throw new RuntimeException('Nome de banco inválido.');
    if (!$db->query('CREATE DATABASE IF NOT EXISTS `' . $databaseName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')) {
        throw new RuntimeException('Não foi possível criar o banco isolado da loja. Verifique se o usuário do MySQL tem permissão CREATE DATABASE.');
    }
    $tenant = @new mysqli(envv('MYSQLHOST', envv('DB_HOST', '127.0.0.1')), envv('MYSQLUSER', envv('DB_USER', 'root')), envv('MYSQLPASSWORD', envv('DB_PASS', '')), $databaseName, (int) envv('MYSQLPORT', envv('DB_PORT', '3306')));
    if ($tenant->connect_errno) throw new RuntimeException('Banco da loja criado, mas não foi possível conectar.');
    $schema = file_get_contents(__DIR__ . '/../DATABASE_UNIFICADO_FINAL.sql');
    foreach (array_filter(array_map('trim', explode(';', (string) $schema))) as $statement) {
        if (!$tenant->query($statement)) throw new RuntimeException('Falha ao instalar o schema da loja.');
    }
    $tenant->close();
}

function page_start(string $title): void { ?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($title) ?> · Criador de Sites</title><link rel="stylesheet" href="/saas/app.css"></head><body><main class="shell"><?php }
function page_end(): void { ?></main></body></html><?php }

ensure_control_schema();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
