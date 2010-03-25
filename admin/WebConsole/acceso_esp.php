<?
// *********************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla

// Fecha Creación: Diciembre-2003
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: acceso.php
// Descripción : Presenta la pantalla de login de la aplicación
// ********************************************************************************************************
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
$TbErr[0]="SIN ERRORES";
$TbErr[1]="ATENCIÓN: Debe acceder a la aplicación a través de la pagina inicial";
$TbErr[2]="ATENCIÓN: La Aplicación no tiene acceso al Servidor de Bases de Datos";
$TbErr[3]="ATENCIÓN: Existen problemas para recuperar el registro, puede que haya sido eliminado";
$TbErr[4]="ATENCIÓN: Usted no tiene acceso a esta aplicación";
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
	var  p=document.fdatos.idcentro.selectedIndex
	if (p==0){  
		var res=confirm("ATENCIÓN: No ha introducido ninguna Unidad Organizativa. NO tendrá acceso al sistema a menos que sea adminstrador general de la Aplicación. ¿Desea acceder con este perfil?");
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
			
			<DIV	style="POSITION:absolute;top:180px;left:265;COLOR: #F9F9F9; FONT-FAMILY: Verdana; FONT-SIZE: 12px;">
				<P>Unidad Organizativa<BR>
			<?

					echo HTMLSELECT($cmd,0,'centros',$idcentro,'idcentro','nombrecentro',220);
			?>
			</P></DIV>

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
