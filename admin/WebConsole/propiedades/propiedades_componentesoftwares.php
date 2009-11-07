<? 
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: propiedades_componentesoftwares.php
// Descripción : 
//		 Presenta el formulario de captura de datos de un componente software para insertar,modificar y eliminar
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../includes/HTMLSELECT.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_componentesoftwares_".$idioma.".php");
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idsoftware=0; 
$descripcion="";
$idtiposoftware=0;
$idtiposo=0;
$grupoid=0;

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"];  // Recoge parametros
if (isset($_GET["idsoftware"])) $idsoftware=$_GET["idsoftware"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idsoftware=$_GET["identificador"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idsoftware);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperaci� de datos.
}
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_componentesoftwares.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_componentesoftwares_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<INPUT type=hidden name=idsoftware value=<?=$idsoftware?>>
	<INPUT type=hidden name=grupoid value=<?=$grupoid?>>
	<P align=center class=cabeceras><?echo $TbMsg[4]?><BR>
	<SPAN align=center class=subcabeceras><? echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR width="100px" style="display:block">
			<TH  width="100px" align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TH>
			<?if ($opcion==$op_eliminacion)
					echo '<TD style="width:215">'.$descripcion.'</TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=descripcion style="width:250" type=text value="'.$descripcion.'"></TD>';?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR  width="100px" style="display:block" >
			<TH  width="100px" align=center>&nbsp;<?echo $TbMsg[6]?>&nbsp;</TH>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD>'.TomaDato($cmd,0,'tiposoftwares',$idtiposoftware,'idtiposoftware','descripcion').'</TD>';
				else
					echo '<TD>'.HTMLSELECT($cmd,0,'tiposoftwares',$idtiposoftware,'idtiposoftware','descripcion',250,"seleccion").'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<?if($idtiposoftware!=1)
			echo '<TR  width="100px" id="tridtiposo" style="display:none">';
		else
			echo '<TR  width="100px"  id="tridtiposo" style="display:block">';
		?>
			<TH  width="100px" align=center>&nbsp;<?echo $TbMsg[7]?>&nbsp;</TH>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD>'.TomaDato($cmd,0,'tiposos',$idtiposo,'idtiposo','descripcion').'</TD>';
				else
					echo '<TD>'.HTMLSELECT($cmd,0,'tiposos',$idtiposo,'idtiposo','descripcion',250).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	</TABLE>
</FORM>
</DIV>
<?
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
//________________________________________________________________________________________________________
//	Recupera los datos de un componente software
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexiónabierta)  
//		- id: El identificador del componente software
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global $descripcion;
	global $idtiposoftware;
	global $idtiposo;
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM softwares WHERE idsoftware=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$descripcion=$rs->campos["descripcion"];
		$idtiposoftware=$rs->campos["idtiposoftware"];
		$idtiposo=$rs->campos["idtiposo"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
?>
