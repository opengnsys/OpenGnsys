// *****************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fichero: servidores.js
// Este fichero implementa las funciones javascript del fichero servidores.php
// *****************************************************************************************************************************************************
//___________________________________________________________________________________________________________
//	
//	Muestra información sobre un servidor rembo
//___________________________________________________________________________________________________________
function muestra_inforServidorrembo(){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripcionservidor=currentNodo.toma_infonodo()
	var whref="../varios/informacion_servidorrembo.php?idservidorrembo="+identificador+"&descripcionservidor="+descripcionservidor
	window.open(whref,"frame_contenidos")
}
//___________________________________________________________________________________________________________
//	
//	Muestra información sobre un servidor dhcp
//___________________________________________________________________________________________________________
function muestra_inforServidordhcp(){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripcionservidor=currentNodo.toma_infonodo()
	var whref="../varios/informacion_servidordhcp.php?idservidordhcp="+identificador+"&descripcionservidor="+descripcionservidor
	window.open(whref,"frame_contenidos")
}

