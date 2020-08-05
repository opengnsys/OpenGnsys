<?php
// ****************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: propiedades_ordenadores.php
// Descripción : 
//		 Presenta el formulario de captura de datos de un ordenador para insertar,modificar y eliminar
// ****************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_ordenadores_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/avisos_".$idioma.".php");
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idordenador=0; 
$ordprofesor=false;
$nombreordenador="";
$numserie="";
$maintenance=0;
$n_row=0;
$n_col=0;
$ip="";
$mac="";
$idperfilhard=0;
$idrepositorio=0;
$idmenu=0;
$idprocedimiento=0;
$idaula=0;
$grupoid=0;
######################## ADV
$netiface="";
$netdriver="";
######################## UHU
$validacion=0;
$paginalogin="";
$paginavalidacion="";
######################## Ramón
$arranque="";

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros 
if (isset($_GET["idordenador"])) $idordenador=$_GET["idordenador"]; 
if (isset($_GET["idaula"])) $idaula=$_GET["idaula"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idordenador=$_GET["identificador"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idordenador);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.
}
//________________________________________________________________________________________________________
?>
<html>
<head>
    <title>Administración web de aulas</title>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="../estilos.css" />
	<SCRIPT language="javascript" src="../jscripts/validators.js"></SCRIPT>
	<script language="javascript" src="../jscripts/propiedades_ordenadores.js"></script>
	<script language="javascript" src="../jscripts/opciones.js"></script>
	<?php echo '<script language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_ordenadores_'.$idioma.'.js"></script>'?>
	<script language=javascript> 
function abrir_ventana(URL){ 
   window.open('../images/ver.php','Imagenes','scrollbars=yes,resizable=yes,width=950,height=640') 
} 
</script>

