<?php
/// funciones php

#devuelve los elementos [texto] multicast para un formulario.
#$ambito (aula=4 y ordenadores=16)
function htmlForm_mcast($cmd,$ambito,$idambito)
{
global $TbMsg;

//if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if ($ambito == 4) 
{
$cmd->texto='SELECT aulas.pormul,aulas.ipmul,aulas.modomul,aulas.velmul,aulas.modp2p,aulas.timep2p FROM  aulas where aulas.idaula=' . $idambito ;
}

if ($ambito == 8) 
{
$cmd->texto='SELECT aulas.pormul,aulas.ipmul,aulas.modomul,aulas.velmul,aulas.modp2p,aulas.timep2p FROM  aulas JOIN gruposordenadores ON aulas.idaula=gruposordenadores.idaula where gruposordenadores.idgrupo=' . $idambito ;
}

if ($ambito == 16)
{
$cmd->texto='SELECT aulas.pormul,aulas.ipmul,aulas.modomul,aulas.velmul,aulas.modp2p,aulas.timep2p FROM  aulas JOIN ordenadores ON ordenadores.idaula=aulas.idaula where ordenadores.idordenador=' . $idambito ;
}

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
if ($rs->Abrir()){
	$rs->Primero(); 
	$SelectHtml = $TbMsg["WDI24"] . ':<input type="text" size="3" name="mcastpuerto" value="'.$rs->campos["pormul"] . '" /> <br />';
		$rs->Siguiente();
		$SelectHtml.= $TbMsg["WDI25"] . ':<input type="text" size="15"  maxlength="15" name="mcastdireccion" value="'.$rs->campos["ipmul"] . '" /> <br />';
		$rs->Siguiente();
		switch ($rs->campos["modomul"]) 
		{
			case 1:
			    $modomulticast="half-duplex";
				break;
			default:
			    $modomulticast="full-duplex";
				break;
		} 
		$SelectHtml.= $TbMsg["WDI26"] . ':<input type="text" size="8" name="mcastmodo" value="'.$modomulticast. '" /> <br />';
		$rs->Siguiente();
		$SelectHtml.= $TbMsg["WDI27"] . ':<input type="text" size="6" name="mcastvelocidad" value="'.$rs->campos["velmul"] . '" /> <br />';
	
	$rs->Cerrar();
	}
	        $SelectHtml.= $TbMsg["WDI28"] . ':<input type="text" size="8" name="mcastnclien" value="50" /> <br />';
			$SelectHtml.= $TbMsg["WDI29"] . ' :<input type="text" size="8" name="mcastseg" value="60" /> <br />';
			
	return($SelectHtml);	
}


#devuelve los elementos [texto] p2p  para un formulario.
#$ambito (aula=4 y ordenadores=16)
function htmlForm_p2p($cmd,$ambito,$idambito)
{
global $TbMsg;
//if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if ($ambito == 4) 
{
$cmd->texto='SELECT aulas.modp2p,aulas.timep2p FROM  aulas where aulas.idaula=' . $idambito ;
}
if ($ambito == 8) 
{
$cmd->texto='SELECT aulas.modp2p,aulas.timep2p FROM  aulas JOIN gruposordenadores ON aulas.idaula=gruposordenadores.idaula where gruposordenadores.idgrupo=' . $idambito ;
}


if ($ambito == 16)
{
$cmd->texto='SELECT aulas.modp2p,aulas.timep2p FROM  aulas JOIN ordenadores ON ordenadores.idaula=aulas.idaula where ordenadores.idordenador=' . $idambito ;
}

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
if ($rs->Abrir()){
		$rs->Primero(); 
        $SelectHtml.= $TbMsg["WDI26"] . ' :<input type="text" size="10" name="modp2p" value="'.$rs->campos["modp2p"] . '" /> <br />';
		$rs->Siguiente();
		$SelectHtml.= $TbMsg["WDI30"] . ' :<input type="text" size="10"  maxlength="15" name="timep2p" value="'.$rs->campos["timep2p"] . '" /> <br />';
		$rs->Siguiente();
		$rs->Cerrar();
	}

return($SelectHtml);	
}


