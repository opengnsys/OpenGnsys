// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación:2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: tareascomandos.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero tareascomandos.php
// *************************************************************************************************************************************************
var currentidtareacomando=null;
//________________________________________________________________________________________________________
function gestion_comandos(id,o,orden){

	if (o.checked)
		var wurl="../gestores/gestor_tareascomandos.php?opcion="+op_modificacion+"&idtareacomando="+id+"&orden="+orden
	else{
		var resul=window.confirm(TbMsg[0]);
		if (!resul){
			o.checked=true;
			return;
		}
		var wurl="../gestores/gestor_tareascomandos.php?opcion="+op_eliminacion+"&idtareacomando="+id
	}
	currentidtareacomando=id // Guarda identificdor de la tarea
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=wurl; // LLama a la página gestora
}
//________________________________________________________________________________________________________
function ActualizarAccion(id){
	var objorden=document.getElementById("orden-"+id)
	var orden=objorden.value
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
		alert(descrierror)
		return
	}
	alert(TbMsg[2])
	var oTABLE=document.getElementById("TABLACOMANDOS");
	var oTRs=oTABLE.getElementsByTagName('TR')
	for(var i=0;i<oTRs.length;i++){
			if(oTRs[i].getAttribute("id")=='TR-'+currentidtareacomando || oTRs[i].getAttribute("id")=='PAR-'+currentidtareacomando)
					oTRs[i].style.display="none"
	}
}
//________________________________________________________________________________________________________
function resultado_modificar_tareacomando(resul,descrierror,id){
	if (!resul){ // Ha habido algún error en la eliminación
		alert(descrierror)
		return
	}
	alert(TbMsg[3])
}
//________________________________________________________________________________________________________
function ver_parametros(o,sw,ida){
		o=o.parentNode
		o.childNodes[sw].style.display="none"
		sw++
		if(sw>1)sw=0
		o.childNodes[sw].style.display="block"
		while (o.tagName!="TBODY"){
			o=o.parentNode
		}
		var oTRs=o.getElementsByTagName('TR')
		for(var i=0;i<oTRs.length;i++){
			if(oTRs[i].getAttribute("id")=='PAR-'+ida)
				if (oTRs[i].style.display=="none") oTRs[i].style.display="block"
				else
					oTRs[i].style.display="none"
		}
	}
