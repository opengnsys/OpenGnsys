// ***********************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: EjecutarScripts.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero EjecutarScripts.php (Comandos)
// version 1.1: Para el deploy imagen se envía al comando RestaurarImagen del ogclient.
// autor: Irina Gomez, ETSII Universidad de Sevilla
// fecha: 2016-10-27
// ***********************************************************************************************************
 function confirmar(){
	if (comprobar_datos()){
		var RC='@';
		document.fdatosejecucion.atributos.value="scp="+escape(document.fdatos.codigo.value)+RC;

		// Pedir confirmación si clonación masiva por Unicast.
		if (document.fdatosejecucion.ambito.value!=16 && document.fdatos.idmetodo !== undefined &&
                   (document.fdatos.idmetodo.value=="UNICAST" || document.fdatos.idmetodo.value=="UNICAST-DIRECT")) {
			if (confirm(TbMsg[4]) !== true) {
				cancelar();
				return false;
			}
		}

		// Si deployImagen y no se ha modificado el codigo cambio a RestaurarImagen
		if (document.getElementById("codigo").disabled == true &&  document.fdatos.modo.value == "deployImage") {
			document.fdatosejecucion.idcomando.value = 3;
			document.fdatosejecucion.funcion.value = "RestaurarImagen";
			document.fdatosejecucion.atributos.value = document.fdatos.atrib_restore.value;
		}

		document.fdatosejecucion.submit();
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
