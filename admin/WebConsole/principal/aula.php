<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: aula.php
// Descripción : 
//		Visualiza los ordenadores de las aulas de un determinado Centro
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/MenuContextual.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/aulas_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/estados_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/mensajes_".$idioma.".php");
//________________________________________________________________________________________________________
$litambito=0; 
$idambito=0; 
$nombreambito=""; 
$idsrvrembo=0;
$idsrvdhcp=0;

if (isset($_GET["litambito"])) $litambito=$_GET["litambito"]; // Recoge parametros
if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if (isset($_GET["nombreambito"])) $nombreambito=$_GET["nombreambito"]; 

$Midordenador=  Array();
$Mnombreordenador=  Array();
$MimgOrdenador=Array();
$Mip= Array();
$Mmac=  Array();
$k=0; // Indice de la Matriz
	
$cadenaip="";
$idaula=0;
$nombreaula="";
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	</HEAD>
<BODY OnContextMenu="return false">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/aula.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/aulas.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>	
	<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/aulas_'.$idioma.'.js"></SCRIPT>'?>
<?php	
//________________________________________________________________________________________________________
switch($litambito){
	case $LITAMBITO_CENTROS :
		$ambito=$AMBITO_CENTROS;
		echo '<p align=center class=cabeceras>'.$TbMsg[22].'<br>'.$TbMsg[24].'<br><span class=subcabeceras>'.$nombreambito.'</span></p>';
		$cmd->texto="SELECT idcentro,nombrecentro FROM centros WHERE idcentro=".$idambito;
		RecorreCentro($cmd);
		break;
	case $LITAMBITO_GRUPOSAULAS :
		$ambito=$AMBITO_GRUPOSAULAS;
		echo '<p align=center class=cabeceras>'.$TbMsg[22].'<br>'.$TbMsg[25].'<br><span class=subcabeceras>'.$nombreambito.'</span></p>';
		$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE tipo=".$AMBITO_GRUPOSAULAS." AND idgrupo=".$idambito;
		RecorreGruposAulas($cmd);
		break;
	case $LITAMBITO_AULAS :
		$ambito=$AMBITO_AULAS;
		$cmd->texto="SELECT idaula, nombreaula, idordprofesor FROM aulas WHERE idaula=".$idambito;
		RecorreAulas($cmd);
		break;
	case $LITAMBITO_GRUPOSORDENADORES :
		$ambito=$AMBITO_GRUPOSORDENADORES;
		echo '<p align=center class=cabeceras>'.$TbMsg[22].'<br>'.$TbMsg[26].'<br><span class=subcabeceras>'.$nombreambito.'</span></p>';
		$cmd->texto="SELECT idgrupo,nombregrupoordenador FROM gruposordenadores WHERE idgrupo=".$idambito;
		RecorreGruposOrdenadores($cmd);
		pintaordenadores();
		break;
}
?>
<FORM name="fcomandos" action="" method="post" target="frame_contenidos">
	<INPUT type="hidden" name="idcomando" value="">
	<INPUT type="hidden" name="descricomando" value="">	
	<INPUT type="hidden" name="ambito" value="<?php echo $ambito?>">
	<INPUT type="hidden" name="idambito" value="<?php echo $idambito?>">
	<INPUT type="hidden" name="nombreambito" value="">
	<INPUT type="hidden" name="gestor" value="">
	<INPUT type="hidden" name="funcion" value="">
	<INPUT type="hidden" name="script" value="">
</FORM>
<?php
$flotante=new MenuContextual(); // Crea objeto menu contextual
$XMLcontextual=ContextualXMLAulas();  // Crea contextual de aulas
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLOrdenadores();  // Crea contextual de ordenadores
echo $flotante->CreaMenuContextual($XMLcontextual);

// Crea contextual de los comandos para los distintosn ámbitos
$XMLcontextual=ContextualXMLComandos($LITAMBITO_AULAS,$AMBITO_AULAS);
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLComandos($LITAMBITO_ORDENADORES,$AMBITO_ORDENADORES);
echo $flotante->CreaMenuContextual($XMLcontextual);

// Crea contextual de los asistentes para los distintosn ámbitos
$XMLcontextual=ContextualXMLAsistentes($LITAMBITO_AULAS,$AMBITO_AULAS);
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLAsistentes($LITAMBITO_ORDENADORES,$AMBITO_ORDENADORES);
echo $flotante->CreaMenuContextual($XMLcontextual);

