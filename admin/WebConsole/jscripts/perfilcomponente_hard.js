// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: perfilcomponente_hard.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero perfilcomponente_hard.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
function gestion_componente(id,o){
	idperfil=document.fdatos.idperfilhard.value
	if (o.checked)
		var wurl="../gestores/gestor_perfilcomponente_hard.php?opcion="+op_alta+"&idperfilhard="+idperfil+"&idhardware="+id
	else
		var wurl="../gestores/gestor_perfilcomponente_hard.php?opcion="+op_eliminacion+"&idperfilhard="+idperfil+"&idhardware="+id
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=wurl; // LLama a la p�ina gestora
}
//________________________________________________________________________________________________________
function resultado_insertar_perfilcomponente_hard(resul,descrierror,nwid){
	if (!resul){ // Ha habido algn error en la inserci�
		alert(descrierror)
		return
	}
	alert(TbMsg[0])
}
//________________________________________________________________________________________________________
function resultado_eliminar_perfilcomponente_hard(resul,descrierror,idh){
	if (!resul){ // Ha habido algn error en la eliminaci�
		alert(descrierror)
		return
	}
	alert(TbMsg[1])
}
