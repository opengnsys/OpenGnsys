// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: perfilcomponente_soft.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero perfilcomponente_soft.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
function gestion_componente(id,o){
	idperfil=document.fdatos.idperfilsoft.value
	if (o.checked)
		var wurl="../gestores/gestor_perfilcomponente_soft.php?opcion="+op_alta+"&idperfilsoft="+idperfil+"&idsoftware="+id
	else
		var wurl="../gestores/gestor_perfilcomponente_soft.php?opcion="+op_eliminacion+"&idperfilsoft="+idperfil+"&idsoftware="+id
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=wurl; // LLama a la página gestora
}
//________________________________________________________________________________________________________
function resultado_insertar_perfilcomponente_soft(resul,descrierror,nwid,tablanodo){
	if (!resul){ // Ha habido algún error en la inserción
		alert(descrierror)
		return
	}
	alert(TbMsg[0])
}
//________________________________________________________________________________________________________
function resultado_eliminar_perfilcomponente_soft(resul,descrierror,idp,idh){
	if (!resul){ // Ha habido algún error en la eliminación
		alert(descrierror)
		return
	}
	alert(TbMsg[1])
}
