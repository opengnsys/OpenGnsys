// ***********************************************************************************************************
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
		var atributos='';
		atributos +='tit='+document.fdatos.titulo.value+RC;
		atributos +='msj='+document.fdatos.mensaje.value+RC;
		document.fdatosejecucion.atributos.value=atributos;

                // Incluimos titulo y mensaje en fdatosejecucion.
                document.fdatosejecucion.appendChild(document.fdatos.titulo);
                document.fdatosejecucion.appendChild(document.fdatos.mensaje);

		filtrado();
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
	function validate (field, validator, msgi) {
		if (!validator (field.value)) {
			alert(TbMsg[msgi]);
			validation_highlight (field);
			return false;
		}
		return true;
	}
	return 	validate (fdatos.titulo, validate_notnull, 1) &&
	 	validate (fdatos.mensaje, validate_notnull, 2) &&
		comprobar_datosejecucion();
}
