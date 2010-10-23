// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: propiedades_entornos.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero propiedades_entornos.php
// *************************************************************************************************************************************************

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
//________________________________________________________________________________________________________
function resultado_modificar_entornos(resul,descrierror){
	if(resul>0)
		alert(CTbMsg[5]);
	else
		alert(descrierror);
	selfclose();
}
//________________________________________________________________________________________________________
function selfclose(){
	document.location.href="../nada.php";
}
//________________________________________________________________________________________________________
//	
//	Esta función desabilita la marca de un checkbox en opcion "bajas"
//________________________________________________________________________________________________________
 function desabilita(o) {
	var b
    b=o.checked
    o.checked=!b
 }
//________________________________________________________________________________________________________
//	
//	Confirma la edición 
//________________________________________________________________________________________________________
function confirmar(op){
	if (op!=op_eliminacion){
		if(!comprobar_datos()) return;
	}
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
	return(true);
}

