// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación:2003-2004
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero: L_Iconos.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero L_Iconos.php
// *************************************************************************************************************************************************
// Opciones
var Menu=0
var Insertar=1
var Eliminar=2
var Modificar=3
var Consultar=4

// Acciones
var Sin_accion=0
var Insertar_registro=1
var Borrar_registro=2
var Modificar_registro=3
var Leer_registro=4
  
var currentImg=null;

//-----------------------------------------------------------------------------------------------------
function consultar(){
	reset_contextual(-1,-1);
	id=document.fdatos.identificador.value
	var whref
	whref="M_Iconos.php"
	whref=whref + "?opcion=" + Consultar 
	whref=whref + "&accion=" + Leer_registro
	whref=whref + "&idicono=" + id
	window.open(whref,"frame_contenidos")
} 
//-----------------------------------------------------------------------------------------------------
function borrar(){
	reset_contextual(-1,-1);
	id=document.fdatos.identificador.value
	var whref
	whref="M_Iconos.php"
	whref=whref + "?opcion=" + Eliminar 
	whref=whref + "&accion=" + Leer_registro
	whref=whref + "&idicono=" + id
	window.open(whref,"frame_contenidos")
}     
//-----------------------------------------------------------------------------------------------------
function modificar(){
	reset_contextual(-1,-1);
	id=document.fdatos.identificador.value
	var whref
	whref="M_Iconos.php"
	whref=whref + "?opcion=" + Modificar 
	whref=whref + "&accion=" + Leer_registro
	whref=whref + "&idicono=" + id
	window.open(whref,"frame_contenidos")
}
//_____________________________________________________________________________________
//
function menu_contextual(o){
	document.fdatos.identificador.value=o.getAttribute("id")
	if (currentImg!=null)
		currentImg.src="../images/iconos/administrar_off.gif" 
	o.src="../images/iconos/administrar_on.gif";
	currentImg=o;
	var coorX=event.clientX;
	var coorY=event.clientY;
	var menuctx=document.getElementById("flo_menu");
	muestra_contextual(coorX,coorY,menuctx);
}

