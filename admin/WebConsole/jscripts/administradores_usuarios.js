// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: administradores_centros.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero administradores_centros
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________

function gestion_administrador(idcentro,o){
	idusuario=document.fdatos.idusuario.value
	if (o.checked)
		var opcion=op_alta;
	else
		var opcion=op_eliminacion;

	var wurl="../gestores/gestor_administradores_usuarios.php?opcion="+opcion+"&idcentro="+idcentro+"&idusuario="+idusuario
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=wurl; // LLama a la página gestora
}
//________________________________________________________________________________________________________
function resultado_insertar_centros_administradores(resul,descrierror,nwid,tablanodo){
	if (!resul){ // Ha habido algún error en la inserción
		alert(descrierror)
		return
	}
	alert(TbMsg[0])
	
}
//________________________________________________________________________________________________________
function resultado_eliminar_centros_administradores(resul,descrierror,idp,idh){
	if (!resul){ // Ha habido algún error en la eliminación
		alert(descrierror)
		return
	}
	alert(TbMsg[1])
}
