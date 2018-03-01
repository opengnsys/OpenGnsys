// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: acciones.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero acciones.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
//	
//	Ejecuta una tarea
//________________________________________________________________________________________________________

function ejecutar_tareas(op)
{

	reset_contextual(-1,-1); // Oculta menu contextual
	var resul=window.confirm(TbMsg[0]);
	if (!resul) return;
	var idtarea=currentNodo.toma_identificador(); // identificador del ambito
	var tarea=currentNodo.toma_infonodo(); // Nombre de la tarea

	/* LLamada a la gestión */
	var wurl="../gestores/gestor_ejecutaracciones.php";
	var prm="opcion="+op+"&idtarea="+idtarea+"&descritarea="+tarea;

	CallPage(wurl,prm,"retornoGestion","POST");
}
//______________________________________________________________________________________________________

function retornoGestion(resul)
{
	//alert(resul)
	if(resul.length>0)
		eval(resul);
}
//________________________________________________________________________________________________________
//	
//	Devuelve el resultado de ejecutar una tarea
//	Parámetros:
//			- resul: resultado de la operación( true si tuvo éxito)
//			- descrierror: Descripción del error en su caso
//________________________________________________________________________________________________________

function resultado_ejecutar_tareas(resul,descrierror)
{
	if (!resul){ // Ha habido algún error en la ejecución
		alert(descrierror);
		return
	}
	alert(TbMsg[2])
}
//________________________________________________________________________________________________________
//	
//		Muestra formulario de programaciones para tareas y trabajos 
//________________________________________________________________________________________________________

function programacion(tipoaccion)
{
	reset_contextual(-1,-1);
	var identificador=currentNodo.toma_identificador();
	var descripcion=currentNodo.toma_infonodo();
	switch(tipoaccion){
		case EJECUCION_COMANDO:
			var whref="../varios/programaciones.php?idcomando="+identificador+"&descripcioncomando="+descripcion+"&tipoaccion="+EJECUCION_COMANDO;
			break;
		case EJECUCION_TAREA:
			var whref="../varios/programaciones.php?idtarea="+identificador+"&descripciontarea="+descripcion+"&tipoaccion="+EJECUCION_TAREA;
			break;	alert(whref);
	}
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Muestra información de procedimientos y tareas
//________________________________________________________________________________________________________

function informacion_acciones(tipo)
{
	reset_contextual(-1,-1);
	var identificador=currentNodo.toma_identificador();
	var descripcionaccion=currentNodo.toma_infonodo();
	var whref="../varios/informacion_acciones.php?idtipoaccion="+identificador+"&descripcionaccion="+descripcionaccion+"&tipoaccion="+tipo;
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Muestra el formulario de Menús disponibles para gestionar la inclusión de procedimientos, tareas o trabajos en ellos 
//________________________________________________________________________________________________________

function insertar_accionmenu(tipo)
{
	reset_contextual(-1,-1);
	var identificador=currentNodo.toma_identificador();
	var descripcionaccion=currentNodo.toma_infonodo();
	var whref="../varios/accionmenu.php?idtipoaccion="+identificador+"&descripcionaccion="+descripcionaccion+"&tipoaccion="+tipo;
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________

function inclusion_acciones(tipo)
{
	reset_contextual(-1,-1);
	var identificador=currentNodo.toma_identificador();
	var descripcionaccion=currentNodo.toma_infonodo();
	var ambito=currentNodo.toma_atributoNodo("value");
	var whref="../varios/inclusionacciones.php";
	whref+="?idtipoaccion="+identificador+"&descripcionaccion="+descripcionaccion+"&tipoaccion="+tipo+"&ambito="+ambito;
	window.open(whref,"frame_contenidos")
}
