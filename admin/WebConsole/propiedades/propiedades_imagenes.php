<?php
// ********************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: propiedades_imagenes.php
// Descripción : 
//		 Presenta el formulario de captura de datos de una imagen para insertar,modificar y eliminar
// *******************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/TomaDato.php");
include_once("../includes/comunes.php");
include_once("../includes/restfunctions.php");
include_once("../idiomas/php/".$idioma."/propiedades_imagenes_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/avisos_".$idioma.".php");
//________________________________________________________________________________________________________

if (isset($_POST["opcion"])) {$opcion=$_POST["opcion"];}else{$opcion=0;} // Recoge parametros
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________

// Valores iniciales para variables.
$idimagen=0;
$nombreca="";
$ruta="";
$descripcion="";
$modelo="";
$numdisk=0;
$numpar=0;
$codpar=0;
$tipopar="";
$idperfilsoft=0;
$perfilsoft="";
$comentarios="";
$inremotepc="";
$scheduler="";
$grupoid=0;
$litamb="";
$tipoimg=0;
$idrepositorio=0;
$repoip="";
$repokey="";
$sistoperativo="";
$fechacreacion="";
$revision=0;
$imagenid=0;
$validnombreca="";
$validdescripcion="";
if (isset($_POST["validnombreca"])) {$opcion=$_POST["validnombreca"];}else{$validnombreca="";} // Recoge parametros
if (isset($_POST["datospost"])) {$datospost=$_POST["datospost"];}else{$datospost=0;} // Recoge parametros
if (isset($_GET["opcion"])) $opcion=$_GET["opcion"];  // Recoge parametros
if (isset($_GET["idimagen"])) $idimagen=$_GET["idimagen"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idimagen=$_GET["identificador"];
if (isset($_GET["litamb"])) $litamb=$_GET["litamb"];
if (isset($_GET["tipoimg"])) $tipoimg=$_GET["tipoimg"];
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
if  ($opcion!=$op_alta)
	$resul=TomaPropiedades($cmd,$idimagen);
else
	$resul=TomaConfiguracion($cmd);
if (!$resul)
	header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.

if ($opcion == 1 && $datospost == 1) {
	if (isset($_POST["opcion"])) $opcion=$_POST["opcion"];// Recoge parametros
	if (isset($_POST["idrepositorio"])) $idrepositorio=$_POST["idrepositorio"];
	if (isset($_POST["idimagen"])) $idimagen=$_POST["idimagen"];
	if (isset($_POST["nombreca"])) {
		$nombreca=$_POST["nombreca"];
		ValidaNombre($cmd,$nombreca,$idrepositorio);
	}
	if ($validnombreca != 1) {$validnombreca=0;}
	if (isset($_POST["ruta"])) $ruta=$_POST["ruta"]; 
	if (isset($_POST["descripcion"])) {$descripcion=$_POST["descripcion"];}
	if (isset($_POST["grupoid"])) $grupoid=$_POST["grupoid"];
	if (isset($_POST["idperfilsoft"])) $idperfilsoft=$_POST["idperfilsoft"]; 
	if (isset($_POST["comentarios"])) $comentarios=$_POST["comentarios"]; 
	if (isset($_POST["inremotepc"])) $inremotepc=$_POST["inremotepc"]; 
	if (isset($_POST["identificador"])) $idimagen=$_POST["identificador"];
	if (isset($_POST["modelo"])) $numpar=$_POST["modelo"]; 
	if (isset($_POST["numdisk"])) $numpar=$_POST["numdisk"]; 
	if (isset($_POST["numpar"])) $numpar=$_POST["numpar"]; 
	if (isset($_POST["codpar"])) $codpar=$_POST["codpar"]; 
	if (isset($_POST["idrepositorio"])) $idrepositorio=$_POST["idrepositorio"]; 
	if (isset($_POST["imagenid"])) $imagenid=$_POST["imagenid"]; 
	if (isset($_POST["tipoimg"])) $tipoimg=$_POST["tipoimg"]; 
	if (isset($_POST["fechacreacion"])) $fechacreacion=$_POST["fechacreacion"]; 
	if (isset($_POST["litamb"])) $litamb=$_POST["litamb"]; 
	}
// Solicitar datos del fichero de imagen a la API REST de su repositorio.
if  ($opcion!=$op_alta and isset($repokey)) {
	$repo[0]['url'] = "https://$repoip/opengnsys/rest/repository/image/$nombreca";
	$repo[0]['header'] = array('Authorization: '.$repokey);
	$result = multiRequest($repo);
	if ($result[0]['code'] === 200) {
		$result = json_decode($result[0]['data']);
		$imgpath = (@$result->type==="dir" ? @$result->name : @$result->name.".".@$result->type);
		$imgsize = humanSize(@$result->size);
		$imgdatasize = humanSize(@$result->datasize);
		$imgbackup = @$result->backedup;
		$imgbksize = isset($result->backupsize) ? humanSize($result->backupsize) : 0;
		$imglock = @$result->locked;
	} else {
		$imgpath = $imgsize = $imgbackup = $imglock = "";
	}
}

//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
    <TITLE>Administración web de aulas</TITLE>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/validators.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/propiedades_imagenes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_imagenes_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<DIV align=center>
<?php if ( $opcion == 1 && $datospost == 1 && $validnombreca == 0 && $validdescripcion == 0 || $opcion != 1) { ?>
<FORM name="fdatos" action="../gestores/gestor_imagenes.php" method="post">
<?php }else{ ?>
<FORM name="fdatos" action="./propiedades_imagenes.php" method="post"> 
<?php } ?>

	<INPUT type="hidden" name="opcion" value="<?php echo $opcion?>">
	<INPUT type="hidden" name="idimagen" value="<?php echo $idimagen?>">
	<INPUT type="hidden" name="grupoid" value="<?php echo $grupoid?>">
	<INPUT type="hidden" name="tipoimg" value="<?php echo $tipoimg?>">
	<INPUT type="hidden" name="litamb" value="<?php echo $litamb?>">
	<INPUT type="hidden" name="datospost" value="1">
	<?php
		switch($tipoimg){
		case $IMAGENES_MONOLITICAS:
			$lit=$TbMsg[4];
			break;
		case
			$IMAGENES_BASICAS:
			$lit=$TbMsg[12];
			break;
		case $IMAGENES_INCREMENTALES:
			$lit=$TbMsg[13];
		}
		
	?>
	<P align=center class=cabeceras><?php echo $lit?><BR>
		<SPAN class=subcabeceras><?php echo $opciones[$opcion]?></SPAN>
	</P>

	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
	<!-- -------------------------------------------------------------------------------- -->
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[11]?>&nbsp;</TH>
			<?php	if ($opcion==$op_eliminacion || !empty($idperfilsoft) || $opcion == 2)
					echo '<TD style="width:150px">'.$nombreca.'
					&nbsp;<INPUT type="hidden" name="nombreca" value="'.$nombreca.'"></TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=nombreca style="width:150px" type=text value="'.$nombreca.'">';
				if ($validnombreca == 1){echo '<div style="color: red; font-weight: bold;">&nbsp;'.$TbMsg[18].'</div>';}
				echo '</TD>';?>
		</TR>
	<!-- -------------------------------------------------------------------------------- -->
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[5]?>&nbsp;</TH>
			<?php	if ($opcion==$op_eliminacion) {
					echo '<TD style="width:300px">'.$descripcion.'
					&nbsp;<INPUT type="hidden" name="descripcion" value="'.$descripcion.'"></TD>';
				} else {
					echo '<TD><INPUT  class="formulariodatos" name=descripcion style="width:350px" type=text value="'.$descripcion.'">';
					if ($validnombreca == 0 && $validdescripcion == 1){echo '<div style="color: red; font-weight: bold;">&nbsp;'.$TbMsg[22].'</div>';}
					echo '</TD>';
				} ?>
		</TR>
	<!-- -------------------------------------------------------------------------------- -->
	<?php if($tipoimg==$IMAGENES_INCREMENTALES){?>
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[14]?>&nbsp;</TH>
			<?php	if ($opcion==$op_eliminacion || !empty($idperfilsoft))
					echo '<TD>'.TomaDato($cmd,$idcentro,'imagenes',$imagenid,'imagenid','descripcion').'
					&nbsp;<INPUT type="hidden" name="imagenid" value="'.$imagenid.'"></TD>';
				else
					echo '<TD>'.HTMLSELECT($cmd,$idcentro,'imagenes',$imagenid,'idimagen','descripcion',300,"",""," 
					tipo=".$IMAGENES_BASICAS,"imagenid").'</TD>';
			?>
		</TR>	
	<?php } ?>
	<?php if($tipoimg!=$IMAGENES_INCREMENTALES){?>
	<!-- -------------------------------------------------------------------------------- -->
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[10]?>&nbsp;</TH>
			<?php	if ($opcion==$op_eliminacion || !empty($idperfilsoft))
					echo '<TD>'.$nombrerepositorio.'
					&nbsp;<INPUT type="hidden" name="idrepositorio" value="'.$idrepositorio.'"></TD>';
				else
					echo '<TD>'.HTMLSELECT($cmd,$idcentro,'repositorios',$idrepositorio,'idrepositorio','nombrerepositorio',300).'</TD>';
			?>
		</TR>				
	<!-- -------------------------------------------------------------------------------- -->
	<?php if($tipoimg==$IMAGENES_BASICAS){?>	
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[16]?>&nbsp;</TH>
			<?php	if ($opcion==$op_eliminacion || !empty($idperfilsoft))
					echo '<TD>'.$ruta.'
					&nbsp;<INPUT type="hidden" name="ruta" value="'.$ruta.'"></TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=ruta style="width:350px" type=text value="'.$ruta.'"></TH>';?>
		</TR>	
	<?php }?>				
	<!-- -------------------------------------------------------------------------------- -->
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[7]?>&nbsp;</TH>
			<?php	if ($opcion==$op_eliminacion)
					echo '<TD>'.$comentarios.'</TD>';
				else
					echo '<TD><TEXTAREA   class="formulariodatos" name=comentarios rows=3 cols=55>'.$comentarios.'</TEXTAREA></TH>';
			?>
		</TR>
		<!-- Acceso remoto -->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg['PROP_REMOTEACCESS']?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion) {
					echo '<td><input name="inremotepc" type="checkbox" disabled readonly';
					if ($inremotepc)  echo ' checked ';
					echo '></td>';
				} else {
					echo '<td><input name="inremotepc" type="checkbox" value="1"';
					if ($inremotepc)  echo ' checked ';
					if ($scheduler)
						echo '> <em>('.$TbMsg['COMM_REMOTEACCESS'].')<em></td>';
					else
						echo 'disabled> <em>'.$TbMsg['WARN_SCHEDULER'].'<em></td>';
				}
			?>
		</tr>

	    <?php if ($opcion!=$op_alta) { ?>
		<!-- Equipo modelo (aula) -->
		<tr>
			<th align=center>&nbsp;<?php echo $TbMsg[19]?>&nbsp;</th>
			<td>&nbsp;<?php echo $modelo ?>
			    &nbsp;<input type="hidden" name="modelo" value="<?php echo $modelo ?>">
		</tr>
		<!-- Disco, partición y tipo de partición -->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg[8]?>&nbsp;</th>
			<td>&nbsp;<?php if (! empty($modelo)) echo "$numdisk, $numpar (".dechex($codpar)."-$tipopar)" ?>
			    <input type="hidden" name="numdisk" value="<?php echo $numdisk ?>">
			    <input type="hidden" name="numpar" value="<?php echo $numpar ?>"></td>
		</tr>
		<!-- Fecha de creación -->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg[20]?>&nbsp;</th>
			<td>&nbsp;<?php if (! empty ($modelo)) echo "$fechacreacion ".($revision>0 ? "(r$revision)" : "") ?>
			    <input type="hidden" name="fechacreacion" value="<?php echo $fechacreacion ?>"></td>
		</tr>
		<!-- Perfil de software -->
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[6]?>&nbsp;</TH>
			<?php
				if (isset($modelo)) {
					echo '<TD>&nbsp;'.$perfilsoft.'
					&nbsp;<INPUT type="hidden" name="idperfilsoft" value="'.$idperfilsoft.'"></TD>';
				} else {
					echo '<TD>'.HTMLSELECT($cmd,$idcentro,'perfilessoft',$idperfilsoft,'idperfilsoft','descripcion',300).'</TD>';
				}
			?>
		</TR>			
		<!-- Sistema Operativo -->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg['PROP_OS']?>&nbsp;</th>
			<td>&nbsp;<?php	echo $sistoperativo?> </td>
		</tr>
		<!-- Tamaño de los datos -->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg['DATA_SIZE']?>&nbsp;</th>
			<td>&nbsp;<?php	echo $imgdatasize?> </td>
		</tr>
	    <?php
		// Datos de imagen en el repositorio
		if (isset($imgpath)) {
			print <<< EOT
		<tr>
			<th colspan="2" align="center">Datos del repositorio</th>
		</tr>
		<tr>
			<th align="center">Camino</th>
			<td>&nbsp;/$imgpath&nbsp;</td>
		</tr>
		<tr>
			<th align="center">Tamaño</th>
			<td>&nbsp;$imgsize&nbsp;</td>
		</tr>
EOT;
			if ($imgbackup) {
				print <<< EOT
		<tr>
			<th align="center">Copia de seguridad</th>
			<td>&nbsp;$imgbksize&nbsp;</td>
		</tr>
EOT;
			}
			if ($imglock) {
				print <<< EOT
		<tr>
			<th align="center">Bloqueada</th>
			<td>&nbsp;Atención: la imagen está bloqueda por operación de uso exclusivo&nbsp;</td>
		</tr>
EOT;
			}
		}
	    ?>
	    <?php  } // fin if != op_alta
	    // Mensaje aviso ruta de origen
	    if ($opcion==$op_alta && $tipoimg==$IMAGENES_BASICAS) {
		echo '<tr><th colspan="14">'.$TbMsg["WARN_SOURCE_PATH"].'</th></tr>';
	    }
	}?>	
	<!-- -------------------------------------------------------------------------------- -->
	</TABLE>
</FORM>

<?php
if (!empty($idperfilsoft)){ // Nota a pie de página indicando que cuando la imagen tiene perfilsoft no pueden modificarse ciertos campos
	echo '
		<DIV id="Layer_nota" align=center >
			<SPAN class=notas><em>'.$TbMsg[15].'</em></SPAN>
		</DIV><br>';
}
//________________________________________________________________________________________________________


if ($validnombreca=="0"){
echo '<script type="text/javascript">';
echo 'confirmar('.$opcion.')';
echo '</script>';
}
if ($validnombreca=="1"){
echo '<script type="text/javascript">';
echo 'alert('.$TbMsg[17].')';
echo '</script>';
}

include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________

?>
</BODY>
</HTML>
<?php
//________________________________________________________________________________________________________

//	Recupera los datos de una imagen
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- id: El identificador de la imagen
//________________________________________________________________________________________________________

function TomaPropiedades($cmd,$idmagen){
	global $nombreca;
	global $ruta;
	global $descripcion;
	global $comentarios;
	global $inremotepc;
	global $scheduler;
	global $idperfilsoft;
	global $modelo;
	global $numdisk;
	global $numpar;
	global $codpar;
	global $tipopar;
	global $nombrerepositorio;
	global $idrepositorio;
	global $repoip;
	global $repokey;
	global $perfilsoft;
	global $sistoperativo;
	global $imagenid;
	global $fechacreacion;
	global $revision;
	
	$rs=new Recordset; 
	$cmd->texto="SELECT imagenes.*, tipospar.tipopar, repositorios.nombrerepositorio, 
			repositorios.ip, repositorios.apikey,
			perfilessoft.descripcion AS perfilsoft, nombreso AS sistoperativo,
			CONCAT (ordenadores.nombreordenador,' (',aulas.nombreaula,')') AS modelo,
			IF(@@GLOBAL.event_scheduler='ON',1,0) AS scheduler
			FROM imagenes
			LEFT OUTER JOIN tipospar ON tipospar.codpar=imagenes.codpar
			LEFT OUTER JOIN repositorios ON repositorios.idrepositorio=imagenes.idrepositorio
			LEFT OUTER JOIN perfilessoft ON perfilessoft.idperfilsoft=imagenes.idperfilsoft
			LEFT OUTER JOIN ordenadores ON ordenadores.idordenador=imagenes.idordenador
			LEFT OUTER JOIN aulas ON ordenadores.idaula=aulas.idaula
			LEFT OUTER JOIN nombresos ON perfilessoft.idnombreso=nombresos.idnombreso
			WHERE imagenes.idimagen=".$idmagen;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombreca=$rs->campos["nombreca"];
		$ruta=$rs->campos["ruta"];
		$descripcion=$rs->campos["descripcion"];		
		$idperfilsoft=$rs->campos["idperfilsoft"];
		$comentarios=$rs->campos["comentarios"];
		$inremotepc=$rs->campos["inremotepc"];
		$scheduler=$rs->campos["scheduler"];
		$modelo=$rs->campos["modelo"];
		$numdisk=$rs->campos["numdisk"];
		$numpar=$rs->campos["numpar"];
		$tipopar=$rs->campos["tipopar"];
		$codpar=$rs->campos["codpar"];
		$idrepositorio=$rs->campos["idrepositorio"];
		$nombrerepositorio=$rs->campos["nombrerepositorio"];
		$repoip=$rs->campos["ip"];
		$repokey=$rs->campos["apikey"];
		$perfilsoft=$rs->campos["perfilsoft"];
		$sistoperativo=$rs->campos["sistoperativo"];
		$imagenid=$rs->campos["imagenid"];
		$fechacreacion=$rs->campos["fechacreacion"];
		$revision=$rs->campos["revision"];
		$rs->Cerrar();
		return(true);
	}
	return(false);
}

