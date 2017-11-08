<?php
// ************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaciónn: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: propiedades_menus.php
// Descripción : 
//		 Presenta el formulario de captura de datos de un menu para insertar,modificar y eliminar
// **************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../includes/tftputils.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_menus_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/avisos_".$idioma.".php");
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idmenu=0; 
$descripcion="";
$titulo="";
$modalidad=0;
$smodalidad=0;
$comentarios="";
$grupoid=0;
$htmlmenupub="";
$htmlmenupri="";
$resolucion="";
$idurlimg=0;

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"];  // Recoge parametros
if (isset($_GET["idmenu"])) $idmenu=$_GET["idmenu"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idmenu=$_GET["identificador"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idmenu);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperaci�n de datos.
}
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_menus.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_menus_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos" action="../gestores/gestor_menus.php" method="post"> 
	<INPUT type=hidden name=opcion value=<?php echo $opcion?>>
	<INPUT type=hidden name=idmenu value=<?php echo $idmenu?>>
	<INPUT type=hidden name=grupoid value=<?php echo $grupoid?>>
	<P align=center class=cabeceras><?php echo $TbMsg[4]?><BR>
	<SPAN align=center class=subcabeceras><?php echo $opciones[$opcion]?></SPAN></P>
	<table align="center" border="0" cellPadding="1" cellSpacing="1" class="tabla_datos">
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg[5]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion)
					echo '<td style="width:300">'.$descripcion.'</td>';
				else
					echo '<td><input class="formulariodatos" name="descripcion" style="width:300" type="text" value="'.$descripcion.'" /></td>';?>
		</tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th  align=center>&nbsp;<?php echo $TbMsg[6]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion)
					echo '<td  style="width:300">'.$titulo.'</td>';
				else
					echo '<td ><input class="formulariodatos" name="titulo" style="width:300" type="text" value="'.$titulo.'" /></td>';?>
		</tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg[18]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion)
					echo '<td colspan="3">'.TomaDato($cmd,0,'iconos',$idurlimg,'idicono','descripcion').'&nbsp;</td>';
				else
					echo '<td colspan="3">'.HTMLSELECT($cmd,0,'iconos',$idurlimg,'idicono','descripcion',150,"","","idtipoicono=3").'</td>';
			?>
		</tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!--<php-->

		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg[17]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion){
					$tbresolucion[788]="800x600   16bits";
					$tbresolucion[791]="1024x768  16bits";
					$tbresolucion[355]="1152x864  16bits";
					$tbresolucion[794]="1280x1024 16bits";
					$tbresolucion[798]="1600x1200 16bits";
					$tbresolucion[789]="800x600   24bits";
					$tbresolucion[792]="1024x768  24bits";
					$tbresolucion[795]="1280x1024 24bits";
					$tbresolucion[799]="1600x1200 24bits";
					$tbresolucion[814]="800x600   32bits";
					$tbresolucion[824]="1024x768  32bits";
					$tbresolucion[829]="1280x1024 32bits";
					$tbresolucion[834]="1600x1200 32bits";
					if (empty ($tbresolucion[$resolucion])) {
						$res = $resolucion;
					} else {
						$res = $tbresolucion[$resolucion];
					}
					echo '<td>'.$res.'</td>';
				}
				else{
					if (clientKernelVersion() < "3.07") {
						// Kernel anterior a 3.7 usa parámetro "vga".
						$parametros ="788=800x600   16bits".chr(13);
						$parametros.="791=1024x768  16bits".chr(13);
						$parametros.="355=1152x864  16bits".chr(13);
						$parametros.="794=1280x1024 16bits".chr(13);
						$parametros.="798=1600x1200 16bits".chr(13);
						$parametros.="789=800x600   24bits".chr(13);
						$parametros.="792=1024x768  24bits".chr(13);
						$parametros.="795=1280x1024 24bits".chr(13);
						$parametros.="799=1600x1200 24bits".chr(13);
						$parametros.="814=800x600   32bits".chr(13);
						$parametros.="824=1024x768  32bits".chr(13);
						$parametros.="829=1280x1024 32bits".chr(13);
						$parametros.="834=1600x1200 32bits";
					} else {
						// Kernel 3.7 y superior usa parámetro "video".
						$parametros ="uvesafb:D=".$TbMsg["PROP_DEFAULT"].chr(13);
						$parametros.="uvesafb:800x600-16=800x600, 16bit".chr(13);
						$parametros.="uvesafb:800x600-24=800x600, 24bit".chr(13);
						$parametros.="uvesafb:800x600-32=800x600, 32bit".chr(13);
						$parametros.="uvesafb:1024x768-16=1024x768, 16bit".chr(13);
						$parametros.="uvesafb:1024x768-24=1024x768, 24bit".chr(13);
						$parametros.="uvesafb:1024x768-32=1024x768, 32bit".chr(13);
						$parametros.="uvesafb:1152x864-16=1152x864, 16bit".chr(13);
						$parametros.="uvesafb:1280x1024,16=1280x1024, 16bit".chr(13);
						$parametros.="uvesafb:1280x1024,24=1280x1024, 24bit".chr(13);
						$parametros.="uvesafb:1280x1024,32=1280x1024, 32bit".chr(13);
						$parametros.="uvesafb:1600x1200,16=1600x1200, 16bit".chr(13);
						$parametros.="uvesafb:1600x1200,24=1600x1200, 24bit".chr(13);
						$parametros.="uvesafb:1600x1200,32=1600x1200, 32bit";
					}
					echo '<td>'.HTMLCTESELECT($parametros,"resolucion","estilodesple","",$resolucion,150).'</td>';
				}
			?>
		</tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg[7]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion)
					echo '<td>'.$comentarios.'&nbsp</TD>';
				else
					echo '<td><textarea class="formulariodatos" name="comentarios" rows="3" cols="55">'.$comentarios.'</textarea></td>';
			?>
		</tr>
