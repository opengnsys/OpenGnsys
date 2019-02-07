<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Marzo-2006
// Nombre del fichero: propiedades_grupos.php
// Descripción : 
//		 Presenta el formulario de captura de datos de un grupo para insertar,modificar y eliminar
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../idiomas/php/".$idioma."/propiedades_grupos_".$idioma.".php");
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$nombregrupo=""; 
$comentarios="";
$grupoid=0; 
$idgrupo=0; 
$tipo=0; 
$literaltipo=""; 
$iduniversidad=0; 
$idaula=0; 

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"];  // Recoge parametros
if (isset($_GET["idgrupo"])) $idgrupo=$_GET["idgrupo"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["tipo"])) $tipo=$_GET["tipo"]; 
if (isset($_GET["literaltipo"])) $literaltipo=$_GET["literaltipo"]; 
if (isset($_GET["iduniversidad"])) $iduniversidad=$_GET["iduniversidad"]; 
if (isset($_GET["idaula"])) $idaula=$_GET["idaula"]; 

if (isset($_GET["identificador"])) $idgrupo=$_GET["identificador"];

//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idgrupo);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.
}
switch($literaltipo){
	case $LITAMBITO_CENTROS :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[7];
		break;
	case $LITAMBITO_GRUPOSAULAS :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[7];
		break;
	case $LITAMBITO_GRUPOSORDENADORES:
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[22];
		break;
	case $LITAMBITO_GRUPOSTAREAS :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[9];
		break;
	case $LITAMBITO_GRUPOSPROCEDIMIENTOS :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[8];
		break;
	case $LITAMBITO_GRUPOSTRABAJOS :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[10];
		break;
	case $LITAMBITO_GRUPOSIMAGENESMONOLITICAS :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[23];
		break;
	case $LITAMBITO_GRUPOSIMAGENESBASICAS :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[24];
		break;
	case $LITAMBITO_GRUPOSIMAGENESINCREMENTALES :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[25];
		break;		
	case $LITAMBITO_GRUPOSCOMPONENTESHARD  :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[12];
		break;
	case $LITAMBITO_GRUPOSCOMPONENTESSOFT :
		$urlimg='../images/iconos/confisoft.gif';
		$textambito=$TbMsg[13];
		break;
	case $LITAMBITO_GRUPOSPERFILESHARD :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[14];
		break;
	case $LITAMBITO_GRUPOSPERFILESSOFT :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[15];
		break;
	case $LITAMBITO_GRUPOSSOFTINCREMENTAL :
		$urlimg='../images/iconos/softcombi.gif';
		$textambito=$TbMsg[16];
		break;
	case $LITAMBITO_GRUPOSSERVIDORESREMBO :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[17];
		break;
	case $LITAMBITO_GRUPOSSERVIDORESDHCP :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[18];
		break;
	case $LITAMBITO_GRUPOSMENUS :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[19];
		break;
	case $LITAMBITO_GRUPOSRESERVAS :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[20];
		break;
	case $LITAMBITO_GRUPOSENTIDADES :
		$urlimg='../images/iconos/carpeta.gif';
		$textambito=$TbMsg[21];
		break;
	default:
			$resul=false;
}
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
	<TITLE>Administración web de aulas</TITLE>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_grupos.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_grupos_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<DIV  align=center>
<FORM  name="fdatos" action="" method=post> 
	<INPUT type=hidden name=opcion value=<?php echo $opcion?>>
	<INPUT type=hidden name=idgrupo value=<?php echo $idgrupo?>>
	<INPUT type=hidden name=grupoid value=<?php echo $grupoid?>>
	<INPUT type=hidden name=tipo value=<?php echo $tipo?>>
	<INPUT type=hidden name=literaltipo value="<?php echo $literaltipo?>">
	<INPUT type=hidden name=iduniversidad value=<?php echo $iduniversidad?>>
	<INPUT type=hidden name=idaula value=<?php echo $idaula?>>
	<P align=center class=cabeceras><IMG src="<?php echo $urlimg?>">&nbsp;<?php echo $textambito?><BR>
	<SPAN class=subcabeceras><?php echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos >
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[5]?>&nbsp;</TH>
			<?php	if ($opcion==$op_eliminacion)
					echo '<TD style="width:300px">'.$nombregrupo.'</TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=nombregrupo style="width:320px" type=text value="'.$nombregrupo.'"></TD>';?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[6]?>&nbsp;</TH>
			<?php	if ($opcion==$op_eliminacion)
					echo '<TD>'.$comentarios.'</TD>';
				else
					echo '<TD><TEXTAREA   class="formulariodatos" name=comentarios rows=3 cols=60>'.$comentarios.'</TEXTAREA></TD>';
			?>
		</TR>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	</TABLE>
</FORM>
<?php
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?php
//________________________________________________________________________________________________________
//	Recupera los datos de una grupo
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- id: El identificador de la grupo
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global $nombregrupo;
	global $comentarios;
	global $literaltipo;
	global $LITAMBITO_GRUPOSORDENADORES;

	$rs=new Recordset; 
	if($literaltipo==$LITAMBITO_GRUPOSORDENADORES)
			$cmd->texto="SELECT * FROM gruposordenadores WHERE idgrupo=".$id;
	else
			$cmd->texto="SELECT * FROM grupos WHERE idgrupo=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		if($literaltipo==$LITAMBITO_GRUPOSORDENADORES)
			$nombregrupo=$rs->campos["nombregrupoordenador"];
		else
			$nombregrupo=$rs->campos["nombregrupo"];
		$comentarios=$rs->campos["comentarios"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
