//______________________________________________________________________________________________________
function enviaping(){ 
	var idambito=document.fdatos.idambito.value;
	var litambito=document.fdatos.litambito.value;
	var wurl="ecoconsola.php";
	var prm="idambito="+idambito+"&ambito="+AMBITO_ORDENADORES+"&sw=2";
	CallPage(wurl,prm,"retorno","POST");
	setTimeout("enviaping();",5000); 
}
//______________________________________________________________________________________________________
function retorno(iHTML){
	if(iHTML.length>0){
		var diveco=document.getElementById("diveco");
		diveco.innerHTML="<PRE>"+iHTML+"</PRE>";
		//setTimeout('conmuta("hidden");',300);
		//conmuta("hidden");
	}
}
//______________________________________________________________________________________________________
function conmuta(estado){
	var layavi=document.getElementById("layer_aviso");
	layavi.style.visibility=estado;
}
