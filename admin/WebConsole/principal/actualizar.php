<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: actualizar.php
// Descripción : 
//		Actualiza la visualización de los ordenadores de un ámbito concreto
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/SockHidra.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/actualizar_".$idioma.".php");
//________________________________________________________________________________________________________
$litambito=0; 
$idambito=0; 

if (isset($_GET["litambito"])) $litambito=$_GET["litambito"]; // Recoge parametros
if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 

$nombrefuncion="Actualizar"; 
$ejecutor="1"; 
$cadenaip="";

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
if($cmd){ // Compone cadena ip recorriendo ámbitos
	switch($litambito){
			case $LITAMBITO_CENTROS :
				$cmd->texto="SELECT idcentro,nombrecentro FROM centros WHERE idcentro=".$idambito;
				RecorreCentro($cmd);
				break;
			case $LITAMBITO_GRUPOSAULAS :
				$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE idgrupo=".$idambito." AND tipo=".$AMBITO_GRUPOSAULAS;
				RecorreGruposAulas($cmd);
				break;
			case $LITAMBITO_AULAS :
				$cmd->texto="SELECT idaula,nombreaula FROM aulas WHERE idaula=".$idambito;
				RecorreAulas($cmd);
				break;
			case $LITAMBITO_GRUPOSORDENADORES :
				$cmd->texto="SELECT idgrupo,nombregrupoordenador FROM gruposordenadores WHERE idgrupo=".$idambito;
				RecorreGruposOrdenadores($cmd);
				break;
			case $LITAMBITO_ORDENADORES :
				$cmd->texto="SELECT ip FROM ordenadores WHERE idordenador=".$idambito;
				RecorreOrdenadores($cmd);
				break;
	}
}
$resul=false;
if($cmd){
	$shidra=new SockHidra($servidorhidra,$hidraport); 
	$cadenaip=ereg_replace( ";", "','", $cadenaip );
	$cmd->texto="SELECT  ordenadores.ip as ipord,servidoresrembo.ip FROM ordenadores INNER JOIN servidoresrembo ON ordenadores.idservidorrembo =servidoresrembo.idservidorrembo WHERE ordenadores.ip IN ('".$cadenaip."') ORDER BY servidoresrembo.ip";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir())	$resul=false; // Error al abrir recordset
	$rs->Primero(); 
	if(!$rs->EOF){
		$resul=true;
		$ipservidorrembo=trim($rs->campos["ip"]); // toma ip servidor rembo
		$cadenaip="";
		while(!$rs->EOF && $resul){
			if($ipservidorrembo!=trim($rs->campos["ip"])){ // compara si cambia el servidor rembo
				$parametros=$ejecutor;
				$parametros.="nfn=".$nombrefuncion.chr(13);
				$cadenaip=substr($cadenaip,0,strlen($cadenaip)-1); // Quita la coma
				$parametros.="iph=".$cadenaip.chr(13);
				$parametros.="rmb=".$ipservidorrembo.chr(13);
				$resul=manda_trama();
				$ipservidorrembo=trim($rs->campos["ip"]); // toma ip servidor rembo
				$cadenaip="";
			}
			$cadenaip.=trim($rs->campos["ipord"]).";"; // toma ip del cliente
			$rs->Siguiente();
		}
	}
	$rs->Cerrar();
	if($resul){
		$parametros=$ejecutor;
		$parametros.="nfn=".$nombrefuncion.chr(13);
		$cadenaip=substr($cadenaip,0,strlen($cadenaip)-1); // Quita la coma
		$parametros.="iph=".$cadenaip.chr(13);
		$parametros.="rmb=".$ipservidorrembo.chr(13);
		$resul=manda_trama();
	}
}
// *************************************************************************************************************************************************
?>
<HTML>
<TITLE>" Administración web de aulas"</TITLE>
<HEAD>
</HEAD>
<BODY>
<? 
	echo '<SCRIPT language="javascript">';
	 if($resul)
 		echo "alert('".$TbMsg[0]."');";
	 else
 		echo "alert('".$TbMsg[1]."');";
	echo '	self.close();';
	echo '</SCRIPT>';
?>
</BODY>
</HTML>
<?
//________________________________________________________________________________________________________
//
//	Manda una trama del comando Actualizar
//________________________________________________________________________________________________________
function manda_trama(){
	global $parametros;
	global $shidra;
	if ($shidra->conectar()){ // Se ha establecido la conexión con el servidor hidra
		$shidra->envia_comando($parametros);
		$shidra->desconectar();
		return(true);
	}
	return(false);
}
//________________________________________________________________________________________________________
function RecorreCentro($cmd){
	global $AMBITO_GRUPOSAULAS;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	if(!$rs->EOF){
		$idcentro=$rs->campos["idcentro"];
		$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE idcentro=".$idcentro." AND grupoid=0 AND tipo=".$AMBITO_GRUPOSAULAS." ORDER BY nombregrupo";
		RecorreGruposAulas($cmd);
		$cmd->texto="SELECT idaula,nombreaula FROM aulas WHERE idcentro=".$idcentro." AND grupoid=0 ORDER BY nombreaula";
		RecorreAulas($cmd);
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreGruposAulas($cmd){
	global $AMBITO_GRUPOSAULAS;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$idgrupo=$rs->campos["idgrupo"];
		$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE grupoid=".$idgrupo." AND tipo=".$AMBITO_GRUPOSAULAS." ORDER BY nombregrupo";
		RecorreGruposAulas($cmd);
		$cmd->texto="SELECT idaula,nombreaula FROM aulas WHERE  grupoid=".$idgrupo." ORDER BY nombreaula";
		RecorreAulas($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreAulas($cmd){
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$idaula=$rs->campos["idaula"];
		$cmd->texto="SELECT idordenador,nombreordenador,ip,mac FROM ordenadores WHERE  idaula=".$idaula;
		$k=0;
		RecorreOrdenadores($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreGruposOrdenadores($cmd){
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$idgrupo=$rs->campos["idgrupo"];
		$cmd->texto="SELECT idgrupo,nombregrupoordenador FROM gruposOrdenadores WHERE grupoid=".$idgrupo." ORDER BY nombregrupoordenador";
		RecorreGruposOrdenadores($cmd);
		$cmd->texto="SELECT idordenador,nombreordenador,ip,mac FROM ordenadores WHERE  grupoid=".$idgrupo;
		RecorreOrdenadores($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreOrdenadores($cmd){
	global $cadenaip;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaip.=$rs->campos["ip"].";";
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
?>
