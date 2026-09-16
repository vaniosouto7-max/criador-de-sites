<?php
session_start();
require_once("../../api/db.php");

if (!isset($_SESSION['login'], $_SESSION['senha'])) {
    die("Acesso negado");
}

$ret = "nenhum arquivo enviado";

if (isset($_FILES['pic']) && $_FILES['pic']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['pic']['name'], PATHINFO_EXTENSION));
    $new_name = md5(time() . rand()) . ".png"; // Forçando PNG como parece ser o padrão do projeto
    $dir = "../../arquivos/logo/";

    // Remove logos antigas para não encher o servidor
    $files = glob($dir . "*");
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    if (move_uploaded_file($_FILES['pic']['tmp_name'], $dir . $new_name)) {
        $ret = "ok";
    } else {
        $ret = "erro ao mover arquivo";
    }
}

if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
    $dir = "../../arquivos/";
    // Save over the existing favicon.png
    if (move_uploaded_file($_FILES['favicon']['tmp_name'], $dir . "favicon.png")) {
        // Copy to assets folder for admin
        copy($dir . "favicon.png", "../assets/img/favicon.png");
        $ret = "ok";
    } else {
        $ret = "erro ao mover favicon";
    }
}

echo $ret;
?>
