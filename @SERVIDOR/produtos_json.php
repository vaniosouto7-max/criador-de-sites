<?php
session_start();
require_once('../api/db.php');
require_once(__DIR__ . '/api_adm/produto_json.php');

if (!isset($_SESSION['login'], $_SESSION['senha'], $_SESSION['tempo']) || $_SESSION['tempo'] < time()) {
    http_response_code(401);
    exit('Sessão expirada.');
}

function produto_json_ler_payload($file) {
    $raw = (is_string($file) && is_file($file)) ? file_get_contents($file) : (string)$file;
    $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
    $raw = trim($raw);
    if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/is', $raw, $matches)) $raw = trim($matches[1]);
    if ($raw === '') throw new Exception('O arquivo está vazio. Baixe novamente a lista pelo botão "Baixar lista JSON".');
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE && function_exists('iconv')) {
        $raw_corrigido = @iconv('UTF-8', 'UTF-8//IGNORE', $raw);
        if ($raw_corrigido !== false) {
            $data = json_decode($raw_corrigido, true);
            if (json_last_error() === JSON_ERROR_NONE) $raw = $raw_corrigido;
        }
    }
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('JSON inválido: ' . json_last_error_msg());
    if (isset($data['produtos']) && is_array($data['produtos'])) return $data['produtos'];
    if (is_array($data) && (empty($data) || array_keys($data) === range(0, count($data) - 1))) return $data;
    return [$data];
}

