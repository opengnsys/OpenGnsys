// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: incrementalcomponente_soft.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero incrementalcomponente_soft.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
function gestion_componente(id,o){
	idincremental=document.fdatos.idsoftincremental.value
	if (o.checked)
		var wurl="../gestores/gestor_incrementalcomponente_soft.php?opcion="+op_alta+"&idsoftincremental="+idincremental+"&idsoftware="+id
	else
		var wurl="../gestores/gestor_incrementalcomponente_soft.php?opcion="+op_eliminacion+"&idsoftincremental="+idincremental+"&idsoftware="+id
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=wurl; // LLama a la página gestora
}
//________________________________________________________________________________________________________
function resultado_insertar_incrementalcomponente_soft(resul,descrierror,nwid,tablanodo){
	if (!resul){ // Ha habido algún error en la inserción
		alert(descrierror)
		return
	}
	alert(TbMsg[0])
}
//________________________________________________________________________________________________________
function resultado_eliminar_incrementalcomponente_soft(resul,descrierror,idp,idh){
	if (!resul){ // Ha habido algún error en la eliminación
		alert(descrierror)
		return
	}
	alert(TbMsg[1])
}