// Crea contextual de los comandos para los distintos ámbitos
$XMLcontextual=ContextualXMLSincronizacion($LITAMBITO_AULAS,$AMBITO_AULAS);
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLSincronizacion($LITAMBITO_ORDENADORES,$AMBITO_ORDENADORES);
echo $flotante->CreaMenuContextual($XMLcontextual);

// Crea contextual de los comandos para los distintos �bitos
$XMLcontextual=ContextualXMLDiferenciacion($LITAMBITO_AULAS,$AMBITO_AULAS);
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLDiferenciacion($LITAMBITO_ORDENADORES,$AMBITO_ORDENADORES);
echo $flotante->CreaMenuContextual($XMLcontextual);

?>
<SCRIPT language="javascript">
	Sondeo();
</SCRIPT>
</BODY>
</HTML>
<?php
// *************************************************************************************************************************************************
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
		$cmd->texto="SELECT idaula,nombreaula,idordprofesor FROM aulas WHERE  grupoid=".$idgrupo." ORDER BY nombreaula";
		RecorreAulas($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreAulas($cmd){
	global $idaula;
	global $nombreaula;
	global $idordprofesor;
	global $k; // Indice de la Matriz
	global $cadenaip;

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$idaula=$rs->campos["idaula"];
		$nombreaula=$rs->campos["nombreaula"];
		$idordprofesor=(isset($rs->campos["idordprofesor"]) ? $rs->campos["idordprofesor"] : 0);
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

	$cmd->texto.= " ORDER BY nombreordenador";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 

	while (!$rs->EOF){
		$idordenador=$rs->campos["idordenador"];
		$Midordenador[$k]=$rs->campos["idordenador"];
		$Mnombreordenador[$k]=$rs->campos["nombreordenador"];
		$MimgOrdenador[$k]="ordenador_OFF.png";
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
	global $idordprofesor;
	global $servidorhidra,$hidraport;
	global $TbMsg;

	$ntr=0; // Numero de ordenadores por fila
	if ($nombreaula!=""){
		echo '<DIV>';
		echo '<p align=center class=cabeceras><img  border=0 nod="'.$LITAMBITO_AULAS.'-'.$idaula.'" value="'.$nombreaula.'"
				style="cursor:pointer" src="../images/iconos/aula.gif" oncontextmenu="nwmenucontextual(this,' ."'flo_".$LITAMBITO_AULAS."'" .')" >&nbsp;&nbsp;'.$TbMsg[23].'</br><span id="'.$LITAMBITO_AULAS.'-'.$idaula.'" class=subcabeceras>'.$nombreaula.'</span></p>';
	}
	echo '<TABLE style="border: 1px solid #d4d0c8;" align="center"><TR>';
	for($i=0;$i<$k;$i++){ // Vuelve a recorrer los datos de ordenadores para crear HTML
		$ntr++;
		echo '<TD>';
		echo '<table border=0>';
		echo '<tr>';
		echo '	<td align=center width=70 height=40>';
		echo '	<a href="#"><img  id="'.$Mip[$i].'" border=0 sondeo=""  nod="'.$LITAMBITO_ORDENADORES.'-'.$Midordenador[$i].'"
							 value="'.$Mnombreordenador[$i].'" src="../images/'.$MimgOrdenador[$i].'" oncontextmenu="nwmenucontextual(this,'."'flo_".$LITAMBITO_ORDENADORES."'" .')"  width="32" height="32"></A>';
		echo '	</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td align=center  id="'.$LITAMBITO_ORDENADORES.'-'.$Midordenador[$i].'">';
		echo '	<font color="#003300" size="1" face="Arial, Helvetica, sans-serif">'.$Mnombreordenador[$i].($Midordenador[$i]==$idordprofesor?' *':'').'</font>';
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
	echo '<p>';
	echo '<table style="border: #d4d0c8 1px solid; background: #eeeeee" align="center">';
	echo '  <tr align="center" valign="top">';
	foreach (Array ("OPG", "WIN", "LNX", "OSX") as $status) {
		echo '    <td><img src="../images/ordenador_'.$status.'.png" alt="'.$status.'" width="24" /><br /><font color="#003300" size="1" face="Arial, Helvetica, sans-serif">'.$TbMsg["STATUS_$status"].'</font></td>';
	}
	echo '  <tr align="center" valign="top">';
	foreach (Array ("BSY", "WINS", "LNXS", "OFF") as $status) {
		echo '    <td><img src="../images/ordenador_'.$status.'.png" alt="'.$status.'" width="24" /><br /><font color="#003300" size="1" face="Arial, Helvetica, sans-serif">'.str_replace(" ", "<br>", $TbMsg["STATUS_$status"]).'</font></td>';
	}
	echo '  </tr>';
	echo '  </tr>';
	echo '</table>';
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
	$layerXML.=' alpulsar="cola_acciones()"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	


	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="actualizar_ordenadores(this)"';
	$layerXML.=' imgitem="../images/iconos/actualizar.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';
	
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="consola_remota()"';
	$layerXML.=' imgitem="../images/iconos/shell.gif"';
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
	$layerXML.=' subflotante="flo_sincronizacion_'.$LITAMBITO_AULAS.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[49];
	$layerXML.='></ITEM>';
		
	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_asistentes_'.$LITAMBITO_AULAS.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[38];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
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
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
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
	$layerXML.=' alpulsar="cola_acciones()"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';
	
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ver_log('.$AMBITO_ORDENADORES.')"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem='.$TbMsg[47];
	$layerXML.='></ITEM>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ver_log_seguimiento('.$AMBITO_ORDENADORES.')"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem='.$TbMsg[48];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="actualizar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/actualizar.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="consola_remota()"';
	$layerXML.=' imgitem="../images/iconos/shell.gif"';
	$layerXML.=' textoitem='.$TbMsg[33];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eco_remoto()"';
	$layerXML.=' imgitem="../images/iconos/ecocon.gif"';
	$layerXML.=' textoitem='.$TbMsg[39];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_comandos_'.$LITAMBITO_ORDENADORES.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';
	
	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_sincronizacion_'.$LITAMBITO_ORDENADORES.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[49];
	$layerXML.='></ITEM>';
		
	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_asistentes_'.$LITAMBITO_ORDENADORES.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[38];
	$layerXML.='></ITEM>';
	
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
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
function ContextualXMLComandos($litambito,$ambito){
	global $cmd;
	global $TbMsg;
 	$maxlongdescri=0;
	$rs=new Recordset; 
	$cmd->texto="SELECT  idcomando,descripcion,pagina,gestor,funcion 
			FROM comandos 
			WHERE activo=1 AND submenu='' AND aplicambito & ".$ambito.">0 
			ORDER BY descripcion";
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$layerXML="";
		$rs->Primero(); 
		while (!$rs->EOF){
			$descrip=$TbMsg["COMMAND_".$rs->campos["funcion"]];
			if (empty ($descrip)) {
				$descrip=$rs->campos["descripcion"];
			}
			$layerXML.='<ITEM';
			$layerXML.=' alpulsar="confirmarcomando('."'".$ambito."'".','.$rs->campos["idcomando"].',\''.$rs->campos["descripcion"].'\',\''.$rs->campos["pagina"]. '\',\''.$rs->campos["gestor"]. '\',\''.$rs->campos["funcion"]. '\')"';
			$layerXML.=' textoitem="'.$descrip.'"';
			$layerXML.='></ITEM>';
			if ($maxlongdescri < strlen($descrip)) // Toma la Descripción de mayor longitud
				$maxlongdescri=strlen($descrip);
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
//________________________________________________________________________________________________________
function ContextualXMLSincronizacion($litambito,$ambito){
	global $cmd;
	global $TbMsg;
 	$maxlongdescri=0;
	$rs=new Recordset; 
	$cmd->texto="SELECT  idcomando,descripcion,pagina,gestor,funcion 
			FROM comandos 
			WHERE activo=1 AND submenu='Sincronizacion' AND aplicambito & ".$ambito.">0 
			ORDER BY descripcion";
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$layerXML="";
		$rs->Primero(); 
		while (!$rs->EOF){
			$descrip=$TbMsg["COMMAND_".$rs->campos["funcion"]];
			if (empty ($descrip)) {
				$descrip=$rs->campos["descripcion"];
			}
			$layerXML.='<ITEM';
			$layerXML.=' alpulsar="confirmarcomando('."'".$ambito."'".','.$rs->campos["idcomando"].',\''.$rs->campos["descripcion"].'\',\''.$rs->campos["pagina"]. '\',\''.$rs->campos["gestor"]. '\',\''.$rs->campos["funcion"]. '\')"';
			$layerXML.=' textoitem="'.$descrip.'"';
			$layerXML.='></ITEM>';
			if ($maxlongdescri < strlen($descrip)) // Toma la Descripción de mayor longitud
				$maxlongdescri=strlen($descrip);
			$rs->Siguiente();
		}
	$layerXML.='</MENUCONTEXTUAL>';
	$prelayerXML='<MENUCONTEXTUAL';
	$prelayerXML.=' idctx="flo_sincronizacion_'.$litambito.'"';
	$prelayerXML.=' maxanchu='.$maxlongdescri*7;
	$prelayerXML.=' clase="menu_contextual"';
	$prelayerXML.='>';
	$finallayerXML=$prelayerXML.$layerXML;
	return($finallayerXML);
	}
}
//________________________________________________________________________________________________________
function ContextualXMLDiferenciacion($litambito,$ambito){
	global $cmd;
	global $TbMsg;
 	$maxlongdescri=0;
	$rs=new Recordset; 
	$cmd->texto="SELECT  idcomando,descripcion,pagina,gestor,funcion 
			FROM comandos 
			WHERE activo=1 AND submenu='diferenciacion' AND aplicambito & ".$ambito.">0 
			ORDER BY descripcion";
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$layerXML="";
		$rs->Primero(); 
		while (!$rs->EOF){
			$descrip=$TbMsg["COMMAND_".$rs->campos["funcion"]];
			if (empty ($descrip)) {
				$descrip=$rs->campos["descripcion"];
			}
			$layerXML.='<ITEM';
			$layerXML.=' alpulsar="confirmarcomando('."'".$ambito."'".','.$rs->campos["idcomando"].',\''.$rs->campos["descripcion"].'\',\''.$rs->campos["pagina"]. '\',\''.$rs->campos["gestor"]. '\',\''.$rs->campos["funcion"]. '\')"';
			$layerXML.=' textoitem="'.$descrip.'"';
			$layerXML.='></ITEM>';
			if ($maxlongdescri < strlen($descrip)) // Toma la Descripción de mayor longitud
				$maxlongdescri=strlen($descrip);
			$rs->Siguiente();
		}
	$layerXML.='</MENUCONTEXTUAL>';
	$prelayerXML='<MENUCONTEXTUAL';
	$prelayerXML.=' idctx="flo_diferenciacion_'.$litambito.'"';
	$prelayerXML.=' maxanchu='.$maxlongdescri*6;
	$prelayerXML.=' clase="menu_contextual"';
	$prelayerXML.='>';
	$finallayerXML=$prelayerXML.$layerXML;
	return($finallayerXML);
	}
}
//________________________________________________________________________________________________________
function ContextualXMLAsistentes($litambito,$ambito){
	global $cmd;
	global $TbMsg;
 	$maxlongdescri=0;
	$rs=new Recordset; 
	$cmd->texto="SELECT  idcomando,descripcion,pagina,gestor,funcion 
			FROM asistentes 
			WHERE activo=1 AND aplicambito & ".$ambito.">0 
			ORDER BY descripcion";
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$layerXML="";
		$rs->Primero(); 
		while (!$rs->EOF){
			$descrip=$TbMsg["WIZARD_".$rs->campos["descripcion"]];
			if (empty ($descrip)) {
				$descrip=$rs->campos["descripcion"];
			}
			$layerXML.='<ITEM';
			$layerXML.=' alpulsar="confirmarcomando('."'".$ambito."'".','.$rs->campos["idcomando"].',\''.$rs->campos["descripcion"].'\',\''.$rs->campos["pagina"]. '\',\''.$rs->campos["gestor"]. '\',\''.$rs->campos["funcion"]. '\')"';
			$layerXML.=' textoitem="'.$descrip.'"';
			$layerXML.='></ITEM>';
			if($maxlongdescri<strlen($descrip)) // Toma la Descripción de mayor longitud
				$maxlongdescri=strlen($descrip);
			$rs->Siguiente();
		}
	$layerXML.='</MENUCONTEXTUAL>';
	$prelayerXML='<MENUCONTEXTUAL';
	$prelayerXML.=' idctx="flo_asistentes_'.$litambito.'"';
	$prelayerXML.=' maxanchu='.$maxlongdescri*7;
	$prelayerXML.=' clase="menu_contextual"';
	$prelayerXML.='>';
	$finallayerXML=$prelayerXML.$layerXML;
	return($finallayerXML);
	}
}