$acao = $_GET['acao'] ?? '';
if ($acao === 'importar_chunk' && isset($_GET['id'], $_GET['parte'], $_GET['total'], $_GET['dados'])) {
    $chave = 'produto_json_chunk_' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$_GET['id']);
    if (!isset($_SESSION[$chave])) $_SESSION[$chave] = ['total' => (int)$_GET['total'], 'partes' => []];
    $_SESSION[$chave]['partes'][(int)$_GET['parte']] = (string)$_GET['dados'];
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true]);
    exit;
}
if ($acao === 'importar_finalizar' && isset($_GET['id'])) {
    $chave = 'produto_json_chunk_' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$_GET['id']);
    $lote = $_SESSION[$chave] ?? null;
    unset($_SESSION[$chave]);
    if (!$lote || count($lote['partes']) < $lote['total']) { http_response_code(400); exit('Blocos incompletos.'); }
    ksort($lote['partes']);
    $_POST['arquivo_json_conteudo'] = base64_decode(implode('', $lote['partes']), true);
    $_POST['modo_importacao'] = 'parcial';
    $acao = 'importar_parcial';
}
if ($acao === 'importar_parcial' && isset($_GET['dados'])) {
    $decodificado = base64_decode((string)$_GET['dados'], true);
    if ($decodificado !== false) {
        $_POST['arquivo_json_conteudo'] = $decodificado;
        $_POST['modo_importacao'] = 'parcial';
    }
}
if ($acao === 'baixar') {
    $result = mysqli_query($conn, 'SELECT * FROM produto ORDER BY id ASC');
    $produtos = [];
    while ($result && ($row = mysqli_fetch_assoc($result))) {
        $produtos[] = produto_json_payload($row);
        sincronizar_produto_json($conn, $row['codigo']);
    }
    $payload = ['formato' => 'lista-produtos-json-v1', 'exportado_em' => date('c'), 'produtos' => $produtos];
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="lista_produtos_' . date('Y-m-d_H-i-s') . '.json"');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !in_array($acao, ['importar_parcial', 'importar_finalizar'], true)) { http_response_code(400); exit('Ação inválida.'); }

try {
    $raw_json = null;
    if (isset($_POST['arquivo_json_conteudo'])) {
        $raw_json = (string)$_POST['arquivo_json_conteudo'];
        $file = ['error' => UPLOAD_ERR_OK, 'size' => strlen($raw_json)];
    } elseif (isset($_FILES['arquivo_json'])) {
        $file = $_FILES['arquivo_json'];
    } else {
        // Fallback para hospedagens sem pasta temporária PHP: o navegador envia o JSON cru.
        $raw_json = file_get_contents('php://input');
        $file = ['error' => UPLOAD_ERR_OK, 'size' => strlen((string)$raw_json)];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $erros_upload = [
            UPLOAD_ERR_INI_SIZE => 'O arquivo excede o limite configurado no servidor. Aumente upload_max_filesize/post_max_size ou use uma lista menor.',
            UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o limite permitido pelo formulário.',
            UPLOAD_ERR_PARTIAL => 'O upload foi interrompido antes de terminar.',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi selecionado.',
            UPLOAD_ERR_NO_TMP_DIR => 'A pasta temporária de upload não está disponível no servidor.',
            UPLOAD_ERR_CANT_WRITE => 'O servidor não conseguiu gravar o arquivo enviado.',
            UPLOAD_ERR_EXTENSION => 'Uma extensão do PHP interrompeu o upload.'
        ];
        throw new Exception(($erros_upload[$file['error']] ?? 'Falha no upload do arquivo.') . ' (código ' . (int)$file['error'] . ')');
    }
    if ($file['size'] > 64 * 1024 * 1024) throw new Exception('O arquivo excede o limite de 64 MB.');
    $produtos = produto_json_ler_payload($raw_json !== null ? $raw_json : $file['tmp_name']);
    if (count($produtos) === 0) throw new Exception('Nenhum produto encontrado no JSON.');

    $colunas = ['codigo','nome','valor','valor_original','img','img1','img2','img3','img4','img5','img6','desconto','descricao','caracteristicas','reviews','oferta','cliques','status','categoria','tipo_produto','variacoes','pix_copia_e_cola','produtos_relacionados','ordem'];
    $inseridos = 0; $atualizados = 0; $ignorados = 0;
    mysqli_begin_transaction($conn);
    foreach ($produtos as $produto) {
        if (!is_array($produto) || trim((string)($produto['nome'] ?? '')) === '') { $ignorados++; continue; }
        $codigo = trim((string)($produto['codigo'] ?? ''));
        if ($codigo === '') $codigo = 'imp_' . time() . '_' . bin2hex(random_bytes(4));
        $codigoEsc = mysqli_real_escape_string($conn, $codigo);
        $result = mysqli_query($conn, "SELECT id FROM produto WHERE codigo='$codigoEsc' LIMIT 1");
        $existe = $result && mysqli_num_rows($result) > 0;
        $sets = [];
        foreach ($colunas as $coluna) {
            if ($coluna === 'codigo' || !array_key_exists($coluna, $produto)) continue;
            $valor = $produto[$coluna];
            if (is_array($valor) || is_object($valor)) $valor = json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($valor === null) $valor = '';
            $sets[] = "`$coluna`='" . mysqli_real_escape_string($conn, (string)$valor) . "'";
        }
        $sets[] = "`codigo`='$codigoEsc'";
        if ($existe) {
            if (!mysqli_query($conn, 'UPDATE produto SET ' . implode(',', $sets) . " WHERE codigo='$codigoEsc'")) throw new Exception(mysqli_error($conn));
            $atualizados++;
        } else {
            $campos = []; $valores = [];
            foreach ($colunas as $coluna) {
                if ($coluna === 'codigo' || !array_key_exists($coluna, $produto)) continue;
                $valor = $produto[$coluna];
                if (is_array($valor) || is_object($valor)) $valor = json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if ($valor === null) $valor = '';
                $campos[] = "`$coluna`"; $valores[] = "'" . mysqli_real_escape_string($conn, (string)$valor) . "'";
            }
            array_unshift($campos, '`codigo`'); array_unshift($valores, "'$codigoEsc'");
            if (!mysqli_query($conn, 'INSERT INTO produto (' . implode(',', $campos) . ') VALUES (' . implode(',', $valores) . ')')) throw new Exception(mysqli_error($conn));
            $inseridos++;
        }
        sincronizar_produto_json($conn, $codigo);
    }
    mysqli_commit($conn);
    if (($_POST['modo_importacao'] ?? '') === 'parcial') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'inseridos' => $inseridos, 'atualizados' => $atualizados, 'ignorados' => $ignorados]);
        exit;
    }
    header('Location: produtos.php?importado=1&inseridos=' . $inseridos . '&atualizados=' . $atualizados . '&ignorados=' . $ignorados);
} catch (Throwable $e) {
    if (isset($conn)) @mysqli_rollback($conn);
    if (($_POST['modo_importacao'] ?? '') === 'parcial') {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
        exit;
    }
    header('Location: produtos.php?importado=0&erro=' . rawurlencode($e->getMessage()));
}
exit;
?>
