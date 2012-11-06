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
$nombreordenador="";
$ip="";
$mac="";
$idperfilhard=0;
$idrepositorio=0;
$idmenu=0;
$idprocedimiento=0;
$idaula=0;
$cache="";
$grupoid=0;
######################## ADV
$netiface="";
$netdriver="";
########################### ADV

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
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_ordenadores.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_ordenadores_'.$idioma.'.js"></SCRIPT>'?>
	<script language=javascript> 
function abrir_ventana(URL){ 
   window.open('../images/ver.php','Imagenes','scrollbars=yes,resizable=yes,width=950,height=640') 
} 
</script>
	
</HEAD>
<BODY>
<FORM  name="fdatos" action="../gestores/gestor_ordenadores.php" method="post" enctype="multipart/form-data"> 
	<INPUT type=hidden name=opcion value="<? echo $opcion?>">
	<INPUT type=hidden name=idordenador value="<? echo $idordenador?>">
	<INPUT type=hidden name=grupoid value="<? echo $grupoid?>">
	<INPUT type=hidden name=idaula value="<? echo $idaula?>">
	<P align=center class=cabeceras><?echo $TbMsg[4]?><BR>
	<SPAN align=center class=subcabeceras><? echo $opciones[$opcion]?></SPAN></P>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<table align="center" border="0" cellPadding="1" cellSpacing="1" class="tabla_datos">
		<tr>
			<th align="center">&nbsp;<?php echo $TbMsg[5]?> <sup>*</sup>&nbsp;</th>
			<?php	if ($opcion==$op_eliminacion)
					echo '<td>'.$nombreordenador.'</td>';
				else
					echo '<td><input class="formulariodatos" name=nombreordenador  type=text value="'.$nombreordenador.'"></td>';
				if (empty ($fotoordenador)) {
					$fotoordenador="fotoordenador.gif";
				}
				$fotomenu=$fotoordenador;
				$dirfotos="../images/fotos";
			?>
