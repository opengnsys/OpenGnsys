<?
// *************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2006
// Nombre del fichero: menubrowser.php
// Descripción : 
//		Muestra menu en el browser del cliente
// ****************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.
//________________________________________________________________________________________________________
$iph="0.0.0.0";
if (isset($_GET["iph"]))	$iph=$_GET["iph"]; 
//________________________________________________________________________________________________________
$rsmenu=RecuperaMenu($cmd,$iph);	// Recupera un recordset con los datos del m enú
?>
	<HTML>
	<HEAD>
	</HEAD>
	<BODY>
<?	
$ITEMS_PUBLICOS=1;
$ITEMS_PRIVADOS=2;

if(!empty($rsmenu)){
	$codeHtml=GeneraMenu($rsmenu,$ITEMS_PUBLICOS,$iph); // Genera menú público
	echo $codeHtml;
}
else
	echo '<H1>NO SE HA DETCTADO NINGÚN MENÚ PARA ESTE CLIENTE</H1>';	
?>
	</BODY>
	</HTML>
<?
//___________________________________________________________________________________________________
//
// Recupera Menú
//___________________________________________________________________________________________________
function RecuperaMenu($cmd,$iph){
	$rs=new Recordset; 
	$cmd->texto="SELECT menus.resolucion,menus.titulo,menus.coorx,menus.coory,menus.modalidad,
						menus.scoorx,menus.scoory,menus.smodalidad,menus.htmlmenupub,menus.htmlmenupri,
						acciones_menus.tipoaccion,acciones_menus.idaccionmenu,acciones_menus.idtipoaccion,
						acciones_menus.tipoitem,acciones_menus.descripitem,acciones_menus.idurlimg 
						FROM ordenadores
						INNER JOIN menus ON menus.idmenu = ordenadores.idmenu 
						INNER JOIN acciones_menus ON acciones_menus.idmenu = menus.idmenu
						WHERE ordenadores.ip='".$iph."' ORDER by acciones_menus.orden";

	$rs->Comando=&$cmd; 
	$resul=$rs->Abrir();
	if (!$rs->Abrir()) return(false);
	if ($rs->EOF) return(false);
	return($rs);
}
//___________________________________________________________________________________________________
//
// Muestra el menu público
//___________________________________________________________________________________________________
function GeneraMenu($rs,$tipo,$iph){	
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

	if($tipo==$ITEMS_PRIVADOS)
		$mod=$smodalidad;
	else
		$mod=$modalidad;
	//	Genera HTML de la página en función de las propiedades del Menú del clioente
	$codeHTML='<DIV style="POSITION:absolute;TOP:'.$coory.";LEFT:".$coorx.'">';
	$codeHTML.='<TABLE cellspacing=3 cellpadding=3 align="center" border=0 >';
	$codeHTML.='<TR>';
	$codeHTML.='<TD align=center colspan="'.($mod*2).'" style="COLOR: #999999;FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE: 36px;">'.$titulo.'</TD>';
	$codeHTML.='</TR>';
	$codeHTML.='<TR height=30>';
	$codeHTML.='<TD>&nbsp;</TD>';
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
			$idurlimg=$rs->campos["idurlimg"]; 
			$codeHTML.='<TD><IMG src="../images/iconos/confirmadas.gif"></TD>';
			$codeHTML.='<TD style="font-family:sans-serif;color: #a71026"><A href="ejecutaritem.php?iph='.$iph.'&idt='.$idaccionmenu.'">'.$descripitem.'</A></TD>';
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
	return($codeHTML);
}
?>
