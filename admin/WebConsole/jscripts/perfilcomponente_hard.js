// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: perfilcomponente_hard.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero perfilcomponente_hard.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
function gestion_componente(id,o){
	idperfil=document.fdatos.idperfilhard.value;
	if (o.checked)
		var opcion=op_alta;
	else
		var opcion=op_eliminacion;
	var wurl="../gestores/gestor_perfilcomponente_hard.php";
	var prm="opcion="+opcion+"&idperfilhard="+idperfil+"&idhardware="+id;
	CallPage(wurl,prm,"retornoGestion","POST");
}
//______________________________________________________________________________________________________
function retornoGestion(resul){
	if(resul.length>0){
		eval(resul)
	}
}
//________________________________________________________________________________________________________
function resultado_insertar_perfilcomponente_hard(resul,descrierror,nwid){
	if (!resul){ // Ha habido algn error en la inserci�
		alert(descrierror);
		return
	}
	alert(TbMsg[0])
}
//________________________________________________________________________________________________________
function resultado_eliminar_perfilcomponente_hard(resul,descrierror,idh){
	if (!resul){ // Ha habido algn error en la eliminaci�
		alert(descrierror);
		return
	}
	alert(TbMsg[1])
}
