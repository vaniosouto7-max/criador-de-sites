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
    <title>Política de Privacidade - <?php echo htmlspecialchars($nome_loja); ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #222; }
        h2 { color: #444; margin-top: 30px; }
        p { margin-bottom: 15px; }
    </style>
</head>
<body>
    <h1>Política de Privacidade</h1>
    <p>A sua privacidade é importante para nós. É política do <?php echo htmlspecialchars($nome_loja); ?> respeitar a sua privacidade em relação a qualquer informação sua que possamos coletar no site.</p>

    <h2>Coleta de Informações</h2>
    <p>Solicitamos informações pessoais apenas quando realmente precisamos delas para lhe fornecer um serviço. Fazemo-lo por meios justos e legais, com o seu conhecimento e consentimento.</p>

    <h2>Uso de Dados</h2>
    <p>Não compartilhamos informações de identificação pessoal publicamente ou com terceiros, exceto quando exigido por lei ou para processar seu pedido (como transportadoras e processadores de pagamento).</p>

    <h2>Segurança</h2>
    <p>Protegemos os dados armazenados dentro de meios comercialmente aceitáveis para evitar perdas e roubos, bem como acesso, divulgação, cópia, uso ou modificação não autorizados.</p>

    <h2>Cookies</h2>
    <p>Utilizamos cookies para melhorar sua experiência de navegação e entender como você utiliza nosso site.</p>

    <p>O uso continuado de nosso site será considerado como aceitação de nossas práticas em torno de privacidade e informações pessoais. Se você tiver alguma dúvida sobre como lidamos com dados do usuário e informações pessoais, entre em contato conosco.</p>

    <p><a href="index.php">Voltar para a loja</a></p>
</body>
</html>
