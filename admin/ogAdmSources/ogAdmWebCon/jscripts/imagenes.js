// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Copyright 2003-2005 -2005  José Manuel Alonso. Todos los derechos reservados.
// Fichero: imagenes.js
// Este fichero implementa las funciones javascript del fichero imagenes.php
// *************************************************************************************************************************************************
//___________________________________________________________________________________________________________
//	
//	Muestra información sobre las imágenes
//___________________________________________________________________________________________________________
function muestra_informacion(){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripcionimagen=currentNodo.toma_infonodo()
	var whref="../varios/informacion_imagenes.php?idimagen="+identificador+"&descripcionimagen="+descripcionimagen
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Muestra formulario para gestionar el software incremental incluido en una imagen
//________________________________________________________________________________________________________
function insertar_imagenincremental(){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripcionimagen=currentNodo.toma_infonodo()
	var whref="../varios/imagenincremental.php?idimagen="+identificador+"&descripcionimagen="+descripcionimagen
	window.open(whref,"frame_contenidos")
}
