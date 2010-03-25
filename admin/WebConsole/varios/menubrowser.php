<?
// *************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha útima modificación: Marzo-2006
// Nombre del fichero: menubrowser.php
// Descripción : 
//		Este fichero implementa el menu del browser de los clientes
// ****************************************************************************

// Recupera la IP del ordenador que solicita la página
$iph=tomaIP();
if(!empty($iph)){
	Header("Location:../controlacceso.php?iph=".$iph); // Accede a la p�ina de menus
	exit;
}
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="estilos.css">
</HEAD>
<SCRIPT LANGUAGE="JAVASCRIPT">
//________________________________________________________________________________________________________
function confirmar(){
	if (comprobar_datos())
		document.fdatos.submit();
}
//________________________________________________________________________________________________________
function comprobar_datos(){
	if (document.fdatos.usu.value==""){
		alert("Debe introducir un nombre de Usuario")
		document.fdatos.usu.focus()
		return(false)
	}
	if (document.fdatos.pss.value==""){
		alert("Debe introducir una contraseña")
		document.fdatos.pss.focus()
		return(false)
	}
	return(true)
}
//______________________________________________________________________________________________________
function PulsaEnter(oEvento){ 
    var iAscii; 
    if (oEvento.keyCode) 
        iAscii = oEvento.keyCode; 
    else{
		if (oEvento.which) 
			iAscii = oEvento.which; 
		else 
			return false; 
	}
    if (iAscii == 13)  confirmar();
	return true; 
} 
//________________________________________________________________________________________________________
</SCRIPT>
</HEAD>
<BODY>
<DIV style="POSITION:absolute;top:20;left:150">
	<FORM action="controlacceso.php" name="fdatos" method="post"></FORM>
</DIV>
</BODY>
</HTML>
<?
//___________________________________________________________________________________________________
//
// Redupera la ip del cliente web
//___________________________________________________________________________________________________
function tomaIP(){	
	// Se asegura que la pagina se solicita desde la IP que viene
	global $HTTP_SERVER_VARS;
	if ($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"] != "")
		$ipcliente = $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
	else
		$ipcliente = $HTTP_SERVER_VARS["REMOTE_ADDR"];
		
	return($ipcliente);
}
?>
