// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fichero: menus.js
// Este fichero implementa las funciones javascript del fichero menus.php
// *************************************************************************************************************************************************
//___________________________________________________________________________________________________________
//	
//	Muestra información sobre los menús y gestiona los items de cada uno de ellos
//___________________________________________________________________________________________________________
function muestra_informacion(){
	reset_contextual(-1,-1);
	var identificador=currentNodo.toma_identificador();
	var descripcionperfil=currentNodo.toma_infonodo();
	var whref="../varios/informacion_menus.php?idmenu="+identificador+"&descripcionmenu="+descripcionperfil;
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Gestiona los items incluidos en un menu
//________________________________________________________________________________________________________
function gestionar_items(){
	reset_contextual(-1,-1);
	var identificador=currentNodo.toma_identificador();
	var descripcionmenu=currentNodo.toma_infonodo();
	var whref="../varios/accionmenu.php?idmenu="+identificador+"&descripcionmenu="+descripcionmenu;
	window.open(whref,"frame_contenidos")
}

