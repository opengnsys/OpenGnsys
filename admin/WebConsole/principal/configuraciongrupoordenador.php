<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: configuraciongrupoordenador.php
// Descripción : 
//		Muestra la configuraci� de las particiones de los ordenadores de un grupo de ordenadores
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/comunes.php");
include_once("../includes/constantes.php");
include_once("../includes/switchparticiones.php");
include_once("../idiomas/php/".$idioma."/configuraciongrupoordenador_".$idioma.".php");
//________________________________________________________________________________________________________
$idgrupo=0;
if (isset($_GET["idgrupo"])) $idgrupo=$_GET["idgrupo"]; 
//________________________________________________________________________________________________________
$nombregrupoordenador="";
$ordenadores=0;

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.
$resul=TomaPropiedades($cmd,$idgrupo);
if (!$resul)
	Header('Location: '.$pagerror.'?herror=3'); // Error de recuperaci� de datos.
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administraci� web de aulas</TITLE>
<HEAD>
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<P align=center class=cabeceras><?echo $TbMsg[0]?></P>
	<P align=center><SPAN align=center class=subcabeceras><? echo $TbMsg[1]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos  style="width=425">
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[2]?>&nbsp;</TD>
			<?
					echo '<TD>'. $nombregrupoordenador.'</TD><TD colspan=2 valign=top align=center rowspan=2><IMG border=3 style="border-color:#63676b" src="../images/aula.jpg"';
					echo '"><br><center>&nbsp;'.$TbMsg[7].':&nbsp;'. $ordenadores.'</center></TD>';
			?>
	</TABLE>
</FORM>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<p align=center>
	<span align=center class=subcabeceras><? echo $TbMsg[3]?></span>
	<FORM  name="fdatos"> 
			<? echo tabla_perfiles($cmd,$idcentro,$idgrupo);?>
	</FORM>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
