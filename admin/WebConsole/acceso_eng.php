<?
// *********************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Agosto-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: acceso.php
// Descripción : Presenta la pantalla de login de la aplicación
// ********************************************************************************************************

# Cambiar a HTTPS
if (empty ($_SERVER["HTTPS"])) {
	header ("Location: https://".$_SERVER["SERVER_NAME"].$_SERVER["PHP_SELF"]);
	exit (0);
}

include_once("controlacceso.php");
include_once("./includes/CreaComando.php");
include_once("./clases/AdoPhp.php");
include_once("./includes/HTMLSELECT.php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cnx); // Crea objeto comando 
if (!$cmd)
   	die("Error de acceso");
//________________________________________________________________________________________________________
$herror=0;
if (isset($_GET["herror"])) $herror=$_GET["herror"]; 
if (isset($_POST["herror"])) $herror=$_POST["herror"]; 

$TbErr=array();
$TbErr[0]="NO ERRORS";
$TbErr[1]="Attention: You must access the application via the homepage";
$TbErr[2]="Attention: The application not have access to the database server";
$TbErr[3]="Attention: There are problems to retrieve the record, it may have been removed";
$TbErr[4]="Attention: You do not have access to this application";
$TbMsg=array();
$TbMsg["ACCESS_TITLE"]="OpenGnSys: Labs web administration";
$TbMsg["ACCESS_OU"]="Organitational Unit";
$TbMsg["ACCESS_NOUSER"]="You must enter a username";
$TbMsg["ACCESS_NOPASS"]="You must enter a password";
$TbMsg["ACCESS_NOUNIT"]='You have not entered any Organizational Unit.\nYou will NOT have access to the system unless that you are a main administrator of the Application.\nDo you want to access with this profile?';

//________________________________________________________________________________________________________
?>
<HTML>
<title><?php echo $TbMsg["ACCESS_TITLE"];?></title>
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
		<?php echo 'alert("'.$TbMsg["ACCESS_NOUSER"].'");' ?>
		document.fdatos.usu.focus()
		return(false)
	}
	if (document.fdatos.pss.value==""){
		<?php echo 'alert("'.$TbMsg["ACCESS_NOUSER"].'");' ?>
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
<DIV style="POSITION:absolute;top:90;left:250">
	<FORM action="controlpostacceso.php" name="fdatos" method="post">
		<DIV align="center">
			<IMG src="./images/login_esp.jpg" width=500 >
			<INPUT onkeypress="PulsaEnter(event)" name="usu"  
				style="POSITION:absolute;top:125px;left:365px;width:90;height:20;COLOR: #999999; FONT-FAMILY: Verdana; FONT-SIZE: 12px;">
			<INPUT onkeypress="PulsaEnter(event)"  name="pss" type="password"  
				style="POSITION:absolute;top:160px;left:365;width:90;height:20;COLOR: #999999; FONT-FAMILY: Verdana; FONT-SIZE: 12px;">
			
			<div style="position:absolute; top:180px; left:265; color:#F9F9F9; font-family:Verdana; font-size:12px;">
			<?php
				echo '<p>'.$TbMsg["ACCESS_OU"].'<br>';
				echo HTMLSELECT($cmd,0,'centros',$idcentro,'idcentro','nombrecentro',220);
			?>
			</p></div>

			<IMG onclick="confirmar()" src="./images/botonok.gif" style="POSITION:absolute;top:240;left:400;CURSOR: hand">
		</DIV>
	</FORM>
</DIV>
<?
//________________________________________________________________________________________________________
echo '<DIV  style="POSITION: absolute;LEFT: 20px;TOP:300px;visibility:hidden" height=300 width=300>';
echo '<IFRAME scrolling=yes height=300 width=310 id="iframes_comodin" src="./nada.php"></IFRAME>';
echo '</DIV>';
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

