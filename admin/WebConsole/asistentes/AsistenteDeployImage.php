<?php 
// *************************************************************************************************************************************************
// Aplicacion WEB: ogAdmWebCon
// Autor: 
// Baso en Codigo  Comando.php de : Jose Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// *************************************************************************************************************************************************


//Haciendo referencia a las funciones registradas y que seran interpretadas como funciones javascript


/********HACIENDO CONSULTA A LA TABLA ordenadores***********/
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../idiomas/php/".$idioma."/comandos/ejecutarscripts_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../includes/TomaDato.php");
include_once("../includes/ConfiguracionesParticiones.php");
include_once("../includes/RecopilaIpesMacs.php");
include_once("./includes/asistentes/AyudanteFormularios.php");


//________________________________________________________________________________________________________
include_once("./includes/capturaacciones.php");
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexiÃ³n con servidor B.D.
//________________________________________________________________________________________________________

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title> AdministraciÃ³n web de aulas </title>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="./jscripts/EjecutarScripts.js"></SCRIPT>
	<SCRIPT language="javascript" src="../comandos/jscripts/comunescomandos.js"></SCRIPT>
	<SCRIPT language="javascript" src="./jscripts/asistentes.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/ejecutarscripts_'.$idioma.'.js"></SCRIPT>'?>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/asistentes_'.$idioma.'.js"></SCRIPT>'?>

</head>


<body>
<?php
# ambito:   4->aulas   16->ordenadores
# idambito:  id de los elementos en su correspondiente tabla-ambito (aulas, ordenadores...)
# nombreambito: nombre del elemento.

switch($ambito){
		case $AMBITO_CENTROS :
			$urlimg='../images/iconos/centros.gif';
			$textambito=$TbMsg[0];
			break;
		case $AMBITO_GRUPOSAULAS :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[1];
			break;
		case $AMBITO_AULAS :
			$urlimg='../images/iconos/aula.gif';
			$textambito=$TbMsg[2];
			if (isset($_GET["idambito"])) $idambito=$_GET["idambito"];
			if (isset($_GET["litambito"])) $litambito=$_GET["litambito"];			
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[3];
			break;
		case $AMBITO_ORDENADORES :
			$urlimg='../images/iconos/ordenador.gif';
			$textambito=$TbMsg[4];
			if (isset($_GET["idambito"])) $idambito=$_GET["idambito"];
			if (isset($_GET["litambito"])) $litambito=$_GET["litambito"];
			break;
	}
	echo '<p align=center><span class=cabeceras>'.$TbMsg["WDI11"].'&nbsp;</span><br>';
	echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras>
				<U>'.$TbMsg[6].': '.$textambito.','.$nombreambito.'</U></span>&nbsp;&nbsp;</span></p>';

	$sws=0x11111;	// Mostrar todas las configuraciones diferentes.
	pintaConfiguraciones($cmd,$idambito,$ambito,7,$sws,false);	
	?>

	<form  align=center name="fdatos" > 
	
		<table  class=tabla_datos border="0" cellpadding="0" cellspacing="1">
			<?php
			include_once("./includes/asistentes/formDeployImage.php");
			?>
			
			<tr> 
				<th >
				    <INPUT TYPE="button" NAME="GenerarInstruccion" Value="<?php echo $TbMsg["WDI12"] ?>" onClick="codeDeployImage(this.form)"> 
				    <INPUT TYPE="button" NAME="ModificarCodigo" Value="<?php echo $TbMsg["WDI10"] ?>" onClick="modificarCodigo()"> 	
				</th>
				<td colspan="5"><textarea class="cajatexto" name="codigo" id="codigo" cols="70" rows="7" disabled></textarea></td>
			</tr>
			<tr>	
                       		 <th  colspan="4"><?php echo $TbMsg["WDI31"] ?></th>
			</tr>
		</table>	
	</form>	

<?php
	//________________________________________________________________________________________________________
	include_once("./includes/formularioacciones.php");
	//________________________________________________________________________________________________________
	//________________________________________________________________________________________________________
	include_once("./includes/opcionesacciones.php");
	//________________________________________________________________________________________________________

?>


</body>
</html>

