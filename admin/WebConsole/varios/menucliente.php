<?php
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
include_once("../controlacceso.php");
include_once("../idiomas/php/".$idioma."/menucliente_".$idioma.".php");
/** Universidad de Huelva, comprueba si existe la variable de sesion validated
 Si validated no es true, hay que comprobar si se requiere validacion, a partir de aqui se
 encarga de todo access_controller.php
*/
if(!isset($_SESSION["validated"]) || $_SESSION["validated"] != true)
{
        $action="checkValidation";
        include("../validacion/access_controller.php");
}
else{

//___________________________________________________________________________________________________
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
		die($TbMsg[0].": ".$TbMsg[1]."=".$iph); 

	$UrlPagina=$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']; // Url página
	$UrlPagina=dirname($UrlPagina);
	$UrlPaginaIconos=dirname($UrlPagina)."/images/iconos";
	//________________________________________________________________________________________________________
	//agp
	$nombre_archivo = "/opt/opengnsys/log/clients/".$iph.".cache.txt";
	$gestor = fopen($nombre_archivo, 'r');
	$contenidofichero = fread($gestor, filesize($nombre_archivo));
	fclose($gestor);
	if (! empty ($contenidofichero)) {
		$cmd->texto="UPDATE ordenadores_particiones
				SET cache='".$contenidofichero."'
				WHERE idordenador=(SELECT idordenador FROM ordenadores
							WHERE ip='".$iph."') AND
					  idsistemafichero=(SELECT idsistemafichero FROM sistemasficheros
							 WHERE descripcion='CACHE')";
		$resul=$cmd->Ejecutar();
	}
	//agp
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
		$codeHtml.='<p style="color:#999999; font-size: 16px; margin: 2em;">'.$TbMsg[2].'</p>';
		$codeHtml.='<p style="font-size: 14px; margin: 2em;">';
		$codeHtml.='  <a href="command:poweroff">'.$TbMsg[3].'</a>';
		$codeHtml.='</p>';
		$codeHtml.='</div>';
	}
	?>
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		</head>
		<?

		if(!empty($rsmenu->campos["idurlimg"])){ // Imagen de fondo
			$urlimg=TomaDato($cmd,0,'iconos',$rsmenu->campos["idurlimg"],'idicono','urlicono');
			$urlimgfondo="../images/iconos/".$urlimg;
			echo '<body bgcolor="white" background="'.$urlimgfondo.'">';
		}
		else{
			echo'<body bgcolor="white" background="../images/iconos/fondo800x600.png">';
			//echo '<p align="left"><img border=0 src="../images/iconos/logoopengnsys.png"><p>';
		}
			include_once("/opt/opengnsys/log/clients/".$iph.".info.html");
			echo $codeHtml;

		?>
		</body>
		</html>
	<?
}
?>