//________________________________________________________________________________________________________
//	Recupera los algunos datos de configuración de la base de datos
//		Parametros: 
//		- cmd: comando ya operativo (con conexión abierta)  
//________________________________________________________________________________________________________
function TomaConfiguracion($cmd) {
	global $scheduler;

	$rs=new Recordset; 
	$cmd->texto="SELECT IF(@@GLOBAL.event_scheduler='ON',1,0) AS scheduler";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	if (!$rs->EOF){
		$scheduler=$rs->campos["scheduler"];
		$rs->Cerrar();
		return(true);
	}
	return(false);
}

//________________________________________________________________________________________________________

//	Comprueba Nombre de la imagen
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- nombreca: Nombre de la imagen
//		- descripcion: Descripcion de la imagen
//________________________________________________________________________________________________________

function ValidaNombre($cmd,$nombreca,$idrepositorio){
	global $nombreca;
	global $validnombreca;
	global $idrepositorio;

	$rs=new Recordset;
	$cmd->texto="SELECT * from imagenes WHERE nombreca='$nombreca'";
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$rs->Primero();
	if (!$rs->EOF){
		$nombrecabase=$rs->campos["nombreca"];
		$idrepositoriobase=$rs->campos["idrepositorio"];
		if ( $nombrecabase == $nombreca &&  $idrepositoriobase == $idrepositorio)
		{$validnombreca="1";}else{$validnombreca="0";}
	}
	$rs->Cerrar();
}

//________________________________________________________________________________________________________

//	Comprueba Descripcion del nombre canónico
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- nombreca: Nombre de la imagen
//		- descripcion: Descripcion de la imagen
//________________________________________________________________________________________________________

function ValidaDescripcion($cmd,$nombreca,$descripcion){
	global $nombreca;
	global $validnombreca;
	global $descripcion;
	global $validdescripcion;

	$rs=new Recordset; 
	$cmd->texto="SELECT * from imagenes WHERE nombreca='$nombreca'";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombrecabase=$rs->campos["nombreca"];
		$descripcionbase=$rs->campos["descripcion"];
		if ( $nombrecabase == $nombreca && "$descripcionbase" == "$descripcion" )
		{$validdescripcion="1";}else{$validdescripcion="0";}
	}
	$rs->Cerrar();
}