</table>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<BR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TD align=center colspan=2>&nbsp;<b><?php echo $TbMsg[8]?></b>&nbsp;</TD>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[11]?>&nbsp;</TH>
			<?php	if ($opcion==$op_eliminacion){
					$tbmodalidad[1]=$TbMsg[13];
					$tbmodalidad[2]=$TbMsg[14];
					echo '<TD style="width:100">'.$tbmodalidad[$modalidad].'</TD>';
				}
				else{
					$parametros="1=1".chr(13);
					$parametros.="2=2".chr(13);
					$parametros.="3=3".chr(13);
					$parametros.="4=4".chr(13);
					$parametros.="5=5";
					echo '<TD>'.HTMLCTESELECT($parametros,"modalidad","estilodesple","",$modalidad,100).'</TD>';
				}
			?>
		</TR>

		<TR>
			<TH align=center>&nbsp; <?php echo $TbMsg[15]?>&nbsp;</TH>
			<?php if ($opcion==$op_eliminacion)
					echo '<TD colspan=5>'.$htmlmenupub.'</TD>';
				else
					echo '<TD colspan=5><INPUT  class="formulariodatos" name=htmlmenupub style="width:350" type=text value="'.$htmlmenupub.'"></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TD align=center colspan=6>&nbsp;<b><?php echo $TbMsg[12]?></b>&nbsp;</TD>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[11]?>&nbsp;</TH>
				<?php	if ($opcion==$op_eliminacion){
						$tbmodalidad[1]=$TbMsg[13];
						$tbmodalidad[2]=$TbMsg[14];
						echo '<TD style="width:100">'.$tbmodalidad[$smodalidad].'</TD>';
					}
					else{
						$parametros="1=1".chr(13);
						$parametros.="2=2".chr(13);
						$parametros.="3=3".chr(13);
						$parametros.="4=4".chr(13);
						$parametros.="5=5";
						echo '<TD>'.HTMLCTESELECT($parametros,"smodalidad","estilodesple","",$smodalidad,100).'</TD>';
					}
			?>
		</TR>
		<TR>
			<TH align=center>&nbsp; <?php echo $TbMsg[15]?>&nbsp;</TH>
			<?php if ($opcion==$op_eliminacion)
					echo '<TD colspan=5">'.$htmlmenupri.'</TD>';
				else
					echo '<TD colspan=5><INPUT  class="formulariodatos" name=htmlmenupri style="width:350" type=text value="'.$htmlmenupri.'"></TD>';
			?>
		</TR>
	</TABLE>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<br>
	<table align="center" border="0" cellpadding="1" cellspacing="1" class="tabla_datos">
		<?php	if ($opcion!=$op_eliminacion)
				echo '<tr><th align="center">&nbsp;'.$TbMsg["WARN_NOTESOFMENU"].'&nbsp;</th></th>';
		?>
	</table>
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
//	Recupera los datos de un menu 
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- id: El identificador del menu 
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global	$descripcion;
	global  $titulo;
	global  $modalidad;
	global  $smodalidad;
	global	$comentarios;
	global	$htmlmenupub;
	global	$htmlmenupri;
	global	$resolucion;
	global  $idurlimg;
	
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM menus WHERE idmenu=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$descripcion=$rs->campos["descripcion"];
		$titulo=$rs->campos["titulo"];
		$modalidad=$rs->campos["modalidad"];
		$smodalidad=$rs->campos["smodalidad"];
		$comentarios=$rs->campos["comentarios"];
		$htmlmenupub=$rs->campos["htmlmenupub"];
		$htmlmenupri=$rs->campos["htmlmenupri"];
		$resolucion=$rs->campos["resolucion"];
		$idurlimg=$rs->campos["idurlimg"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
?>
