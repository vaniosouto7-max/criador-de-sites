/*****************
 REQUEST E FUNÇÕES
 BY THE-FAKE CODER
*****************/
var uf = "";

setTimeout(()=>{
var jsonString2 = localStorage.getItem("dadosCliente");
var dateCliente = JSON.parse(jsonString2);
document.getElementById("imprimir_nome").innerHTML=dateCliente.nome;
document.getElementById("imprimir_email").innerHTML=dateCliente.email;
document.getElementById("imprimir_cpf").innerHTML=dateCliente.cpf;
}, 1500);

/*
setTimeout(()=>{
 $.post('api/',{api:"dadosUsuario"},function(retorno){
 var dados = retorno.trim().split("|");
 if(!dados){
 console.log("Erro, dados vazio => "+retorno);
 }else{
 document.getElementById("imprimir_nome").innerHTML=dados[0];
 document.getElementById("receiver").value=dados[0];
 document.getElementById("imprimir_email").innerHTML=dados[1];
 document.getElementById("imprimir_cpf").innerHTML=dados[2];
 }
  });
}, 1000);
*/

//##########################################

function chk(){

 var c = document.getElementById("zipcode").value;
 var e = document.getElementById("street").value;
 var n = document.getElementById("number").value;
 var b = document.getElementById("neighborhood").value;
 var city = document.getElementById("city").value;
 var co = document.getElementById("address_complement").value;
 var d = document.getElementById("receiver").value;
 var ci = city +"-" +uf;
 if( !c || !e || !n || !b || !d || !ci){

 }else{

	document.getElementById("buttonx").setAttribute("class","btn btn-primary btn-block btn-send sending");

	var dadosEndereco = {cep:c, endereco:e, numero:n, bairro:b, cidade:ci, complemento:co, destinatario:d};
	localStorage.setItem('dadosEndereco', JSON.stringify(dadosEndereco));

	$.post("api/index.php",{api:"address", cep:c, endereco:e, numero:n, bairro:b, cidade:ci, complemento:co, destinatario:d},function(retorno){
	var resp = retorno.trim();

	if(resp=="ok"){
	document.getElementById("buttonx").setAttribute("class","btn btn-primary btn-block btn-send");
	window.location.href="confirm_address.php?produto="+code+"";
	}else{
	console.log("erro => "+retorno);
	}

	});
 }
}

