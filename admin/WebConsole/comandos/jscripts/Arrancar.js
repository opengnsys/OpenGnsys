// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: Arrancar.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero Arrancar.php (Comandos)
// *************************************************************************************************************************************************
 function confirmar(){
	if (comprobar_datos()){
		document.fdatosejecucion.submit();
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

