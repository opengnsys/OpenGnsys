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
	function validate (field, validator, msgi) {
		if (!validator (field.value)) {
			alert(TbMsg[msgi]);
			validation_highlight (field);
			return false;
		}
	return true;
	}

        var form = document.fdatos;
        // Si se activa la validación, comprobar que se incluyen los datos adecuados.
        if (form.validacion.options[form.validacion.selectedIndex].value == 1 && (form.paginalogin.value == '' || form.paginavalidacion.value == '')) {
                alert(TbMsg[6]);
                validation_highlight (document.fdatos.paginalogin);
                validation_highlight (document.fdatos.paginavalidacion);
                return(false);
        }

	return 	validate (form.nombreordenador, validate_notnull, 0) &&
		validate (form.ip, validate_ipadress_notnull, 1) &&
		validate (form.mac, validate_macaddress_notnull, 2) &&
		validate (form.idrepositorio, validate_number_notnull, 5) ;
}
