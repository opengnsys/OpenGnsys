<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: RecopilaIpesMacs.php
// Descripción : 
//		Recopila las IPes, las Macs y los identificadores de ordenadores de un ámbito determinado
//
// Especificaciones:
//		Esta Función recibe tres parámatros:
//			cmd : Un objeto comando totalmente operativo
//			ambito:  Ámbito
//			 idambito: Identificador del ámbito
//
//	Devuelve:
//		Todas los identificadores de ordenadores , las ipes y las macs de los ordenadores que componen el ámbito
//		Para ellos habrá que tener declarada tres variables globales :
//				$cadenaid, $cadenaip y $cadenamac
// *************************************************************************************************************************************************
function RecopilaIpesMacs($cmd,$ambito,$idambito,$filtroip=""){
	global $cadenaid;
	global $cadenaip;
	global $cadenamac;

	global $AMBITO_CENTROS;
	global $AMBITO_GRUPOSAULAS;
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;

	$cadenaid="";
	$cadenaip="";
	$cadenamac="";

	$rs=new Recordset; 
		
	if(!empty($filtroip)){
		$filtroip="'".str_replace(";","','",$filtroip)."'"; // Cambia caracter ; para consulta alfanumérica
		$cmd->texto="SELECT ip, mac, nombreordenador, idordenador, agentkey
			       FROM ordenadores
			      WHERE ip IN (".$filtroip.")";
		RecorreOrdenadores($cmd);
	}
	else{
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
				$cmd->texto="SELECT ip, mac, nombreordenador, idordenador, agentkey
					       FROM ordenadores
					      WHERE idordenador=".$idambito;
				RecorreOrdenadores($cmd);
				break;
			default: // Se trata de un conjunto aleatorio de ordenadores
				$cmd->texto="SELECT ip, mac, nombreordenador, idordenador, agentkey
					       FROM ordenadores
					      WHERE idordenador IN (".$idambito.")";
				RecorreOrdenadores($cmd);
		}
	}
	$cadenaid=substr($cadenaid,0,strlen($cadenaid)-1); // Quita la coma
	$cadenaip=substr($cadenaip,0,strlen($cadenaip)-1); // Quita la coma
	$cadenamac=substr($cadenamac,0,strlen($cadenamac)-1); // Quita la coma
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
		$cmd->texto="SELECT idgrupo,nombregrupoordenador FROM gruposordenadores WHERE idaula=".$idaula." AND grupoid=0";
		RecorreGruposOrdenadores($cmd);
		$cmd->texto="SELECT ip, mac, nombreordenador, idordenador, agentkey
			       FROM ordenadores
			      WHERE idaula=".$idaula." AND grupoid=0";
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
		$cmd->texto="SELECT idgrupo,nombregrupoordenador FROM gruposordenadores WHERE grupoid=".$idgrupo;
		RecorreGruposOrdenadores($cmd);
		$cmd->texto="SELECT ip, mac, nombreordenador, idordenador, agentkey
			       FROM ordenadores
			      WHERE grupoid=".$idgrupo;
		RecorreOrdenadores($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreOrdenadores($cmd){
	global $cadenaid;
	global $cadenaip;
	global $cadenamac;
	global $cadenaoga;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	while (!$rs->EOF){
		$cadenaid.=$rs->campos["idordenador"].",";
		$cadenaip.=$rs->campos["ip"].";";
		$cadenamac.=$rs->campos["mac"].";";
		$cadenaoga.=(is_null($rs->campos["agentkey"])?"":$rs->campos["agentkey"]).";";
		$rs->Siguiente();
	}
	$rs->Cerrar();
}

function get_netmasks($cmd, &$macs, &$netmasks){
	$macs = str_replace(";", "','", $macs);
	$cmd->texto="SELECT mac, mascara
		     FROM ordenadores
		     WHERE mac IN ('".$macs."')";
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return;
	$macs = "";
	while (!$rs->EOF){
		$macs.=$rs->campos["mac"].";";
		$netmasks.=$rs->campos["mascara"].";";
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$macs = substr($macs, 0, -1);
	$netmasks = substr($netmasks, 0, -1);
}
