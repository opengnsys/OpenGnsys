// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: propiedades_ordenadores.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero propiedades_ordenadores.php
// *************************************************************************************************************************************************
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
// Devuelve el resultado de insertar un registro
// Especificaciones:
//		Los parámetros recibidos son:
//			- resul: resultado de la operación de inserción (true si tuvo éxito)
//			- descrierror: Descripción del error en su caso
//			- nwid: Identificador asignado al nuevo registro
//			- tablanodo: Tabla nodo generada para el nuevo registro (árbol de un sólo un elemento)
//________________________________________________________________________________________________________
function resultado_insertar_ordenadores(resul,descrierror,nwid,tablanodo){
	farbol.resultado_insertar(resul,descrierror,nwid,tablanodo);
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
function resultado_modificar_ordenadores(resul,descrierror,lit){
	farbol.resultado_modificar(resul,descrierror,lit);
	selfclose();
}
//________________________________________________________________________________________________________
//	
//		Devuelve el resultado de eliminar un registro
//		Especificaciones:
//		Los parámetros recibidos son:
//			- resul: resultado de la operación de inserción ( true si tuvo éxito)
//			- descrierror: Descripción del error en su caso
//			- id: Identificador del registro que se quiso modificar
//________________________________________________________________________________________________________
function resultado_eliminar_ordenadores(resul,descrierror,id){
	farbol.resultado_eliminar(resul,descrierror,id);
	selfclose();
}
//________________________________________________________________________________________________________
function selfclose(){
	//document.location.href="../nada.php";
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
	if (document.fdatos.nombreordenador.value=="") {
		alert(TbMsg[0]);
		document.fdatos.nombreordenador.focus();
		return(false);
	}
	if (document.fdatos.ip.value=="") {
		alert(TbMsg[1]);
		document.fdatos.ip.focus();
		return(false);
	}
	if (document.fdatos.mac.value=="") {
		alert(TbMsg[2]);
		document.fdatos.mac.focus();
		return(false);
	}
	var  p=document.fdatos.idrepositorio.selectedIndex
	 if (p==0){  
         alert(TbMsg[5])
         document.forms.fdatos.idrepositorio.focus()
         return(false)
	}
	return(true);
}