<td colspan="2" valign="top" align="left" rowspan="3">
<img border="2" style="border-color:#63676b" src="<?php echo $dirfotos.'/'.$fotoordenador?>" />
<br />(150X110)-(jpg - gif) ---- <?php echo $TbMsg[5091]?>
<br />
<input name="archivo" type="file" id="archivo" size="16" />
</td>
		</tr>		
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<th align="center">&nbsp;<?php echo $TbMsg[6]?> <sup>*</sup>&nbsp;</th>
			<?php
				if ($opcion==$op_eliminacion)
					echo '<TD>'.$ip.'</TD>';
				else
					echo '<TD><INPUT class="formulariodatos" name=ip  type=text value="'.$ip.'"></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<th align="center">&nbsp;<?php echo $TbMsg[7]?> <sup>*</sup>&nbsp;</th>
			<?php
				if ($opcion==$op_eliminacion)
					echo '<TD>'.$mac.'</TD>';
				else	
					echo '<TD><INPUT class="formulariodatos" name=mac  type=text value="'. $mac.'"></TD>';
			?>
		</TR>	
		<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
				<TR>
			<th align=center>&nbsp;<?echo $TbMsg[509]?>&nbsp;</th>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.$fotoordenador.'</TD>';
				else	{
					if ($fotoordenador=="")
					$fotoordenador="../images/fotos/fotoordenador.gif";
					$fotoordenador;
					
					?>
					<TD colspan=3><SELECT class="formulariodatos" name="fotoordenador" >
						<?php if($fotomenu==""){
						echo '<option value="fotoordenador.gif"></option>';}else{
						echo '<option value="'.$fotomenu.'">'.$fotomenu.'</option>';}
						if ($handle = opendir("../images/fotos")) {
						while (false !== ($entry = readdir($handle))) {
						if ($entry != "." && $entry != "..") {?>
						
						<option value="<? echo $entry ?>"><? echo $entry ?></option>
						<?}
						}
						closedir($handle);
						} 
						?>
					 </SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="javascript:abrir_ventana('../images/ver.php')" onClick="MM_openBrWindow('../images/ver.php','Imagenes','scrollbars=yes,resizable=yes,width=950,height=640')"><? echo $TbMsg[5092] ?></a>
					</TD>
					<?
					}
					?>
			
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<th align=center>&nbsp;<?echo $TbMsg[8]?>&nbsp;</th>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.TomaDato($cmd,$idcentro,'perfileshard',$idperfilhard,'idperfilhard','descripcion').'</TD>';
				else
					echo '<TD colspan=3>'.HTMLSELECT($cmd,$idcentro,'perfileshard',$idperfilhard,'idperfilhard','descripcion',250).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<th align=center>&nbsp;<?echo $TbMsg[10]?>&nbsp;</th>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.TomaDato($cmd,$idcentro,'repositorios',$idrepositorio,'idrepositorio','nombrerepositorio').'</TD>';
				else
					echo '<TD colspan=3>'.HTMLSELECT($cmd,$idcentro,'repositorios',$idrepositorio,'idrepositorio','nombrerepositorio',250).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<th align=center>&nbsp;<?echo $TbMsg[11]?>&nbsp;</th>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.TomaDato($cmd,$idcentro,'menus',$idmenu,'idmenu','descripcion').'</TD>';
				else
					echo '<TD colspan=3>'.HTMLSELECT($cmd,$idcentro,'menus',$idmenu,'idmenu','descripcion',250).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<th align=center>&nbsp;<?echo $TbMsg[9]?>&nbsp;</th>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.TomaDato($cmd,$idcentro,'procedimientos',$idprocedimiento,'idprocedimiento','descripcion').'&nbsp;</TD>';
				else
					echo '<TD colspan=3>'.HTMLSELECT($cmd,$idcentro,'procedimientos',$idprocedimiento,'idprocedimiento','descripcion',250).'</TD>';
			?>
		</TR>		
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<th align=center>&nbsp;<?echo $TbMsg[12]?>&nbsp;</th>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.$cache.'</TD>';
				else	
					echo '<TD colspan=3><INPUT style="width=250" class="formulariodatos" name="cache" type="text" readonly value="'. $cache.'"></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!-----ADV -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th align=center&nbsp;>&nbsp;<?echo $TbMsg[13]?>&nbsp;</th>
			<?
				echo '<td colspan="3">';
				$iface="eth0=eth0".chr(13);
				$iface.="eth1=eth1".chr(13);
				$iface.="eth2=eth2";
				echo HTMLCTESELECT($iface,"netiface","estilodesple","",$netiface,100).'</td>';
			?>
		</tr>				
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		
		<tr>
			<th align="center">&nbsp;<?echo $TbMsg[14]?>&nbsp;</th>
			<?
				echo '<td colspan="3">';
				$driver="generic=generic";
				echo HTMLCTESELECT($driver,"netdriver","estilodesple","",$netdriver,100).'</td>';
			?>
		</tr>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!--------------------------------------------------------------UHU comprobar si se requiere validacion ------------------------------------------------------------------------------->

                <TR>
                        <TH align=center&nbsp;><? echo $TbMsg[15]; ?> &nbsp;</TD>
                        <?
                                echo '<TD colspan=3>';
                                $validaciones="1=Si".chr(13);
                                $validaciones.="0=No";
                                echo HTMLCTESELECT($validaciones,"validacion","estilodesple","",$validacion,100).'</TD>';
                        ?>
                </TR>
                 <TR>
                        <TH align=center>&nbsp;<?echo $TbMsg[16]?>&nbsp;</TD>
                        <?
                                if ($opcion==$op_eliminacion)
                                        echo '<TD colspan=3>'.$paginalogin.'</TD>';
                                else
                                        echo '<TD colspan=3><INPUT class="formulariodatos" name=paginalogin  type=text value="'.$paginalogin.'"></TD>';
                        ?>
                </TR>
                <TR>
                        <TH align=center>&nbsp;<?echo $TbMsg[17]?>&nbsp;</TD>
                        <?
                                if ($opcion==$op_eliminacion)
                                        echo '<TD colspan=3>'.$paginavalidacion.'</TD>';
                                else
                                        echo '<TD colspan=3><INPUT class="formulariodatos" name=paginavalidacion  type=text value="'.$paginavalidacion.'"></TD>';
                        ?>
                </TR>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<tr>
			<th colspan="4" align="center">&nbsp;<sup>*</sup> <?php echo $TbMsg["WARN_NETBOOT"]?>&nbsp;</th>
		</tr>

	</table>
</FORM>
</DIV>
<?
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________
?>
<BR>
<?
//________________________________________________________________________________________________________
//
// Frame con la información de la configuración
echo '<DIV align=center>';
echo '<IFRAME scrolling=auto height=500 width=90% frameborder=0
		 src="../principal/configuraciones.php?swp=1&idambito='.$idordenador.'&ambito='.$AMBITO_ORDENADORES.'"></IFRAME>';
echo '</DIV>';
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
//________________________________________________________________________________________________________
//	Recupera los datos de un ordenador
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- id: El identificador del ordenador
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global $idordenador; 
	global $nombreordenador;
	global $ip;
	global $mac;
	global $fotoordenador;
	global $idperfilhard;
	global $idrepositorio;
	global $idmenu;
	global $idprocedimiento;
	global $cache;
	global $netiface;
	global $netdriver;
########################### UHU
        global $validacion;
        global $paginalogin;
        global $paginavalidacion;
########################### UHU

	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM ordenadores WHERE idordenador=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombreordenador=$rs->campos["nombreordenador"];
		$ip=$rs->campos["ip"];
		$mac=$rs->campos["mac"];
		$idperfilhard=$rs->campos["idperfilhard"];
		$idrepositorio=$rs->campos["idrepositorio"];
		$idmenu=$rs->campos["idmenu"];
		$idprocedimiento=$rs->campos["idproautoexec"];
		$cache=$rs->campos["cache"];
		$netiface=$rs->campos["netiface"];
		$fotoordenador=$rs->campos["fotoord"];	//Creado para foto
		$netdriver=$rs->campos["netdriver"];
########################### UHU
                $validacion=$rs->campos["validacion"];
                $paginalogin=$rs->campos["paginalogin"];
                $paginavalidacion=$rs->campos["paginavalidacion"];
########################### UHU

		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
?>

