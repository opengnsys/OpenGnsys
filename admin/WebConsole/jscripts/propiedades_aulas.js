// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: propiedades_aulas.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero propiedades_aulas.php
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
// Devuelve el resultado de insertar un registro
// Especificaciones:
//		Los parámetros recibidos son:
//			- resul: resultado de la operación de inserción (true si tuvo éxito)
//			- descrierror: Descripción del error en su caso
//			- nwid: Identificador asignado al nuevo registro
//			- tablanodo: Tabla nodo generada para el nuevo registro (árbol de un sólo un elemento)
//________________________________________________________________________________________________________
function resultado_insertar_aulas(resul,descrierror,nwid,tablanodo){
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
function resultado_modificar_aulas(resul,descrierror,lit){
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
function resultado_eliminar_aulas(resul,descrierror,id){
	farbol.resultado_eliminar(resul,descrierror,id);
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
	function validate (field, validator, msgi) {
		if (!validator (field.value)) {
			alert(TbMsg[msgi]);
			validation_highlight (field);
			return false;
		}
		return true;
	}

/* HORA DE RESERVA TEMPORALMENTE DESHABILITADA.
	if (parseInt(document.fdatos.horaresevini.value)>parseInt(document.fdatos.horaresevfin.value)) {
		alert(TbMsg[3]);
		validation_highlight (document.fdatos.horaresevini);
		validation_highlight (document.fdatos.horaresevfin);
		return(false);
	}
*/

	var form = document.fdatos;
	// Si se activa la validación, comprobar que se incluyen los datos adecuados.
	if (form.validacion.options[form.validacion.selectedIndex].value == 1 && (form.paginalogin.value == '' || form.paginavalidacion.value == '')) {
		alert(TbMsg[14]);
		validation_highlight (document.fdatos.paginalogin);
		validation_highlight (document.fdatos.paginavalidacion);
		return(false);
	}
	// Validación general de datos del formulario.
	return validate (form.nombreaula, validate_notnull, 0) &&
	       validate (form.puestos, validate_number_notnull, 1) &&
	       validate (form.router, validate_ipadress_notnull, 4) &&
	       validate (form.netmask, validate_ipadress_notnull, 5) && 
	       validate (form.dns, validate_ipadress, 12) && 
	       validate (form.proxy, validate_url, 13) && 
	       validate (form.modp2p, validate_notnull, 6) &&
	       validate (form.timep2p, validate_number_notnull, 7) &&
	       validate (form.modomul, validate_notnull, 8) &&
	       validate (form.ipmul, validate_ipadress_notnull, 9) &&
	       validate (form.pormul, validate_notnull, 10) &&
	       validate (form.velmul, validate_number_notnull, 11);
}

//________________________________________________________________________________________________________
	function vertabla_horas(ohora){
		currentHora=ohora;
		url="../varios/horareser_ventana.php?hora="+ohora.value
		window.open(url,"vh","top=200,left=250,height=120,width=160,scrollbars=no")
	}
//________________________________________________________________________________________________________
	function anade_hora(hora){
		currentHora.value=hora
	}

