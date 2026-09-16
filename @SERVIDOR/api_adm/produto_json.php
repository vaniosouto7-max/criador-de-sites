<?php
/** Rotinas de backup e restauração dos produtos em JSON. */
function produto_json_diretorio() {
    $dir = __DIR__ . '/../dados_produtos_json';
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    return $dir;
}

function produto_json_payload($row) {
    unset($row['id']);
    $row['_formato'] = 'produto-json-v1';
    $row['_exportado_em'] = date('c');
    return $row;
}

function sincronizar_produto_json($conn, $codigo) {
    $codigo = mysqli_real_escape_string($conn, (string)$codigo);
    $result = mysqli_query($conn, "SELECT * FROM produto WHERE codigo='$codigo' LIMIT 1");
    if (!$result || !($row = mysqli_fetch_assoc($result))) return false;
    $safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$row['codigo']);
    $payload = produto_json_payload($row);
    return (bool)file_put_contents(produto_json_diretorio() . '/' . $safe . '.json', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE), LOCK_EX);
}

function remover_produto_json($codigo) {
    $safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$codigo);
    $file = produto_json_diretorio() . '/' . $safe . '.json';
    return !file_exists($file) || @unlink($file);
}
?>
