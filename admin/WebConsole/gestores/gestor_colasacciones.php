<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: colasacciones.php
// Descripción : 
//		Gestiona las operaciones realizadas sobre las acciones registradas
// *************************************************************************************************************************************************

include_once("../includes/ctrlacc.php");
include_once("../includes/TomanDatos.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/restfunctions.php");
//________________________________________________________________________________________________________
	
$opcion=0; // Inicializa parametros
$acciones="";

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; 
if (isset($_POST["acciones"])) $acciones=$_POST["acciones"]; 

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=gestiona($cmd,$opcion,$acciones);
	$cmd->Conexion->Cerrar();
}
if($resul) 
	echo $opcion; // Proceso con éxito, devuelve la opción
else 
	echo 0; // Proceso con algún error, devuelve cero
//
// *****************************************************************************************************************
//
function gestiona($cmd,$opcion,$acciones){

	global $ACCION_EXITOSA; 
	global $ACCION_FALLIDA; 
	global $ACCION_SINRESULTADO; 
	
	global $LITACCION_EXITOSA;
	global $LITACCION_FALLIDA;
	
	global $ACCION_DETENIDA;
	global $ACCION_INICIADA;
	global $ACCION_FINALIZADA;

	$tbAcciones=explode(";",$acciones);
	for($i=0;$i<sizeof($tbAcciones)-1;$i++){
		list($tipoaccion,$idtipoaccion,$sesion,$idaccion)=explode(",",$tbAcciones[$i]);
		switch($opcion){
		
			case 1: // Eliminar ============================================================
				$cmd->texto="DELETE  FROM  acciones ";
				if(!empty($idaccion))
					$cmd->texto.=" WHERE idaccion=".$idaccion;
				else
					$cmd->texto.=" WHERE tipoaccion=".$tipoaccion." AND idtipoaccion=".$idtipoaccion." AND sesion=".$sesion;
				$resul=$cmd->Ejecutar();
				break;
				
			case  2: // Reiniciar ============================================================
				/* Recordset para envío de comando actualizar */
				$cmd->texto="SELECT	count(*) as con,
									group_concat(cast(ordenadores.idordenador AS char( 11 ) ) SEPARATOR ',' ) AS cadenaid,
									group_concat(ordenadores.ip SEPARATOR ';' ) AS cadenaip
								FROM acciones
								INNER JOIN ordenadores ON ordenadores.idordenador=acciones.idordenador";
				if(!empty($idaccion))
					$cmd->texto.=" WHERE idaccion=".$idaccion;
				else	
					$cmd->texto.=" WHERE tipoaccion=".$tipoaccion." AND idtipoaccion=".$idtipoaccion." 
									AND sesion=".$sesion;
							
				$rs=new Recordset; 
				$rs->Comando=&$cmd; 
				if (!$rs->Abrir()) return(false); // Error al abrir recordset
				if ($rs->EOF) return(true); // No existen registros			
				$aplicacion="ido=".$rs->campos["cadenaid"].chr(13)."iph=".$rs->campos["cadenaip"].chr(13);

				/* Update de la tabla */
				$cmd->texto="UPDATE acciones 
						SET estado=".$ACCION_INICIADA.",resultado=".$ACCION_SINRESULTADO.",descrinotificacion='',fechahorafin=DEFAULT";
				if(!empty($idaccion))
					$cmd->texto.=" WHERE idaccion=".$idaccion;
				else	
					$cmd->texto.=" WHERE tipoaccion=".$tipoaccion." AND idtipoaccion=".$idtipoaccion." AND sesion=".$sesion;
				$resul=$cmd->Ejecutar();
				if($resul)
					run_schedule($rs->campos["cadenaip"]);
				break;	
				
			case  3: // Para acciones ============================================================
				$cmd->texto="UPDATE acciones SET estado=".$ACCION_DETENIDA;
				if(!empty($idaccion))
					$cmd->texto.=" WHERE idaccion=".$idaccion." AND estado=".$ACCION_INICIADA;
				else	
					$cmd->texto.=" WHERE tipoaccion=".$tipoaccion." AND idtipoaccion=".$idtipoaccion." AND sesion=".$sesion." AND estado=".$ACCION_INICIADA;
				$resul=$cmd->Ejecutar();
				break;	
				
			case  4: // Reanuda acciones ============================================================
				$cmd->texto="UPDATE acciones SET estado=".$ACCION_INICIADA;
				if(!empty($idaccion))
					$cmd->texto.=" WHERE idaccion=".$idaccion." AND estado=".$ACCION_DETENIDA;
				else
					$cmd->texto.=" WHERE tipoaccion=".$tipoaccion." AND idtipoaccion=".$idtipoaccion." AND sesion=".$sesion." AND estado=".$ACCION_DETENIDA;
				$resul=$cmd->Ejecutar();
				break;	
				
			case  5: // Finalizar sin errores ============================================================
				$ahora=date("y/m/d H:i:s");
				$cmd->texto="UPDATE acciones SET estado=".$ACCION_FINALIZADA.",resultado=".$ACCION_EXITOSA.",
							fechahorafin='".$ahora."'".",descrinotificacion='".$LITACCION_EXITOSA."'";
				if(!empty($idaccion))
					$cmd->texto.=" WHERE idaccion=".$idaccion." AND estado=".$ACCION_INICIADA;
				else
					$cmd->texto.=" WHERE tipoaccion=".$tipoaccion." AND idtipoaccion=".$idtipoaccion." AND sesion=".$sesion." AND estado=".$ACCION_INICIADA;
				$resul=$cmd->Ejecutar();
				break;	
				
			case  6: // Finalizar con errores ============================================================
				$ahora=date("y/m/d H:i:s");
				$cmd->texto="UPDATE acciones SET estado=".$ACCION_FINALIZADA.",resultado=".$ACCION_FALLIDA.",
							fechahorafin='".$ahora."'".",descrinotificacion='".$LITACCION_FALLIDA."'";
				if(!empty($idaccion))
					$cmd->texto.=" WHERE idaccion=".$idaccion." AND estado=".$ACCION_INICIADA;
				else
					$cmd->texto.=" WHERE tipoaccion=".$tipoaccion." AND idtipoaccion=".$idtipoaccion." AND sesion=".$sesion." AND estado=".$ACCION_INICIADA;
				$resul=$cmd->Ejecutar();
				break;					
		}
		//echo $cmd->texto;
	}
	return($resul);
}
