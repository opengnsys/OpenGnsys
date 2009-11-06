<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: dialogostramas.php
// Descripción : 
//		Prepara los parametros de las tramas de todos los comando
//
// Especificaciones:
//		Estos parametros lo enviar� todas las p�inas que enlazan con �ta
//		 Par�etros:
//			identificador: Identificador del comando
//			nombrefuncion: Nombre de la funci� de llamada al comando en el cliente rembo 
//			tipotrama: Tipo de trama
//						CMD=Comando 
//			ambito: Elemento al que se aplica la trama
//					0x01= Centros 
//					0x02= Grupo de aulas
//					0x04= Aulas
//					0x08= Grupo de ordenadores
//					0x10= Ordenadores
//			 idambito: Identificador del ambito
//			 cadenaip: Cadena con las ipes a las que se aplicar�el comando
//			 cadenamac: Cadena con las mac a las que se aplicar�el comando( Arrancar )
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
//________________________________________________________________________________________________________
$identificador=0;
$tipotrama=""; 
$ambito=0; 
$idambito=0;
$nombreambito="";
$cadenaip="";
$cadenamac="";

if (isset($_GET["identificador"]))	$identificador=$_GET["identificador"]; 
if (isset($_GET["tipotrama"]))	$tipotrama=$_GET["tipotrama"]; 
if (isset($_GET["ambito"]))	$ambito=$_GET["ambito"]; 
if (isset($_GET["idambito"]))	$idambito=$_GET["idambito"]; 
if (isset($_GET["nombreambito"]))	$nombreambito=$_GET["nombreambito"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if ($cmd){
	$rs=new Recordset; 
	switch($ambito){
		case $AMBITO_CENTROS :
			$cmd->texto="SELECT idcentro,nombrecentro FROM centros WHERE idcentro=".$idambito;
 			RecorreCentro($cmd);
			break;
		case $AMBITO_GRUPOSAULAS :
			$cmd->texto="SELECT idgrupo,nombregrupo   FROM grupos WHERE idgrupo=".$idambito." AND tipo=".$AMBITO_GRUPOSAULAS;
			RecorreGruposAulas($cmd);
			break;
		case $AMBITO_AULAS :
			$cmd->texto="SELECT idaula,nombreaula  FROM aulas WHERE idaula=".$idambito;
			RecorreAulas($cmd);
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$cmd->texto="SELECT idgrupo,nombregrupoordenador   FROM gruposordenadores WHERE idgrupo=".$idambito;
			RecorreGruposOrdenadores($cmd);
			break;
		case $AMBITO_ORDENADORES :
			$cmd->texto="SELECT ip,mac,nombreordenador,idservidorrembo  FROM ordenadores WHERE idordenador=".$idambito;
			RecorreOrdenadores($cmd);
			break;
	}
	$cadenaip=substr($cadenaip,0,strlen($cadenaip)-1); // Quita la coma
	$cadenamac=substr($cadenamac,0,strlen($cadenamac)-1); // Quita la coma
	switch($tipotrama){
		case 'CMD':
			$rsc=new Recordset; 
			$cmd->texto="SELECT * FROM comandos WHERE idcomando=".$identificador;
			$rsc->Comando=&$cmd; 
			if ($rsc->Abrir()){
				if(!$rsc->EOF){
					$parametros="identificador=".$identificador.chr(13);
					$parametros.="tipotrama=".$tipotrama.chr(13);
					$parametros.="idambito=".$idambito.chr(13);
					$parametros.="nombreambito=".$nombreambito.chr(13);
					$parametros.="ambito=".$ambito.chr(13);
					$parametros.="cadenaip=".$cadenaip.chr(13);
					$parametros.="cadenamac=".$cadenamac.chr(13);
					switch($ambito){
						case $AMBITO_CENTROS :
							$parametros.="nombrefuncion=".$rsc->campos["nfuncion1"].chr(13);
							$wurl=$rsc->campos["urlamb1"].chr(13);
							break;
						case $AMBITO_GRUPOSAULAS :
							$parametros.="nombrefuncion=".$rsc->campos["nfuncion2"].chr(13);
							$wurl=$rsc->campos["urlamb2"].chr(13);
							break;
						case $AMBITO_AULAS :
							$parametros.="nombrefuncion=".$rsc->campos["nfuncion4"].chr(13);
							$wurl=$rsc->campos["urlamb4"].chr(13);
							break;
						case $AMBITO_GRUPOSORDENADORES :
							$parametros.="nombrefuncion=".$rsc->campos["nfuncion8"].chr(13);
							$wurl=$rsc->campos["urlamb8"].chr(13);
							break;
						case $AMBITO_ORDENADORES :
							$parametros.="nombrefuncion=".$rsc->campos["nfuncion10"].chr(13);
							$wurl=$rsc->campos["urlamb10"].chr(13);
							break;
						}
					$parametros.="ejecutor=".$rsc->campos["ejecutor"].chr(13);
				}
			$rsc->Cerrar(); // Cierra Recordset
			$cmd->Conexion->Cerrar();
			$fp = fopen($fileparam,"w"); 
			fwrite($fp, $parametros,strlen($parametros)); 
			fclose($fp); 
			Header('Location: '.$wurl);
			break;
		}
	}
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
		$cmd->texto="SELECT idgrupo,nombregrupo  FROM grupos WHERE idcentro=".$idcentro." AND grupoid=0  AND tipo=".$AMBITO_GRUPOSAULAS;
		RecorreGruposAulas($cmd);
		$cmd->texto="SELECT idaula,nombreaula   FROM aulas WHERE idcentro=".$idcentro." AND grupoid=0";
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
		$cmd->texto="SELECT idgrupo,nombregrupo   FROM grupos WHERE grupoid=".$idgrupo. "  AND tipo=".$AMBITO_GRUPOSAULAS;
		RecorreGruposAulas($cmd);
		$cmd->texto="SELECT idaula,nombreaula   FROM aulas WHERE  grupoid=".$idgrupo;
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
		$cmd->texto="SELECT idgrupo,nombregrupoordenador   FROM gruposOrdenadores WHERE idaula=".$idaula." AND grupoid=0";
		RecorreGruposOrdenadores($cmd);
		$cmd->texto="SELECT ip,mac,nombreordenador,idservidorrembo   FROM ordenadores WHERE  idaula=".$idaula." AND grupoid=0";
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
		$cmd->texto="SELECT idgrupo,nombregrupoordenador   FROM gruposOrdenadores WHERE grupoid=".$idgrupo;
		RecorreGruposOrdenadores($cmd);
		$cmd->texto="SELECT ip,mac,nombreordenador ,idservidorrembo  FROM ordenadores WHERE  grupoid=".$idgrupo;
		RecorreOrdenadores($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreOrdenadores($cmd){
	global $cadenaip;
	global $cadenamac;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	while (!$rs->EOF){
		$cadenaip.=$rs->campos["ip"].";";
		$cadenamac.=$rs->campos["mac"].";";
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
?>