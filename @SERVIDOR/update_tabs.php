<?php
$dir = __DIR__;
$files = [
    "dashboard.php", "produtos.php", "cadastros.php", "add_produto.php",
    "edit_produto.php", "estatisticas.php", "bloqueados.php",
    "administrador.php", "pix.php", "config.php", "apis.php", "pixel.php", "index.php"
];

$success = [];
foreach ($files as $file) {
    $path = $dir . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        // Replace the entire <aside class="sidenav ... </aside> block
        $new_content = preg_replace('/<aside class="sidenav.*?<\/aside>/s', "<?php include 'sidebar.php'; ?>", $content);
        if ($new_content !== null && $new_content !== $content) {
            file_put_contents($path, $new_content);
            $success[] = "Updated $file";
        }
    }
}
echo implode("<br>\n", $success);
if (empty($success)) echo "No files updated.";
?>
