<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: configuraciones.php
// Descripción : 
//		Muestra la configuración de las particiones de los ordenadores de un aula
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/comunes.php");
include_once("../includes/constantes.php");
include_once("../includes/ConfiguracionesParticiones.php");
include_once("../idiomas/php/".$idioma."/configuraciones_".$idioma.".php");
//________________________________________________________________________________________________________
//
// Captura parámetros
//________________________________________________________________________________________________________

$ambito=0;
$idambito=0;
$swp=0; // Switch que indica si viene de las propiedades de ordenadores
// Agrupamiento por defecto
$fk_sysFi=0;
$fk_nombreSO=0;
$fk_tamano=0;
$fk_imagen=0;
$fk_perfil=0;
$fk_cache=0;

if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if (isset($_GET["ambito"])) $ambito=$_GET["ambito"]; 
if (isset($_GET["swp"])) $swp=$_GET["swp"]; 

if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
if (isset($_POST["ambito"])) $ambito=$_POST["ambito"]; 

if (isset($_POST["fk_sysFi"])) $fk_sysFi=$_POST["fk_sysFi"]; 
if (isset($_POST["fk_nombreSO"])) $fk_nombreSO=$_POST["fk_nombreSO"]; 
if (isset($_POST["fk_tamano"])) $fk_tamano=$_POST["fk_tamano"]; 
if (isset($_POST["fk_imagen"])) $fk_imagen=$_POST["fk_imagen"]; 
if (isset($_POST["fk_perfil"])) $fk_perfil=$_POST["fk_perfil"]; 
if (isset($_POST["fk_cache"])) $fk_cache=$_POST["fk_cache"];

//________________________________________________________________________________________________________

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
</HEAD>
<BODY>
<?
	switch($ambito){
			case $AMBITO_AULAS :
				$urlimg='../images/iconos/aula.gif';
				$textambito=$TbMsg[2];
				break;
			case $AMBITO_GRUPOSORDENADORES :
				$urlimg='../images/iconos/carpeta.gif';
				$textambito=$TbMsg[3];
				break;
			case $AMBITO_ORDENADORES :
				$urlimg='../images/iconos/ordenador.gif';
				$textambito=$TbMsg[4];
	}
	if(!$swp){
		echo '<p align=center><span class=cabeceras>'.$TbMsg[0].'&nbsp;</span><br>';
		echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras><U>'.$TbMsg[1].'
				: '.$textambito.'</U></span>&nbsp;&nbsp;</span></p>';
	}

	switch($ambito){
		case $AMBITO_AULAS :
			$resul=datosAulas($cmd,$idambito);
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$resul=datosGruposOrdenadores($cmd,$idambito);
			break;
		case $AMBITO_ORDENADORES :
			if(!$swp)
				$resul=datosOrdenadores($cmd,$idambito);
			break;
	}
	if($ambito!=$AMBITO_ORDENADORES){			
	?>
		<P align=center><SPAN align=center class=subcabeceras><? echo $TbMsg[19]?></SPAN></P>
		<FORM action="configuraciones.php" name="fdatos" method="POST">
				<INPUT type="hidden" name="idambito" value="<? echo $idambito?>">
				<INPUT type="hidden" name="ambito" value="<? echo $ambito?>">			
				<TABLE class="tabla_busquedas" align=center border=0 cellPadding=0 cellSpacing=0>
				<TR>
					<TH height=15 align="center" colspan=17><? echo $TbMsg[18]?></TH>
				</TR>
				<TR>

					<TD align=right><? echo $TbMsg[30]?></TD>
					<TD align=center><INPUT type="checkbox" value="<? echo $msk_sysFi?>" name="fk_sysFi" <? if($fk_sysFi==$msk_sysFi) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>
					<TD align=right><? echo $TbMsg[31]?></TD>
					<TD align=center><INPUT type="checkbox" value="<? echo $msk_nombreSO?>" name="fk_nombreSO" <? if($fk_nombreSO==$msk_nombreSO) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>
					<TD align=right><? echo $TbMsg[32]?></TD>
					<TD align=center><INPUT type="checkbox" value="<? echo $msk_tamano?>" name="fk_tamano" <? if($fk_tamano==$msk_tamano) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>
					<TD align=right><? echo $TbMsg[33]?></TD>
					<TD align=center><INPUT type="checkbox" value="<? echo $msk_imagen?>" name="fk_imagen" <? if($fk_imagen==$msk_imagen) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>		
					<TD align=right><? echo $TbMsg[34]?></TD>
					<TD align=center><INPUT type="checkbox" value="<? echo $msk_perfil?>" name="fk_perfil" <? if($fk_perfil==$msk_perfil) echo " checked "?>></TD>
                    <TD width="20" align=center>&nbsp;</TD>
					<TD align=right><? echo $TbMsg[495]?></TD>
                    <TD align=center><INPUT type="checkbox" value="<? echo $msk_cache?>" name="fk_cache" <? if($fk_cache==$msk_cache) echo " checked "?>></TD>


				</TR>
				<TR>
					<TD height=2 style="BORDER-TOP:#999999 1px solid;" align="center" colspan=17>&nbsp;</TD>			
				</TR>
				<TR>
					<TD height=20 align="center" colspan=14>
						<A href=#>
						<IMG border=0 src="../images/boton_confirmar.gif" onClick="document.fdatos.submit()"></A></TD>			
				</TR>
			</TABLE>
		</FORM>	
<?
	}
	$sws=$fk_sysFi | $fk_nombreSO | $fk_tamano | $fk_imagen | $fk_perfil | $fk_cache;	
	pintaConfiguraciones($cmd,$idambito,$ambito,8,$sws,false);	
