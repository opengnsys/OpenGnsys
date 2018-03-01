// *****************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fichero: servidores.js
// Este fichero implementa las funciones javascript del fichero servidores.php
// *****************************************************************************************************************************************************
//___________________________________________________________________________________________________________
//	
//	Muestra información sobre un servidor rembo
//___________________________________________________________________________________________________________
function muestra_inforRepositorios(){
	reset_contextual(-1,-1);
	var identificador=currentNodo.toma_identificador();
	var descripcionrepositorio=currentNodo.toma_infonodo();
	var whref="../varios/informacion_repositorios.php?idrepositorio="+identificador+"&descripcionrepositorio="+descripcionrepositorio;
	window.open(whref,"frame_contenidos")
}

