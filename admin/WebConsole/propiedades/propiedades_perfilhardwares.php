<?  
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: propiedades_perfilhardwares.php
// Descripción : 
//		 Presenta el formulario de captura de datos de un pefil hardware para insertar,modificar y eliminar
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_perfilhardwares_".$idioma.".php"); 
include_once("../idiomas/php/".$idioma."/avisos_".$idioma.".php"); 
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idperfilhard=0; 
$descripcion="";
$comentarios="";
$grupoid=0;
$ordenadores=0;		// Número de ordenadores que tienen este perfil
$winboot="reboot";	// Método de arranque para Windows (por defecto, reboot).

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idperfilhard"])) $idperfilhard=$_GET["idperfilhard"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idperfilhard=$_GET["identificador"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idperfilhard);
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
	<script language="javascript" src="../jscripts/propiedades_perfilhardwares.js"></script>
	<script language="javascript" src="../jscripts/opciones.js"></script>
	<?php echo '<script language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_perfilhardwares_'.$idioma.'.js"></script>'?>
</head>
<body>
<div align="center">
<form name="fdatos" action="../gestores/gestor_perfilhardwares.php" method="post"> 
	<input type="hidden" name="opcion" value="<?=$opcion?>" />
	<input type="hidden" name="idperfilhard" value="<?=$idperfilhard?>" />
	<input type="hidden" name="ordenadores" value="<?=$ordenadores?>" />
	<input type="hidden" name="grupoid" value="<?=$grupoid?>" />
	<p align="center" class="cabeceras"><?echo $TbMsg["HARD_TITLE"]?><br />
	<span align="center" class="subcabeceras"><? echo $opciones[$opcion]?></span></p>
	<table align="center" border="0" cellPadding="1" cellSpacing="1" class="tabla_datos">
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?echo $TbMsg["HARD_NAME"]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion)
					echo '<td style="width:215">'.$descripcion.'</td>';
				else
					echo '<td><input class="formulariodatos" name="descripcion" style="width:215" type="text" value="'.$descripcion.'" /></td>'; ?>
			<td align="left" rowspan="3"><img border="3" style="border-color:#63676b" src="../images/aula.jpg" /><br /> <?php echo $TbMsg["HARD_COMPUTERS"].": $ordenadores"?></td>
		</tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg["HARD_COMMENTS"]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion)
					echo '<td>'.$comentarios.'</td>';
				else
					echo '<td><textarea class="formulariodatos" name="comentarios" rows="3" cols="40">'.$comentarios.'</textarea></td>';
			?>
		</tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg["HARD_WINBOOT"]?> <sup>*</sup>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion)
					echo "<td>$winboot</td>";
				else {
					$params = "reboot=".$TbMsg["HARD_REBOOT"].chr(13);
					$params.= "kexec=".$TbMsg["HARD_KEXEC"];
					echo "<td>".HTMLCTESELECT($params,"winboot","estilodesple","","$winboot",110)."</td>";
				}
			?>
		</tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	</table>
</form>
</div>
<?
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________
?>
</body>
</html>
<?
//________________________________________________________________________________________________________
//	Recupera los datos de un perfil hardware
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- id: El identificador del perfil hardware
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id)
{
	global $descripcion;
	global $comentarios;
	global $ordenadores;
	global $winboot;
	$rs=new Recordset; 
	$cmd->texto="SELECT perfileshard.*, COUNT(*) AS numordenadores
			FROM perfileshard 
	 		INNER JOIN ordenadores ON ordenadores.idperfilhard=perfileshard.idperfilhard
			WHERE perfileshard.idperfilhard=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$descripcion=$rs->campos["descripcion"];
		$comentarios=$rs->campos["comentarios"];
		$ordenadores=$rs->campos["numordenadores"];
		$winboot=$rs->campos["winboot"];
	}
	$rs->Cerrar();
	return(true);
}
?>
