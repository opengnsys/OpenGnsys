<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: menus_eliminacion.php
// Descripción :
//	Elimina en cascada registros de la tabla menus 
//		Parametros: 
//		-	cmd:Una comando ya operativo (con conexión abierta)  
//		-	identificador: El identificador por el que se eliminará el menu
//		-	nombreid: Nombre del campo identificador del registro 
//		-	swid: Indica 0= El identificador es tipo alfanumérico	1= EI identificador es tipo numérico ( valor por defecto) *************************************************************************************************************************************************
function	EliminaMenus($cmd,$identificador,$nombreid,$swid=1){
	global $EJECUCION_PROCEDIMIENTO;
	if (empty($identificador)) return(true);
	if($swid==0)
		$cmd->texto="SELECT  idmenu  FROM  menus WHERE ".$nombreid."='".$identificador."'";
	else
		$cmd->texto='SELECT  idmenu  FROM menus WHERE '.$nombreid.'='.$identificador;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	if ($rs->numeroderegistros==0) return(true);
	$rs->Primero(); 
	while (!$rs->EOF){
		$cmd->texto="UPDATE ordenadores SET idmenu=0 where idmenu=".$rs->campos["idmenu"];;
		$resul=$cmd->Ejecutar();
		if (!$resul){
			$rs->Cerrar();
			return(false);
		}
		$rs->Siguiente();
	}
	if($swid==0)
		$cmd->texto="DELETE  FROM menus WHERE ".$nombreid."='".$identificador."'";
	else
		$cmd->texto='DELETE  FROM menus  WHERE '.$nombreid.'='.$identificador;
	$resul=$cmd->Ejecutar();
	return($resul);
}
?>
