<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: incorporaordenadores.php
// Descripción : 
//		Da de alta en la base de datos nuevos ordenadores desde un fichero de configuración dhcp
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/incorporaordenadores_".$idioma.".php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//___________________________________________________________________________________________________
$swf=0; 
$idaula=0; 
$nombreaula="";
$contenido="";

if (isset($_GET["idaula"])) $idaula=$_GET["idaula"]; 
if (isset($_GET["nombreaula"])) $nombreaula=$_GET["nombreaula"]; 

if (isset($_POST["swf"])) $swf=$_POST["swf"]; // Recoge parametros
if (isset($_POST["contenido"])) $contenido=$_POST["contenido"]; 
if (isset($_POST["idaula"])) $idaula=$_POST["idaula"]; 
if (isset($_POST["nombreaula"])) $nombreaula=$_POST["nombreaula"]; 

$resul=0;

if(!empty($contenido)){ // Se ha introducido contenido en lugar de fichero
	$resul=procesaLineas($cmd,$idaula,$contenido);
}
else{
	if(!empty($swf)){ // Se ha enviado un fichero de configuración
		// Se envía fichero de script
		$ficheroPOST = $HTTP_POST_FILES['userfile']['tmp_name']; 
		$nombreOriginal_archivo = $HTTP_POST_FILES['userfile']['name']; 
		$URLPATHFILECFG="../includes";
		$FISPATHFILECFG=realpath($URLPATHFILECFG);
		$NOMBREFILESCFG="filedhcp.cfg";
		$ficheroLOCAL=$FISPATHFILECFG."/".$NOMBREFILESCFG;
		$resul=Gestiona($cmd,$idaula);
	}
}
//___________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
</HEAD>
<BODY>
<FORM action="incorporaordenadores.php" method="post" enctype="multipart/form-data" name="fdatos">
	<INPUT type=hidden name=swf value=1>
	<INPUT type=hidden name=idaula value=<?echo $idaula?>>
	<INPUT type=hidden name=nombreaula value=<?echo $nombreaula?>>
	<BR>
	<P align=center class=cabeceras><?echo $TbMsg[0]?><BR>
	<SPAN align=center class=subcabeceras><IMG src="../images/iconos/aula.gif">&nbsp;<?echo $TbMsg[1].":".$nombreaula ?></SPAN></P>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<table align=center  class=tabla_datos border="0" cellpadding="0" cellspacing="1">
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
        <tr> 
            <th>&nbsp;<? echo $TbMsg[8]?>&nbsp;</th>
			<td><textarea class="cajatexto" name="contenido" cols="70" rows="18"></textarea></td></tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
        <tr> 
            <th>&nbsp;<? echo $TbMsg[2]?>&nbsp;</th>
			<td ><input  class="cajatexto" name="userfile" type="file"  size="52"></td></tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
</TABLE>
 </FORM>
<TABLE align=center>
	<TR>
		<TD><IMG src="../images/boton_cancelar.gif" style="cursor:hand"  onclick=""></TD>
		<TD width=20></TD>
		<TD><IMG src="../images/boton_confirmar.gif" style="cursor:hand"  onclick="javascript:document.fdatos.submit();"></TD>
	</TR>
</TABLE>
<?
//________________________________________________________________________________________________________
// Mensaje con el resultado del proceso
echo '<SCRIPT LANGUAGE="javascript">';
if (!empty($resul))
	echo "	alert('".$TbMsg[$resul]."');";
echo '</SCRIPT>';
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
function Gestiona($cmd,$idaula){
	global $ficheroPOST;
	global $ficheroLOCAL;

	$codigo="";
	// Se envía fichero de script 
	if(!empty($ficheroPOST)){
		if (salvafichero_POST($ficheroPOST,$ficheroLOCAL)){
			$fp = fopen ($ficheroLOCAL, "r");
			$codigo = fread ($fp, filesize ($ficheroLOCAL));
			fclose ($fp);
			return(procesaLineas($cmd,$idaula,$codigo));
		}
		else
			return(6);
	}
	else
		return(4);
}
//________________________________________________________________________________________________________
function procesaLineas($cmd,$idaula,$buffer){
	$nombre="";
	$MAC="";
	$IP="";
	$sw=false;
	$posa=-1;

	while($posa<strlen($buffer)){
			if ('host'==substr($buffer,$posa,4)){
				$posa=$posa+4;
				$posb=$posa;
				while(	substr($buffer,$posb,1)!="{") $posb++;
				$nombre=substr($buffer,$posa,$posb-$posa);
			}
			if ('hardware ethernet'==substr($buffer,$posa,17)){
				$posa=$posa+17;
				$posb=$posa;
				while(	substr($buffer,$posb,1)!=";") $posb++;
				$MAC=substr($buffer,$posa,$posb-$posa);
			}

			if ('fixed-address'==substr($buffer,$posa,13)){
				$posa=$posa+13;
				$posb=$posa;
				while(	substr($buffer,$posb,1)!=";") $posb++;
				$IP=substr($buffer,$posa,$posb-$posa);
			}
			if(!empty($nombre) && !empty($MAC) && !empty($IP)){
				if(!Inserta($cmd,$idaula,$nombre,$MAC,$IP)) return(3);
				$sw=true;
				$nombre="";
				$MAC="";
				$IP="";
				$resul=true;
			}
			$posa++;
	}
	if($sw)
		return(7);
	else
		return(5);

}
//________________________________________________________________________________________________________
function Inserta($cmd,$idaula,$nombre,$lamac,$laip){
	$grupoid=0;
	$nombreordenador=trim($nombre);
	$ip=trim($laip);
	$auxmac=trim($lamac);
	$mac="";
	for($i=0;$i<strlen($auxmac);$i++)
		if(substr($auxmac,$i,1)!=":")
			$mac.=substr($auxmac,$i,1);
	$idperfilhard=0;
	$idservidordhcp=0;
	$idservidorrembo=0;
	$idconfiguracion=0;
	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@idaula",$idaula,1);
	$cmd->CreaParametro("@nombreordenador",$nombreordenador,0);
	$cmd->CreaParametro("@ip",$ip,0);
	$cmd->CreaParametro("@mac",$mac,0);
	$cmd->CreaParametro("@idperfilhard",$idperfilhard,1);
	$cmd->CreaParametro("@idservidordhcp",$idservidordhcp,1);
	$cmd->CreaParametro("@idservidorrembo",$idservidorrembo,1);
	$cmd->CreaParametro("@idconfiguracion",$idconfiguracion,1);
	
	$cmd->texto="INSERT INTO ordenadores(nombreordenador,ip,mac,idperfilhard,idservidordhcp,idservidorrembo,idaula,grupoid) VALUES (@nombreordenador,@ip,@mac,@idperfilhard,@idservidordhcp,@idservidorrembo,@idaula,@grupoid)";
	$resul=$cmd->Ejecutar();
	return($resul);
}
//________________________________________________________________________________________________________
//
//	Salva un fichero enviado por POST
//________________________________________________________________________________________________________
function salvafichero_POST($ficheroPost,$ficheroLocal){
	if (file_exists($ficheroLocal)) // Borra el fichero si existe
        unlink($ficheroLocal);
	return(move_uploaded_file($ficheroPost,$ficheroLocal)); // salva el fichero
}
?>
