<?php

session_start();

require_once("db.php");

require_once('src/PHPMailer.php');
require_once('src/SMTP.php');
require_once('src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

$ip = base64_encode($_SERVER['REMOTE_ADDR']);

$sql = mysqli_query($conn, "SELECT * from clientes WHERE ip='$ip'");
		 while($sql && $row = mysqli_fetch_array($sql)){
			   $emailCliente = $row["email"];
               $nome = $row["nome"];
			   $cell = $row["celular"];
			   $endereco = $row["endereco"];
			   $numero = $row["numero"];
			   $bairro = $row["bairro"];
			   $cidadeXestado = $row["cidade"];
		   	   $cep = $row["cep"];
		}

		$sql = mysqli_query($conn, "SELECT * from apis");
		 while($sql && $row = mysqli_fetch_array($sql)){
			   $emailPHPMAILER = $row["email"];
			   $htmlEmail = $row["htmlemail"];
			   $texto1email = $row["texto1email"];
		}

		$recorte = explode("|", $emailPHPMAILER);
		$MeuEmail = $recorte[0];
		$MinhaSenha = $recorte[1];


		$sql = mysqli_query($conn, "SELECT * from config");
		 while($sql && $row = mysqli_fetch_array($sql)){
			   $loja = $row["nome"];
		}



header('Content-type: text/html; charset=iso-8859-1');

$x = str_replace('$nome', $nome, $htmlEmail);
$x = str_replace('$idCliente', $idCliente, $x);
$x = str_replace('$valores', $valores, $x);
$x = str_replace('$endereco', $endereco, $x);
$x = str_replace('$numero', $numero, $x);
$x = str_replace('$bairro', $bairro, $x);
$x = str_replace('$cidadeXestado', $cidadeXestado, $x);
$x = str_replace('$loja', $loja, $x);
$x = str_replace('$cep', $cep, $x);
$texto = $x;


try {
	//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
	$mail->isSMTP();
	$mail->Host = 'smtp.gmail.com';
	$mail->SMTPAuth = true;
	$mail->Username = "$MeuEmail"; // seu email marketing
	$mail->Password = "$MinhaSenha"; // senha marketing
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
	$mail->Port = 465;

	$mail->setFrom("$MeuEmail", "$loja"); // seu email marketing novamente
	$mail->addAddress("$emailCliente"); //email do cliente

	$mail->isHTML(true);
	$mail->Subject = utf8_decode("$texto1email id:$idCliente");
	$mail->Body = utf8_decode("$texto");
	//$mail->AltBody = 'Chegou o email teste do Canal TI';

	if($mail->send()) {
		echo 'Email enviado com sucesso';
	} else {
		echo 'Email nao enviado';
	}
} catch (Exception $e) {
	echo "Erro ao enviar mensagem: {$mail->ErrorInfo}";
}
##

?>
