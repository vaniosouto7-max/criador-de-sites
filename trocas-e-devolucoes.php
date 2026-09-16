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
    <title>Trocas e Devoluções - <?php echo htmlspecialchars($nome_loja); ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #222; }
        h2 { color: #444; margin-top: 30px; }
        p { margin-bottom: 15px; }
    </style>
</head>
<body>
    <h1>Política de Trocas e Devoluções</h1>
    <p>Nossa política de trocas e devoluções é baseada no Código de Defesa do Consumidor.</p>

    <h2>Direito de Arrependimento</h2>
    <p>O consumidor tem o direito de desistir da compra em até 7 dias corridos após o recebimento do produto, sem necessidade de justificativa, desde que o produto esteja em sua embalagem original e sem sinais de uso.</p>

    <h2>Produtos com Defeito</h2>
    <p>Caso o produto apresente algum defeito de fabricação, o cliente tem até 30 dias para entrar em contato e solicitar a troca ou reparo.</p>

    <h2>Procedimento</h2>
    <p>Para iniciar um processo de troca ou devolução, entre em contato com nosso suporte através do WhatsApp ou e-mail informado no rodapé do site.</p>

    <p><a href="index.php">Voltar para a loja</a></p>
</body>
</html>
