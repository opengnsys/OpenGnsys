<?
// *************************************************************************
// Aplicación� WEB: ogAdmWebCon
// Autor: Jos�Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaci�: A� 2003-2004
// Fecha �tima modificaci�: Marzo-2006
// Nombre del fichero: menubrowser.php
// Descripci� : 
//		Muestra menu en el browser del cliente
// ****************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexi�con servidor B.D.
//________________________________________________________________________________________________________
$ITEMS_PUBLICOS=1;
$ITEMS_PRIVADOS=2;
$iph="0.0.0.0";
$tip=$ITEMS_PUBLICOS; // Tipo de items 1=Públicos 2=privados

if (isset($_GET["iph"]))	$iph=$_GET["iph"]; 
if (isset($_GET["tip"]))	$tip=$_GET["tip"]; 

// Se asegura que la pagina se solicita desde la IP que viene
global $HTTP_SERVER_VARS;
if ($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"] != ""){
	$ipcliente = $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
}
else{
	$ipcliente = $HTTP_SERVER_VARS["REMOTE_ADDR"]; 
}
/*
if($ipcliente!=$iph)
	die("***ATENCION.- Usted no esta accediendo desde un ordenador permitido"); 
*/
//________________________________________________________________________________________________________
$rsmenu=RecuperaMenu($cmd,$iph);	// Recupera un recordset con los datos del m en
?>
	<HTML>
	<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	</HEAD>
	<BODY>
<?	
if(!empty($rsmenu)){
	switch($tip){
		case $ITEMS_PUBLICOS:
			if($rsmenu->campos["htmlmenupub"])
				$codeHtml=$rsmenu->campos["htmlmenupub"];
			else{
				$_SESSION["widcentro"]=$rsmenu->campos["idcentro"]; 
				$codeHtml=GeneraMenu($rsmenu,$ITEMS_PUBLICOS,$iph); // Genera men pblico
			}
			break;
			
		case $ITEMS_PRIVADOS:
			if($rsmenu->campos["htmlmenupri"])
				$codeHtml=$rsmenu->campos["htmlmenupri"];
			else{
				$_SESSION["widcentro"]=$rsmenu->campos["idcentro"]; 
				$codeHtml=GeneraMenu($rsmenu,$ITEMS_PRIVADOS,$iph); // Genera men pblico
			}
			break;
	}			
}
else{
	$codeHtml='<P align=center>';
	$codeHtml.='<SPAN style="COLOR: #999999;FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE: 16px;">NO SE HA DETEACTADO NINGÚN MENÚ PARA ESTE CLIENTE</SPAN>';
	$codeHtml.='</P>';
}
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
	$cmd->texto="SELECT menus.idcentro,menus.resolucion,menus.titulo,menus.coorx,menus.coory,menus.modalidad,
						menus.scoorx,menus.scoory,menus.smodalidad,menus.htmlmenupub,menus.htmlmenupri,
						acciones_menus.tipoaccion,acciones_menus.idaccionmenu,acciones_menus.idtipoaccion,
						acciones_menus.tipoitem,acciones_menus.descripitem,iconos.urlicono 
						FROM ordenadores
						INNER JOIN menus ON menus.idmenu = ordenadores.idmenu 
						INNER JOIN acciones_menus ON acciones_menus.idmenu = menus.idmenu
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
			

	$UrlPagina=$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']; // Url página
	$UrlPagina=dirname($UrlPagina);
	$UrlPaginaIconos=dirname($UrlPagina)."/images/iconos";
			
	if($tipo==$ITEMS_PRIVADOS)
		$mod=$smodalidad;
	else
		$mod=$modalidad;
	$codeHTML="";

	//	Genera HTML de la p�ina en funci� de las propiedades del Men del clioente
	//$codeHTML.='<DIV style="POSITION:absolute;TOP:'.$coory."px;LEFT:".$coorx.'px">';

	$codeHTML.='<P align=left><IMG border=0 src="http://'.$UrlPaginaIconos.'/logoopengnsys.png" width=64><P>';
	
	$codeHTML.='<P align=center>';
	$codeHTML.='<SPAN style="COLOR: #999999;FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE: 20px;"><U>'.$titulo.'</U></SPAN>';
	$codeHTML.='</BR>';
	
	$codeHTML.='<TABLE cellspacing=4 cellpadding=0 align="center" border=0 >';
	$codeHTML.='<TR>';
	$codeHTML.='<TD colspan="'.($mod*2).'" >&nbsp;</TD>';
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

			$codeHTML.='<TD align=center><A href="ejecutaritem.php?iph='.$iph.'&idt='.$idaccionmenu.'"><IMG border=0 src="http://'.$UrlPaginaIconos.'/'.$urlicono.'" width=64></A></TD>';
			$codeHTML.='<TD style="font-family:Arial;color: #a71026;FONT-SIZE:14"><A href="ejecutaritem.php?iph='.$iph.'&idt='.$idaccionmenu.'">'.$descripitem.'</A></TD>';
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
			$codeHTML.='<A href="acceso_operador.php?iph='.$iph.'"><SPAN style="COLOR: blue;FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE: 10px;">Administración</SPAN>';
			break;
		case $ITEMS_PRIVADOS:
			$codeHTML.='<A href="menucliente.php?iph='.$iph.'"><SPAN style="COLOR: blue;FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE: 10px;">Volver</SPAN>';
			break;
	}			
	$codeHTML.='</P>';
	//$codeHTML.='</DIV>';
	return($codeHTML);
}
?>