function htmlForm_unicast($cmd,$ambito,$idambito)
{
global $TbMsg;
//if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if ($ambito == 4)
{
$cmd->texto='SELECT nombreordenador,idordenador,ip FROM  ordenadores where idaula=' . $idambito ;
}

if ($ambito == 8) 
{
$cmd->texto='SELECT nombreordenador,idordenador,ip FROM  ordenadores where grupoid=' . $idambito ;
}
if ($ambito == 16)
{
$cmd->texto='SELECT nombreordenador,idordenador,ip FROM  ordenadores where idaula=' . $idambito ;
}

	$SelectHtml="";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 

	if ($rs->Abrir()){
		$rs->Primero(); 
		$ucastclient="";
		while (!$rs->EOF){
			$ucastclient.= $rs->campos["ip"] . ":" ;
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
		$SelectHtml.= $TbMsg["WDI24"] . ' :<input type="text" size="8" name="ucastport" value="8000" /> <br />';
		$SelectHtml.= $TbMsg["WDI28"] . ' :<input type="text" size="98" name="ucastclient" value="' . $ucastclient . '" /> <br />';
		
	return($SelectHtml);	
}



function htmlOPTION_equipos($cmd,$ambito,$idambito)
{

//if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if ($ambito == 4)
{
$cmd->texto='SELECT nombreordenador,idordenador,ip FROM  ordenadores where idaula=' . $idambito ;
}

if ($ambito == 8) 
{
$cmd->texto='SELECT nombreordenador,idordenador,ip FROM  ordenadores where grupoid=' . $idambito ;
}
if ($ambito == 16)
{
$cmd->texto='SELECT nombreordenador,idordenador,ip FROM  ordenadores where idaula=' . $idambito ;
}

	$SelectHtml="";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 

	if ($rs->Abrir()){
		$rs->Primero(); 
		while (!$rs->EOF){
			$SelectHtml.='<OPTION value="'.$rs->campos["ip"] . '" ';
			$SelectHtml.='>';
			$SelectHtml.= $rs->campos["nombreordenador"] .'</OPTION>';
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
	//$SelectHtml.= '</SELECT>';
	return($SelectHtml);	
}






function htmlOPTION_images($cmd,$ambito,$idambito)
{
if ($ambito == 4)
{
$subconsultarepo='SELECT DISTINCT idrepositorio from ordenadores where idaula=' . $idambito ;
}
if ($ambito == 8) 
{
$subconsultarepo='SELECT DISTINCT idrepositorio FROM  ordenadores where grupoid=' . $idambito ;
}
if ($ambito == 16)
{
$subconsultarepo='SELECT idrepositorio FROM  ordenadores where idordenador=' . $idambito ;
}	
	
	
	$SelectHtml="";
	$cmd->texto="SELECT *,repositorios.ip as iprepositorio	FROM  imagenes
				INNER JOIN repositorios ON repositorios.idrepositorio=imagenes.idrepositorio AND repositorios.idrepositorio=(" . $subconsultarepo . ")"; 
	
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	
	if ($rs->Abrir()){
		$rs->Primero(); 
		while (!$rs->EOF){
			$SelectHtml.='<OPTION value="'.$rs->campos["nombreca"] . '" ';
			$SelectHtml.='>';
			$SelectHtml.= $rs->campos["descripcion"] .'</OPTION>';
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
	else
	{
		$SelectHtml.='<option value=""> ERROR: Ambito con multiples Repositorios --</option>';
	
	}
	return($SelectHtml);	
}



function HTMLSELECT_imagenes($cmd,$idimagen,$numpar,$codpar,$icp,$sw)
{
	$SelectHtml="";
	$cmd->texto="SELECT *,repositorios.ip as iprepositorio	FROM  imagenes
				INNER JOIN repositorios ON repositorios.idrepositorio=imagenes.idrepositorio"; 
	if($sw) // ImÃ¡genes con el mismo tipo de particiÃ³n 
		$cmd->texto.=	"	WHERE imagenes.codpar=".$codpar;								
	else
		$cmd->texto.=	"	WHERE imagenes.codpar<>".$codpar;		
		
	$cmd->texto.=" AND imagenes.numpar>0 AND imagenes.codpar>0 AND imagenes.idrepositorio>0"; // La imagene debe existir y estar creada	
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if($sw) $des=1; else $des=0;
	$SelectHtml.= '<SELECT class="formulariodatos" id="despleimagen_'.$icp.'_'.$des.'" style="WIDTH:220">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';

	if ($rs->Abrir()){
		$rs->Primero(); 
		while (!$rs->EOF){
			$SelectHtml.='<OPTION value="'.$rs->campos["idimagen"]."_".$rs->campos["nombreca"]."_".$rs->campos["iprepositorio"]."_".$rs->campos["idperfilsoft"].'"';
			if($idimagen==$rs->campos["idimagen"]) $SelectHtml.=" selected ";
			$SelectHtml.='>';
			$SelectHtml.= $rs->campos["descripcion"].'</OPTION>';
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
	$SelectHtml.= '</SELECT>';
	return($SelectHtml);
}



function htmlOPTION_typepartnotcache($cmd)
{
	$SelectHtml="";
	$cmd->texto='SELECT tipopar FROM tipospar WHERE NOT tipopar = "CACHE"';
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	//$SelectHtml.= '<SELECT class="formulariodatos" name="nombre" id="identificador" style="WIDTH:220" ;"    >';
	//$SelectHtml.= '    <OPTION value="0"></OPTION>';

	if ($rs->Abrir()){
		$rs->Primero(); 
		while (!$rs->EOF){
			$SelectHtml.='<OPTION value="'.$rs->campos["tipopar"] . '" ';
			$SelectHtml.='>';
			$SelectHtml.= $rs->campos["tipopar"] .'</OPTION>';
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
	//$SelectHtml.= '</SELECT>';
	return($SelectHtml);	
}


function htmlForm_typepart($cmd,$numpar)
{
	$SelectHtml="";
	$cmd->texto='SELECT DISTINCT tipopar FROM tipospar
			JOIN ordenadores_particiones ON ordenadores_particiones.codpar = tipospar.codpar
			WHERE numpar = ' .$numpar;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 

	if ($rs->Abrir()){
		$rs->Primero(); 
		while (!$rs->EOF){
			$valor=$rs->campos["tipopar"];
			$SelectHtml.='<option value="'.$valor.'"> '.$valor.' </option>';
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
	return($SelectHtml);	
}


function htmlForm_typepartnotcacheEngine10($npart)
{
$SelectHtml="";
if ($npart == 4) {
    $SelectHtml.='<OPTION value="CACHE"> CACHE </OPTION>';
}
$SelectHtml.='<OPTION value="NTFS"> NTFS </OPTION>';
$SelectHtml.='<OPTION value="FAT32"> FAT32 </OPTION>';
$SelectHtml.='<OPTION value="FAT16"> FAT16 </OPTION>';
$SelectHtml.='<OPTION value="FAT12"> FAT12 </OPTION>';
$SelectHtml.='<OPTION value="HNTFS"> Hidden NTFS </OPTION>';
$SelectHtml.='<OPTION value="HFAT32"> Hidden FAT32 </OPTION>';
$SelectHtml.='<OPTION value="HFAT16"> Hidden FAT16 </OPTION>';
$SelectHtml.='<OPTION value="HFAT12"> Hidden FAT12 </OPTION>';
$SelectHtml.='<OPTION value="LINUX"> LINUX </OPTION>';
$SelectHtml.='<OPTION value="LINUX-SWAP"> LINUX-SWAP </OPTION>';
$SelectHtml.='<OPTION value="LINUX-RAID"> LINUX-RAID </OPTION>';
$SelectHtml.='<OPTION value="LINUX-LVM"> LINUX-LVM </OPTION>';
$SelectHtml.='<OPTION value="HFS"> HFS </OPTION>';
$SelectHtml.='<OPTION value="FREEBSD"> FREEBSD </OPTION>';
$SelectHtml.='<OPTION value="OPENBSD"> OPENBSD </OPTION>';
$SelectHtml.='<OPTION value="SOLARIS"> SOLARIS </OPTION>';
$SelectHtml.='<OPTION value="SOLARIS-BOOT"> SOLARIS-BOOT </OPTION>';
$SelectHtml.='<OPTION value="VMFS"> VMFS </OPTION>';
$SelectHtml.='<OPTION value="DATA"> DATA </OPTION>';
$SelectHtml.='<OPTION value="EFI"> EFI </OPTION>';
$SelectHtml.='<OPTION value="GPT"> GPT </OPTION>';
if ($npart <= 4) {
    $SelectHtml.='<OPTION value="EXTENDED"> EXTENDED </OPTION>';
}
return($SelectHtml);
}

function htmlForm_typepartnotcacheGPT($npart)
{
$SelectHtml="";
if ($npart == 4) {
    $SelectHtml.='<OPTION value="CACHE"> CACHE </OPTION>';
}
$SelectHtml.='<OPTION value="WINDOWS"> Windows </OPTION>';
$SelectHtml.='<OPTION value="WIN-RESERV"> Windows Reserved </OPTION>';
$SelectHtml.='<OPTION value="LINUX"> Linux </OPTION>';
$SelectHtml.='<OPTION value="LINUX-RESERV"> Linux Reserved </OPTION>';
$SelectHtml.='<OPTION value="LINUX-SWAP"> Linux Swap </OPTION>';
$SelectHtml.='<OPTION value="LINUX-RAID"> Linux RAID </OPTION>';
$SelectHtml.='<OPTION value="LINUX-LVM"> Linux LVM </OPTION>';
$SelectHtml.='<OPTION value="CHROMEOS"> ChromeOS </OPTION>';
$SelectHtml.='<OPTION value="CHROMEOS-KRN"> ChromeOS Kernel </OPTION>';
$SelectHtml.='<OPTION value="CHROMEOS-RESERV"> ChromeOS Reserved </OPTION>';
$SelectHtml.='<OPTION value="HFS"> MacOS HFS </OPTION>';
$SelectHtml.='<OPTION value="HFS-RAID"> MacOS HFS RAID </OPTION>';
$SelectHtml.='<OPTION value="FREEBSD"> FreeBSD </OPTION>';
$SelectHtml.='<OPTION value="FREEBSD-DISK"> FreeBSD Disk </OPTION>';
$SelectHtml.='<OPTION value="FREEBSD-BOOT"> FreeBSD Boot </OPTION>';
$SelectHtml.='<OPTION value="FREEBSD-SWAP"> FreeBSD Swap </OPTION>';
$SelectHtml.='<OPTION value="SOLARIS"> Solaris </OPTION>';
$SelectHtml.='<OPTION value="SOLARIS-DISK"> Solaris Disk </OPTION>';
$SelectHtml.='<OPTION value="SOLARIS-BOOT"> Solaris Boot </OPTION>';
$SelectHtml.='<OPTION value="SOLARIS-SWAP"> Solaris Swap </OPTION>';
$SelectHtml.='<OPTION value="EFI"> EFI </OPTION>';
$SelectHtml.='<OPTION value="MBR"> MBR </OPTION>';
$SelectHtml.='<OPTION value="BIOS-BOOT"> BIOS Boot </OPTION>';
return($SelectHtml);
}

function htmlForm_sizepart($cmd,$numpar)
{
	$SelectHtml="";
	$cmd->texto='SELECT DISTINCT tamano FROM ordenadores_particiones WHERE numpar = ' .$numpar . ' AND NOT tamano = 0';
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 

	if ($rs->Abrir()){
		$rs->Primero(); 
		while (!$rs->EOF){
			$SelectHtml.='<OPTION value="'.$rs->campos["tamano"] . '" ';
			$SelectHtml.='>';
			$SelectHtml.= $rs->campos["tamano"] .'</OPTION>';
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
	return($SelectHtml);	
}

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
				if ($tbKeys[$k]["numpar"] == 0) { // Info del disco (umpart=0)
					$disksize = tomaTamano($tbKeys[$k]["numpar"],$idordenadores);
				}
				else {  // Información de partición (numpart>0)
					echo'<TR height=16>'.chr(13);
					echo'<TD align=center>&nbsp;'.$tbKeys[$k]["numpar"].'&nbsp;</TD>'.chr(13);
					echo'<TD align=center>&nbsp;'.$tbKeys[$k]["tipopar"].'&nbsp;</TD>'.chr(13);

					//echo'<TD align=center>&nbsp;'.$tbKeys[$k]["sistemafichero"].'&nbsp;</TD>'.chr(13);
					echo'<TD align=center>&nbsp;'.tomaSistemasFicheros($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);

					//echo '<TD>&nbsp;'.$tbKeys[$k]["nombreso"].'&nbsp;</TD>'.chr(13);
					echo '<TD align=center>&nbsp;'.tomaNombresSO($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);					

					//echo'<TD align=rigth>&nbsp;'.formatomiles($tbKeys[$k]["tamano"]).'&nbsp;</TD>'.chr(13);
					echo'<TD align=right>&nbsp;'.tomaTamano($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);
										
					//echo'<TD>&nbsp;'.$tbKeys[$k]["imagen"].'&nbsp;</TD>'.chr(13);
					echo'<TD align=center>&nbsp;'.tomaImagenes($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);
					
					//echo'<TD>&nbsp;'.$tbKeys[$k]["perfilsoft"].'&nbsp;</TD>'.chr(13);
					echo'<TD align=center>&nbsp;'.tomaPerfiles($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);
					
					echo'</TR>'.chr(13);
					break;
				}
			}
		}
	}	
	// Mostrar información del disco, si se ha obtenido.
	if (!empty ($disksize)) {
		echo'<tr height="16">'.chr(13);
		echo'<td align="center">&nbsp;'.$TbMsg[35].'&nbsp;</td>'.chr(13);
		echo'<td></td>'.chr(13);
		echo'<td></td>'.chr(13);
		echo'<td></td>'.chr(13);
		echo'<td name="disksize" id="disksize" align="right">&nbsp;'.$disksize.'&nbsp;</td>'.chr(13);
		echo'<td></td>'.chr(13);
		echo'<td></td>'.chr(13);
		echo'</tr>'.chr(13);
	}
	echo '<TR height=5><TD colspan='.$colums.' style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #FFFFFF;">&nbsp;</TD></TR>';
}

?> 