?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
//	Descripción:
//		Crea una taba html con las especificaciones de particiones de un ambito ya sea ordenador,
//		grupo de ordenadores o aula
//	Parametros:
//		$configuraciones: Cadena con las configuraciones de particioners del ámbito. El formato 
//		sería una secuencia de cadenas del tipo "clave de configuración" separados por "@" 
//			Ejemplo:1;7;30000000;3;3;0;@2;130;20000000;5;4;0;@3;131;1000000;0;0;0;0
//________________________________________________________________________________________________________
function pintaParticiones($cmd,$configuraciones,$idordenadores,$cc)
{
	global $tbKeys; // Tabla contenedora de claves de configuración
	global $conKeys; // Contador de claves de configuración
	global $TbMsg;

	$colums=8;
	echo '<tr height="16">';
	echo '<th align="center">&nbsp;'.$TbMsg[20].'&nbsp;</th>'; // Número de partición
	echo '<th align="center">&nbsp;'.$TbMsg[24].'&nbsp;</th>'; // Tipo de partición
	echo '<th align="center">&nbsp;'.$TbMsg[27].'&nbsp;</th>'; // Sistema de ficheros
	echo '<th align="center">&nbsp;'.$TbMsg[21].'&nbsp;</th>'; // Sistema Operativo Instalado
	echo '<th align="center">&nbsp;'.$TbMsg[22].'&nbsp;</th>'; // Tamaño
	echo '<th align="center">&nbsp;'.$TbMsg[25].'&nbsp;</th>'; // Imagen instalada
	echo '<th align="center">&nbsp;'.$TbMsg[26].'&nbsp;</th>'; // Perfil software 
	echo '<th align="center">&nbsp;'.$TbMsg[495].'&nbsp;</th>';
	echo '</tr>';

	$auxCfg=split("@",$configuraciones); // Crea lista de particiones
	for($i=0;$i<sizeof($auxCfg);$i++){
		$auxKey=split(";",$auxCfg[$i]); // Toma clave de configuracion
		for($k=0;$k<$conKeys;$k++){ // Busca los literales para las claves de esa partición
			if($tbKeys[$k]["cfg"]==$auxCfg[$i]){ // Claves encontradas
				if ($tbKeys[$k]["numpar"] == 0) { // Info del disco (umpart=0)
					$disksize = tomaTamano($tbKeys[$k]["numpar"],$idordenadores);
					if (empty ($disksize)) {
						$disksize = '<em>'.$TbMsg[42].'</em>';
					}
					switch ($tbKeys[$k]["codpar"]) {
						case 1:  $disktable = "MSDOS";
							 break;
						case 2:  $disktable = "GPT";
							 break;
						default: $disktable = "";
					}
				}
				else {  // Información de partición (numpart>0)
					echo'<tr height="16">'.chr(13);
					echo'<td align="center">'.$tbKeys[$k]["numpar"].'</td>'.chr(13);
					if (is_numeric ($tbKeys[$k]["tipopar"])) {
						echo '<td align="center"><em>'.sprintf("%02X",$tbKeys[$k]["tipopar"]).'</em></td>'.chr(13);
					}
					else {
						echo '<td align="center">'.$tbKeys[$k]["tipopar"].'</td>'.chr(13);
					}
					echo'<td align="center">&nbsp;'.tomaSistemasFicheros($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</td>'.chr(13);

					echo '<td align="center">&nbsp;'.tomaNombresSO($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</td>'.chr(13);					

					echo'<td align="right">&nbsp;'.tomaTamano($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</td>'.chr(13);

					echo'<td align="center">&nbsp;'.tomaImagenes($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</td>'.chr(13);
					
					echo'<td align="center">&nbsp;'.tomaPerfiles($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</td>'.chr(13);

					if ($tbKeys[$k]["numpar"] == "4") {
						$rs=new Recordset; 
						$cmd->texto="SELECT * FROM  ordenadores_particiones WHERE idordenador='".$idordenadores."' AND numpar=4";
						$rs->Comando=&$cmd; 
						if (!$rs->Abrir()) return(false); // Error al abrir recordset
						$rs->Primero(); 
						if (!$rs->EOF){
							$campocache=$rs->campos["cache"];
						}
						$rs->Cerrar();
						echo '<td align="leght">&nbsp;';
						$ima=split(",",$campocache);
						$numero=1;
						for ($x=0;$x<count($ima); $x++) {
							if(substr($ima[$x],-3)==".MB") {
								echo '<strong>'.$TbMsg[4951].':  '.$ima[$x].'</strong>';
							} else {
								if(substr($ima[$x],-4)==".img") {
									echo '<br />'.$numero++.'.-'.$ima[$x];
								} else {
									echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;'.$ima[$x];
								}
							}
						}
						echo '&nbsp;</td>'.chr(13);
					} else {
						echo'<td align="center">&nbsp;&nbsp;</td>'.chr(13);
					}
					
					echo'</tr>'.chr(13);
				}
				break;
			}
		}
	}	
	// Mostrar información del disco, si se ha obtenido.
	if (!empty ($disksize)) {
		echo'<tr height="16">'.chr(13);
		echo'<td align="center">&nbsp;'.$TbMsg[35].'&nbsp;</td>'.chr(13);
		echo'<td align="center">&nbsp;'.$disktable.'&nbsp;</td>'.chr(13);
		echo'<td></td>'.chr(13);
		echo'<td></td>'.chr(13);
		echo'<td align="right">&nbsp;'.$disksize.'&nbsp;</td>'.chr(13);
		echo'<td></td>'.chr(13);
		echo'<td></td>'.chr(13);
		echo'<td></td>'.chr(13);
		echo'</tr>'.chr(13);
	}	
	echo '<tr height="5"><td colspan="'.$colums.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #FFFFFF;">&nbsp;</td></tr>';
}
//________________________________________________________________________________________________________
function datosAulas($cmd,$idaula)
{
	global $TbMsg;
	
	$cmd->texto="SELECT DISTINCT aulas.*, COUNT(ordenadores.idordenador) AS numordenadores
			 FROM aulas
			 LEFT JOIN ordenadores ON ordenadores.idaula=aulas.idaula
			 WHERE aulas.idaula=$idaula";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$rs->Primero(); 
		if (!$rs->EOF){
			$nombreaula=$rs->campos["nombreaula"];
			$urlfoto=$rs->campos["urlfoto"];
			$cagnon=$rs->campos["cagnon"];
			$pizarra=$rs->campos["pizarra"];
			$ubicacion=$rs->campos["ubicacion"];
			$comentarios=$rs->campos["comentarios"];
			$puestos=$rs->campos["puestos"];
			$ordenadores=$rs->campos["numordenadores"];
		}
		$rs->Cerrar();
	}
?> 
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
			<TR>	
			<TH align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TD>
			<?
					echo '<TD>'. $nombreaula.'</TD>';
					echo '<TH align=center>&nbsp;'.$TbMsg[7].'&nbsp;</TH>';
					echo '<TD><INPUT  class="formulariodatos" name=cagnon type=checkbox ';
					if ($cagnon) echo ' checked ';
					echo '></TD>';
			?>
							<TD valign=top align=center rowspan=3>
					<IMG border=3 style="border-color:#63676b"
					src="<? echo "../images/fotos/".$urlfoto?>">
					 <BR><center>&nbsp;<? echo $TbMsg[13].':&nbsp;'. $ordenadores?></center></TD>
		</TR>
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[6]?>&nbsp;</TH>
			<?
					echo '<TD>'.$ubicacion.'</TD>';
			?>
			<TH align=center>&nbsp;<?echo $TbMsg[8]?>&nbsp;</TD>
			<?
					echo '<TD><INPUT  class="formulariodatos" name=pizarra type=checkbox ';
					if ($pizarra) echo ' checked ';
					echo '></TD>';					
			?>
		</TR>	
		<TR>
			<TH align=center&nbsp;><?echo $TbMsg[9]?>&nbsp;</TD>
			<?
					echo '<TD>'.$puestos.'</TD>';
			?>
			<TH align=center>&nbsp;<?echo $TbMsg[11]?>&nbsp;</TD>
			<?
					echo '<TD>'.$comentarios.'</TD>';
			?>
		</TR>	
	</TABLE>
<?
}
//________________________________________________________________________________________________________
function datosOrdenadores($cmd,$idordenador)
{
	global $TbMsg;

	$cmd->texto="SELECT nombreordenador, ip, mac, fotoord, perfileshard.descripcion AS perfilhard 
			 FROM ordenadores
			 LEFT JOIN perfileshard ON perfileshard.idperfilhard=ordenadores.idperfilhard
			 WHERE ordenadores.idordenador=$idordenador";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$rs->Primero(); 
		if (!$rs->EOF){
			$nombreordenador=$rs->campos["nombreordenador"];
			$ip=$rs->campos["ip"];
			$mac=$rs->campos["mac"];
			$fotoordenador=$rs->campos["fotoord"];
			$perfilhard=$rs->campos["perfilhard"];
		}
		$rs->Cerrar();
	}
?> 
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[14]?>&nbsp;</TD>
			<TD><?php echo $nombreordenador;?></TD>
			<TD colspan=2 valign=top align=left rowspan=4><IMG border=2 style="border-color:#63676b"
src="<?php if ($fotoordenador==""){echo "../images/fotos/fotoordenador.gif";}
                else{echo "../images/fotos/".$fotoordenador;}?>">
			</TD>
			
			</TR>	
		<TR>
				<TH align=center>&nbsp;<?echo $TbMsg[15]?>&nbsp;</TD>
				<?echo '<TD>'.$ip.'</TD>';?>
			</TR>
		<TR>
				<TH align=center>&nbsp;<?echo $TbMsg[16]?>&nbsp;</TD>
				<? echo '<TD>'.$mac.'</TD>';?>
			</TR>	
		<TR>
				<TH align=center>&nbsp;<?echo $TbMsg[17]?>&nbsp;</TD>
				<? echo '<TD>'.$perfilhard.'</TD>';?>
			</TR>	
		<TR>
	</TABLE>
<?
}
?>	
<?
//________________________________________________________________________________________________________
function datosGruposOrdenadores($cmd,$idgrupo)
{
	global $TbMsg;

	$cmd->texto="SELECT DISTINCT gruposordenadores.*, COUNT(*) AS numordenadores
			 FROM gruposordenadores
			 INNER JOIN ordenadores ON ordenadores.grupoid=gruposordenadores.idgrupo
			 WHERE gruposordenadores.idgrupo=$idgrupo";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$rs->Primero(); 
		if (!$rs->EOF){
			$nombregrupoordenador=$rs->campos["nombregrupoordenador"];
			$ordenadores=$rs->campos["numordenadores"];
			$idaula=$rs->campos["idaula"];
		}
		$rs->Cerrar();
	}
		if ($numordenadores==0)
		{
		$cmd->texto="SELECT *, COUNT(*) AS numordenadores
			 FROM gruposordenadores
			 WHERE idgrupo=".$idgrupo;
		$rs=new Recordset; 
		$rs->Comando=&$cmd; 
		if ($rs->Abrir()){
			$rs->Primero(); 
			if (!$rs->EOF){
				$nombregrupoordenador=$rs->campos["nombregrupoordenador"];
				$ordenadores=$rs->campos["numordenadores"];
				$idaula=$rs->campos["idaula"];
			}
			$rs->Cerrar();
					}
		}
	//////////////////////////////////////
    $cmd->texto="SELECT DISTINCT aulas.*,count(*) as numordenadores
            	FROM aulas
                INNER JOIN ordenadores ON ordenadores.idaula=aulas.idaula
                WHERE aulas.idaula=".$idaula;  
				 
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$rs->Primero(); 
		if (!$rs->EOF){
			$urlfoto=$rs->campos["urlfoto"];
			$nombreaula=$rs->campos["nombreaula"];
		}
		$rs->Cerrar();
	}
?> 
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[5].'</br>'.$nombreaula?>&nbsp;</TD>
			<?
					echo '<TD>'.$nombregrupoordenador.'</TD>
								<TD colspan=2 valign=top align=center rowspan=2>
					<IMG border=3 style="border-color:#63676b" src="../images/fotos/'.$urlfoto.'"><br>
									<center>&nbsp;'.$TbMsg[13].':&nbsp;'. $ordenadores.'</center>
								</TD>';

			?>
		</TR>
	</TABLE>
<?
}
?>	