</head>
<body>
<form name="fdatos" action="../gestores/gestor_ordenadores.php" method="post" enctype="multipart/form-data"> 
	<input type="hidden" name="opcion" value="<?php echo $opcion?>" />
	<input type="hidden" name="idordenador" value="<?php echo $idordenador?>" />
	<input type="hidden" name="grupoid" value="<?php echo $grupoid?>" />
	<input type="hidden" name="idaula" value="<?php echo $idaula?>" />
	<input type="hidden" name="arranque" value="<?php echo $arranque?>" />
	<p align="center" class="cabeceras"><?php echo $TbMsg[4]?><br />
	<span class="subcabeceras"><?php echo $opciones[$opcion]?></span></p>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<table align="center" border="0" cellPadding="1" cellSpacing="1" class="tabla_datos">
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg[5]?> <sup>*</sup>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion)
					echo '<td>'.$nombreordenador.($ordprofesor?' ('.$TbMsg["WARN_PROFESSOR"].')':'').'</td>';
				else
					echo '<td><input class="formulariodatos" name=nombreordenador  type=text value="'.$nombreordenador.'">'.($ordprofesor?' ('.$TbMsg["WARN_PROFESSOR"].')':'').'</td>';
				if (empty ($fotoordenador)) {
					$fotoordenador="fotoordenador.gif";
				}
				$fotomenu=$fotoordenador;
				$dirfotos="../images/fotos";
			?>
			<td colspan="2" valign="top" align="left" rowspan="5">
			<img border="2" style="border-color:#63676b; opacity: <?php echo 1-0.5*$maintenance ?>;" src="<?php echo $dirfotos.'/'.$fotoordenador?>" />
			<?php	if ($opcion!=$op_eliminacion) {
				echo '<br />(150X110)-(jpg - gif - png) ---- '.$TbMsg[5091].'><br />';
				echo '<input name="archivo" type="file" id="archivo" size="16" />';
				}
			?>
			</td>
		</tr>		
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg[6]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion)
					echo '<td>'.$ip.'</td>';
				else
					echo '<td><input class="formulariodatos" name=ip  type=text value="'.$ip.'"></td>';
			?>
		</tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg[7]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion)
					echo '<td><input type="hidden" name="mac" value="'.$mac.'" />'.$mac.'</td>';
				else	
					echo '<td><input class="formulariodatos" name=mac  type=text value="'. $mac.'"></td>';
			?>
		</tr>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg["LABEL_SERIALNO"]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion) {
					echo '<td>'.(isset($numserie)?$numserie:$TbMsg["WARN_NOTDETECTED"]).'</td>';
				} else {
					echo '<td><input class="formulariodatos" name="numserie" type="text" value="'.$numserie.'">';
					if (empty($numserie)) {
						echo $TbMsg["WARN_NOTDETECTED"];
					}
					echo "</td>\n";
				}
			?>
		</tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg["LABEL_MAINTENANCE"] ?>&nbsp;</th>
			<td>
			<?php   if ($opcion==$op_eliminacion) {
					echo '<input class="formulariodatos" name="maintenance" type="checkbox" disabled'. ($maintenance ? ' checked' : '') .">\n";
				} else {
					echo '<input class="formulariodatos" name="maintenance" type="checkbox" value="1"'. ($maintenance ? ' checked' : '') .">\n";
				}
			?>
			</td>
		</tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg["LABEL_LOCATION"] ?>&nbsp;</th>
			<td colspan="3">
			<?php	if ($opcion==$op_eliminacion) {
					if ($n_row != 0 and $n_col != 0) {
						echo $TbMsg["PROP_ROW"]." $n_row, ".$TbMsg["PROP_COLUMN"]." $n_col";
					}
				} else {
					$row="0=".$TbMsg["VAL_UNSPECIFIED"].chr(13);
					foreach (range(1, 15) as $n) {
						$row.="$n=".$TbMsg["PROP_ROW"]." $n".chr(13);
					}
					echo HTMLCTESELECT($row,"n_row","estilodesple","",$n_row,150);
					$col="0=".$TbMsg["VAL_UNSPECIFIED"].chr(13);
					foreach (range(1, 15) as $n) {
						$col.="$n=".$TbMsg["PROP_COLUMN"]." $n".chr(13);
					}
					echo HTMLCTESELECT($col,"n_col","estilodesple","",$n_col,150);
				}
			?>
			</td>
		</tr>				
		<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<th align=center>&nbsp;<?php echo $TbMsg[509]?>&nbsp;</th>
			<?php
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.$fotoordenador.'</TD>';
				else	{
					if ($fotoordenador=="")
					$fotoordenador="../images/fotos/fotoordenador.gif";
					?>
					<TD colspan=3><SELECT class="formulariodatos" name="fotoordenador" >
						<?php if($fotomenu==""){
						echo '<option value="fotoordenador.gif"></option>';}else{
						echo '<option value="'.$fotomenu.'">'.$fotomenu.'</option>';}
						if ($handle = opendir("../images/fotos")) {
						while (false !== ($entry = readdir($handle))) {
						if ($entry != "." && $entry != "..") {?>
						<option value="<?php echo $entry ?>"><?php echo $entry ?></option>
						<?php }
						}
						closedir($handle);
						}
						?>
					</SELECT>
<a href="javascript:abrir_ventana('../images/ver.php')" onclick="MM_openBrWindow('../images/ver.php','Imagenes','scrollbars=yes,resizable=yes,width=950,height=640')"><?php echo $TbMsg[5092] ?></a>
					</TD>
					<?php
					}
					?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<th align=center>&nbsp;<?php echo $TbMsg[8]?>&nbsp;</th>
			<?php
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.TomaDato($cmd,$idcentro,'perfileshard',$idperfilhard,'idperfilhard','descripcion').'</TD>';
				else
					echo '<TD colspan=3>'.HTMLSELECT($cmd,$idcentro,'perfileshard',$idperfilhard,'idperfilhard','descripcion',250).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg[10]?>&nbsp;</th>
			<?php
				if ($opcion==$op_eliminacion) {
					echo '<td colspan="3">'.TomaDato($cmd,$idcentro,'repositorios',$idrepositorio,'idrepositorio','nombrerepositorio').'</td>';
				} else {
					echo '<td colspan="3">'.HTMLSELECT($cmd,$idcentro,'repositorios',$idrepositorio,'idrepositorio','nombrerepositorio',250);
					echo ($idrepositorio==0?$TbMsg["WARN_NOREPO"]:'').'</td>';
				}
			?>
		</tr>
