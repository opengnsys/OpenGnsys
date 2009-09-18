// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Copyright 2003-2005 -2005  José Manuel Alonso. Todos los derechos reservados.
// Fichero: hardwares.js
// Este fichero implementa las funciones javascript del fichero hardwares.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
//	
//	Muestra formulario para gestionar los componentes incluidos en un perfil hardware 
//________________________________________________________________________________________________________
function insertar_perfilcomponente(){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripcionperfil=currentNodo.toma_infonodo();
	var whref="../varios/perfilcomponente_hard.php?idperfilhard="+identificador+"&descripcionperfil="+descripcionperfil
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Muestra información sobre los perfiles hardware
//________________________________________________________________________________________________________
function muestra_informacion(){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripcionperfil=currentNodo.toma_infonodo();
	var whref="../varios/informacion_perfileshardware.php?idperfil="+identificador+"&descripcionperfil="+descripcionperfil
	window.open(whref,"frame_contenidos")
}