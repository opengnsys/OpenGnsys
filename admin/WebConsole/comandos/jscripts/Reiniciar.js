// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: Reiniciar.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero Reiniciar.php (Comandos)
// *************************************************************************************************************************************************
 function confirmar(){
	if (comprobar_datos()){
		document.fdatosejecucion.submit();
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

