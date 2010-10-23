// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
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

var IE=(navigator.appName=="Microsoft Internet Explorer");
var NS=(navigator.appName=="Netscape");
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
	var menuctx=document.getElementById("flo_menu");
	muestra_contextual(ClickX,ClickY,menuctx);
}
//____________________________________________________________________________
//
//	Se ejecuta cada vez que se hace click con el puntero del ratón. Se usa para desmarca
//	cualquier item de menu contextual que estuviese activo
//____________________________________________________________________________
function click_de_raton(e){	
	if(IE){
		botonraton=event.button
		event.returnValue=true;
	}
	if(NS){
		botonraton=e.which;
		e.returnValue=true;
	}
}
//____________________________________________________________________________
//
//	Se ejecuta cada vez que se mueve el puntero del ratón. Se usa para capturar coordenadas
//____________________________________________________________________________
function move_de_raton(e){	
	if(IE){
		ClickX=event.clientX
		ClickY=event.clientY
		event.returnValue=true;
	}
	if(NS){
		ClickX=e.clientX
		ClickY=e.clientY
		e.returnValue=true;
	}
}
//____________________________________________________________________________
//
//	Redirecciona el evento onmousedown a la función de usuario especificada. 
//____________________________________________________________________________
document.onmousedown = click_de_raton; // Redefine el evento onmousedown
document.onmousemove = move_de_raton; // Redefine el evento onmousedown



