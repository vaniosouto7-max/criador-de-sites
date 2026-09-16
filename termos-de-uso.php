<?php
require_once("api/db.php");
$sql = mysqli_query($conn, "SELECT * from config");
$nome_loja = 'Nossa Loja';
while ($sql && $row = mysqli_fetch_array($sql)) { $nome_loja = $row["nome"]; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso - <?php echo htmlspecialchars($nome_loja); ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #222; }
        h2 { color: #444; margin-top: 30px; }
        p { margin-bottom: 15px; }
    </style>
</head>
<body>
    <h1>Termos de Uso</h1>
    <p>Ao acessar o site <?php echo htmlspecialchars($nome_loja); ?>, você concorda em cumprir estes termos de serviço, todas as leis e regulamentos aplicáveis ​​e concorda que é responsável pelo cumprimento de todas as leis locais aplicáveis.</p>

    <h2>Uso de Licença</h2>
    <p>É concedida permissão para baixar temporariamente uma cópia dos materiais (informações ou software) no site <?php echo htmlspecialchars($nome_loja); ?> , apenas para visualização transitória pessoal e não comercial.</p>

    <h2>Isenção de Responsabilidade</h2>
    <p>Os materiais no site da <?php echo htmlspecialchars($nome_loja); ?> são fornecidos 'como estão'. <?php echo htmlspecialchars($nome_loja); ?> não oferece garantias, expressas ou implícitas, e, por este meio, isenta e nega todas as outras garantias.</p>

    <h2>Limitações</h2>
    <p>Em nenhum caso o <?php echo htmlspecialchars($nome_loja); ?> ou seus fornecedores serão responsáveis ​​por quaisquer danos decorrentes do uso ou da incapacidade de usar os materiais em <?php echo htmlspecialchars($nome_loja); ?>.</p>

    <h2>Precisão dos Materiais</h2>
    <p>Os materiais exibidos no site podem incluir erros técnicos, tipográficos ou fotográficos. <?php echo htmlspecialchars($nome_loja); ?> não garante que qualquer material em seu site seja preciso, completo ou atual.</p>

    <p><a href="index.php">Voltar para a loja</a></p>
</body>
</html>