function TomaPropiedades($cmd,$idg){
	global $idgrupo;
	global $nombregrupoordenador;
	global $ordenadores;
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM gruposordenadores WHERE idgrupo=".$idg;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombregrupoordenador=$rs->campos["nombregrupoordenador"];
		$rs->Cerrar();
		$cmd->texto="SELECT count(*) as numordenadores FROM ordenadores WHERE grupoid=".$idg;
		$rs->Comando=&$cmd; 
		if (!$rs->Abrir()) return(false); // Error al abrir recordset
		$rs->Primero(); 
		if (!$rs->EOF)
			$ordenadores=$rs->campos["numordenadores"];
		return(true);
	}
	else
		return(false);
}
//________________________________________________________________________________________________________
function tabla_perfiles($cmd,$idcentro,$idgrupo){
	global $cadenaip;
	$tablaHtml="";
	$rs=new Recordset; 
	$numorde=0;
	$cmd->texto="SELECT COUNT(*) AS numorde FROM ordenadores WHERE grupoid=".$idgrupo;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	if(!$rs->EOF)
		$numorde=$rs->campos["numorde"];
	$idconfiguracion="";
	$cmd->texto="SELECT COUNT(*) AS cuenta,configuraciones.descripcion,configuraciones.idconfiguracion FROM gruposordenadores";
	$cmd->texto.=" INNER JOIN ordenadores ON gruposordenadores.idgrupo = ordenadores.grupoid";
	$cmd->texto.=" INNER JOIN configuraciones ON ordenadores.idconfiguracion = configuraciones.idconfiguracion";
	$cmd->texto.=" WHERE (gruposordenadores.idgrupo = ".$idgrupo.") AND configuraciones.idconfiguracion>0";
	$cmd->texto.=" GROUP BY configuraciones.descripcion, configuraciones.idconfiguracion";
	$cmd->texto.=" HAVING configuraciones.idconfiguracion>0";
	$cmd->texto.=" ORDER BY configuraciones.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	if(!$rs->EOF){
		if($numorde!=$rs->campos["cuenta"]){ 
			while (!$rs->EOF){
				if($idconfiguracion!=$rs->campos["idconfiguracion"]){
					if($idconfiguracion!=0)
						$tablaHtml.="</TABLE>";

					$tablaHtml.= '<TABLE  align=center border=0 cellPadding=1 cellSpacing=1'; 
					$descripcion=$rs->campos["descripcion"];
					$tablaHtml.= "<TR>";
					$tablaHtml.= '<TD align=center ><IMG  src="../images/iconos/configuraciones.gif">';
					$tablaHtml.='&nbsp;&nbsp<span style="COLOR: #000000;FONT-FAMILY: Verdana;FONT-SIZE: 12px; "><U><b>Configuraci�:</b>&nbsp;'.$rs->campos["descripcion"].'</U></SPAN></TD>';
					$tablaHtml.= "</TR>";
				}
				$tablaHtml.= '<TR><TD>';
				$tablaHtml.=PintaOrdenadores($cmd,$idgrupo,$rs->campos["idconfiguracion"],$rs->campos["cuenta"]);
				$tablaHtml.= '</TD></TR>';
			
				$tablaHtml.= '<TR><TD>';
				$tablaHtml.=tabla_ConfiguracionGrupo($cmd,$idcentro,$idgrupo,$rs->campos["idconfiguracion"],$rs->campos["cuenta"]);
				$tablaHtml.= '</TD></TR>';
				$rs->Siguiente();
			}
			$tablaHtml.="</TABLE>";
		}
		else{
			$tablaHtml.=tabla_ConfiguracionGrupo($cmd,$idcentro,$idgrupo,$rs->campos["idconfiguracion"],$rs->campos["cuenta"]);
			$tablaHtml.='<INPUT type=hidden name="nuevasipes" id="ipes_'.$rs->campos["idconfiguracion"].'" value="'.$cadenaip.'">';
		}
	}
	echo $tablaHtml;
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function PintaOrdenadores($cmd,$idgrupo,$idconfiguracion){
	$ipidpidc="";
	$rs=new Recordset; 
	$contor=0;
	$maxcontor=10;
	$cmd->texto=" SELECT nombreordenador,ip FROM ordenadores WHERE  idconfiguracion=".$idconfiguracion." AND grupoid=".$idgrupo." ORDER BY nombreordenador";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	$tablaHtml='<TABLE align=center border=0><TR>';
	while (!$rs->EOF){
		$contor++;
		$tablaHtml.= '<TD align=center style="FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE: 8px"><br><IMG src="../images/iconos/ordenador.gif"><br><span style="FONT-SIZE:9px" >'.$rs->campos["nombreordenador"].'</TD>';
		if($contor>$maxcontor){
			$contor=0;
			$tablaHtml.='</TR><TR>';
		}
		$ipidpidc.=$rs->campos["ip"].";";
		$rs->Siguiente();
	}
	$ipidpidc=	substr($ipidpidc,0,strlen($ipidpidc)-1); // Quita la coma
	$tablaHtml.='</TR>';
	$tablaHtml.= '</TR></TABLE>';
	$tablaHtml.='<INPUT type=hidden name="nuevasipes" id="ipes_'.$idconfiguracion.'" value="'.$ipidpidc.'">';
	return($tablaHtml);
}
//________________________________________________________________________________________________________
function tabla_ConfiguracionGrupo($cmd,$idcentro,$idgrupo,$idconfiguracion,$cuenta){
	global $TbMsg;
	$tablaHtml="";
	$configuracion="";
	$rs=new Recordset; 
	$cmd->texto="SELECT configuracion FROM configuraciones WHERE idconfiguracion=".$idconfiguracion;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	if(!$rs->EOF)
		$configuracion=$rs->campos["configuracion"];
	$rs->Cerrar();
	$auxsplit=split("\t",$configuracion);
	$tablaHtml.= '<TABLE  class=tabla_listados_sin  align=center border=0 cellPadding=1 cellSpacing=1 >';
		$tablaHtml.= '<TR>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[4].'&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[5].'&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[6].'&nbsp;</TH>';
	$tablaHtml.= '</TR>';
	for($j=0;$j<sizeof($auxsplit)-1;$j++){
		$ValorParametros=extrae_parametros($auxsplit[$j],chr(10),'=');
		$particion=$ValorParametros["numpart"]; // Toma la partici�
		$nombreso=$ValorParametros["nombreso"]; // Toma nombre del sistema operativo
		$tiposo=$ValorParametros["tiposo"];
		$tipopart=$ValorParametros["tipopart"];
		$tamapart=$ValorParametros["tamapart"];
		$tablaHtml.='<TR>'.chr(13);
		$tablaHtml.='<TD align=center>&nbsp;'.$particion.'&nbsp;</TD>'.chr(13);
		$tablaHtml.='<TD>&nbsp;'. nombreSO($tipopart,$tiposo,$nombreso).'&nbsp;</TD>'.chr(13);
		$tablaHtml.='<TD align=rigth>&nbsp;'. formatomiles( $tamapart).'&nbsp;</TD>'.chr(13);
		$tablaHtml.='</TR>'.chr(13);
	}
	$tablaHtml.='</TABLE><br><br>';
	return($tablaHtml);
}
//________________________________________________________________________________________________________
function formatomiles($cadena){
	$len=strlen($cadena);
	$cadenafinal="";
	$m=1;
	for($i=$len-1;$i>=0;$i--){
		$cadenafinal=substr($cadena,$i,1).$cadenafinal;
		if($m%3==0 && $i>0){
				$cadenafinal=".".$cadenafinal;
				$m=0;
		}
		$m++;
	}
	return($cadenafinal);
}