<!----	AGP	--------------------------------------------------------------------	OGLIVE	--------------------------------------------------------------------------------------------------------->
		<TR>
			<th align=center>&nbsp;<?php echo $TbMsg[18]?>&nbsp;</th>
<?php
$bdogLive="";
$cmd->texto="SELECT * FROM ordenadores WHERE idordenador=".$idordenador;
$rs=new Recordset;
$rs->Comando=&$cmd;
if (!$rs->Abrir()) return(true); // Error al abrir recordset
$rs->Primero();
if (!$rs->EOF){
	$bdogLive=$rs->campos["oglivedir"];
}
$rs->Cerrar();

if ($opcion==$op_eliminacion){
	echo '<td colspan="3">'.$bdogLive.'</td>';
}else{
	exec("bash /opt/opengnsys/bin/oglivecli list", $listogcli);
	echo '<TD colspan=3><select class="formulariodatos" name="seleoglive" style=width:250px>'."\n";
	echo '<option value="ogLive">'.$TbMsg['COMM_DEFOGLIVE'].'</option>';
	foreach ($listogcli as $oglive) {
		if (preg_match("/ogLive/",$oglive)){
			$oglive=substr($oglive,1);
			$oglive=trim($oglive);
			$Selectcli = '<option value="'.$oglive.'"';
			if ($bdogLive==$oglive)  $Selectcli.= ' selected ' ;
			$Selectcli.= '>'.$oglive.'</OPTION>';
			echo $Selectcli;
		}
	}
	echo '      </select>'."\n";
}
?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<th align=center>&nbsp;<?php echo $TbMsg[11]?>&nbsp;</th>
			<?php
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.TomaDato($cmd,$idcentro,'menus',$idmenu,'idmenu','descripcion').'</TD>';
				else
					echo '<TD colspan=3>'.HTMLSELECT($cmd,$idcentro,'menus',$idmenu,'idmenu','descripcion',250).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<th align=center>&nbsp;<?php echo $TbMsg[9]?>&nbsp;</th>
			<?php
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.TomaDato($cmd,$idcentro,'procedimientos',$idprocedimiento,'idprocedimiento','descripcion').'&nbsp;</TD>';
				else
					echo '<TD colspan=3>'.HTMLSELECT($cmd,$idcentro,'procedimientos',$idprocedimiento,'idprocedimiento','descripcion',250).'</TD>';
			?>
		</TR>		
<!-----ADV -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align=center&nbsp;>&nbsp;<?php echo $TbMsg[13]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion) {
					echo '<td colspan="3">'.$netiface.'</td>';
				} else {
					echo '<td colspan="3">';
					$iface="eth0=eth0".chr(13);
					$iface.="eth1=eth1".chr(13);
					$iface.="eth2=eth2";
					echo HTMLCTESELECT($iface,"netiface","estilodesple","",$netiface,100).'</td>';
				}
			?>
		</tr>				
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg[14]?>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion) {
					echo '<td colspan="3">'.$netdriver.'</td>';
				} else {
					echo '<td colspan="3">';
					$driver="generic=generic";
					echo HTMLCTESELECT($driver,"netdriver","estilodesple","",$netdriver,100).'</td>';
				}
			?>
		</tr>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!--------------------------------------------------------------UHU comprobar si se requiere validacion ------------------------------------------------------------------------------->

                <tr>
                        <th align=center&nbsp;><?php echo $TbMsg[15]; ?> &nbsp;</th>
			<?php	if ($opcion==$op_eliminacion) {
					echo '<td colspan="3">'.$validacion.'</td>';
				} else {
					echo '<TD colspan="3">';
					$validaciones="1=Si".chr(13);
					$validaciones.="0=No";
					echo HTMLCTESELECT($validaciones,"validacion","estilodesple","",$validacion,100).'</TD>';
				}
                        ?>
                </tr>
                <tr>
                        <th align=center>&nbsp;<?php echo $TbMsg[16]?>&nbsp;</th>
                        <?php	if ($opcion==$op_eliminacion)
                                        echo '<td colspan="3">'.$paginalogin.'</td>';
                                else
                                        echo '<td colspan="3"><input class="formulariodatos" name=paginalogin  type=text value="'.$paginalogin.'" /></td>';
                        ?>
                </tr>
                <tr>
                        <th align=center>&nbsp;<?php echo $TbMsg[17]?>&nbsp;</th>
                        <?php	if ($opcion==$op_eliminacion)
                                        echo '<td colspan="3">'.$paginavalidacion.'</td>';
                                else
                                        echo '<td colspan="3"><input class="formulariodatos" name=paginavalidacion  type=text value="'.$paginavalidacion.'" /></td>';
                        ?>
                </tr>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<?php	if ($opcion!=$op_eliminacion) { ?>
		<tr>
			<th colspan="4" align="center">&nbsp;<sup>*</sup> <?php echo $TbMsg["WARN_NAMELENGTH"]?>&nbsp;</th>
		</tr>
<?php   }
	if ($opcion==$op_alta) { ?>
		<tr>
			<th colspan="4" align="center">&nbsp;<?php echo $TbMsg["WARN_NETBOOT"]?>&nbsp;</th>
		</tr>
<?php	} ?>
	</table>
