
function chk(){
var nome = document.getElementById("name").value;
var email = document.getElementById("email1").value;
var cpf = document.getElementById("cpf").value;
var celular = document.getElementById("homephone").value;

if( !nome || !email || !cpf || !celular ){

}else if(!email.includes("@")){

}
else if(!email.includes(".")){

}else{
 chkCPF();
}

}



//======================== VALIDA CPF

function chkCPF(){

var cpf = document.getElementById('cpf').value;

if(cpf.length == 14){
var c = cpf.replace('.','');
var cc = c.replace('.','');
var ccc = cc.replace('.','');
var cccc = ccc.replace('-','');

if(valida_cpf(cccc)){

	console.log("valido");
	document.getElementById("grouperror").setAttribute("class","form-group w-62 keep-size");
	document.getElementById("button").setAttribute("class","btn btn-primary btn-block btn-send sending");
	proximo();


}else{

  document.getElementById("cpf").value="";
  document.getElementById("cpf").focus();
  document.getElementById("showcpf").setAttribute("class","holder-input invalid");
  document.getElementById("grouperror").setAttribute("class","form-group w-62 keep-size group-error");
  console.log("invalido");

}
}else{

}
}



function proximo(){
var n = document.getElementById("name").value;
var e = document.getElementById("email1").value;
var c = document.getElementById("cpf").value;
var cell = document.getElementById("homephone").value;

$.post("api/index.php",{api:"checkout", nome:n, email:e, cpf:c, celular:cell},function(retorno){
var resp = retorno.trim();

if(resp=="ok"){


var dadosCliente = {nome:n, email:e, cpf:c, celular:cell};
localStorage.setItem('dadosCliente', JSON.stringify(dadosCliente));

setTimeout(()=>{
	document.getElementById("button").setAttribute("class","btn btn-primary btn-block btn-send");
	var randomx1 = Math.floor(Math.random() * 9999999999999);
	var randomx2 = Math.floor(Math.random() * 999999999);
	window.location.href="address.php?marketingID="+randomx1+"__"+randomx2+"&produto="+code+"";
	}, 1000);


}else{
console.log("erro => "+retorno);
}

});

}








function valida_cpf(cpf){
				  var numeros, digitos, soma, i, resultado, digitos_iguais;
				  digitos_iguais = 1;
				  if (cpf.length < 11)
						return false;
				  for (i = 0; i < cpf.length - 1; i++)
						if (cpf.charAt(i) != cpf.charAt(i + 1))
							  {
							  digitos_iguais = 0;
							  break;
							  }
				  if (!digitos_iguais)
						{
						numeros = cpf.substring(0,9);
						digitos = cpf.substring(9);
						soma = 0;
						for (i = 10; i > 1; i--)
							  soma += numeros.charAt(10 - i) * i;
						resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
						if (resultado != digitos.charAt(0))
							  return false;
						numeros = cpf.substring(0,10);
						soma = 0;
						for (i = 11; i > 1; i--)
							  soma += numeros.charAt(11 - i) * i;
						resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
						if (resultado != digitos.charAt(1))
							  return false;
						return true;
						}
				  else
						return false;
}
