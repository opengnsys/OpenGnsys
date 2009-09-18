// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación:2003-2005
// Fecha Última modificación: abril-2005
// Nombre del fichero: ejecutarprocedimientos.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero ejecutarprocedimientos.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
//	
//  Envía un comando para su ejecución o incorporación a procedimientos o tareas
//________________________________________________________________________________________________________
function EjecutarProcedimiento(){
	reset_contextual(-1,-1); // Oculta menu contextual
	var resul=window.confirm(TbMsg[0]);
	if (!resul) return
	var ambito=document.fdatos.ambito.value
	var idambito=document.fdatos.idambito.value
	var idprocedimiento=currentNodo.toma_identificador() // identificador del ambito
	var wurl="../gestores/gestor_ejecutarprocedimientos.php?ambito="+ambito+"&idambito="+idambito+"&idprocedimiento="+idprocedimiento
	var ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=wurl; // LLama a la página gestora
}
//________________________________________________________________________________________________________
//	
//	Devuelve el resultado de ejecutar un procedimiento sobre un ámbito
//	Parámetros:
//			- resul: resultado de la operación( true si tuvo éxito)
//			- descrierror: Descripción del error en su caso
//			- idt: Identificador de la tarea
//________________________________________________________________________________________________________
function resultado_ejecutar_procedimiento(resul,descrierror,idt){
	if (!resul){ // Ha habido algún error en la ejecución
		alert(descrierror)
		return
	}
	alert(TbMsg[1])
}
