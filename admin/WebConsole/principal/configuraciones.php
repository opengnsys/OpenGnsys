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
					<TH height=15 align="center" colspan=14><? echo $TbMsg[18]?></TH>
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
				</TR>
				<TR>
					<TD height=2 style="BORDER-TOP:#999999 1px solid;" align="center" colspan=14>&nbsp;</TD>			
				</TR>
				<TR>
					<TD height=20 align="center" colspan=14>
						<A href=#>
						<IMG border=0 src="../images/boton_confirmar.gif" onclick="document.fdatos.submit()"></A></TD>			
				</TR>
			</TABLE>
		</FORM>	
<?
	}
	$sws=$fk_sysFi | $fk_nombreSO | $fk_tamano | $fk_imagen | $fk_perfil;
	pintaConfiguraciones($cmd,$idambito,$ambito,7,$sws,false);	
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
	
	$colums=7;
	echo '<TR height=16>';
	echo '<TH align=center>&nbsp;'.$TbMsg[20].'&nbsp;</TH>';	// Número de partición
	echo '<TH align=center>&nbsp;'.$TbMsg[24].'&nbsp;</TH>'; // Tipo de partición
	echo '<TH align=center>&nbsp;'.$TbMsg[27].'&nbsp;</TH>'; // Sistema de ficheros
	echo '<TH align=center>&nbsp;'.$TbMsg[21].'&nbsp;</TH>'; // Sistema Operativo Instalado
	echo '<TH align=center>&nbsp;'.$TbMsg[22].'&nbsp;</TH>'; // Tamaño
	echo '<TH align=center>&nbsp;'.$TbMsg[25].'&nbsp;</TH>'; // Imagen instalada
	echo '<TH align=center>&nbsp;'.$TbMsg[26].'&nbsp;</TH>'; // Perfil software 
	echo '</TR>';

	$auxCfg=split("@",$configuraciones); // Crea lista de particiones
	for($i=0;$i<sizeof($auxCfg);$i++){
			$auxKey=split(";",$auxCfg[$i]); // Toma clave de configuracion
			for($k=0;$k<$conKeys;$k++){ // Busca los literales para las claves de esa partición
				if($tbKeys[$k]["cfg"]==$auxCfg[$i]){ // Claves encontradas
					echo'<TR height=16>'.chr(13);
					echo'<TD align=center>&nbsp;'.$tbKeys[$k]["numpar"].'&nbsp;</TD>'.chr(13);
					echo'<TD align=center>&nbsp;'.$tbKeys[$k]["tipopar"].'&nbsp;</TD>'.chr(13);

					//echo'<TD align=center>&nbsp;'.$tbKeys[$k]["sistemafichero"].'&nbsp;</TD>'.chr(13);
					echo'<TD align=center>&nbsp;'.tomaSistemasFicheros($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);

					//echo '<TD>&nbsp;'.$tbKeys[$k]["nombreso"].'&nbsp;</TD>'.chr(13);
					echo '<TD align=center>&nbsp;'.tomaNombresSO($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);					

					//echo'<TD align=rigth>&nbsp;'.formatomiles($tbKeys[$k]["tamano"]).'&nbsp;</TD>'.chr(13);
					echo'<TD align=center>&nbsp;'.tomaTamano($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);
										
					//echo'<TD>&nbsp;'.$tbKeys[$k]["imagen"].'&nbsp;</TD>'.chr(13);
					echo'<TD align=center>&nbsp;'.tomaImagenes($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);
					
					//echo'<TD>&nbsp;'.$tbKeys[$k]["perfilsoft"].'&nbsp;</TD>'.chr(13);
					echo'<TD align=center>&nbsp;'.tomaPerfiles($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);
					
					echo'</TR>'.chr(13);
					break;
				}
			}
	}	
	echo '<TR height=5><TD colspan='.$colums.' style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #FFFFFF;">&nbsp;</TD></TR>';
}
//________________________________________________________________________________________________________
function datosAulas($cmd,$idaula)
{
	global $TbMsg;
	
	$cmd->texto="SELECT DISTINCT aulas.*,count(*) as numordenadores
							 FROM aulas
							 INNER JOIN ordenadores ON ordenadores.idaula=aulas.idaula
							 WHERE aulas.idaula=".$idaula;							 
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
					 src="<? if ($urlfoto=="") 	echo "../images/aula.jpg"; else 	echo $urlfoto;?>">
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

	$cmd->texto="SELECT nombreordenador,ip,mac,perfileshard.descripcion as perfilhard 
							 FROM ordenadores
							 INNER JOIN perfileshard ON perfileshard.idperfilhard=ordenadores.idperfilhard
							 WHERE ordenadores.idordenador=".$idordenador;				 
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$rs->Primero(); 
		if (!$rs->EOF){
			$nombreordenador=$rs->campos["nombreordenador"];
			$ip=$rs->campos["ip"];
			$mac=$rs->campos["mac"];
			$perfilhard=$rs->campos["perfilhard"];
		}
		$rs->Cerrar();
	}
?> 
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[14]?>&nbsp;</TD>
			<? echo '<TD>'.$nombreordenador.'</TD>';?>
			<TD colspan=2 valign=top align=left rowspan=4><IMG border=2 style="border-color:#63676b" src="../images/fotoordenador.gif"></TD>
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

	$cmd->texto="SELECT DISTINCT gruposordenadores.*,count(*) as numordenadores
							 FROM gruposordenadores
							 INNER JOIN ordenadores ON ordenadores.grupoid=gruposordenadores.idgrupo
							 WHERE gruposordenadores.idgrupo=".$idgrupo;			 
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$rs->Primero(); 
		if (!$rs->EOF){
			$nombregrupoordenador=$rs->campos["nombregrupoordenador"];
			$ordenadores=$rs->campos["numordenadores"];
		}
		$rs->Cerrar();
	}
?> 
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TD>
			<?
					echo '<TD>'.$nombregrupoordenador.'</TD>
								<TD colspan=2 valign=top align=center rowspan=2>
									<IMG border=3 style="border-color:#63676b" src="../images/aula.jpg"><br>
									<center>&nbsp;'.$TbMsg[13].':&nbsp;'. $ordenadores.'</center>
								</TD>';

			?>
		</TR>
	</TABLE>
<?
}
?>	

