// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Copyright 2003-2005 Jos�Manuel Alonso. Todos los derechos reservados.
// Fecha Creació�:2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: InventarioHardware.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero InventarioHardware.php (Comandos)
// *************************************************************************************************************************************************
 function confirmar(){
	if (comprobar_datos()){
		var wurl="./gestores/gestor_InventarioHardware.php?" +compone_urlejecucion();
		ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
		ifr.src=wurl; // LLama a la p�ina gestora
	}
 }
//________________________________________________________________________________________________________
  function cancelar(){
	alert(CTbMsg[0]);
	location.href="../nada.php"
  }
//________________________________________________________________________________________________________
  function comprobar_datos(){
		return(comprobar_datosejecucion())
}
//________________________________________________________________________________________________________
//	
//	Comprobar retorno
//________________________________________________________________________________________________________
function resultado_inventariohardware(resul){
	if (!resul){
		alert(CTbMsg[1]);	
		return
	}
	alert(CTbMsg[2]);	
}