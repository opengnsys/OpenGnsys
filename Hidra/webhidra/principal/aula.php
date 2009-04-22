<?
// *************************************************************************************************************************************************
// Aplicaci� WEB: Hidra
// Copyright 2003-2005  Jos�Manuel Alonso. Todos los derechos reservados.
// Fecha Creaci�: A� 2003-2004
// Fecha �tima modificaci�: Marzo-2005
// Nombre del fichero: aula.php
// Descripci� : 
//		Visualiza los ordenadores de las aulas de un determinado Centro
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/MenuContextual.php");
include_once("../clases/SockHidra.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/aulas_".$idioma.".php");
//________________________________________________________________________________________________________
$litambito=0; 
$idambito=0; 
$nombreambito=""; 
$idsrvrembo=0;
$idsrvdhcp=0;

if (isset($_GET["litambito"])) $litambito=$_GET["litambito"]; // Recoge parametros
if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if (isset($_GET["nombreambito"])) $nombreambito=$_GET["nombreambito"]; 
if (isset($_GET["idsrvrembo"])) $idsrvrembo=$_GET["idsrvrembo"]; 
if (isset($_GET["idsrvdhcp"])) $idsrvdhcp=$_GET["idsrvdhcp"]; 

$Midordenador=  Array();
$Mnombreordenador=  Array();
$MimgOrdenador=Array();
$Mip= Array();
$Mmac=  Array();
$k=0; // Indice de la Matriz
	
$cadenaip="";
$idaula=0;
$nombreaula="";

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexi� con servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
<LINK rel="stylesheet" type="text/css" href="../hidra.css">
</HEAD>
<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/aula.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/aulas_'.$idioma.'.js"></SCRIPT>'?>
<BODY OnContextMenu="return false">
<?	
//________________________________________________________________________________________________________
switch($litambito){
	case $LITAMBITO_CENTROS :
		echo '<p align=center class=cabeceras>'.$TbMsg[22].'<br>'.$TbMsg[24].'<br><span class=subcabeceras>'.$nombreambito.'</span></p>';
		$cmd->texto="SELECT idcentro,nombrecentro FROM centros WHERE idcentro=".$idambito;
		RecorreCentro($cmd);
		break;
	case $LITAMBITO_GRUPOSAULAS :
		echo '<p align=center class=cabeceras>'.$TbMsg[22].'<br>'.$TbMsg[25].'<br><span class=subcabeceras>'.$nombreambito.'</span></p>';
		$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE tipo=".$AMBITO_GRUPOSAULAS." AND idgrupo=".$idambito;
		RecorreGruposAulas($cmd);
		break;
	case $LITAMBITO_AULAS :
		$cmd->texto="SELECT idaula,nombreaula FROM aulas WHERE idaula=".$idambito;
		RecorreAulas($cmd);
		break;
	case $LITAMBITO_GRUPOSORDENADORES :
		echo '<p align=center class=cabeceras>'.$TbMsg[22].'<br>'.$TbMsg[26].'<br><span class=subcabeceras>'.$nombreambito.'</span></p>';
		$cmd->texto="SELECT idgrupo,nombregrupoordenador FROM gruposordenadores WHERE idgrupo=".$idambito;
		RecorreGruposOrdenadores($cmd);
		pintaordenadores();
		break;
}
$flotante=new MenuContextual(); // Crea objeto MenuContextual
$XMLcontextual=ContextualXMLAulas();  // Crea contextual de aulas
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLOrdenadores();  // Crea contextual de ordenadores
echo $flotante->CreaMenuContextual($XMLcontextual);

// Crea contextual de los comandos para los distintos �bitos
$XMLcontextual=ContextualXMLComandos($LITAMBITO_AULAS,$AMBITO_AULAS);
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLComandos($LITAMBITO_ORDENADORES,$AMBITO_ORDENADORES);
echo $flotante->CreaMenuContextual($XMLcontextual);

