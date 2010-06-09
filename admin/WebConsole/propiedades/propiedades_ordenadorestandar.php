<? 
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: propiedades_ordenadorestandar.php
// Descripción : 
//		 Presenta el formulario de captura de datos de un ordenador estandar para insertar,modificar y eliminar
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_ordenadorestandar_".$idioma.".php");
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idaula=0;
$nombreaula="";
$idordenador=0; 
$modomul="";
$ipmul="";
$pormul="";
$velmul="";
$cache="";
$idperfilhard=0;
$idservidordhcp=0;
$idservidorrembo=0;

if (isset($_GET["idaula"])) $idaula=$_GET["idaula"]; // Recoge parametros
if (isset($_GET["nombreaula"])) $nombreaula=$_GET["nombreaula"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idaula);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.
}
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_ordenadorestandar.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_ordenadorestandar_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<P align=center class=cabeceras><IMG  border=0 src="../images/iconos/aula.gif">&nbsp;<?echo $TbMsg[0]?>:<SPAN  class=cabeceras><? echo $nombreaula?></SPAN><BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[3]?>&nbsp;</TD>
			<?
				echo '<TD><INPUT class="formulariodatos" name=ipmul  type=text value="'.$ipmul.'"></TD>';

			?>
			<TD colspan=2 valign=top align=left rowspan=4><IMG border=2 style="border-color:#63676b" src="../images/fotoordenador.gif"></TD>
		</TR>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[4]?>&nbsp;</TD>
			<?
				echo '<TD><INPUT class="formulariodatos" name=pormul  type=text value="'. $pormul.'"></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[12]?>&nbsp;</TD>
			<?
				echo '<TD><INPUT class="formulariodatos" name=velmul  type=text value="'. $velmul.'"></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[2]?>&nbsp;</TD>
			<?
				$metodos="0=".chr(13);
				$metodos.="1=Half-Duplex".chr(13);
				$metodos.="2=Full-Duplex";
				echo '<TD>'.HTMLCTESELECT($metodos,"modomul","estilodesple","",$modomul,100).'</TD>'.chr(13);
			?>
		</TR>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TD>
			<?
				echo '<TD colspan=3>'.HTMLSELECT($cmd,$idcentro,'perfileshard',$idperfilhard,'idperfilhard','descripcion',250).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[7]?>&nbsp;</TD>
			<?
				echo '<TD colspan=3>'.HTMLSELECT($cmd,$idcentro,'servidoresrembo',$idservidorrembo,'idservidorrembo','nombreservidorrembo',250).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[11]?>&nbsp;</TD>
			<?
				echo '<TD colspan=3><INPUT style="width:250"class="formulariodatos" name=cache  type=text value="'. $cache.'"></TD>';
			?>
		</TR>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<!--TR>
			<TH align=center>&nbsp;<?echo $TbMsg[8]?>&nbsp;</TD>
			<?
				echo '<TD colspan=3><INPUT class="formulariodatos" name=numorde  type=text value=0 style="width:250"></TD>';
			?>
		</TR-->
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	</TABLE>
</FORM>
	<TABLE border=0 align=center>
		<!--TR>
			<TD width=20>&nbsp;</TD>
			<TD colspan=3 align=left ><SPAN class=notas><I><?echo $TbMsg[9]?><br><br><?echo $TbMsg[10]?></I></SPAN></TD>
			<TD width=20>&nbsp;</TD></TR>
		<TR>
			<TD colspan=5 width=20>&nbsp;</TD>
		</TR-->
		<TR>
			<TD width=20>&nbsp;</TD>
			<TD align=right><A href=#><IMG border=0 src="../images/boton_cancelar.gif" style="cursor:hand"  onclick="cancelar()"></A></TD>
			<TD width=20></TD>
			<TD align=left ><A href=#><IMG border=0 src="../images/boton_confirmar.gif" style="cursor:hand"  onclick="confirmar(<? echo $idaula?>)" ></A></TD>
			<TD width=20>&nbsp;</TD>
		</TR>
	</TABLE>
<?
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
//________________________________________________________________________________________________________
//	Recupera los datos de un ordenador estandar 
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- id: El identificador del ordenador estandar 
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global $modomul;
	global $ipmul;
	global $pormul;
	global $velmul;
	global $cache;
	global $idperfilhard;
	global $idservidordhcp;
	global $idservidorrembo;

	$wmodomul="";
	$wipmul="";
	$wpormul="";
	$wvelmul="";
	$wcache="";
	$widperfilhard=0;
	$widservidordhcp=0;
	$widservidorrembo=0;

	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM ordenadores WHERE idaula=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	if ($rs->EOF) return(false);
	$rs->Primero(); 
	$modomul=$rs->campos["modomul"];
	$ipmul=$rs->campos["ipmul"];
	$pormul=$rs->campos["pormul"];
	$velmul=$rs->campos["velmul"];
	$cache=$rs->campos["cache"];
	$idperfilhard=$rs->campos["idperfilhard"];
	$idservidordhcp=$rs->campos["idservidordhcp"];
	$idservidorrembo=$rs->campos["idservidorrembo"];

	while(!$rs->EOF){
		$wmodomul=$rs->campos["modomul"];
		$wipmul=$rs->campos["ipmul"];
		$wpormul=$rs->campos["pormul"];
		$wvelmul=$rs->campos["velmul"];
		$wcache=$rs->campos["cache"];
		$widperfilhard=$rs->campos["idperfilhard"];
		$widservidordhcp=$rs->campos["idservidordhcp"];
		$widservidorrembo=$rs->campos["idservidorrembo"];
	
		if(strlen($wmodomul)!=strlen($modomul))
			$modomul="";
		else{
			for($i=0;$i<strlen($modomul);$i++){
				if(substr($modomul,$i,1)!=substr($wmodomul,$i,1)){
					//$modomul=substr($modomul,0,$i);
					$modomul="";
					break;
				}
			}
		}
		if(strlen($wipmul)!=strlen($ipmul))
			$ipmul="";
		else{
			for($i=0;$i<strlen($ipmul);$i++){
				if(substr($ipmul,$i,1)!=substr($wipmul,$i,1)){
					$ipmul="";
					break;
				}
			}
		}
		if(strlen($wpormul)!=strlen($pormul))
			$pormul="";
		else{
			for($i=0;$i<strlen($pormul);$i++){
				if(substr($pormul,$i,1)!=substr($wpormul,$i,1)){
					$pormul="";
					break;
				}
			}
		}

		if(strlen($wvelmul)!=strlen($velmul))
			$velmul="";
		else{
			for($i=0;$i<strlen($velmul);$i++){
				if(substr($velmul,$i,1)!=substr($wvelmul,$i,1)){
					$velmul="";
					break;
				}
			}
		}
		if($cache!=$wcache) $cache=0;
		if($idperfilhard!=$widperfilhard) $idperfilhard=0;
		if($idservidordhcp!=$widservidordhcp) $idservidordhcp=0;
		if($idservidorrembo!=$widservidorrembo) $idservidorrembo=0;
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return(true);
}
