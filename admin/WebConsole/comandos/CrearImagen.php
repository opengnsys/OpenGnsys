<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: CrearImagen.php
// Descripción : 
//		Implementación del comando "CrearImagen.php"
// Version 1.1.1: Si no existe repositorio asignado al ordenador se muestra un mensaje informativo (ticket-870).
//     Autora: Irina Gomez, ETSII Universidad de Sevilla
//     Fecha: 2018-11-08
// Version 1.2: Soporta imágenes de disco. Nueva función HTMLSELECT_imagenes_disco
//     Autora: Irina Gomez, ETSII Universidad de Sevilla
//     Fecha: 2020-06-19
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/TomaDato.php");
include_once("../idiomas/php/".$idioma."/comandos/crearimagen_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");
include_once("../includes/pintaTablaConfiguraciones.php");

//________________________________________________________________________________________________________
include_once("./includes/capturaacciones.php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
$resul=tomaPropiedades($cmd,$idambito);
if (!$resul){
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.
}
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
<TITLE>Administración web de aulas</TITLE>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="./jscripts/CrearImagen.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/crearimagen_'.$idioma.'.js"></SCRIPT>'?>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
</HEAD>
<BODY>
<?php
	$urlimg='../images/iconos/ordenador.gif';
	$textambito=$TbMsg[15];

	echo '<p align="center"><span class="cabeceras">'.$TbMsg[0].'&nbsp;</span><br>';
	echo '<img src="'.$urlimg.'" alt="*">&nbsp;&nbsp;<span align=center class=subcabeceras>
			<u>'.$TbMsg[14].': '.$textambito.','.$nombreambito.'</u></span>&nbsp;&nbsp;</p>';

	echo '<p align="center"><SPAN class="subcabeceras">'.$TbMsg[6].'</span></p>'."\n";

	if (tiene_repo($idambito)) {
		echo '<FORM  align=center name="fdatos">'."\n".
		     tablaConfiguracionesCrearImagen($cmd,$idambito,$idrepositorio).
		     '</FORM>'."\n";

	} else {
		echo '<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>'."\n".
		     '	  <TR>'."\n".
		     '        <TH align=center>'.$TbMsg["CREATE_NOREPO"].'</TH>'."\n".
		     '    </TR>'."\n".
		     '</TABLE>'."\n";
	}

	//________________________________________________________________________________________________________
	include_once("./includes/formularioacciones.php");
	//________________________________________________________________________________________________________
	//________________________________________________________________________________________________________
	include_once("./includes/opcionesacciones.php");
	//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?php
/**************************************************************************************************************************************************
	Recupera los datos de un ordenador
		Parámetros:
		- cmd: Una comando ya operativo (con conexiónabierta)
		- ido: El identificador del ordenador
________________________________________________________________________________________________________*/
function tomaPropiedades($cmd,$ido){
	global $nombreordenador;
	global $ip;
	global $mac;
	global $idperfilhard;
	global $idrepositorio;
	$rs=new Recordset;
	$cmd->texto="SELECT nombreordenador,ip,mac,idperfilhard,idrepositorio FROM ordenadores WHERE idordenador='".$ido."'";
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero();
	if (!$rs->EOF){
		$nombreordenador=$rs->campos["nombreordenador"];
		$ip=$rs->campos["ip"];
		$mac=$rs->campos["mac"];
		$idperfilhard=$rs->campos["idperfilhard"];
		$idrepositorio=$rs->campos["idrepositorio"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de los perfiles softwares
//	UHU - 2013/05/17 - Ahora las imagenes pueden ser en cualquier disco
//	Version 0.1: La consulta SQL se limita a IMAGENES_MONOLITICAS.
//		US ETSII - Irina Gomez - 2014-11-11
________________________________________________________________________________________________________*/
function HTMLSELECT_imagenes($cmd,$idrepositorio,$idperfilsoft,$disk,$particion,$masterip)
{
	global $IMAGENES_MONOLITICAS;
	$SelectHtml="";
	$cmd->texto="SELECT DISTINCT imagenes.idimagen,imagenes.descripcion,imagenes.nombreca,
                imagenes.idperfilsoft, repositorios.nombrerepositorio, repositorios.ip
		FROM  imagenes INNER JOIN repositorios USING  (idrepositorio)
		WHERE imagenes.tipo=".$IMAGENES_MONOLITICAS."
		AND   repositorios.idrepositorio IN (SELECT idrepositorio FROM ordenadores WHERE ordenadores.ip='".$masterip."')
		OR repositorios.ip='".$masterip."' ORDER BY imagenes.descripcion";

	$rs=new Recordset;
	$rs->Comando=&$cmd;
	$SelectHtml.= '<SELECT class="formulariodatos" id="despleimagen_'.$disk."_".$particion.'" style="WIDTH: 300">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';
	if ($rs->Abrir()){
		$rs->Primero();
		while (!$rs->EOF){
			$SelectHtml.='<OPTION value="'.$rs->campos["idimagen"]."_".$rs->campos["nombreca"]."_".$rs->campos["ip"].'"';
			if($idperfilsoft==$rs->campos["idperfilsoft"]) $SelectHtml.=" selected ";
			$SelectHtml.='>';
			$SelectHtml.= $rs->campos["descripcion"]. ' -- '. $rs->campos['nombrerepositorio']  . '</OPTION>';
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
	$SelectHtml.= '</SELECT>';
	return($SelectHtml);
}

/*________________________________________________________________________________________________________
        Crea la etiqueta html <SELECT> de las imágenes de disco con identificador "despleimagen_"
________________________________________________________________________________________________________*/
function HTMLSELECT_imagenes_disco($cmd,$idordenador)
{
        global $IMAGENES_DISCO;

        // 1.1 Imagenes de todos los repositorios de la UO.
        $selectrepo='select repositorios.idrepositorio from repositorios INNER JOIN aulas INNER JOIN ordenadores where repositorios.idcentro=aulas.idcentro AND aulas.idaula=ordenadores.idaula AND idordenador='.$idordenador;

        $SelectHtml="";
        $cmd->texto="SELECT *,repositorios.ip as iprepositorio, repositorios.nombrerepositorio as nombrerepo FROM imagenes
                       INNER JOIN repositorios ON repositorios.idrepositorio=imagenes.idrepositorio";

        $cmd->texto.=" AND imagenes.idrepositorio>0";   // La imagene debe existir en el repositorio.
        $cmd->texto.=" AND imagenes.tipo=".$IMAGENES_DISCO;
        $cmd->texto.=" AND repositorios.idrepositorio IN (".$selectrepo.") ORDER BY imagenes.descripcion";


        $rs=new Recordset;
        $rs->Comando=&$cmd;
                $SelectHtml.= '<SELECT class="formulariodatos" id="despleimagen_" style="WIDTH:220">';
        $SelectHtml.= '    <OPTION value="0"></OPTION>';

        if ($rs->Abrir()){
                $rs->Primero();
                while (!$rs->EOF){
                        $SelectHtml.='<OPTION value="'.$rs->campos["idimagen"]."_".$rs->campos["nombreca"]."_".$rs->campos["iprepositorio"]."_".$rs->campos["idperfilsoft"].'"';
                        $SelectHtml.='>';
                        $SelectHtml.= $rs->campos["descripcion"].' ('.$rs->campos["nombrerepo"].') </OPTION>';

                        $rs->Siguiente();
                }
                $rs->Cerrar();
        }
        $SelectHtml.= '</SELECT>';
        return($SelectHtml);
}


//____________________________________________________________________________________________________
//	Devuelve si tiene repositorio asignado o no (true o false)
//	Param:
//	  - idordenador: identificador del ordenador
//____________________________________________________________________________________________________
function tiene_repo ($idordenador) {
	global $cmd;

	$idrepositorio = 0;
	$rs=new Recordset;
	$cmd->texto="SELECT idrepositorio from ordenadores WHERE idordenador=$idordenador";
	$rs->Comando=&$cmd;
	if ($rs->Abrir()) {
		$rs->Primero();
		$idrepositorio = $rs->campos["idrepositorio"];
	}
	$rs->Cerrar();
	if ($idrepositorio == 0) {
		return false;
	} else {
		return true;
	}
}
?>
