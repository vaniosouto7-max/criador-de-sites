-- ==========================================================
-- BANCO DE DADOS COMPLETO, CONSOLIDADO E ATUALIZADO
-- Inclui as novas tabelas e colunas do Catálogo (Estilo ML)
-- ==========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 1. Tabela de Acesso ao Painel
CREATE TABLE IF NOT EXISTS `acesso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(100) NOT NULL,
  `senha` varchar(100) NOT NULL,
  `acesso` varchar(20) DEFAULT 'ativo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabela de Configurações Gerais da Loja
CREATE TABLE IF NOT EXISTS `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT 'Minha Loja',
  `cor` varchar(50) DEFAULT '#ffe600',
  `cor_botao` varchar(20) DEFAULT '#3483fa',
  `img` varchar(255) DEFAULT 'logo.png',
  `numero` varchar(50) DEFAULT '',
  `zap` varchar(50) DEFAULT '',
  `texto` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabela de Configurações de Pagamento (PIX e Gateways)
CREATE TABLE IF NOT EXISTS `pix` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(255) DEFAULT '',
  `cidade` varchar(255) DEFAULT '',
  `identificador` varchar(255) DEFAULT '',
  `descricao` varchar(255) DEFAULT '',
  `beneficiario` varchar(255) DEFAULT '',
  `use_freepay` tinyint(1) DEFAULT 0,
  `freepay_public_key` text,
  `freepay_secret_key` text,
  `use_mercadopago` tinyint(1) DEFAULT 0,
  `mp_access_token` text,
  `mp_webhook_secret` text,
  `use_pixgo` tinyint(1) DEFAULT 0,
  `pixgo_api_key` text,
  `pixgo_webhook_secret` text,
  `use_carthero` tinyint(1) DEFAULT 0,
  `carthero_private_key` text,
  `carthero_public_key` text,
  `use_pix_produto` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabela de PIX Gerados (Controle e Histórico)
CREATE TABLE IF NOT EXISTS `pixgerado` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) DEFAULT NULL,
  `useragent` text,
  `valor` varchar(20) DEFAULT NULL,
  `produto` varchar(50) DEFAULT NULL,
  `hora` varchar(20) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `variacoes` text,
  `pix_code` text,
  `pix_qr_base64` longtext,
  `mp_transaction_id` varchar(64) DEFAULT '',
  `mp_status` varchar(32) DEFAULT 'pending',
  `freepay_transaction_id` varchar(64) DEFAULT '',
  `freepay_status` varchar(32) DEFAULT 'PENDING',
  `pixgo_payment_id` varchar(64) DEFAULT '',
  `pixgo_status` varchar(32) DEFAULT 'pending',
  `carthero_payment_id` varchar(255) DEFAULT NULL,
  `carthero_status` varchar(50) DEFAULT NULL,
  `data_atualizacao` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabela de Produtos (ATUALIZADA COM destaque_catalogo)