// Crea submenu contextual de clas de acciones
$XMLcontextual=ContextualXMLColasAcciones();  // Crea submenu contextual de acciones
echo $flotante->CreaMenuContextual($XMLcontextual);
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
//________________________________________________________________________________________________________
function RecorreCentro($cmd){
	global $AMBITO_GRUPOSAULAS;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	if(!$rs->EOF){
		$idcentro=$rs->campos["idcentro"];
		$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE idcentro=".$idcentro." AND grupoid=0 AND tipo=".$AMBITO_GRUPOSAULAS." ORDER BY nombregrupo ";
		RecorreGruposAulas($cmd);
		$cmd->texto="SELECT idaula,nombreaula FROM aulas WHERE idcentro=".$idcentro." AND grupoid=0 ORDER BY nombreaula";
		RecorreAulas($cmd);
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreGruposAulas($cmd){
	global $AMBITO_GRUPOSAULAS;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$idgrupo=$rs->campos["idgrupo"];
		$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE grupoid=".$idgrupo." AND tipo=".$AMBITO_GRUPOSAULAS." ORDER BY nombregrupo";
		RecorreGruposAulas($cmd);
		$cmd->texto="SELECT idaula,nombreaula FROM aulas WHERE  grupoid=".$idgrupo." ORDER BY nombreaula";
		RecorreAulas($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreAulas($cmd){
	global $idaula;
	global $nombreaula;
	global $k; // Indice de la Matriz
	global $cadenaip;

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$idaula=$rs->campos["idaula"];
		$nombreaula=$rs->campos["nombreaula"];
		$cmd->texto="SELECT idordenador,nombreordenador,ip,mac FROM ordenadores WHERE  idaula=".$idaula;
		$k=0;
		$cadenaip="";
		RecorreOrdenadores($cmd);
		pintaordenadores();
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreGruposOrdenadores($cmd){
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
while (!$rs->EOF){
		$idgrupo=$rs->campos["idgrupo"];
		$cmd->texto="SELECT idgrupo,nombregrupoordenador FROM gruposOrdenadores WHERE grupoid=".$idgrupo." ORDER BY nombregrupoordenador";
		RecorreGruposOrdenadores($cmd);
		$cmd->texto="SELECT idordenador,nombreordenador,ip,mac FROM ordenadores WHERE  grupoid=".$idgrupo;
		RecorreOrdenadores($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreOrdenadores($cmd){
	global $Midordenador;
	global $Mnombreordenador;
	global $MimgOrdenador;
	global $Mip;
	global $Mmac;
	global $k; // Indice de la Matriz
	
	global $cadenaip;
	global $idsrvrembo;
	global $idsrvdhcp;

	if (!empty($idsrvrembo)) $cmd->texto.=" AND idservidorrembo=".$idsrvrembo ;
	if (!empty($idsrvdhcp)) $cmd->texto.=" AND idservidordhcp=".$idsrvdhcp ;

	$cmd->texto.= " ORDER BY nombreordenador";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 

	while (!$rs->EOF){
		$idordenador=$rs->campos["idordenador"];
		$Midordenador[$k]=$rs->campos["idordenador"];
		$Mnombreordenador[$k]=$rs->campos["nombreordenador"];
		$MimgOrdenador[$k]="ordenador_OFF.gif";
		$Mip[$k]=$rs->campos["ip"];
		$Mmac[$k]=$rs->campos["mac"];
		$cadenaip.=$rs->campos["ip"].";";
		$k++;
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function pintaordenadores(){
	global $AMBITO_AULAS;
	global $AMBITO_ORDENADORES;
	global $LITAMBITO_AULAS;
	global $LITAMBITO_ORDENADORES;
	global $LONCABECERA;
	global $Midordenador;
	global $Mnombreordenador;
	global $MimgOrdenador;
	global $Mip;
	global $Mmac;
	global $k; // Indice de la Matriz
	global $cadenaip;
	global $idaula;
	global $nombreaula;
	global $servidorhidra,$hidraport;
	global $TbMsg;

	$shidra=new SockHidra($servidorhidra,$hidraport); 
	$parametros="1"; // Ejecutor
	$parametros.="nfn=Sondeo".chr(13);
	$parametros.="iph=".$cadenaip.chr(13);

	$resul=$shidra->conectar(); // Se ha establecido la conexi� con el servidor hidra
	if($resul){
		$resul=$shidra->envia_comando($parametros);
		$trama=$shidra->recibe_respuesta();
		$parametros=substr($trama,$LONCABECERA,strlen($trama)-$LONCABECERA);
		$ValorParametros=extrae_parametros($parametros,chr(13),'=');
		$trama_notificacion=$ValorParametros["tso"];
		$shidra->desconectar();
	}
	for($i=0;$i<$k;$i++){ // Vuelve a recorrer los datos de ordenadores para crear HTML
		$patron=$Mip[$i].'/';
		$pos=EnCadena($trama_notificacion,$patron);
		if($pos>-1){
			$tiposo=substr($trama_notificacion,$pos+strlen($patron),3);
			switch($tiposo){
				case 'INI':
								$MimgOrdenador[$i]="ordenador_INI.gif";  // Cliente ocupado
								break;
				case 'BSY':
								$MimgOrdenador[$i]="ordenador_BSY.gif";  // Cliente ocupado
								break;
				case 'RMB':
								$MimgOrdenador[$i]="ordenador_RMB.gif";  // Cliente Rembo
								break;
				case 'WS2': 
								$MimgOrdenador[$i]="ordenador_WS2.gif"; // Windows Server 2003
								break;
				case 'W2K':
								$MimgOrdenador[$i]="ordenador_W2K.gif"; // Windows 2000
								break;
				case 'WXP':
								$MimgOrdenador[$i]="ordenador_WXP.gif"; // Windows XP
								break;
				case 'WNT':
								$MimgOrdenador[$i]="ordenador_WNT.gif"; // Windows NT
								break;
				case 'W95':
								$MimgOrdenador[$i]="ordenador_W95.gif"; // Windows 95
								break;
				case 'W98':
								$MimgOrdenador[$i]="ordenador_W98.gif"; // Windows 98
								break;
				case 'WML':
								$MimgOrdenador[$i]="ordenador_WML.gif"; // Windows Millenium
								break;
				case 'LNX':
								$MimgOrdenador[$i]="ordenador_LNX.gif"; // Linux
								break;
			}
		}
	}
	$ntr=0; // Numero de ordenadores por fila
	if ($nombreaula!=""){
		echo '<DIV>';
		echo '<p align=center class=cabeceras><A href="#"><img  border=0 id="'.$LITAMBITO_AULAS.'-'.$idaula.'" value="'.$nombreaula.'" src="../images/iconos/aula.gif" onclick="veraulas(this);" oncontextmenu="menucontextual(this,' ."'flo_".$LITAMBITO_AULAS."'" .')" ></A>&nbsp;&nbsp;'.$TbMsg[23].'</br><span id="'.$LITAMBITO_AULAS.'-'.$idaula.'" class=subcabeceras>'.$nombreaula.'</span></p>';
	}
	echo '<TABLE style="BORDER-BOTTOM: #d4d0c8 1px solid;BORDER-LEFT: #d4d0c8 1px solid;BORDER-RIGHT: #d4d0c8 1px solid;BORDER-TOP: #d4d0c8 1px solid" align=center><TR>';
	for($i=0;$i<$k;$i++){ // Vuelve a recorrer los datos de ordenadores para crear HTML
		$ntr++;
		echo '<TD>';
		echo '<table border=0>';
		echo '<tr>';
		echo '	<td align=center width=70 height=40>';
		echo '	<a href="#"><img  id="'.$LITAMBITO_ORDENADORES.'-'.$Midordenador[$i].'" border=0   value="'.$Mnombreordenador[$i].'" src="../images/'.$MimgOrdenador[$i].'" oncontextmenu="menucontextual(this,'."'flo_".$LITAMBITO_ORDENADORES."'" .')"  width="32" height="32"></A>';
		echo '	</td>';
		echo '</tr>';
		echo '<tr>';
		
		echo '<td align=center  id="'.$LITAMBITO_ORDENADORES.'-'.$Midordenador[$i].'">';
		echo '	<font color="#003300" size="1" face="Arial, Helvetica, sans-serif">'.$Mnombreordenador[$i].'</font>';
		echo '	</br>';
		echo '	<font color="#003300" size="1" face="Arial, Helvetica, sans-serif">';
		echo '	<strong><font color="#D0A126">'.$Mip[$i].'</font></strong>';			
		echo '	</br>';
		echo '	<font color="#003300" size="1" face="Arial, Helvetica, sans-serif">'.$Mmac[$i].'</font>';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo '</TD>';
		if ($ntr>4){
			$ntr=0;
			echo '</TR><TR>';
		}
	}
	echo '</TABLE>';
	if ($nombreaula!="")
		echo '</DIV>';
}
//________________________________________________________________________________________________________
function ContextualXMLAulas(){
	global $TbMsg;
	global $AMBITO_AULAS;
	global $LITAMBITO_AULAS;
	global $RESERVA_CONFIRMADA;
	global $OPERADOR;
	
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_AULAS.'"';
	$layerXML.=' maxanchu=185';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_colasacciones"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="actualizar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/actualizar.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="purgar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/purgar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="conmutar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/hidra.gif"';
	$layerXML.=' textoitem='.$TbMsg[33];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_comandos_'.$LITAMBITO_AULAS.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="confirmarprocedimiento('.$AMBITO_AULAS.')"';
	$layerXML.=' imgitem="../images/iconos/procedimiento.gif"';
	$layerXML.=' textoitem='.$TbMsg[28];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="incorporarordenador()"';
	$layerXML.=' imgitem="../images/iconos/aula.gif"';
	$layerXML.=' textoitem='.$TbMsg[27];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ordenador_estandar()"';
	$layerXML.=' imgitem="../images/iconos/ordenadores.gif"';
	$layerXML.=' textoitem='.$TbMsg[12];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="configuraciones('.$AMBITO_AULAS.')"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.=' imgitem="../images/iconos/configuraciones.gif"';
	$layerXML.='></ITEM>';

	$wLeft=170;
	$wTop=80;
	$wWidth=480;
	$wHeight=480;
	$wpages="../propiedades/propiedades_aulas.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';	
	$layerXML.=' textoitem='.$TbMsg[13];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	// Crear operador
	$wLeft=140;
	$wTop=115;
	$wWidth=400;
	$wHeight=320;
	$wpages="../propiedades/propiedades_usuarios.php?idtipousuario=".$OPERADOR;
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.',3)"';
	$layerXML.=' imgitem="../images/iconos/operadores.gif"';
	$layerXML.=' textoitem='.$TbMsg[37];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="cola_reservas('.$RESERVA_CONFIRMADA.')"';
	$layerXML.=' imgitem="../images/iconos/reservas.gif"';
	$layerXML.=' textoitem='.$TbMsg[29];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLOrdenadores(){
	global $TbMsg;
	global $AMBITO_ORDENADORES;
	global $LITAMBITO_ORDENADORES;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_ORDENADORES.'"';
	$layerXML.=' maxanchu=140';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_colasacciones"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';
	
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="actualizar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/actualizar.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="purgar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/purgar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="conmutar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/hidra.gif"';
	$layerXML.=' textoitem='.$TbMsg[33];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_comandos_'.$LITAMBITO_ORDENADORES.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="confirmarprocedimiento('.$AMBITO_ORDENADORES.')"';
	$layerXML.=' imgitem="../images/iconos/procedimiento.gif"';
	$layerXML.=' textoitem='.$TbMsg[28];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="configuraciones('.$AMBITO_ORDENADORES.')"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.=' imgitem="../images/iconos/configuraciones.gif"';
	$layerXML.='></ITEM>';

	$wLeft=170;
	$wTop=80;
	$wWidth=480;
	$wHeight=400;
	$wpages="../propiedades/propiedades_ordenadores.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';	

	$layerXML.=' textoitem='.$TbMsg[13];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';	
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[18];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLColasAcciones(){
	global $TbMsg;
	global $EJECUCION_COMANDO;
	global $EJECUCION_TAREA;
	global $EJECUCION_TRABAJO;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_colasacciones"';
	$layerXML.=' maxanchu=90';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="cola_acciones('.$EJECUCION_COMANDO.')"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="cola_acciones('.$EJECUCION_TAREA.')"';
	$layerXML.=' imgitem="../images/iconos/tareas.gif"';
	$layerXML.=' textoitem='.$TbMsg[19];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="cola_acciones('.$EJECUCION_TRABAJO.')"';
	$layerXML.=' imgitem="../images/iconos/trabajos.gif"';
	$layerXML.=' textoitem='.$TbMsg[20];
	$layerXML.='></ITEM>'; 
	
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' alpulsar="cola_acciones(0)"';
	$layerXML.=' textoitem='.$TbMsg[21];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLComandos($litambito,$ambito){
	global $cmd;
 	$maxlongdescri=0;
	$rs=new Recordset; 
	$cmd->texto="SELECT idcomando,descripcion,interactivo FROM comandos WHERE activo=1 AND  aplicambito & ".$ambito.">0 ORDER BY descripcion";
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$layerXML="";
		$rs->Primero(); 
		while (!$rs->EOF){
			$layerXML.='<ITEM';
			$layerXML.=' alpulsar="confirmarcomando('."'".$ambito."'".','.$rs->campos["idcomando"].','.$rs->campos["interactivo" ]. ')"';
			$layerXML.=' textoitem="'.$rs->campos["descripcion"].'"';
			$layerXML.='></ITEM>';
			if($maxlongdescri<strlen($rs->campos["descripcion"])) // Toma la descripci� de mayor longitud
				$maxlongdescri=strlen($rs->campos["descripcion"]);
			$rs->Siguiente();
		}
	$layerXML.='</MENUCONTEXTUAL>';
	$prelayerXML='<MENUCONTEXTUAL';
	$prelayerXML.=' idctx="flo_comandos_'.$litambito.'"';
	$prelayerXML.=' maxanchu='.$maxlongdescri*7;
	$prelayerXML.=' clase="menu_contextual"';
	$prelayerXML.='>';
	$finallayerXML=$prelayerXML.$layerXML;
	return($finallayerXML);
	}
}