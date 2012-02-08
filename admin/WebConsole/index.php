<?php
// *****************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Agosto-2010
// Nombre del fichero: acceso.php
// Descripción: Pantalla principal de acceso a la consola de administración web.
// Versión 1.0.3: Unificación de ficheros e internacionalización.
// Autor: Ramón Gómez - ETSII, Universidad de Sevilla
// Fecha: 2012-02-07
// *****************************************************************************
if(isset($_SESSION)){ 	// Si existe algua sesión ...
	session_unset(); // Elimina variables
	session_destroy(); // Destruye sesión
}
# Cambiar a HTTPS
if (empty ($_SERVER["HTTPS"])) {
	header ("Location: https://".$_SERVER["SERVER_NAME"].$_SERVER["PHP_SELF"]);
	exit (0);
}

// Cargar configuración.
include_once("controlacceso.php");
include_once("./includes/CreaComando.php");
include_once("./clases/AdoPhp.php");
include_once("./includes/HTMLSELECT.php");

// Valores por defecto.
$herror=0;
$idcentro="";

// Control de errores.
if (isset($_GET["herror"])) $herror=$_GET["herror"]; 
if (isset($_POST["herror"])) $herror=$_POST["herror"]; 
// Idioma.
if (isset($_GET["idi"])) $parmidi=$_GET["idi"]; 
if (isset($_POST["idi"])) $parmidi=$_POST["idi"]; 
if (!empty ($parmidi) and file_exists ("idiomas/php/$parmidi/acceso_$parmidi.php")) {
	$idi=$parmidi;
}
include ("idiomas/php/$idi/acceso_$idi.php");

$cmd=CreaComando($cnx); // Crea objeto comando 
if (!$cmd)
   	die($TbMsg["ACCESS_ERROR"]);

?>
<html>
<title><?php echo $TbMsg["ACCESS_TITLE"];?></title>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<link rel="stylesheet" type="text/css" href="estilos.css">
<script language="javascript">
//______________________________________________________________________________
function confirmar(){
	if (comprobar_datos())
		document.fdatos.submit();
}
//______________________________________________________________________________
function comprobar_datos(){
	if (document.fdatos.usu.value==""){
		<?php echo 'alert("'.$TbMsg["ACCESS_NOUSER"].'");' ?>
		document.fdatos.usu.focus()
		return(false)
	}
	if (document.fdatos.pss.value==""){
		<?php echo 'alert("'.$TbMsg["ACCESS_NOPASS"].'");' ?>
		document.fdatos.pss.focus()
		return(false)
	}
	var  p=document.fdatos.idcentro.selectedIndex
	if (p==0){  
		<?php echo 'var res=confirm("'.$TbMsg["ACCESS_NOUNIT"].'");' ?>
	if(!res)
		return(false)
	}
	return(true)
}
//______________________________________________________________________________
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
//______________________________________________________________________________
</script>
</head>

<body>
<div class="acceso">
<h1> <?php echo $TbMsg["ACCESS_HEADING"]; ?> </h1>
<h2> <?php echo $TbMsg["ACCESS_SUBHEAD"]; ?> </h2>
<form action="controlpostacceso.php" name="fdatos" method="post">
    <fieldset>
	<p><label for="usu"><?php echo $TbMsg["ACCESS_USERNAME"]; ?></label>
	   <input name="usu" type="text" onkeypress="PulsaEnter(event)" /></p>
	<p><label for="pss"><?php echo $TbMsg["ACCESS_PASSWORD"]; ?></label>
	   <input name="pss" type="password" onkeypress="PulsaEnter(event)" /></p>
	<p><label for="idcentro"><?php echo $TbMsg["ACCESS_ORGUNIT"]; ?></label>
	   <?php echo HTMLSELECT($cmd,0,'centros',$idcentro,'idcentro','nombrecentro',220); ?></p>
	<button type="submit" onclick="confirmar()"><?php echo $TbMsg["ACCESS_OK"]; ?></button>
    </fieldset>
</form>
</div>
<?
//______________________________________________________________________________
// Posiciona cursor en campo usuario y muestra mensaje de error si lo hubiera
echo '<script language="javascript">';
if (!empty($herror)) {
	if (!empty($TbErr[$herror])) {
		echo "	alert('".$TbErr[$herror]."');";
	} else {
		echo "	alert('".$TbMsg["ACCESS_UNKNOWNERROR"]."');";
	}
}
echo '  document.fdatos.usu.focus()';
echo '</script>';
//______________________________________________________________________________
?>
</body>
</html>

