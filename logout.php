<?php
require_once __DIR__ . '/saas/bootstrap.php';
$_SESSION = []; session_destroy(); header('Location: /'); exit;
