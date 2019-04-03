var sw=0;
var ambito;
//______________________________________________________________________________________________________
function confirmar()
{ 
	ambito=document.fdatos.ambito.value;
 	if(ambito==AMBITO_ORDENADORES){
		var diveco=document.getElementById("diveco"); // Contenedor de salida de código
		diveco.innerHTML="&nbsp"; //
	}
	var Obtcmd=document.getElementById("comando");
	var cmd=Obtcmd.value;
	conmuta("visible");
	sw=1;
	enviaMsg(cmd);
}
//______________________________________________________________________________________________________
function enviaMsg(cmd)
{ 
	var idambito=document.fdatos.idambito.value;
	var litambito=document.fdatos.litambito.value;
	ambito=document.fdatos.ambito.value;

	switch(sw){
		case 1:
			var urlRetorno="resultadocmd";
			var wurl="ecoconsola.php";
			var prm="idambito="+idambito+"&ambito="+ambito+"&comando="+cmd+"&sw="+sw;
			break;
		case 2:
			var urlRetorno="resultadoeco";
			var wurl="ecoconsola.php";
			var prm="idambito="+idambito+"&ambito="+ambito+"&sw="+sw;
			break;
	}
	CallPage(wurl,prm,urlRetorno,"POST");
}
//______________________________________________________________________________________________________
function resultadocmd(resul){
	if(resul==1){ // Si todo va bien se llama a la función que recupera elfichero de eco
		//alert(TbMsg[1])
		if(ambito==AMBITO_ORDENADORES){
			sw=2;
			enviaMsg(null);
		}
	}
	else
		alert(TbMsg[0])
}
//______________________________________________________________________________________________________
function resultadoeco(resul){
	if(resul.length>0){
		var diveco=document.getElementById("diveco");
		diveco.innerHTML="<PRE>"+resul+"</PRE>";
		conmuta("hidden");
	}
	setTimeout("enviaMsg()",5000); 		
}
//______________________________________________________________________________________________________
function conmuta(estado){
	var layavi=document.getElementById("layer_aviso");
	if(layavi)
		layavi.style.visibility=estado;
}
