<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: configuracionaula.php
// Descripción : 
//		Muestra la configuraci� de las particiones de los ordenadores de un aula
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/comunes.php");
include_once("../includes/constantes.php");
include_once("../includes/switchparticiones.php");
include_once("../idiomas/php/".$idioma."/configuracionaula_".$idioma.".php");
//________________________________________________________________________________________________________
$idaula=0;
if (isset($_GET["idaula"])) $idaula=$_GET["idaula"]; 
//________________________________________________________________________________________________________
$nombreaula="";
$urlfoto="";
$cagnon=false;
$pizarra=false;
$ubicacion="";
$comentarios="";
$ordenadores=0;
$puestos=0;
$grupoid=0;

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.
$resul=TomaPropiedades($cmd,$idaula);
if (!$resul)
	Header('Location: '.$pagerror.'?herror=3'); // Error de recuperaci� de datos.
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
</HEAD>
<BODY>
	<P align=center class=cabeceras><?echo $TbMsg[0]?></P>
	<P align=center><SPAN align=center class=subcabeceras><? echo $TbMsg[1]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos  style="width=425">
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[2]?>&nbsp;</TD>
			<?
					echo '<TD>'. $nombreaula.'</TD><TD colspan=2 valign=top align=center rowspan=2><IMG border=3 style="border-color:#63676b" src="';
					if ($urlfoto=="") 	echo "../images/aula.jpg"; else 	echo $urlfoto;
					echo '"><br><center>&nbsp;'.$TbMsg[13].':&nbsp;'. $ordenadores.'</center></TD>';

			?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		</TR>
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[3]?>&nbsp;</TD>
			<?
					echo '<TD>'.$ubicacion.'</TD>';
			?>
		</TR>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[4]?>&nbsp;</TD>
			<?
					echo '<TD colspan=3><INPUT  class="formulariodatos" name=cagnon type=checkbox  onclick="desabilita(this)" ';
					if ($cagnon) echo ' checked ';
					echo '></TD>';
			?>
			</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TD>
			<?
					echo '<TD colspan=3><INPUT  class="formulariodatos" name=pizarra type=checkbox  onclick="desabilita(this)" ';
					if ($pizarra) echo ' checked ';
					echo '></TD>';
			?>
		</TR	>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center&nbsp;><?echo $TbMsg[6]?>&nbsp;</TD>
			<?
					echo '<TD colspan=3>'.$puestos.'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[7]?>&nbsp;</TD>
			<?
					echo '<TD  colspan=3>'.$urlfoto.'</TD>';
			?>
		</TR>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[8]?>&nbsp;</TD>
			<?
					echo '<TD colspan=3>'.$comentarios.'</TD>';
			?>
		</TR>	
	</TABLE>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<P align=center><SPAN align=center class=subcabeceras><? echo $TbMsg[9]?></SPAN></P>
	<?echo tabla_perfiles($cmd,$idcentro,$idaula);?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
