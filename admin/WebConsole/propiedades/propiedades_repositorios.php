<?php
// *********************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaciónn: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: propiedades_repositorios.php
// Descripción :
//		 Presenta el formulario de captura de datos de un repositorio para insertar,modificar y eliminar
// Version 1.1.1: Si las OU están separadas por directorios, sólo muestra las imágenes del subdir definido
// **********************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_repositorios_".$idioma.".php");
// Fichero con funciones para trabajar con el webservice
include_once("../includes/restfunctions.php");
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idrepositorio=0;
$nombrerepositorio="";
$ip="";
$apiKeyRepo="";
$grupoid=0;
$comentarios="";
$ordenadores=0; // Número de ordenador a los que da servicio
$numordenadores=0; // Número de ordenador a los que da servicio
$dirOU=""; // Directorio de la unidad organizativa

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idrepositorio"])) $idrepositorio=$_GET["idrepositorio"];
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"];
if (isset($_GET["identificador"])) $idrepositorio=$_GET["identificador"];
$idcentro = (isset($_SESSION["widcentro"])) ? $_SESSION["widcentro"] : "";

//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con repositorio B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idrepositorio);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperaci�n de datos.
}
// Obtenemos directorio de la Unidad Organizativa
if ($idcentro != "") TomaDirectorioOU($cmd,$idcentro);

//________________________________________________________________________________________________________
//#########################################################################

// Si tenemos un apiKey podemos obtener la información desde el webservice en el repositorio
if($apiKeyRepo != ""){
	$repo[0]['url'] = "https://$ip/opengnsys/rest/repository/images";
	$repo[0]['header'] = array('Authorization: '.$apiKeyRepo);
	$result = multiRequest($repo);
	if ($result[0]['code'] === 200) {
		$result = json_decode($result[0]['data']);
		$repodir = $result->directory;
		$totalrepo = humanSize($result->disk->total);
		$librerepo = humanSize($result->disk->free);
		$ocupadorepo = humanSize($result->disk->total - $result->disk->free);
		$porcentajerepo = 100 - floor(100 * $result->disk->free / $result->disk->total);
		$repoOus = $result->ous;
		$repoImages = $result->images;
		$repoWithApi = true;
	} else {
		// Error de acceso a la API REST.
		$repoWithApi = false;
		$repoImages = null;
	}
} else {
	// Error de acceso a la API REST.
	$repoWithApi = false;
	$repoImages = null;
}

//#########################################################################
?>
<HTML>
<HEAD>
    <TITLE>Administración web de aulas</TITLE>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/validators.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/propiedades_repositorios.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_repositorios_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<DIV  align=center>
<FORM  name="fdatos" action="../gestores/gestor_repositorios.php" method="post">
	<INPUT type=hidden name=opcion value="<?php echo $opcion?>">
	<INPUT type=hidden name=idrepositorio value="<?php echo $idrepositorio?>">
	<INPUT type=hidden name=grupoid value="<?php echo $grupoid?>">
	<INPUT type=hidden name=ordenadores value="<?php echo $ordenadores?>">

	<P align=center class=cabeceras><?php echo $TbMsg[4]?><BR>
	<SPAN class=subcabeceras><?php echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos >
<!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- -->
		<TR>
			<TH align="center">&nbsp;<?php echo $TbMsg[5]?>&nbsp;</TH>
			<?php
				if ($opcion==$op_eliminacion)
					echo '<TD>'.$nombrerepositorio.'</TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name="nombrerepositorio" style="width:200px" type="text" value="'.$nombrerepositorio.'"></TD>';
			?>
			<TD valign="top" align="center" rowspan="4">
				<IMG border="3" style="border-color:#63676b" src="../images/aula.jpg">
				<BR>&nbsp;Ordenadores:&nbsp;<?php echo $ordenadores?></TD>
		</TR>
<!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- -->
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[6]?>&nbsp;</TH>
			<?php
			if ($opcion==$op_eliminacion)
					echo '<TD>'.$ip.'</TD>';
			else
				echo'<TD><INPUT  class="formulariodatos" name="ip" type="text" style="width:200px" value="'.$ip.'"></TD>';
			?>
		</TR>
<!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- -->
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[17]?>&nbsp;</TH>
			<?php
				if ($opcion==$op_eliminacion)
					echo '<TD>********</TD>';
				else
					echo'<TD><INPUT  class="formulariodatos" name="apiKeyRepo" type="text" style="width:200px" value="'.$apiKeyRepo.'"></TD>';
			?>
		</TR>
<!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- -->
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[7]?>&nbsp;</TH>
			<?php
				if ($opcion==$op_eliminacion)
					echo '<TD colspan="2">'.$comentarios.'</TD>';
				else
					echo '<TD colspan="2"><TEXTAREA   class="formulariodatos" name="comentarios" rows=2 cols=50>'.$comentarios.'</TEXTAREA></TD>';
			?>
		</TR>

