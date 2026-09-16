<?php
// api/schema_check.php - Garantia total de integridade do Banco de Dados
if (!isset($conn) || !$conn) {
    return;
}

// if (!empty($_SESSION['schema_check_ok'])) {
//     return;
// }

// $flag_file = sys_get_temp_dir() . '/loja_schema_' . md5(__FILE__) . '.flag';
// if (file_exists($flag_file) && (time() - filemtime($flag_file) < 86400)) {
//     $_SESSION['schema_check_ok'] = true;
//     return;
// }

// 1. Criar tabelas fundamentais se não existirem
$queries_tables = [
    "CREATE TABLE IF NOT EXISTS `clientes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `nome` varchar(255) DEFAULT NULL,
      `email` varchar(255) DEFAULT NULL,
      `cpf` varchar(30) DEFAULT NULL,
      `celular` varchar(40) DEFAULT NULL,
      `cep` varchar(20) DEFAULT NULL,
      `endereco` varchar(255) DEFAULT NULL,
      `numero` varchar(50) DEFAULT NULL,
      `bairro` varchar(100) DEFAULT NULL,
      `cidade` varchar(100) DEFAULT NULL,
      `estado` varchar(10) DEFAULT NULL,
      `complemento` varchar(255) DEFAULT NULL,
      `destinatario` varchar(255) DEFAULT NULL,
      `referencia` varchar(255) DEFAULT NULL,
      `tipo_endereco` varchar(50) DEFAULT NULL,
      `quantidade` int(11) DEFAULT 1,
      `valortotal` varchar(50) DEFAULT NULL,
      `variacoes` longtext DEFAULT NULL,
      `produto_codigo` varchar(100) DEFAULT NULL,
      `produto_nome` varchar(255) DEFAULT NULL,
      `ip` text DEFAULT NULL,
      `ip_real` varchar(60) DEFAULT NULL,
      `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `pixgerado` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `ip` varchar(45) DEFAULT NULL,
      `useragent` text,
      `valor` varchar(30) DEFAULT NULL,
      `produto` varchar(100) DEFAULT NULL,
      `produto_nome` varchar(255) DEFAULT NULL,
      `cliente_nome` varchar(255) DEFAULT NULL,
      `cliente_telefone` varchar(40) DEFAULT NULL,
      `cliente_cpf` varchar(30) DEFAULT NULL,
      `cliente_email` varchar(255) DEFAULT NULL,
      `status` varchar(50) DEFAULT 'pendente',
      `hora` varchar(20) DEFAULT NULL,
      `time` int(11) DEFAULT NULL,
      `variacoes` longtext DEFAULT NULL,
      `pix_code` longtext DEFAULT NULL,
      `pix_qr_base64` longtext DEFAULT NULL,
      `mp_transaction_id` varchar(64) DEFAULT '',
      `mp_status` varchar(32) DEFAULT 'pending',
      `freepay_transaction_id` varchar(64) DEFAULT '',
      `freepay_status` varchar(32) DEFAULT 'PENDING',
      `pixgo_payment_id` varchar(64) DEFAULT '',
      `pixgo_status` varchar(32) DEFAULT 'pending',
      `carthero_payment_id` varchar(255) DEFAULT NULL,
      `carthero_status` varchar(50) DEFAULT NULL,
      `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
      `data_atualizacao` datetime DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `pix` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `chave` text DEFAULT NULL,
      `tipo_chave` varchar(20) NOT NULL DEFAULT 'aleatoria',
      `beneficiario` text DEFAULT NULL,
      `cidade` text DEFAULT NULL,
      `identificador` text DEFAULT NULL,
      `mp_public_key` text DEFAULT NULL,
      `mp_access_token` text DEFAULT NULL,
      `use_mercadopago` tinyint(1) DEFAULT 0,
      `freepay_token` text DEFAULT NULL,
      `use_freepay` tinyint(1) DEFAULT 0,
      `pixgo_token` text DEFAULT NULL,
      `use_pixgo` tinyint(1) DEFAULT 0,
      `carthero_private_key` text DEFAULT NULL,
      `carthero_public_key` text DEFAULT NULL,
      `use_carthero` tinyint(1) DEFAULT 0,
      `use_pix_produto` tinyint(1) NOT NULL DEFAULT 1,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `online` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `ip` varchar(50) DEFAULT NULL,
      `useragent` longtext DEFAULT NULL,
      `etapa` varchar(50) DEFAULT NULL,
      `cidade` varchar(100) DEFAULT NULL,
      `estado` varchar(50) DEFAULT NULL,
      `dispositivo` varchar(50) DEFAULT NULL,
      `hora` varchar(20) DEFAULT NULL,
      `time` int(11) DEFAULT NULL,
      `situacao` varchar(20) DEFAULT 'ativo',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `bot` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `ip` varchar(50) DEFAULT NULL,
      `useragent` text DEFAULT NULL,
      `hora` varchar(20) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `mobile` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `ip` varchar(50) DEFAULT NULL,
      `useragent` text DEFAULT NULL,
      `hora` varchar(20) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `desktop` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `ip` varchar(50) DEFAULT NULL,
      `useragent` text DEFAULT NULL,
      `hora` varchar(20) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($queries_tables as $q) {
    @mysqli_query($conn, $q);
}

// 2. Garantir linha inicial em pix
$chk_pix = @mysqli_query($conn, "SELECT id FROM `pix` WHERE id='1'");
if (!$chk_pix || mysqli_num_rows($chk_pix) == 0) {
    @mysqli_query($conn, "INSERT INTO `pix` (id, chave, tipo_chave, beneficiario, cidade, identificador) VALUES (1, 'suachavepix', 'aleatoria', 'Loja', 'SAO PAULO', '***')");
}

// 3. Garantir colunas necessárias em tabelas já existentes
$colunas_por_tabela = [
    'clientes' => [
        'variacoes'      => "LONGTEXT DEFAULT NULL",
        'produto_codigo' => "VARCHAR(100) DEFAULT NULL",
        'produto_nome'   => "VARCHAR(255) DEFAULT NULL",
        'data_cadastro'  => "DATETIME DEFAULT CURRENT_TIMESTAMP",
        'ip_real'        => "VARCHAR(60) DEFAULT NULL",
        'estado'         => "VARCHAR(10) DEFAULT NULL",
        'referencia'     => "VARCHAR(255) DEFAULT NULL",
        'tipo_endereco'  => "VARCHAR(50) DEFAULT NULL"
    ],
    'pixgerado' => [
        'variacoes'             => "LONGTEXT DEFAULT NULL",
        'produto_nome'          => "VARCHAR(255) DEFAULT NULL",
        'cliente_nome'          => "VARCHAR(255) DEFAULT NULL",
        'cliente_telefone'      => "VARCHAR(40) DEFAULT NULL",
        'cliente_cpf'           => "VARCHAR(30) DEFAULT NULL",
        'cliente_email'         => "VARCHAR(255) DEFAULT NULL",
        'status'                => "VARCHAR(50) DEFAULT 'pendente'",
        'data_criacao'          => "DATETIME DEFAULT CURRENT_TIMESTAMP",
        'mp_transaction_id'     => "VARCHAR(64) DEFAULT ''",
        'mp_status'             => "VARCHAR(32) DEFAULT 'pending'",
        'freepay_transaction_id'=> "VARCHAR(64) DEFAULT ''",
        'freepay_status'        => "VARCHAR(32) DEFAULT 'PENDING'",
        'pixgo_payment_id'      => "VARCHAR(64) DEFAULT ''",
        'pixgo_status'          => "VARCHAR(32) DEFAULT 'pending'",
        'carthero_payment_id'   => "VARCHAR(255) DEFAULT NULL",
        'carthero_status'       => "VARCHAR(50) DEFAULT NULL",
        'pix_code'              => "LONGTEXT DEFAULT NULL",
        'pix_qr_base64'         => "LONGTEXT DEFAULT NULL"
    ],
    'pix' => [
        'tipo_chave'           => "VARCHAR(20) NOT NULL DEFAULT 'aleatoria'",
        'carthero_private_key' => "TEXT DEFAULT NULL",
        'carthero_public_key'  => "TEXT DEFAULT NULL",
        'use_carthero'         => "TINYINT(1) DEFAULT 0",
        'use_pix_produto'      => "TINYINT(1) NOT NULL DEFAULT 1"
    ],
    'online' => [
        'useragent' => "LONGTEXT DEFAULT NULL"
    ]
];

foreach ($colunas_por_tabela as $tabela => $cols) {
    foreach ($cols as $coluna => $definicao) {
        $chk_col = @mysqli_query($conn, "SHOW COLUMNS FROM `$tabela` LIKE '$coluna'");
        if ($chk_col && mysqli_num_rows($chk_col) == 0) {
            @mysqli_query($conn, "ALTER TABLE `$tabela` ADD COLUMN `$coluna` $definicao");
        }
    }
}

// Salva flag de sucesso
$_SESSION['schema_check_ok'] = true;
// @file_put_contents($flag_file, time());
?>
