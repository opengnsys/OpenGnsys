<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: incrementales_eliminacion.php
// Descripción :
//	Elimina en cascada registros de la tabla softincrementales 
//		Parametros: 
//		-	cmd:Una comando ya operativo (con conexión abierta)  
//		-	identificador: El identificador por el que se eliminará el software incremental
//		-	nombreid: Nombre del campo identificador del registro 
//		-	swid: Indica 0= El identificador es tipo alfanumérico	1= EI identificador es tipo numérico ( valor por defecto) *************************************************************************************************************************************************
function	EliminaSoftincremental($cmd,$identificador,$nombreid,$swid=1){
	if (empty($identificador)) return(true);
	if($swid==0)
		$cmd->texto="SELECT  idsoftincremental  FROM  softincrementales WHERE ".$nombreid."='".$identificador."'";
	else
		$cmd->texto='SELECT  idsoftincremental  FROM softincrementales WHERE '.$nombreid.'='.$identificador;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	if ($rs->numeroderegistros==0) return(true);
	$rs->Primero(); 
	while (!$rs->EOF){
		$cmd->texto="DELETE  FROM softincremental_softwares  WHERE idsoftincremental=".$rs->campos["idsoftincremental"];
		$resul=$cmd->Ejecutar();
		if ($resul){
			$cmd->texto="DELETE FROM imagenes_softincremental  WHERE idsoftincremental=".$rs->campos["idsoftincremental"];
			$resul=$cmd->Ejecutar();
		}
		if (!$resul){
			$rs->Cerrar();
			return(false);
		}
		$rs->Siguiente();
	}
	if($swid==0)
		$cmd->texto="DELETE  FROM softincrementales WHERE ".$nombreid."='".$identificador."'";
	else
		$cmd->texto='DELETE  FROM softincrementales  WHERE '.$nombreid.'='.$identificador;
	$resul=$cmd->Ejecutar();
	return($resul);
}
?>
