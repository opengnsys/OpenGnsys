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
        include_once("../validacion/functions.php");

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
	$codeHtml="";
	//________________________________________________________________________________________________________
	//agp Tomamos el tipo de disco DISK o NVM
//________________________________________________________________________________________________________
	// Leemos el fichero que contiene la información de discos
	$nom_fich = "/opt/opengnsys/log/clients/".$iph.".tdisk.txt";
	$cont_fich = file_get_contents($nom_fich);//echo $cont_fich;
	// quitamos 2 ultimos caracteres (espacio y ;)
	$cont_fich = substr($cont_fich, 0, -2);
	$disk_l=explode(";",$cont_fich);

	for($i=0;$i<count($disk_l);$i++){
		// Obtenemos los 3 primeros caracteres del disco
		$dcar=substr($disk_l[$i], 0, 3);
		// Comprobamos si los 2 primeros caracteres son DISCOS ejemp: 1:0  ,  2:0  , 3:0
		if ( $dcar == $i.":0"){
			// Obtenemos el último campo DISK - NVM
			$disko_enc = explode(":",$disk_l[$i]);
			$NUMDISK=$dcar2[0];
			$TIPODISK=$disko_enc[7];
		// Actualizamos la base de datos en el campo
			$cmd->texto="UPDATE ordenadores_particiones
				SET tdisk='".$TIPODISK."'
				WHERE idordenador=(SELECT idordenador
						     FROM ordenadores
						    WHERE ip='".$iph."')
				AND numdisk='".$NUMDISK."'";
		$resul=$cmd->Ejecutar();
		}
	}
//________________________________________________________________________________________________________
	//agp
	$nombre_archivo = "/opt/opengnsys/log/clients/".$iph.".cache.txt";
	$contenidofichero = file_get_contents($nombre_archivo);
	if (empty ($contenidofichero)) {
		// Sin caché local.
		$cmd->texto="UPDATE ordenadores_particiones
				SET cache=''
				WHERE idordenador=(SELECT idordenador
						     FROM ordenadores
						    WHERE ip='".$iph."')";
	} else {
		// Actualizar datos de caché local.
		$cmd->texto="UPDATE ordenadores_particiones
				SET cache='".$contenidofichero."'
				WHERE idordenador=(SELECT idordenador
						     FROM ordenadores
						    WHERE ip='".$iph."')
				  AND idsistemafichero=(SELECT idsistemafichero
							  FROM sistemasficheros
							 WHERE descripcion='CACHE')";
	}
	$resul=$cmd->Ejecutar();
	//agp
	//________________________________________________________________________________________________________
	$rsmenu=RecuperaMenu($cmd,$iph);	// Recupera un recordset con los datos del m en
	if(!empty($rsmenu)){
		switch($tip){
			case $ITEMS_PUBLICOS:
				if(!empty($rsmenu->campos["htmlmenupub"])){
					$urlHtml=$rsmenu->campos["htmlmenupub"];
					//if(strtoupper(substr($urlHtml,0,7))!="HTTP://") $urlHtml="http://".$urlHtml;
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
					
					//if(strtoupper(substr($urlHtml,0,7))!="HTTP://") $urlHtml="http://".$urlHtml;
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
		// Si existe, incluir menú por defecto.
		if (file_exists("/opt/opengnsys/log/clients/$iph.info.html")) {
			$codeHtml=file_get_contents("/opt/opengnsys/log/clients/$iph.info.html");
		}
		else{
			// Componer mensaje para cliente sin menú.
			$codeHtml='<div align="center" style="font-family: Arial, Helvetica, sans-serif;">';
			$codeHtml.='<p style="color:#999999; font-size: 16px; margin: 2em;">'.$TbMsg[2].'</p>';
			$codeHtml.='<p style="font-size: 14px; margin: 2em;">';
			$codeHtml.='  <a href="command:poweroff">'.$TbMsg[3].'</a>';
			$codeHtml.='</p>';
			$codeHtml.='</div>';
		}
	}
	?>
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		</head>

		<?php
		if(!empty($rsmenu->campos["idurlimg"])){ // Imagen de fondo
			$urlimg=TomaDato($cmd,0,'iconos',$rsmenu->campos["idurlimg"],'idicono','urlicono');
			$urlimgfondo="../images/iconos/".$urlimg;
			echo '<body bgcolor="white" background="'.$urlimgfondo.'">';
		}
		else{
			echo'<body bgcolor="white" background="../images/iconos/fondo800x600.png">';
		}
		echo $codeHtml;
		?>
		</body>
		</html>
<?php
}
?>

