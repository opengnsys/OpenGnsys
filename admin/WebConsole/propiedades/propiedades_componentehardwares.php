<?php 
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: propiedades_componentehardwares.php
// Descripción : 
//		 Presenta el formulario de captura de datos de un componente hardware para insertar,modificar y eliminar
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../includes/HTMLSELECT.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_componentehardwares_".$idioma.".php");
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idhardware=0; 
$descripcion="";
$idtipohardware=0;
$grupoid=0;

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"];  // Recoge parametros
if (isset($_GET["idhardware"])) $idhardware=$_GET["idhardware"];
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idhardware=$_GET["identificador"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idhardware);
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
	<SCRIPT language="javascript" src="../jscripts/propiedades_componentehardwares.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_componentehardwares_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos" action="../gestores/gestor_componentehardwares.php" method="post"> 
	<INPUT type=hidden name=opcion value="<?php echo $opcion?>">
	<INPUT type=hidden name=idhardware value=<?php echo $idhardware?>>
	<INPUT type=hidden name=grupoid value=<?php echo $grupoid?>>
	<P align=center class=cabeceras><?php echo $TbMsg[4]?><BR>
	<SPAN align=center class=subcabeceras><?php echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[5]?>&nbsp;</TD>
			<?php if ($opcion==$op_eliminacion)
					echo '<TD style="width:215">'.$descripcion.'</TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=descripcion style="width:250" type=text value="'.$descripcion.'"></TD>';?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[6]?>&nbsp;</TD>
			<?php
				if ($opcion==$op_eliminacion)
					echo '<TD>'.TomaDato($cmd,0,'tipohardwares',$idtipohardware,'idtipohardware','descripcion').'</TD>';
				else
					echo '<TD>'.HTMLSELECT($cmd,0,'tipohardwares',$idtipohardware,'idtipohardware','descripcion',250).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	</TABLE>
</FORM>
</DIV>
<?php
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?php
//________________________________________________________________________________________________________
//	Recupera los datos de un componente hardware
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexiónabierta)  
//		- id: El identificador del componente hardware
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global $descripcion;
	global $idtipohardware;
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM hardwares WHERE idhardware=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$descripcion=$rs->campos["descripcion"];
		$idtipohardware=$rs->campos["idtipohardware"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
?>
