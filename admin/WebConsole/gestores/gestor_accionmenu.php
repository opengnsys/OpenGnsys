<?
// *************************************************************************************************************************************************
// Aplicaci�n WEB: ogAdmWebCon
// Autor: Jos� Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_accionmenu.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de acciones_menus
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________

$altas=""; 
$bajas=""; 
$modificaciones=""; 

if (isset($_POST["altas"])) $altas=$_POST["altas"]; // Recoge parametros
if (isset($_POST["bajas"])) $bajas=$_POST["bajas"];
if (isset($_POST["modificaciones"])) $modificaciones=$_POST["modificaciones"];

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
$literal="resultado_gestion_accionmenu";

if ($resul)
	echo $literal."(1,'".$cmd->DescripUltimoError()."');";
else
	echo $literal."(0,'".$cmd->DescripUltimoError()."');";

// *************************************************************************************************************************************************
function Gestiona()
{
	global $cmd;
	global $altas; 
	global $bajas; 
	global $modificaciones; 
	global $op_alta;
	global $op_modificacion;
	global $op_eliminacion;

	$cmd->CreaParametro("@idtipoaccion",0,1);
	$cmd->CreaParametro("@idmenu",0,1);
	$cmd->CreaParametro("@tipoaccion",0,1);
	$cmd->CreaParametro("@tipoitem",0,1);
	$cmd->CreaParametro("@idurlimg","",1);
	$cmd->CreaParametro("@descripitem","",0);
	$cmd->CreaParametro("@orden",0,1);

	/* Altas */
	if(!empty($altas)){
		$altas=substr($altas,0,strlen($altas)-1); // Quita el último ";"
		$tbAltas=split(";",$altas);
		for($i=0;$i<sizeof($tbAltas);$i++){
				$tbAlta=split(",",$tbAltas[$i]);
				/* Toma datos  altas */
				$idmenu=$tbAlta[0];
				$idtipoaccion=$tbAlta[1];
				$tipoaccion=$tbAlta[2];
				$tipoitem=$tbAlta[3];
				$idurlimg=$tbAlta[4];
				$descripitem=$tbAlta[5];
				$orden=$tbAlta[6];
			
				$cmd->ParamSetValor("@idtipoaccion",$idtipoaccion);
				$cmd->ParamSetValor("@idmenu",$idmenu);
				$cmd->ParamSetValor("@tipoaccion",$tipoaccion);
				$cmd->ParamSetValor("@tipoitem",$tipoitem);
				$cmd->ParamSetValor("@idurlimg",$idurlimg);
				$cmd->ParamSetValor("@descripitem",$descripitem);
				$cmd->ParamSetValor("@orden",$orden);
			
				$cmd->texto="INSERT INTO acciones_menus (idmenu,idtipoaccion,tipoaccion,tipoitem,idurlimg,descripitem,orden) 
											VALUES (@idmenu,@idtipoaccion,@tipoaccion,@tipoitem,@idurlimg,@descripitem,@orden)";
				$resul=$cmd->Ejecutar();	
				//echo $cmd->texto;
				if(!$resul)
					return(false);		
		}
	}
	
	/* Bajas */
	if(!empty($bajas)){
		$bajas=substr($bajas,0,strlen($bajas)-1); // Quita el último ";"
		$tbBajas=split(";",$bajas);
		for($i=0;$i<sizeof($tbBajas);$i++){
				$tbBaja=split(",",$tbBajas[$i]);
				/* Toma datos  bajas */
				$idmenu=$tbBaja[0];
				$idtipoaccion=$tbBaja[1];
				$tipoaccion=$tbBaja[2];
		
				$cmd->ParamSetValor("@idtipoaccion",$idtipoaccion);
				$cmd->ParamSetValor("@idmenu",$idmenu);
				$cmd->ParamSetValor("@tipoaccion",$tipoaccion);
			
				$cmd->texto="DELETE FROM acciones_menus 
											WHERE idmenu=@idmenu AND idtipoaccion=@idtipoaccion AND tipoaccion=@tipoaccion";
				$resul=$cmd->Ejecutar();	
				//echo $cmd->texto;
				if(!$resul)
					return(false);		
		}	
	}
/* Modificaciones */
	if(!empty($modificaciones)){
		$modificaciones=substr($modificaciones,0,strlen($modificaciones)-1); // Quita el último ";"
		$tbModificaciones=split(";",$modificaciones);
		for($i=0;$i<sizeof($tbModificaciones);$i++){
				$tbtbModificacion=split(",",$tbModificaciones[$i]);
				/* Toma datos  modificaciones */
				$idmenu=$tbtbModificacion[0];
				$idtipoaccion=$tbtbModificacion[1];
				$tipoaccion=$tbtbModificacion[2];
				$tipoitem=$tbtbModificacion[3];
				$idurlimg=$tbtbModificacion[4];
				$descripitem=$tbtbModificacion[5];
				$orden=$tbtbModificacion[6];
			
				$cmd->ParamSetValor("@idtipoaccion",$idtipoaccion);
				$cmd->ParamSetValor("@idmenu",$idmenu);
				$cmd->ParamSetValor("@tipoaccion",$tipoaccion);
				$cmd->ParamSetValor("@tipoitem",$tipoitem);
				$cmd->ParamSetValor("@idurlimg",$idurlimg);
				$cmd->ParamSetValor("@descripitem",$descripitem);
				$cmd->ParamSetValor("@orden",$orden);
			
				$cmd->texto="UPDATE acciones_menus set tipoitem=@tipoitem,idurlimg=@idurlimg,descripitem=@descripitem,orden=@orden
											 WHERE idmenu=@idmenu AND idtipoaccion=@idtipoaccion AND tipoaccion=@tipoaccion";
				$resul=$cmd->Ejecutar();	
				//echo $cmd->texto;
				if(!$resul)
					return(false);		
		}
	}
	return(true);
}
?>
