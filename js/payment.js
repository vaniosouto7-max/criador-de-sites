/*****************
 REQUEST E FUNÇÕES
 BY THE-FAKE CODER
*****************/

setTimeout(()=>{

var jsonString2 = localStorage.getItem("dadosCliente");
var dateCliente = JSON.parse(jsonString2);
document.getElementById("imprimir_nome").innerHTML=dateCliente.nome;
document.getElementById("imprimir_email").innerHTML=dateCliente.email;
document.getElementById("imprimir_cpf").innerHTML=dateCliente.cpf;

var jsonString3 = localStorage.getItem("dadosEndereco");
var dadosEndereco = JSON.parse(jsonString3);

document.getElementById("imprimir_rua").innerHTML=dadosEndereco.endereco+", "+dadosEndereco.numero+" - "+dadosEndereco.bairro;
document.getElementById("imprimir_cidade").innerHTML=dadosEndereco.cidade+" | CEP";
document.getElementById("imprimir_cep").innerHTML=dadosEndereco.cep;

 /*
 $.post('api/',{api:"dadosUsuario"},function(retorno){
 var dados = retorno.trim().split("|");
 if(!dados){
 console.log("Erro, dados vazio => "+retorno);
 }else{
 document.getElementById("imprimir_nome").innerHTML=dados[0];
 document.getElementById("imprimir_email").innerHTML=dados[1];
 document.getElementById("imprimir_cpf").innerHTML=dados[2];
 }
  });

 $.post('api/',{api:"dadosEndereco"},function(retorno2){
 var dados2 = retorno2.trim().split("|");
 if(!dados2){
 console.log("Erro, dados vazio => "+retorno);
 }else{
 document.getElementById("imprimir_rua").innerHTML=dados2[0]+", "+dados2[1]+" - "+dados2[2];
 document.getElementById("imprimir_cidade").innerHTML=dados2[3]+" | CEP";
 document.getElementById("imprimir_cep").innerHTML=dados2[4];
 }
  });
  */
}, 1500);