</form>
<?php
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________
?>
<br />
<?php
//________________________________________________________________________________________________________
//
// Frames para descargas de clientes y con información de la configuración
// Si es la opcion insertar no muestra nada -> opcion=$op_alta
if ($opcion!=$op_alta) {
	echo '<div align="center">';
	echo '<iframe scrolling="auto" height="70" width="90%" frameborder="0" src="../descargas/"></iframe>';
	echo '</div>';
	echo '<div align="center">';
	echo '<iframe scrolling="auto" height="500" width="90%" frameborder="0"
		 src="../principal/configuraciones.php?swp=1&idambito='.$idordenador.'&ambito='.$AMBITO_ORDENADORES.'"></iframe>';
	echo '</div>';
}
//________________________________________________________________________________________________________
?>
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
	global $ordprofesor;
	global $nombreordenador;
	global $numserie;
	global $maintenance;
	global $n_row;
	global $n_col;
	global $ip;
	global $mac;
	global $fotoordenador;
	global $idperfilhard;
	global $idrepositorio;
	global $idmenu;
	global $idprocedimiento;
	global $netiface;
	global $netdriver;
########################### UHU
	global $validacion;
	global $paginalogin;
	global $paginavalidacion;
########################### Ramón
	global $arranque;

	$rs=new Recordset; 
	$cmd->texto=<<<EOD
SELECT ordenadores.*, IF(idordprofesor=idordenador, 1, 0) AS ordprofesor
  FROM ordenadores
  JOIN aulas USING(idaula)
 WHERE idordenador='$id';
EOD;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombreordenador=$rs->campos["nombreordenador"];
		$ordprofesor = $rs->campos["ordprofesor"] == 1;
		$numserie=$rs->campos["numserie"];
		$ip=$rs->campos["ip"];
		$mac=$rs->campos["mac"];
		$idperfilhard=$rs->campos["idperfilhard"];
		$idrepositorio=$rs->campos["idrepositorio"];
		$idmenu=$rs->campos["idmenu"];
		$idprocedimiento=$rs->campos["idproautoexec"];
		$netiface=$rs->campos["netiface"];
		$fotoordenador=$rs->campos["fotoord"];	//Creado para foto
		$netdriver=$rs->campos["netdriver"];
########################### UHU
                $validacion=$rs->campos["validacion"];
                $paginalogin=$rs->campos["paginalogin"];
                $paginavalidacion=$rs->campos["paginavalidacion"];
########################### Ramón
                $arranque=$rs->campos["arranque"];
		$n_row=$rs->campos["n_row"]??0;
		$n_col=$rs->campos["n_col"]??0;
		$maintenance=$rs->campos["maintenance"]??0;
		$rs->Cerrar();
		return(true);
	}
	return(false);
}
