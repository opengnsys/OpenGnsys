// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: administradores_centros.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero administradores_centros
// *************************************************************************************************************************************************
function gestion_administrador(idcentro,o){
	idusuario=document.fdatos.idusuario.value
	if (o.checked)
		var opcion=op_alta;
	else
		var opcion=op_eliminacion;

	var wurl="../gestores/gestor_administradores_usuarios.php";
	var prm="opcion="+opcion+"&idcentro="+idcentro+"&idusuario="+idusuario
	CallPage(wurl,prm,"retornoGestion","POST");
}
//______________________________________________________________________________________________________
function retornoGestion(resul){
	if(resul.length>0){
		eval(resul)
	}
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
