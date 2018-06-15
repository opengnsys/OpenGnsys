// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: tareascomandos.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero tareascomandos.php
// *************************************************************************************************************************************************
var currentidtareacomando=null;
//________________________________________________________________________________________________________
function gestion_comandos(id,o,orden){
	if (o.checked)
		var opcion=op_modificacion;
	else
		var opcion=op_eliminacion;
	var wurl="../gestores/gestor_tareascomandos.php";
	var prm="opcion="+opcion+"&idtareacomando="+id+"&orden="+orden;
	CallPage(wurl,prm,"retornoGestion","POST");
}
//______________________________________________________________________________________________________
function retornoGestion(resul){
	if(resul.length>0){
		eval(resul);
	}
}
//________________________________________________________________________________________________________
function ActualizarAccion(id){
	var objorden=document.getElementById("orden-"+id);
	var orden=objorden.value;
	if(orden=="" || orden<1){
			alert(TbMsg[1]);
			oorden.focus();
			return
	}
	var ocheckbox=document.getElementById("checkbox-"+id);
	gestion_comandos(id,ocheckbox,orden)
}
//________________________________________________________________________________________________________
function resultado_eliminar_tareacomando(resul,descrierror,idtt){
	if (!resul){ // Ha habido algún error en la eliminación
		alert(descrierror);
		return
	}
	alert(TbMsg[2]);
	var oTABLE=document.getElementById("TABLACOMANDOS");
	var oTRs=oTABLE.getElementsByTagName('TR');
	for(var i=0;i<oTRs.length;i++){
			if(oTRs[i].getAttribute("id")=='TR-'+currentidtareacomando || oTRs[i].getAttribute("id")=='PAR-'+currentidtareacomando)
					oTRs[i].style.display="none"
	}
}
//________________________________________________________________________________________________________
function resultado_modificar_tareacomando(resul,descrierror,id){
	if (!resul){ // Ha habido algún error en la eliminación
		alert(descrierror);
		return
	}
	alert(TbMsg[3])
}
//________________________________________________________________________________________________________
function ver_parametros(o,sw,ida){
		o=o.parentNode;
		o.childNodes[sw].style.display="none";
		sw++;
		if(sw>1)sw=0;
		o.childNodes[sw].style.display="block";
		while (o.tagName!="TBODY"){
			o=o.parentNode
		}
		var oTRs=o.getElementsByTagName('TR');
		for(var i=0;i<oTRs.length;i++){
			if(oTRs[i].getAttribute("id")=='PAR-'+ida)
				if (oTRs[i].style.display=="none") oTRs[i].style.display="block";
				else
					oTRs[i].style.display="none"
		}
	}
