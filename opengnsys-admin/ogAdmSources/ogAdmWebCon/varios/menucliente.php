<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Copyright 200-2005 José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2005
// Fecha Última modificación: Abril-2005
// Nombre del fichero: menumliente.php
// Descripción :
//		Pagina del menu del cliente. Éste la solicita a través de su browser local
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../clases/MenuContextual.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/menumliente_".$idioma.".php");
//________________________________________________________________________________________________________
$ipordenador="0.0.0.0";

if (isset($_GET["ipordenador"]))	$ipordenador=$_GET["ipordenador"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2');  // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
$rsmenu=RecuperaMenu($cmd,$ipordenador);	// Recupera un recordset con los datos del m enú
?>
	<HTML>
	<HEAD>
	</HEAD>
	<BODY>
<?	
if(!empty($rsmenu))
	GeneraMenuPublico($rsmenu);
?>
	</BODY>
	</HTML>
<?
//___________________________________________________________________________________________________
//
// Recupera Menú
//___________________________________________________________________________________________________
function RecuperaMenu($cmd,$ipordenador);	
	$rs=new Recordset; 
	$cmd->texto="SELECT menus.resolucion,menus.titulo,menus.coorx,menus.coory,menus.modalidad,
						menus.scoorx,menus.scoory,menus.smodalidad,menus.htmlmenupub,menus.htmlmenupri,
						acciones_menus.tipoaccion,acciones_menus.idaccionmenu,acciones_menus.idtipoaccion,
						acciones_menus.tipoitem,acciones_menus.descripitem,acciones_menus.idurlimg 
						FROM ordenadores
						INNER JOIN menus ON menus.idmenu = ordenadores.idmenu 
						INNER JOIN acciones_menus ON acciones_menus.idmenu = menus.idmenu
						WHERE ordenadores.ip='".$ipordenador."' ORDER by acciones_menus.orden";
	$rs->Comando=&$cmd; 
	$resul=$rs->Abrir();
	if (!$rs->Abrir()) return(false);
	if ($rs->EOF()) return(false);
	return($rs);
}
//___________________________________________________________________________________________________
//
// Muestra el menu público
//___________________________________________________________________________________________________
function GeneraMenuPublico($rs){	
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

	//	Genera HTML de la página en función de las propiedades del Menú del clioente
	$codeHTML='<DIV style="POSITION:absolute;TOP:'.$coory.";LEFT:".$coorx.'">';
	$codeHTML.='<H1>'.$titulo.'</H1>';	
	$codeHTML.='<TABLE align="center" style="font-family:sans-serif;color: #a71026">';
	$codeHTML.='<TR>';

	$c=0; // Contador de columnas
	
	while (!$rs->EOF){ // Recorre acciones del menu
		$tipoaccion=$rs->campos["tipoaccion"]; 
		$tipoitem=$rs->campos["tipoitem"]; 
		$idtipoaccion=$rs->campos["idtipoaccion"]; 
		$idaccionmenu=$rs->campos["idaccionmenu"]; 
		$descripitem=$rs->campos["descripitem"]; 
		$idurlimg=$rs->campos["idurlimg"]; 

		$codeHTML.='<TD><IMG src="./imagenes/icono:item"></TD>';
		$codeHTML.='<TD>'.$descripitem.'</TD>';
		if($c%$modalidad==0){
			$codeHTML.='/<TR>';
			$codeHTML.='<TR>';
		}
		$codeHTML.='/<TR>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($codeHTML);
}
?>
