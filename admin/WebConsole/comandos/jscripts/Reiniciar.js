// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla

// Fecha Creación:2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: Reiniciar.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero Reiniciar.php (Comandos)
// *************************************************************************************************************************************************
 function confirmar(){
	if (comprobar_datos()){
		var wurl="./gestores/gestor_Reiniciar.php?" +compone_urlejecucion();
		ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
		ifr.src=wurl; // LLama a la página gestora
	}
 }
//__________________________________________________________________________________________________
  function cancelar(){
	alert(CTbMsg[0]);
	location.href="../nada.php"
}
//__________________________________________________________________________________________________
  function comprobar_datos(){
			return(comprobar_datosejecucion())
}
//__________________________________________________________________________________________________
//	
//	Comprobar retorno
//__________________________________________________________________________________________________
function resultado_reiniciar(resul){
	if (!resul){
		alert(CTbMsg[1]);	
		return
	}
	alert(CTbMsg[2]);	
}
