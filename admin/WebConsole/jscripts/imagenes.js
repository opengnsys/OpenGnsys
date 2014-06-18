// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
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
//________________________________________________________________________________________________________
//	
//	Inserta nueva imagen
//________________________________________________________________________________________________________
//
function insertar_imagen(litamb,tipoimg)
{
	reset_contextual(-1,-1) // Oculta menu contextual
	var identificador=currentNodo.toma_identificador()
	var whref="../propiedades/propiedades_imagenes.php?opcion="+op_alta+"&grupoid="+identificador+"&litamb="+litamb+"&tipoimg="+tipoimg
	window.open(whref,"frame_contenidos");
}
//________________________________________________________________________________________________________
//	
//	Modificar datos de imagen
//________________________________________________________________________________________________________
//
function modificar_imagen(tipoimg)
{
	reset_contextual(-1,-1) // Oculta menu contextual
	var identificador=currentNodo.toma_identificador()
	var whref="../propiedades/propiedades_imagenes.php?opcion="+op_modificacion+"&tipoimg="+tipoimg+"&identificador="+identificador
	window.open(whref,"frame_contenidos");
}
//________________________________________________________________________________________________________
//	
//	Eliminar una imagen
//________________________________________________________________________________________________________
//
function eliminar_imagen(tipoimg)
{
	reset_contextual(-1,-1) // Oculta menu contextual
	var identificador=currentNodo.toma_identificador()
	var whref="../propiedades/propiedades_imagenes.php?opcion="+op_eliminacion+"&tipoimg="+tipoimg+"&identificador="+identificador
	window.open(whref,"frame_contenidos");
}