function TomaPropiedades($cmd,$ida){
	global $idaula;
	global $nombreaula;
	global $urlfoto;
	global $cagnon;
	global $pizarra;
	global $ubicacion;
	global $comentarios;
	global $ordenadores;
	global $puestos;
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM aulas WHERE idaula=".$ida;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombreaula=$rs->campos["nombreaula"];
		$urlfoto=$rs->campos["urlfoto"];
		$cagnon=$rs->campos["cagnon"];
		$pizarra=$rs->campos["pizarra"];
		$ubicacion=$rs->campos["ubicacion"];
		$comentarios=$rs->campos["comentarios"];
		$puestos=$rs->campos["puestos"];
		$rs->Cerrar();
		$cmd->texto="SELECT count(*) as numordenadores FROM ordenadores WHERE idaula=".$ida;
		$rs->Comando=&$cmd; 
		if (!$rs->Abrir()) return(0); // Error al abrir recordset
		$rs->Primero(); 
		if (!$rs->EOF)
			$ordenadores=$rs->campos["numordenadores"];
		return(true);
	}
	else
		return(false);
}
//________________________________________________________________________________________________________
function tabla_perfiles($cmd,$idcentro,$idaula){
	global $cadenaip;
	$tablaHtml="";
	$rs=new Recordset; 
	$numorde=0;
	$cmd->texto="SELECT COUNT(*) AS numorde FROM ordenadores WHERE idaula=".$idaula;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	if(!$rs->EOF)
		$numorde=$rs->campos["numorde"];
	$idconfiguracion="";
	$cmd->texto="SELECT COUNT(*) AS cuenta,configuraciones.descripcion,configuraciones.idconfiguracion FROM aulas";
	$cmd->texto.=" INNER JOIN ordenadores ON aulas.idaula = ordenadores.idaula";
	$cmd->texto.=" INNER JOIN configuraciones ON ordenadores.idconfiguracion = configuraciones.idconfiguracion";
	$cmd->texto.=" WHERE aulas.idaula = ".$idaula;
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
				$tablaHtml.=PintaOrdenadores($cmd,$idaula,$rs->campos["idconfiguracion"],$rs->campos["cuenta"]);
				$tablaHtml.= '</TD></TR>';
				$tablaHtml.= '<TR><TD>';
				$tablaHtml.=tabla_ConfiguracionAula($cmd,$idcentro,$idaula,$rs->campos["idconfiguracion"],$rs->campos["cuenta"]);
				$tablaHtml.= '</TD></TR>';
				$rs->Siguiente();
			}
			$tablaHtml.="</TABLE>";
		}
		else{
			$tablaHtml.=tabla_ConfiguracionAula($cmd,$idcentro,$idaula,$rs->campos["idconfiguracion"],$rs->campos["cuenta"]);
			$tablaHtml.='<INPUT type=hidden name="nuevasipes" id="ipes_'.$rs->campos["idconfiguracion"].'" value="'.$cadenaip.'">';
		}
	}
	echo $tablaHtml;
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function PintaOrdenadores($cmd,$idaula,$idconfiguracion){
	$ipidpidc="";
	$rs=new Recordset; 
	$contor=0;
	$maxcontor=10;
	$cmd->texto=" SELECT idordenador,nombreordenador,ip FROM ordenadores WHERE  idconfiguracion=".$idconfiguracion." AND idaula=".$idaula." ORDER BY nombreordenador";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	$tablaHtml='<TABLE align=center border=0><TR>';
	while (!$rs->EOF){
		$contor++;
		$tablaHtml.= '<TD 	style="cursor:hand" oncontextmenu=resalta('.$rs->campos["idordenador"].',"'.$rs->campos["nombreordenador"].'","flo_ordenadores") align=center style="FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE: 8px"><br><IMG src="../images/iconos/ordenador.gif" ><br><span style="FONT-SIZE:9px" >'.$rs->campos["nombreordenador"].'</TD>';
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
function tabla_ConfiguracionAula($cmd,$idcentro,$idaula,$idconfiguracion,$cuenta){
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
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[10].'&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[11].'&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[12].'&nbsp;</TH>';
	$tablaHtml.= '</TR>';
	for($j=0;$j<sizeof($auxsplit)-1;$j++){
		$ValorParametros=extrae_parametros($auxsplit[$j],chr(10),'=');
		$particion=$ValorParametros["numpart"]; // Toma la partici�
		$nombreso=$ValorParametros["nombreso"]; // Toma nombre del sistema operativo
		$tiposo=$ValorParametros["tiposo"];
		$tipopart=$ValorParametros["tipopart"];
		$tamapart=$ValorParametros["tamapart"];
		$nomso=nombreSO($tipopart,$tiposo,$nombreso);
		if($nomso!="CACHE"){
			$tablaHtml.='<TR>'.chr(13);
			$tablaHtml.='<TD align=center>&nbsp;'.$particion.'&nbsp;</TD>'.chr(13);
			$tablaHtml.='<TD>&nbsp;'.$nomso.'&nbsp;</TD>'.chr(13);
			$tablaHtml.='<TD align=rigth>&nbsp;'. formatomiles( $tamapart).'&nbsp;</TD>'.chr(13);
			$tablaHtml.='</TR>'.chr(13);
		}
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
?>
