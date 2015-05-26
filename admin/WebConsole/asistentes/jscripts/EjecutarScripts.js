﻿// ***********************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: EjecutarScripts.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero EjecutarScripts.php (Comandos)
// ***********************************************************************************************************
 function confirmar(){
	if (comprobar_datos()){
		var RC='@';
		document.fdatosejecucion.atributos.value="scp="+escape(document.fdatos.codigo.value)+RC;
		// Enviar datos si el formulario no tiene método de clonación.
		if (document.fdatos.idmetodo == undefined) {
			document.fdatosejecucion.submit();
		}else{
			// Pedir confirmación si clonación masiva por Unicast.
			if( document.fdatosejecucion.ambito.value!=16 && document.fdatos.idmetodo.value=="UNICAST" || document.fdatos.idmetodo.value=="UNICAST-DIRECT"){
				if (confirm(TbMsg[4]) == true) {
					document.fdatosejecucion.submit();
				} else {
					cancelar();
				}
			}else{
				document.fdatosejecucion.submit();
			}
		}
	}
 }
//________________________________________________________________________________________________________

  function cancelar()
{
	alert(CTbMsg[0]);
	location.href="../nada.php"
 }
//________________________________________________________________________________________________________

  function comprobar_datos()
{
	if (document.fdatos.codigo.value=="") {
		alert(TbMsg[1]);
		document.fdatos.codigo.focus();
		return(false);
	}
	return(comprobar_datosejecucion())
}
