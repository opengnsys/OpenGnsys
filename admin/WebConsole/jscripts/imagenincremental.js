// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: imagenincremental.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero imagenincremental.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
function gestion_componente(id,o){
	idimagen=document.fdatos.idimagen.value;
	if (o.checked)
		var wurl="../gestores/gestor_imagenincremental.php?opcion="+op_alta+"&idimagen="+idimagen+"&idsoftincremental="+id;
	else
		var wurl="../gestores/gestor_imagenincremental.php?opcion="+op_eliminacion+"&idimagen="+idimagen+"&idsoftincremental="+id;
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=wurl; // LLama a la página gestora
}
//________________________________________________________________________________________________________
function resultado_insertar_imagenincremental(resul,descrierror,nwid,tablanodo){
	if (!resul){ // Ha habido algún error en la inserción
		alert(descrierror);
		return
	}
	alert(TbMsg[0])
}
//________________________________________________________________________________________________________
function resultado_eliminar_imagenincremental(resul,descrierror,idp,idh){
	if (!resul){ // Ha habido algún error en la eliminación
		alert(descrierror);
		return
	}
	alert(TbMsg[1])
}
