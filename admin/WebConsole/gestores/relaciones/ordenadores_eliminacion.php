<?php
//Ficheros de inclusión.
include_once (dirname (__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR. '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'tftputils.php');

// ************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: ordenadores_eliminacion.php
// Descripción :
//	Elimina en cascada registros de la tabla ordenadores 
//		Parametros: 
//		-	cmd:Una comando ya operativo (con conexión abierta)  
//		-	identificador: El identificador por el que se eliminará el ordenador
//		-	nombreid: Nombre del campo identificador del registro 
//		-	swid: Indica 0= El identificador es tipo alfanumérico	1= EI identificador es tipo numérico ( valor por defecto) 
//*************************************************************************************************
function EliminaOrdenadores($cmd,$identificador,$nombreid,$swid=1)
{
	if (empty ($identificador)) return (true);	// Salir si identificador nulo.
	if($swid==0)
		$cmd->texto="SELECT idordenador, mac FROM ordenadores WHERE ".$nombreid."='".$identificador."'";
	else
		$cmd->texto='SELECT idordenador, mac FROM ordenadores WHERE '.$nombreid.'='.$identificador;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return (false);		// Error al abrir recordset.
	if ($rs->numeroderegistros==0) return (true);	// No hay registros que borrar.
	$rs->Primero(); 
	while (!$rs->EOF){	
		$mac = $rs->campos["mac"];
		$cmd->texto="DELETE FROM ordenadores_particiones WHERE idordenador=".$rs->campos["idordenador"];
		$resul=$cmd->Ejecutar();
		if ($resul) {
			// Borrar fichero PXE asociado.
			deleteBootFile ($mac);
		} else {
			$rs->Cerrar();
			return(false);
		}
		$rs->Siguiente();
	}
	if ($swid==0)
		$cmd->texto="DELETE FROM ordenadores WHERE ".$nombreid."='".$identificador."'";
	else
		$cmd->texto='DELETE FROM ordenadores WHERE '.$nombreid.'='.$identificador;
	$resul=$cmd->Ejecutar();
	return($resul);
}