CREATE TABLE IF NOT EXISTS `produto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(100) NOT NULL,
  `tipo_produto` varchar(50) DEFAULT 'generico',
  `categoria` varchar(100) DEFAULT 'Geral',
  `nome` varchar(255) NOT NULL,
  `valor` varchar(20) NOT NULL,
  `valor_original` varchar(20) DEFAULT NULL,
  `img` varchar(255) NOT NULL,
  `img1` varchar(255) DEFAULT NULL,
  `img2` varchar(255) DEFAULT NULL,
  `img3` varchar(255) DEFAULT NULL,
  `img4` varchar(255) DEFAULT NULL,
  `img5` varchar(255) DEFAULT NULL,
  `img6` varchar(255) DEFAULT NULL,
  `oferta` varchar(10) DEFAULT '0',
  `desconto` varchar(10) DEFAULT '0',
  `descricao` text,
  `caracteristicas` text DEFAULT NULL,
  `reviews` longtext DEFAULT NULL,
  `variacoes` longtext DEFAULT NULL,
  `venda` varchar(10) DEFAULT '0',
  `cliques` varchar(10) DEFAULT '0',
  `pix_copia_e_cola` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'ativo',
  `destaque_catalogo` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idx_tipo_produto` (`tipo_produto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabela de Clientes (Captura de Leads)
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `complemento` varchar(255) DEFAULT NULL,
  `destinatario` varchar(255) DEFAULT NULL,
  `quantidade` varchar(10) DEFAULT '1',
  `valortotal` varchar(20) DEFAULT NULL,
  `variacoes` longtext DEFAULT NULL,
  `produto_codigo` varchar(100) DEFAULT NULL,
  `produto_nome` varchar(255) DEFAULT NULL,
  `ip_real` varchar(100) DEFAULT NULL,
  `data_cadastro` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tabela de Visitantes Online (Heartbeat)
CREATE TABLE IF NOT EXISTS `online` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `useragent` longtext DEFAULT NULL,
  `etapa` varchar(50) DEFAULT 'produto',
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `dispositivo` varchar(50) DEFAULT 'desktop',
  `hora` varchar(20) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `situacao` varchar(20) DEFAULT 'ativo',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Tabela de Códigos Pix por Produto (Multi-Pix)
CREATE TABLE IF NOT EXISTS `produto_pix_codigos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_codigo` varchar(100) NOT NULL,
  `pix_codigo` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'disponivel',
  `data_cadastro` timestamp DEFAULT CURRENT_TIMESTAMP,
  `data_uso` datetime DEFAULT NULL,
  `cliente_ip` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_produto_codigo` (`produto_codigo`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Tabela de Vendas Confirmadas
CREATE TABLE IF NOT EXISTS `vendas_confirmadas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_codigo` varchar(100) NOT NULL,
  `cliente_ip` varchar(100) NOT NULL,
  `transaction_id` varchar(128) NOT NULL,
  `valor` varchar(20) NOT NULL DEFAULT '0',
  `data_venda` timestamp DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) NOT NULL DEFAULT 'PAID',
  `gateway` varchar(30) NOT NULL DEFAULT 'mercadopago',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Tabela de Configuração do Facebook Pixel
CREATE TABLE IF NOT EXISTS `facebook_pixel` (
  `id` int(11) NOT NULL,
  `pixel_id` varchar(64) NOT NULL DEFAULT '',
  `ativo` tinyint(1) NOT NULL DEFAULT 0,
  `purchase_event` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Tabelas de Tráfego (Legacy)
CREATE TABLE IF NOT EXISTS `desktop` ( `id` int(11) NOT NULL AUTO_INCREMENT, `ip` varchar(100), `useragent` text, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `mobile` ( `id` int(11) NOT NULL AUTO_INCREMENT, `ip` varchar(100), `useragent` text, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `bot` ( `id` int(11) NOT NULL AUTO_INCREMENT, `ip` varchar(100), `useragent` text, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Tabela de IPs Bloqueados
CREATE TABLE IF NOT EXISTS `bloqueados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(100) NOT NULL,
  `motivo` varchar(255) DEFAULT '',
  `data_bloqueio` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Tabela de APIs (Configurações Extras)
CREATE TABLE IF NOT EXISTS `apis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zap` varchar(255) DEFAULT '',
  `email` varchar(255) DEFAULT '',
  `htmlemail` text,
  `texto1email` text,
  `textozap` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. NOVA TABELA: Banners do Catálogo
CREATE TABLE IF NOT EXISTS `catalogo_banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imagem` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT '',
  `ordem` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- DADOS INICIAIS PADRÃO
-- ==========================================================

INSERT IGNORE INTO `acesso` (`id`, `login`, `senha`, `acesso`) VALUES (1, 'thefake', '12345', 'ativo');
INSERT IGNORE INTO `config` (`id`, `nome`, `cor`, `cor_botao`, `img`, `numero`, `zap`, `texto`) VALUES (1, 'Loja Ester', '#ffe600', '#3483fa', 'logo.png', '5511999999999', '5511999999999', 'Olá, vim pelo site!');
INSERT IGNORE INTO `pix` (`id`, `chave`, `cidade`, `descricao`, `identificador`, `beneficiario`, `use_pix_produto`) VALUES (1, 'SUA_CHAVE_PIX_AQUI', 'SAO PAULO', 'Pagamento Loja', '***', 'Minha Loja', 1);
INSERT IGNORE INTO `facebook_pixel` (`id`, `pixel_id`, `ativo`, `purchase_event`) VALUES (1, '', 0, 1);
INSERT IGNORE INTO `apis` (`id`) VALUES (1);

COMMIT;
