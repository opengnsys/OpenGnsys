<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: perfileshard_eliminacion.php
// Descripción :
//	Elimina en cascada registros de la tabla perfileshard 
//		Parametros: 
//		-	cmd:Una comando ya operativo (con conexión abierta)  
//		-	identificador: El identificador por el que se eliminará el el perfil hardware
//		-	nombreid: Nombre del campo identificador del registro 
//		-	swid: Indica 0= El identificador es tipo alfanumérico	1= EI identificador es tipo numérico ( valor por defecto) *************************************************************************************************************************************************
function	EliminaPerfileshard($cmd,$identificador,$nombreid,$swid=1){
	if (empty($identificador)) return(true);
	if($swid==0)
		$cmd->texto="SELECT  idperfilhard  FROM  perfileshard WHERE ".$nombreid."='".$identificador."'";
	else
		$cmd->texto='SELECT  idperfilhard  FROM perfileshard WHERE '.$nombreid.'='.$identificador;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	if ($rs->numeroderegistros==0) return(true);
	$rs->Primero(); 
	while (!$rs->EOF){
		$cmd->texto="UPDATE ordenadores SET idperfilhard=0 WHERE idperfilhard=".$rs->campos["idperfilhard"];
		$resul=$cmd->Ejecutar();
		if ($resul){
			$cmd->texto="DELETE FROM  perfileshard_perfilessoft  WHERE idperfilhard=".$rs->campos["idperfilhard"];
			$resul=$cmd->Ejecutar();
			if ($resul)
					$cmd->texto="DELETE  FROM perfileshard_hardwares WHERE idperfilhard=".$rs->campos["idperfilhard"];;
		}
	
		if (!$resul){
			$rs->Cerrar();
			return(false);
		}
		$rs->Siguiente();
	}
	if($swid==0)
		$cmd->texto="DELETE  FROM perfileshard WHERE ".$nombreid."='".$identificador."'";
	else
		$cmd->texto='DELETE FROM  perfileshard  WHERE '.$nombreid.'='.$identificador;
	$resul=$cmd->Ejecutar();
	return($resul);
}
?>