<!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- -->

	</TABLE>
		<?php	if ( $opcion == 1 ){} else { ?>

	<TABLE  align=center border=0 cellPadding=2 cellSpacing=2 class=tabla_datos >
    <!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- -->

		<?php  if ($repoWithApi) { ?>
		<TR>
			<TH align=center width=125>&nbsp;<?php echo $TbMsg[11]?>&nbsp;</TH>
			<TH align=center width=120>&nbsp;<?php echo $TbMsg[12]?>&nbsp;</TH>
			<TH align=center width=120>&nbsp;<?php echo $TbMsg[13]?>&nbsp;</TH>
			<TH align=center width=101>&nbsp;<?php echo $TbMsg[14]?>&nbsp;</TH>
		</TR>
                <TR>
			<TD align=center width=125>&nbsp;<?php echo $totalrepo?>&nbsp;</TD>
            		<TD align=center width=120>&nbsp;<?php echo $ocupadorepo?>&nbsp;</TD>
           		<TD align=center width=120>&nbsp;<?php echo $librerepo?>&nbsp;</TD>
           		<TD align=center width=101>&nbsp;<?php echo "$porcentajerepo %" ?>&nbsp;</TD>
                </TR>
                <?php
				// Si tenemos informacion del repositorio remoto, mostramos las imagenes
				if ($repoWithApi == true) {
					$cabeceraTabla= "<tr class='tabla_listados_sin'><th colspan='4'>".$TbMsg['MSG_CONTENT']." $repodir</th></tr>\n".
							"<tr><td>".$TbMsg['MSG_IMAGE']." (".$TbMsg['MSG_TYPE'].")</td><td>".$TbMsg['MSG_SIZE']."</td><td>".$TbMsg['MSG_MODIFIED']."</td><td>".$TbMsg['MSG_PERMISSIONS']."</td></tr>\n";

				    if ($dirOU == "" && is_array($repoImages) && !empty($repoImages)) {
					echo $cabeceraTabla;
					$cabeceraTabla = "";
		   			foreach($repoImages as $image){
		   				echo "<tr class='tabla_listados_sin'>";
		   				echo "<td>".$image->name." (".$image->type.")</td>";
		   				echo "<td>".humanSize($image->size)."</td>";
		   				echo "<td>".$image->modified."</td>";
		   				echo "<td>".$image->mode."</td>";
		   				echo "</tr>\n";
		   			}
				    }
				    foreach($repoOus as $ou) {
						if ($dirOU != "" && $ou->subdir != $dirOU) continue;
						echo $cabeceraTabla;
						$cabeceraTabla = "";
		   				foreach($ou->images as $image) {
		   					echo "<tr class='tabla_listados_sin'>";
		   					echo "<td>".$ou->subdir." / ".$image->name." (".$image->type.")</td>";
							echo "<td>".humanSize($image->size)."</td>";
		   					echo "<td>".$image->modified."</td>";
		   					echo "<td>".$image->mode."</td>";
		   					echo "</tr>\n";
		   				}
				    }
		   		}
		   	?>
		<?php }else { ?>
		<tr>
			<th align="center">&nbsp;<?php echo '<strong>'.$TbMsg[15].'</strong></br>'.$TbMsg[16] ?></th>
		</tr>
        		<?php } ?>
		<?php } ?>
<!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- -->

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
//	Recupera los datos de un repositorio
//		Parametros:
//		- cmd: Una comando ya operativo (con conexión abierta)
//		- id: El identificador del repositorio
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global $nombrerepositorio;
	global $ip;
	global $comentarios;
	global $apiKeyRepo;
	global $ordenadores;

	$cmd->texto=<<<EOT
SELECT repositorios.*, COUNT(ordenadores.idordenador) AS numordenadores
  FROM repositorios
  LEFT JOIN ordenadores USING(idrepositorio)
 WHERE repositorios.idrepositorio='$id';
EOT;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(true); // Error al abrir recordset
	$rs->Primero();
	if (!$rs->EOF){
		$nombrerepositorio=$rs->campos["nombrerepositorio"];
		$ip=$rs->campos["ip"];
		$comentarios=$rs->campos["comentarios"];
		$apiKeyRepo=$rs->campos["apikey"];
		$ordenadores=$rs->campos["numordenadores"];
	}
	$rs->Cerrar();
	return(true);
}

//______________________________________________________________________________
//	Recupera directorio de la unidad organizativa (si no están separados '')
//		Parametros:
//		- cmd: Una comando ya operativo (con conexión abierta)
//		- id: El identificador del repositorio
//________________________________________________________________________________________________________
function TomaDirectorioOU($cmd,$idOU){
	global $dirOU;
	$cmd->texto=<<<EOT
SELECT if(ogunit=1, directorio, "") AS dirOU
  FROM entidades, centros
 WHERE idcentro='$idOU';
EOT;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(true); // Error al abrir recordset
	$rs->Primero();
	if (!$rs->EOF){
		$dirOU=$rs->campos["dirOU"];
	}
	$rs->Cerrar();
	return(true);
}
