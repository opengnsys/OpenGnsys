<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: RecopilaIpesMacs.php
// Descripción : 
//		Prepara los parametros de las tramas de todos los comando
//
// Especificaciones:
//		Esta Función recibe tres parámatros:
//			cmd : Un objeto comando totalmente operativo
//			ambito:  Ámbito
//			 idambito: Identificador del ámbito
//
//	Devuelve:
//		Todas las ipes y las macs de los ordenadores que componen el ámbito
//		Para ellos habrá que tener declarada dos variables globales :
//				$cadenaip  y	$cadenamac
// *************************************************************************************************************************************************
function RecopilaIpesMacs($cmd,$ambito,$idambito){
	global $cadenaip;
	global $cadenamac;

	global $AMBITO_CENTROS;
	global $AMBITO_GRUPOSAULAS;
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;

	$cadenaip="";
	$cadenamac="";

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