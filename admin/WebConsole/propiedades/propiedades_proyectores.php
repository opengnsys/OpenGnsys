<?php
// ****************************************************************************************************
// Autor: Ramón M. Gómez, ETSII Universidad de Sevilla
// Fecha Creación: junio 2018
// Fecha Última modificación: junio 2018
// Nombre del fichero: propiedades_proyectores.php
// Descripción :
//		 Presenta el formulario de captura de datos de un proyector para insertar, modificar y eliminar
// ****************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_proyectores_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/avisos_".$idioma.".php");
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idproyector=0;
$nombreproyector="";
$ip="";
$modelo="";
$tipo="";
$idaula=0;

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros.
if (isset($_GET["idproyector"])) $idordenador=$_GET["idproyector"];
if (isset($_GET["idaula"])) $idaula=$_GET["idaula"];
if (isset($_GET["identificador"])) $idproyector=$_GET["identificador"];
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idproyector);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.
}
//________________________________________________________________________________________________________
?>
<html>
<title>Administración web de aulas</title>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="../estilos.css" />
	<SCRIPT language="javascript" src="../jscripts/validators.js"></SCRIPT>
	<script language="javascript" src="../jscripts/propiedades_proyectores.js"></script>
	<script language="javascript" src="../jscripts/opciones.js"></script>
	<?php echo '<script language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_proyectores_'.$idioma.'.js"></script>'?>
</head>
<body>
<form name="fdatos" action="../gestores/gestor_proyectores.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="opcion" value="<?php echo $opcion?>" />
	<input type="hidden" name="idproyector" value="<?php echo $idproyector?>" />
	<input type="hidden" name="idaula" value="<?php echo $idaula?>" />
	<p align="center" class="cabeceras"><?php echo $TbMsg[4]?><br />
	<span class="subcabeceras"><?php echo $opciones[$opcion]?></span></p>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<table align="center" border="0" cellPadding="1" cellSpacing="1" class="tabla_datos">
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg["PROP_NAME"]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion) {
					echo '<td>'.$nombreproyector.'</td>'."\n";
				} else {
					echo '<td><input class="formulariodatos" name="nombreproyector" type="text" value="'.$nombreproyector.'"></td>'."\n";
				}
			?>
		</tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg["PROP_MODEL"]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion) {
					echo '<td><input type="hidden" name="modelo" value="'.$modelo.'" />'.$modelo.'</td>';
				} else {
					echo '<td><input class="formulariodatos" name="modelo" type="text" value="'. $modelo.'"></td>'."\n";
				}
			?>
		</tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg["PROP_TYPE"]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion) {
					echo '<td><input type="hidden" name="modelo" value="'.$tipo.'" />'.$tipo.'</td>'."\n";
				} else {
					$tiposproy ="standalone=standalone".chr(13);
					$tiposproy.="pjlink=pjlink".chr(13);
					$tiposproy.="unknown=unknown";
					echo '<td>'.HTMLCTESELECT($tiposproy,"tipo","estilodesple","",$tipo,100, "activaip").'</td>'."\n";
				}
			?>
		</tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg["PROP_IPADDR"]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion) {
					echo '<td>'.$ip.'</td>'."\n";
				} else {
					echo '<td><input class="formulariodatos" name="ip" type="text" value="'.$ip.'" readonly></td>'."\n";
				}
			?>
		</tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	</table>
</form>
<?php
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________
?>
<br />
</body>
</html>
<?php
//________________________________________________________________________________________________________
//	Recupera los datos de un ordenador
//		Parametros:
//		- cmd: Una comando ya operativo (con conexión abierta)
//		- id: El identificador del ordenador
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global $nombreproyector;
	global $modelo;
	global $tipo;
	global $ip;

	$rs=new Recordset;
	$cmd->texto=<<<EOD
SELECT projectors.*
  FROM projectors
  JOIN aulas ON aulas.idaula=projectors.lab_id
 WHERE id='$id';
EOD;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero();
	if (!$rs->EOF){
		$nombreproyector=$rs->campos["name"];
		$modelo=$rs->campos["model"];
		$tipo=$rs->campos["type"];
		$ip=$rs->campos["ipaddr"];
		$rs->Cerrar();
		return(true);
	}
	return(false);
}
