<?
/// funciones php

#devuelve los elementos [texto] multicast para un formulario.
#$ambito (aula=4 y ordenadores=16)
function htmlForm_mcast($cmd,$ambito,$idambito)
{
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
        $SelectHtml.='puerto    :<input type="text" size="3" name="mcastpuerto" value="'.$rs->campos["pormul"] . '" /> <br />';
		$rs->Siguiente();
		$SelectHtml.='direccion Mcast :<input type="text" size="15"  maxlength="15" name="mcastdireccion" value="'.$rs->campos["ipmul"] . '" /> <br />';
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
		$SelectHtml.='modo      :<input type="text" size="8" name="mcastmodo" value="'.$modomulticast. '" /> <br />';
		$rs->Siguiente();
		$SelectHtml.='velocidad   :<input type="text" size="6" name="mcastvelocidad" value="'.$rs->campos["velmul"] . '" /> <br />';
	
	$rs->Cerrar();
	}
	        $SelectHtml.='nº Max. clientes      :<input type="text" size="8" name="mcastnclien" value="50" /> <br />';
			$SelectHtml.='Tiempo(seg) Max. Espera     :<input type="text" size="8" name="mcastseg" value="60" /> <br />';
			
	return($SelectHtml);	
}


#devuelve los elementos [texto] p2p  para un formulario.
#$ambito (aula=4 y ordenadores=16)
function htmlForm_p2p($cmd,$ambito,$idambito)
{
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
        $SelectHtml.='modo    :<input type="text" size="10" name="modp2p" value="'.$rs->campos["modp2p"] . '" /> <br />';
		$rs->Siguiente();
		$SelectHtml.='tiempo de semilla :<input type="text" size="10"  maxlength="15" name="timep2p" value="'.$rs->campos["timep2p"] . '" /> <br />';
		$rs->Siguiente();
		$rs->Cerrar();
	}

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






function htmlOPTION_images($cmd)
{
	$SelectHtml="";
	$cmd->texto="SELECT *,repositorios.ip as iprepositorio	FROM  imagenes
				INNER JOIN repositorios ON repositorios.idrepositorio=imagenes.idrepositorio"; 
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
	$cmd->texto='SELECT DISTINCT tipopar FROM tipospar JOIN ordenadores_particiones ON ordenadores_particiones.codpar = tipospar.codpar WHERE numpar = ' .$numpar;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 

	if ($rs->Abrir()){
		$rs->Primero(); 
		while (!$rs->EOF){
			if ( $rs->campos["tipopar"] == "LINUX" )
			{
			$valor="EXT4";
			$valormostrar="LINUX:EXT[2:3:4]";
			}
			else
			{
			$valor=$rs->campos["tipopar"];
			$valormostrar=$rs->campos["tipopar"];
			}
			$SelectHtml.='<OPTION value="'.$valor . '" ';
			$SelectHtml.='>';
			$SelectHtml.= $valormostrar .'</OPTION>';
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
	return($SelectHtml);	
}







function htmlForm_typepartnotcacheEngine10()
{
$SelectHtml="";
$SelectHtml.='<OPTION value="FAT12"> FAT12 </OPTION>';
$SelectHtml.='<OPTION value="FAT16"> FAT16 </OPTION>';
$SelectHtml.='<OPTION value="FAT32"> FAT32 </OPTION>';
$SelectHtml.='<OPTION value="NTFS"> NTFS </OPTION>';
#$SelectHtml.='<OPTION value="EXT2"> EXT2 </OPTION>';
#$SelectHtml.='<OPTION value="EXT3"> EXT3 </OPTION>';
$SelectHtml.='<OPTION value="EXT4"> LINUX:EXT[2:3:4] </OPTION>';
$SelectHtml.='<OPTION value="LINUX-SWAP"> LINUX-SWAP </OPTION>';
$SelectHtml.='<OPTION value="REISERFS"> REISERFS </OPTION>';
$SelectHtml.='<OPTION value="REISER4"> RESISER4 </OPTION>';
$SelectHtml.='<OPTION value="XFS"> XFS </OPTION>';
$SelectHtml.='<OPTION value="JFS"> JFS </OPTION>';
$SelectHtml.='<OPTION value="LINUX-RAID"> LINUX-RAID </OPTION>';
$SelectHtml.='<OPTION value="LINUX-LVM"> LINUX-LVM </OPTION>';
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

?> 