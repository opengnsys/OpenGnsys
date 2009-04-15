<? 
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: MArzo-2005
// Nombre del fichero: propiedades_reservas.php
// Descripción : 
//		 Presenta el formulario de captura de datos de una reserva para insertar,modificar y eliminar
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/TomaDato.php");
include_once("../includes/HTMLSELECT.php");
include_once("../idiomas/php/".$idioma."/propiedades_reservas_".$idioma.".php");
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idreserva=0; 
$descripcion="";
$solicitante="";
$email="";
$idestatus=0;
$idaula=0;
$idimagen=0;
$idtarea=0;
$idtrabajo=0;
$estado=0;
$comentarios="";
$grupoid=0;

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"];  // Recoge parametros
if (isset($_GET["idreserva"])) $idreserva=$_GET["idreserva"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idreserva=$_GET["identificador"];
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idreserva);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.
}
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../hidra.css">
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/propiedades_reservas.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_reservas_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<DIV  align=center>
<FORM  name="fdatos"> 
	<INPUT type=hidden name=idreserva value=<?=$idreserva?>>
	<INPUT type=hidden name=grupoid value=<?=$grupoid?>>
	<INPUT type=hidden name=estado value=<?=$estado?>>
	<P align=center class=cabeceras><?echo $TbMsg[4]?><BR>
	<SPAN align=center class=subcabeceras><? echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TH>
			<?if ($opcion==$op_eliminacion)
					echo '<TD style="width:300">'.$descripcion.'</TD>';
				else
					echo '<TD><TEXTAREA   class="formulariodatos" name=descripcion rows=3 cols=55">'.$descripcion.'</TEXTAREA></TD>';?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[6]?>&nbsp;</TH>
			<?if ($opcion==$op_eliminacion)
					echo '<TD style="width:300">'.$solicitante.'</TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=solicitante style="width:300" type=text value="'.$solicitante.'"></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[8]?>&nbsp;</TH>
			<?if ($opcion==$op_eliminacion)
					echo '<TD style="width:300">'.$email.'</TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=email style="width:300" type=text value="'.$email.'"></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[9]?>&nbsp;</TH>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.TomaDato($cmd,0,'estatus',$idestatus,'idestatus','descripcion').'</TD>';
				else
					echo '<TD colspan=3>'.HTMLSELECT($cmd,0,'estatus',$idestatus,'idestatus','descripcion',300).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[10]?>&nbsp;</TH>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.TomaDato($cmd,$idcentro,'aulas',$idaula,'idaula','nombreaula').'</TD>';
				else
					echo '<TD colspan=3>'.HTMLSELECT($cmd,$idcentro,'aulas',$idaula,'idaula','nombreaula',300).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[11]?>&nbsp;</TH>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.TomaDato($cmd,$idcentro,'imagenes',$idimagen,'idimagen','descripcion').'</TD>';
				else
					echo '<TD colspan=3>'.HTMLSELECT($cmd,$idcentro,'imagenes',$idimagen,'idimagen','descripcion',300).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[19]?>&nbsp;</TH>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.TomaDato($cmd,$idcentro,'tareas',$idtarea,'idtarea','descripcion').'</TD>';
				else
					echo '<TD colspan=3>'.HTMLSELECT($cmd,$idcentro,'tareas',$idtarea,'idtarea','descripcion',300).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[20]?>&nbsp;</TH>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.TomaDato($cmd,$idcentro,'trabajos',$idtrabajo,'idtrabajo','descripcion').'</TD>';
				else
					echo '<TD colspan=3>'.HTMLSELECT($cmd,$idcentro,'trabajos',$idtrabajo,'idtrabajo','descripcion',300).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<?
		$tbimg[$RESERVA_CONFIRMADA]='../images/iconos/confirmadas.gif';
		$tbimg[$RESERVA_PENDIENTE]='../images/iconos/pendientes.gif';
		$tbimg[$RESERVA_DENEGADA]='../images/iconos/denegadas.gif';
		?>
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[16]?>&nbsp;</TH>
			<?if ($opcion==$op_eliminacion)
					echo '<TD style="width:300"><IMG src="'.$tbimg[$estado].'">&nbsp;&nbsp;('.$TbMsg[12+$estado].')</TD>';
			else{
					echo '<TD>';
					echo '<INPUT  name=xestado type=radio value="'.$RESERVA_CONFIRMADA.'"';
					if($estado==$RESERVA_CONFIRMADA) echo ' checked ';
					echo ' onclick="document.fdatos.estado.value='.$RESERVA_CONFIRMADA.'">'.$TbMsg[13].'&nbsp;';

					echo '<INPUT  name=xestado type=radio value="'.$RESERVA_PENDIENTE.'"';
					if($estado==$RESERVA_PENDIENTE) echo ' checked ';
					echo 'onclick="document.fdatos.estado.value='.$RESERVA_PENDIENTE.'">'.$TbMsg[14].'&nbsp;';

					echo '<INPUT  name=xestado type=radio value="'.$RESERVA_DENEGADA.'"';
					if($estado==$RESERVA_DENEGADA) echo ' checked ';
					echo 'onclick="document.fdatos.estado.value='.$RESERVA_DENEGADA.'">'.$TbMsg[15].'&nbsp;';

					echo '</TD>';
			}
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[7]?>&nbsp;</TD>
			<?if ($opcion==$op_eliminacion)
					echo '<TD>'.$comentarios.'</TD>';
				else
					echo '<TD><TEXTAREA   class="formulariodatos" name=comentarios rows=3 cols=55>'.$comentarios.'</TEXTAREA></TD>';
			?>
		</TR>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	</TABLE>
</FORM>
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
//	Recupera los datos de una reserva
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- id: El identificador de la reserva
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global $descripcion;
	global $comentarios;
	global $solicitante;
	global $email;
	global $idestatus;
	global $idaula;
	global $idimagen;
	global $idtarea;
	global $idtrabajo;
	global $estado;
	
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM reservas WHERE idreserva=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$descripcion=$rs->campos["descripcion"];
		$solicitante=$rs->campos["solicitante"];
		$email=$rs->campos["email"];
		$idestatus=$rs->campos["idestatus"];
		$idaula=$rs->campos["idaula"];
		$idimagen=$rs->campos["idimagen"];
		$idtarea=$rs->campos["idtarea"];
		$idtrabajo=$rs->campos["idtrabajo"];
		$estado=$rs->campos["estado"];
		$comentarios=$rs->campos["comentarios"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
?>