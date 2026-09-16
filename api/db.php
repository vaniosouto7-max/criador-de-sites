<?php
declare(strict_types=1);
require_once __DIR__ . '/../saas/bootstrap.php';

// As páginas legadas continuam usando $conn, mas cada domínio aponta para o
// banco exclusivo do tenant. Credenciais vêm somente de variáveis de ambiente.
$conn = is_platform_host() ? control_db() : tenant_db();
