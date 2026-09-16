<?php
require_once("db.php");

$colunas = [
    "status" => "VARCHAR(50) DEFAULT 'ativo'",
    "valor_original" => "VARCHAR(50) DEFAULT ''",
    "tipo_produto" => "VARCHAR(50) DEFAULT 'generico'",
    "categoria" => "VARCHAR(100) DEFAULT 'Geral'",
    "variacoes" => "TEXT",
    "pix_copia_e_cola" => "TEXT",
    "img1" => "TEXT",
    "img2" => "TEXT",
    "img3" => "TEXT",
    "img4" => "TEXT",
    "img5" => "TEXT",
    "img6" => "TEXT",
    "caracteristicas" => "TEXT",
    "reviews" => "TEXT"
];

echo "Iniciando atualização do banco de dados...\n";

foreach ($colunas as $coluna => $definicao) {
    $sql = "ALTER TABLE produto ADD COLUMN $coluna $definicao";
    if (mysqli_query($conn, $sql)) {
        echo "Coluna '$coluna' adicionada com sucesso!\n";
    } else {
        echo "Coluna '$coluna' já existe ou erro: " . mysqli_error($conn) . "\n";
    }
}

// Garantir que todos os produtos atuais estejam como 'ativo'
mysqli_query($conn, "UPDATE produto SET status = 'ativo' WHERE status IS NULL OR status = ''");

echo "\nBanco de dados atualizado com sucesso!";
?>
