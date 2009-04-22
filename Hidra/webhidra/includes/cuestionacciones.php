<?
/* -------------------------------------------------------------------------------------------
	Inserta, modifica o elimina un grupo de servidores dhcp de la base de datos
---------------------------------------------------------------------------------------------*/
function CuestionAcciones($cmd,$shidra,$parametros){

	global $sw_ejya;
	global $sw_seguimiento;

	global $sw_mktarea;
	global $nwidtarea;
	global $nwdescritarea;

	global $sw_mkprocedimiento;
	global $nwidprocedimiento;
	global $nwdescriprocedimiento;

	global $identificador;

	if($sw_ejya=='true' ){ // switch de ejecución inmediata ----------------------------------------------------------------------
		if($sw_seguimiento=='true' ){ // switch de ejecución con seguimiento
			$cmd->texto="INSERT INTO acciones (tipoaccion,idtipoaccion,cateaccion,ambito,idambito,fechahorareg,estado,resultado,idcentro,parametros,accionid,idnotificador) VALUES (@tipoaccion,@idtipoaccion,@cateaccion,@ambito,@idambito,@fechahorareg,@estado,@resultado,@idcentro,@parametros,0,0)";
			$resul=$cmd->Ejecutar();
			if($resul){
				$parametros.="ids=".$cmd->Autonumerico().chr(13);
			}
		}
		// Envio al servidor hidra
		if ($shidra->conectar()){ // Se ha establecido la conexión con el servidor hidra
			$shidra->envia_comando($parametros);
			$shidra->desconectar();
		}
		else
			return(false);
	}
	// Fin ejecución inmediata -------------------------------------------------------------------------------------------------------------

	if($sw_mkprocedimiento=='true'){ // switch de creación o inclusión en procedimiento ---------------------------------------------------------
		if($nwidprocedimiento==0){
			$cmd->ParamSetValor("@descripcion",$nwdescriprocedimiento,0);
			$cmd->texto="INSERT INTO procedimientos(descripcion,idcentro) VALUES (@descripcion,@idcentro)";
			$resul=$cmd->Ejecutar();
			if($resul)
				$nwidprocedimiento=$cmd->Autonumerico();
			else
				return(false);
		}
		if($nwidprocedimiento>0){ //  inclusión en procedimiento existente 
			$cmd->ParamSetValor("@idprocedimiento",$nwidprocedimiento,1);
			$cmd->ParamSetValor("@idcomando",$identificador,1);
			$cmd->ParamSetValor("@parametros",Sin_iph($parametros),0);
			$cmd->texto="INSERT INTO procedimientos_comandos(idprocedimiento,orden,idcomando,parametros) VALUES (@idprocedimiento,0,@idcomando,@parametros)";
			$resul=$cmd->Ejecutar();
			$cmd->ParamSetValor("@parametros",$parametros);
			if(!$resul) return(false);
		}
	}	

	if($sw_mktarea=='true'){ // switch de creación o inclusión en tarea -----------------------------------------------------------
		if($nwidtarea==0){ // Nueva tarea
			$cmd->ParamSetValor("@descripcion",$nwdescritarea);
			$cmd->texto="INSERT INTO tareas(descripcion,idcentro) VALUES (@descripcion,@idcentro)";
			$resul=$cmd->Ejecutar();
			if($resul)
				$nwidtarea=$cmd->Autonumerico();
			else
				return(false);
		}
		if($nwidtarea>0){ //  inclusión en tarea existente 
			$cmd->ParamSetValor("@idtarea",$nwidtarea);
			$cmd->ParamSetValor("@idcomando",$identificador);
			$cmd->texto="INSERT INTO tareas_comandos(idtarea,orden,idcomando,ambito,idambito,parametros) VALUES (@idtarea,0,@idcomando,@ambito,@idambito,@parametros)";
			$resul=$cmd->Ejecutar();
			if(!$resul) return(false);
		}
	}
	return(true);
}
	?>