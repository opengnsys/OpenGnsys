// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: propiedades_entornos.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero propiedades_entornos.php
// *************************************************************************************************************************************************
var currentHora=null;

var wpadre=window.parent; // Toma frame padre
var farbol=wpadre.frames["frame_arbol"];
//________________________________________________________________________________________________________
//	
//	Cancela la edición 
//________________________________________________________________________________________________________
function cancelar(){
	selfclose();
}
//________________________________________________________________________________________________________
//	
//		Devuelve el resultado de modificar algún dato de un registro
//		Especificaciones:
//		Los parámetros recibidos son:
//			- resul: resultado de la operación de inserción ( true si tuvo éxito)
//			- descrierror: Descripción del error en su caso
//			- lit: Nuevo nombre del grupo
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
//	
//	Confirma la edición 
//________________________________________________________________________________________________________
function confirmar(op){
	if(!comprobar_datos()) 	return
	document.fdatos.submit();
}
//________________________________________________________________________________________________________
//	
//	Comprobar_datos 
//________________________________________________________________________________________________________
function comprobar_datos(){
	if (document.fdatos.ipserveradm.value=="") {
		alert(TbMsg[0]);
		document.fdatos.ipserveradm.focus();
		return(false);
	}
	if (document.fdatos.portserveradm.value=="") {
		alert(TbMsg[1]);
		document.fdatos.portserveradm.focus();
		return(false);
	}
	if (document.fdatos.repositorio.checked==false && document.fdatos.repositorio.checked==false) {
		alert(TbMsg[2]);
		document.fdatos.xrepositorio[0].focus();
		return(false);
	}
	return(true);
}

