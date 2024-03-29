<?php 
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
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
<HEAD>
    <TITLE>Administración web de aulas</TITLE>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_componentesoftwares.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_componentesoftwares_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<div align="center">
<FORM  name="fdatos" action="../gestores/gestor_componentesoftwares.php" method="post"> 
	<INPUT type=hidden name=opcion value="<?php echo $opcion?>">
	<INPUT type=hidden name=idsoftware value=<?php echo $idsoftware?>>
	<INPUT type=hidden name=grupoid value=<?php echo $grupoid?>>
	<P align=center class=cabeceras><?php echo $TbMsg[4]?><BR>
	<SPAN class=subcabeceras><?php echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR style="display:block">
			<TH  width="100px" align=center>&nbsp;<?php echo $TbMsg[5]?>&nbsp;</TH>
			<?php if ($opcion==$op_eliminacion)
					echo '<TD style="width:215px">'.$descripcion.'</TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=descripcion style="width:250px" type=text value="'.$descripcion.'"></TD>';?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR style="display:block" >
			<TH  width="100px" align=center>&nbsp;<?php echo $TbMsg[6]?>&nbsp;</TH>
			<?php
				if ($opcion==$op_eliminacion)
					echo '<TD>'.TomaDato($cmd,0,'tiposoftwares',$idtiposoftware,'idtiposoftware','descripcion').'</TD>';
				else
					echo '<TD>'.HTMLSELECT($cmd,0,'tiposoftwares',$idtiposoftware,'idtiposoftware','descripcion',250,"seleccion").'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<?php if($idtiposoftware!=1)
			    echo '<tr id="tridtiposo" style="display:none">';
		    else
			    echo '<tr id="tridtiposo" style="display:block">';
		    echo '    <TH  width="100px" align=center>&nbsp;<?php echo $TbMsg[7]?>&nbsp;</TH>';
			if ($opcion==$op_eliminacion)
				echo '<TD>'.TomaDato($cmd,0,'tiposos',$idtiposo,'idtiposo','descripcion').'</TD>';
			else
				echo '<TD>'.HTMLSELECT($cmd,0,'tiposos',$idtiposo,'idtiposo','descripcion',250).'</TD>';
			echo '</tr>';
		?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	</TABLE>
</FORM>
</div>
<?php
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?php
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
