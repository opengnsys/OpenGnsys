<?
// *************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha útima modificación: Marzo-2006
// Nombre del fichero: menubrowser.php
// Descripción : 
//		Muestra menu en el browser del cliente
// ****************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/TomaDato.php");
include_once("../includes/CreaComando.php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión servidor B.D.
//________________________________________________________________________________________________________
$ITEMS_PUBLICOS=1;
$ITEMS_PRIVADOS=2;

$tip=$ITEMS_PUBLICOS; // Tipo de items 1=Públicos 2=privados
if (isset($_GET["tip"]))	$tip=$_GET["tip"]; 

$iph=tomaIP();
if(empty($iph))
	die("***ATENCION.- Usted no esta accediendo desde un ordenador permitido: Dirección IP=".$iph); 

$UrlPagina=$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']; // Url página
$UrlPagina=dirname($UrlPagina);
$UrlPaginaIconos=dirname($UrlPagina)."/images/iconos";
//________________________________________________________________________________________________________
$rsmenu=RecuperaMenu($cmd,$iph);	// Recupera un recordset con los datos del m en
if(!empty($rsmenu)){
	switch($tip){
		case $ITEMS_PUBLICOS:
			if(!empty($rsmenu->campos["htmlmenupub"])){
				$urlHtml=$rsmenu->campos["htmlmenupub"];
				if(strtoupper(substr($urlHtml,0,7))!="HTTP://") $urlHtml="http://".$urlHtml;
				Header('Location: '.$urlHtml); // Url del menu personalizado
			}
			else{
				$_SESSION["widcentro"]=$rsmenu->campos["idcentro"]; 
				$codeHtml=GeneraMenu($rsmenu,$ITEMS_PUBLICOS,$iph); // Genera men pblico
			}
			break;
			
		case $ITEMS_PRIVADOS:
			if(!empty($rsmenu->campos["htmlmenupri"])){
				$urlHtml=$rsmenu->campos["htmlmenupri"];
				
				if(strtoupper(substr($urlHtml,0,7))!="HTTP://") $urlHtml="http://".$urlHtml;
				Header('Location: '.$urlHtml); // Url del menu personalizado
			}
			else{
				$_SESSION["widcentro"]=$rsmenu->campos["idcentro"]; 
				$codeHtml=GeneraMenu($rsmenu,$ITEMS_PRIVADOS,$iph); // Genera men pblico
			}
			break;
	}			
}
else{
	$codeHtml='<div align="center" style="font-family: Arial, Helvetica, sans-serif;">';
	$codeHtml.='<p style="color:#999999; font-size: 16px; margin: 2em;">';
	$codeHtml.='  NO SE HA DETECTADO NING&Uacute;N MEN&Uacute; PARA ESTE CLIENTE';
	$codeHtml.='</p>';
	$codeHtml.='<p style="font-size: 14px; margin: 2em;">';
	$codeHtml.='  <a href="command:poweroff">Apagar el equipo</a>';
	$codeHtml.='</p>';
	$codeHtml.='</div>';
}
?>
	<HTML>
	<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	</HEAD>
	<?

	if(!empty($rsmenu->campos["idurlimg"])){ // Imagen de fondo
		$urlimg=TomaDato($cmd,0,'iconos',$rsmenu->campos["idurlimg"],'idicono','urlicono');
		$urlimgfondo="../images/iconos/".$urlimg;
		echo '<BODY  bgcolor=white background="'.$urlimgfondo.'">';
	}
	else{
		echo'<BODY  bgcolor=white background="../images/iconos/fondo800x600.png">';
		#echo '<P align=left><IMG border=0 src="../images/iconos/logoopengnsys.png"><P>';
	}
		include_once("/opt/opengnsys/log/clients/".$iph.".info.html");
		echo $codeHtml;
	?>
	</BODY>
	</HTML>
<?
//___________________________________________________________________________________________________
//
// Recupera Men
//___________________________________________________________________________________________________
function RecuperaMenu($cmd,$iph){
	$rs=new Recordset; 
	$cmd->texto="SELECT menus.idcentro,menus.resolucion,menus.titulo,menus.idurlimg,menus.coorx,menus.coory,
			menus.modalidad,menus.scoorx,menus.scoory,menus.smodalidad,menus.htmlmenupub,menus.htmlmenupri,
			acciones_menus.tipoaccion,acciones_menus.idaccionmenu,acciones_menus.idtipoaccion,
			acciones_menus.tipoitem,acciones_menus.descripitem,iconos.urlicono
			FROM ordenadores
			INNER JOIN menus ON menus.idmenu = ordenadores.idmenu 
			LEFT OUTER JOIN acciones_menus ON acciones_menus.idmenu = menus.idmenu
			LEFT OUTER JOIN iconos ON iconos.idicono=acciones_menus.idurlimg
			WHERE ordenadores.ip='".$iph."' ORDER by acciones_menus.orden";

	$rs->Comando=&$cmd; 
	$resul=$rs->Abrir();
	if (!$rs->Abrir()) return(false);
	if ($rs->EOF) return(false);
	return($rs);
}
//___________________________________________________________________________________________________
//
// Muestra el menu pblico
//___________________________________________________________________________________________________
function GeneraMenu($rs,$tipo,$iph){	
	global $ITEMS_PUBLICOS;
	global $ITEMS_PRIVADOS;
	global $UrlPaginaIconos;

	$titulo=$rs->campos["titulo"]; 
	$coorx=$rs->campos["coorx"]; 
	$coory=$rs->campos["coory"]; 
	$modalidad=$rs->campos["modalidad"]; 
	$scoorx=$rs->campos["scoorx"]; 
	$scoory=$rs->campos["scoory"]; 
	$smodalidad=$rs->campos["smodalidad"]; 
	$scoory=$rs->campos["scoory"]; 
	$resolucion=$rs->campos["resolucion"]; 
	$htmlmenupub=$rs->campos["htmlmenupub"]; 
	$htmlmenupri=$rs->campos["htmlmenupri"]; 
			
	if($tipo==$ITEMS_PRIVADOS)
		$mod=$smodalidad;
	else
		$mod=$modalidad;
	$codeHTML="";

	//	Genera HTML de la p�ina en funci� de las propiedades del Men del clioente
	//$codeHTML.='<DIV style="POSITION:absolute;TOP:'.$coory."px;LEFT:".$coorx.'px">';
	$codeHTML.='<P align=center>';
	$codeHTML.='<SPAN style="COLOR: #999999;FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE: 20px;"><U>'.$titulo.'</U></SPAN>';
	$codeHTML.='</BR>';
	
	$codeHTML.='<TABLE cellspacing=4 cellpadding=0 align="center" border=0 >';
	$codeHTML.='<TR>';
	$codeHTML.='<TD colspan="'.(($mod*2)+1).'" >&nbsp;</TD>';
	$codeHTML.='</TR>';
	$codeHTML.='<TR>';

	$c=0; // Contador de columnas
	
	while (!$rs->EOF){ // Recorre acciones del menu
		$tipoitem=$rs->campos["tipoitem"]; 
		if($tipoitem==$tipo){
			$tipoaccion=$rs->campos["tipoaccion"]; 
			$idtipoaccion=$rs->campos["idtipoaccion"]; 
			$idaccionmenu=$rs->campos["idaccionmenu"]; 
			$descripitem=$rs->campos["descripitem"]; 
			$urlicono=$rs->campos["urlicono"]; 
			if(empty($urlicono))
				$urlicono="defaultitem.gif"; 

			$codeHTML.='<TD align=center>
				<A href="ejecutaritem.php?iph='.$iph.'&idt='.$idaccionmenu.'">
					<IMG border=0 src="http://'.$UrlPaginaIconos.'/'.$urlicono.'" width=64></A></TD>';
			$codeHTML.='<TD style="font-family:Arial;color: #a71026;FONT-SIZE:14">
				<A style="text-decoration:none" href="ejecutaritem.php?iph='.$iph.'&idt='.$idaccionmenu.'">
					<span style="FONT-FAMILY: Verdana,Arial, Helvetica, sans-serif;FONT-SIZE: 12px;COLOR:#999999">'.$descripitem.'</span></A></TD>';
			if($mod>1){
				//separación de columnas
				$codeHTML.='<TD width=10>&nbsp;</TD>';
			}
			$c++;
			if($c%$mod==0){
				$codeHTML.='</TR>';
				$codeHTML.='<TR>';
			}
		}
		$rs->Siguiente();
	}
	$codeHTML.='</TR>';
	$rs->Cerrar();
	$codeHTML.='</TABLE>';
	$codeHTML.='</P>';
	$codeHTML.='<BR><BR>';
	$codeHTML.='<P align=center>';

	switch($tipo){
		case $ITEMS_PUBLICOS:
			$url.='acceso_operador.php';
			$lit="Administrar";
			break;
		case $ITEMS_PRIVADOS:
			$url.='menucliente.php';
			$lit="Volver";
			break;
	}		
	$codeHTML.='<A style="text-decoration:none" href="'.$url.'?iph='.$iph.'">';
	$codeHTML.='<SPAN style="
				BORDER-BOTTOM: #999999 1px solid;
				BORDER-LEFT: #999999 1px solid;
				BORDER-RIGHT: #999999 1px solid;
				BORDER-TOP: #999999 1px solid;
				COLOR:#999999;FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE:9px;">&nbsp;'.$lit.'&nbsp;</SPAN></A>';
	
	$codeHTML.='</P>';
	//$codeHTML.='</DIV>';
	return($codeHTML);
}
//___________________________________________________________________________________________________
//
// Redupera la ip del cliente web
//___________________________________________________________________________________________________
function tomaIP(){	
	// Se asegura que la pagina se solicita desde la IP que viene
	global $HTTP_SERVER_VARS;
	if ($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"] != "")
		$ipcliente = $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
	else
		$ipcliente = $HTTP_SERVER_VARS["REMOTE_ADDR"]; 
	if (empty ($ipcliente))
		$ipcliente = $_SERVER["REMOTE_ADDR"];

	return($ipcliente);
}
?>
