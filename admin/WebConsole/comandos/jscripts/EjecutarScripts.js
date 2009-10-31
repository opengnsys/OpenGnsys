// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: EjecutarScripts.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero EjecutarScripts.php (Comandos)
// *************************************************************************************************************************************************
 function confirmar(){
	if (comprobar_datos()){
		if(confirm(TbMsg[0])){
			document.fdatos.pseudocodigo.value=convierte_a_pseudocodigo("#!/bin/bash \n"+document.fdatos.codigo.value);
			document.fdatos.sw_ejya.value=document.fdatosejecucion.sw_ejya.checked
			document.fdatosejecucion.sw_seguimiento.value=document.fdatosejecucion.sw_seguimiento[0].checked;
			document.fdatos.sw_seguimiento.value=document.fdatosejecucion.sw_seguimiento.value
			document.fdatos.sw_mkprocedimiento.value=document.fdatosejecucion.sw_mkprocedimiento.checked
			document.fdatos.nwidprocedimiento.value=document.fdatosejecucion.idprocedimiento.value
			document.fdatos.nwdescriprocedimiento.value=document.fdatosejecucion.nombreprocedimiento.value
			document.fdatos.sw_mktarea.value=document.fdatosejecucion.sw_mktarea.checked
			document.fdatos.nwidtarea.value=document.fdatosejecucion.idtarea.value
			document.fdatos.nwdescritarea.value=document.fdatosejecucion.nombretarea.value
			document.fdatos.submit();
		}
	}
 }
//________________________________________________________________________________________________________
  function convierte_a_pseudocodigo(codi){
	  pseudo=""
	  for(var i=0;i<codi.length;i++)
		  pseudo+=escape(codi.charAt(i));
	 return(pseudo);
  }
//________________________________________________________________________________________________________
  function cancelar(){
	alert(CTbMsg[0]);
	location.href="../nada.php"
  }
//________________________________________________________________________________________________________
  function comprobar_datos(){
	var sw_seguimientocon=document.fdatosejecucion.sw_seguimiento[0].checked;
	var sw_mkprocedimiento=document.fdatosejecucion.sw_mkprocedimiento.checked;
	var sw_mktarea=document.fdatosejecucion.sw_mktarea.checked;
	if (document.fdatos.codigo.value=="" && document.fdatos.userfile.value=="" ) {
		alert(TbMsg[1]);
		document.fdatos.codigo.focus();
		return(false);
	}
	if(!sw_seguimientocon && !sw_mkprocedimiento && !sw_mktarea) return(true)
	if (document.fdatos.titulo.value=="" ) {
		alert(TbMsg[2]);
		document.fdatos.titulo.focus();
		return(false);
	}
	if (document.fdatos.descripcion.value=="" ) {
		alert(TbMsg[3]);
		document.fdatos.descripcion.focus();
		return(false);
	}
	return(comprobar_datosejecucion())
}
