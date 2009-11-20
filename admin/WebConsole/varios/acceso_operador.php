<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla

// Fecha Creación: Diciembre-2003
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: acceso.php
// Descripción : Presenta la pantalla de login del operador
// *************************************************************************************************************************************************

session_start(); // Activa variables de sesi�

$herror=0;

if (isset($_GET["herror"])) $herror=$_GET["herror"]; 
if (isset($_POST["herror"])) $herror=$_POST["herror"]; 

$ITEMS_PUBLICOS=1;
$ITEMS_PRIVADOS=2;

if (isset($_SESSION["swop"])){
	// Acceso al menu de adminitración del aula
	$wurl="menucliente.php?tip=".$ITEMS_PRIVADOS;
	Header('Location:'.$wurl); 
}

$TbErr=array();
$TbErr[0]="SIN ERRORES";
$TbErr[1]="ATENCIÓN: Usted no tiene acceso al menú de administración";
$TbErr[2]="ERROR de conexión con el servidor de datos";
//________________________________________________________________________________________________________
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
	<FORM action="accesoperadores.php" name="fdatos" method="post">
		<DIV align="center">
			<IMG src="../images/login_operador.png" width=500 >
			<INPUT onkeypress="PulsaEnter(event)" name="usu"  style="POSITION:absolute;top:125px;left:365px;width:90;height:20;COLOR: #999999; FONT-FAMILY: Verdana; FONT-SIZE: 12px;">
			<INPUT onkeypress="PulsaEnter(event)"  name="pss" type="password"  style="POSITION:absolute;top:160px;left:365;width:90;height:20;COLOR: #999999; FONT-FAMILY: Verdana; FONT-SIZE: 12px;">
			<IMG onclick="confirmar()" src="../images/botonok.png" style="POSITION:absolute;top:190;left:400;CURSOR: hand">
		</DIV>
	</FORM>
</DIV>
<?
//________________________________________________________________________________________________________
// Posiciona cursor en campo usuario y muestra mensaje de error si lo hubiera
echo '<SCRIPT LANGUAGE="javascript">';
if (!empty($herror))
	echo "	alert('".$TbErr[$herror]."');";
echo 'document.fdatos.usu.focus()';
echo '</SCRIPT>';
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
