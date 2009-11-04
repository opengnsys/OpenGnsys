// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: acciones.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero acciones.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
//	
//	Ejecuta una tarea
//________________________________________________________________________________________________________
function ejecutar_tareas(){
	reset_contextual(-1,-1)
	var resul=window.confirm(TbMsg[0]);
	if (!resul) return
	var identificador=currentNodo.toma_identificador()
	var seguimiento=currentNodo.value
	var wurl="../gestores/gestor_tareas.php?opcion="+op_ejecucion+"&idtarea="+identificador+"&seguimiento="+seguimiento;
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=wurl; // LLama a la página gestora
}
//________________________________________________________________________________________________________
//	
//	Devuelve el resultado de ejecutar una tarea
//	Parámetros:
//			- resul: resultado de la operación( true si tuvo éxito)
//			- descrierror: Descripción del error en su caso
//			- idt: Identificador de la tarea
//________________________________________________________________________________________________________
function resultado_ejecutar_tareas(resul,descrierror,idt){
	if (!resul){ // Ha habido algún error en la ejecución
		alert(descrierror)
		return
	}
	alert(TbMsg[2])
}
//________________________________________________________________________________________________________
//	
//	Ejecuta un trabajo
//________________________________________________________________________________________________________
function ejecutar_trabajos(){
	reset_contextual(-1,-1)
	var resul=window.confirm(TbMsg[1]);
	if (!resul) return
	var identificador=currentNodo.toma_identificador()
	var wurl="../gestores/gestor_trabajos.php?opcion="+op_ejecucion+"&idtrabajo="+identificador;
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=wurl; // LLama a la página gestora
}
//________________________________________________________________________________________________________
//	
//	Devuelve el resultado de ejecutar un trabajo
//	Parámetros:
//			- resul: resultado de la operación( true si tuvo éxito)
//			- descrierror: Descripción del error en su caso
//			- idt: Identificador de la tarea
//________________________________________________________________________________________________________
function resultado_ejecutar_trabajos(resul,descrierror,idt){
	if (!resul){ // Ha habido algún error en la ejecución
		alert(descrierror)
		return
	}
	alert(TbMsg[3])
}
//________________________________________________________________________________________________________
//	
//	Muestra formulario para gestionar los comandos incluidos en un procedimiento 
//________________________________________________________________________________________________________
function gestionar_procedimientocomando(){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripcionprocedimiento=currentNodo.toma_infonodo()
	var whref="../varios/procedimientoscomandos.php?idprocedimiento="+identificador+"&descripcionprocedimiento="+descripcionprocedimiento
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Muestra formulario para gestionar los comandos incluidos en una tarea 
//________________________________________________________________________________________________________
function gestionar_tareacomando(){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripciontarea=currentNodo.toma_infonodo()
	var whref="../varios/tareascomandos.php?idtarea="+identificador+"&descripciontarea="+descripciontarea
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Muestra formulario para gestionar las tareas incluidas en un trabajo 
//________________________________________________________________________________________________________
function insertar_trabajotarea(){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripciontrabajo=currentNodo.toma_infonodo()
	var whref="../varios/trabajostareas.php?idtrabajo="+identificador+"&descripciontrabajo="+descripciontrabajo
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//		Muestra formulario de programaciones para tareas y trabajos 
//________________________________________________________________________________________________________
function programacion(tipoaccion){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripcion=currentNodo.toma_infonodo()
	if(tipoaccion==EJECUCION_TAREA)
		var whref="../varios/programaciones.php?idtarea="+identificador+"&descripciontarea="+descripcion+"&tipoaccion="+EJECUCION_TAREA
	if(tipoaccion==EJECUCION_TRABAJO)
		var whref="../varios/programaciones.php?idtrabajo="+identificador+"&descripciontrabajo="+descripcion+"&tipoaccion="+EJECUCION_TRABAJO
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Muestra los comandos inluidos en un procedimiento 
//________________________________________________________________________________________________________
function ver_comandosprocedimientos(){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripcionprocedimiento=currentNodo.toma_infonodo()
	var whref="../varios/informacion_procedimientos.php?idprocedimiento="+identificador+"&descripcionprocedimiento="+descripcionprocedimiento
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Muestra los comandos incluidos en una tarea 
//________________________________________________________________________________________________________
function ver_comandostareas(){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripciontarea=currentNodo.toma_infonodo()
	var whref="../varios/informacion_tareas.php?idtarea="+identificador+"&descripciontarea="+descripciontarea
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Muestra las tareas y comandos incluidos en un trabajo 
//________________________________________________________________________________________________________
function ver_tareastrabajos(){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripciontrabajo=currentNodo.toma_infonodo()
	var whref="../varios/informacion_trabajos.php?idtrabajo="+identificador+"&descripciontrabajo="+descripciontrabajo
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Muestra el formulario de Menús disponibles para gestionar la inclusión de procedimientos, tareas o trabajos en ellos 
//________________________________________________________________________________________________________
function insertar_accionmenu(tipo){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripcionaccion=currentNodo.toma_infonodo()
	var whref="../varios/accionmenu.php?idtipoaccion="+identificador+"&descripcionaccion="+descripcionaccion+"&tipoaccion="+tipo
	window.open(whref,"frame_contenidos")
}