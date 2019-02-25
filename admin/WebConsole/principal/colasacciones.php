<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: colasacciones.php
// Descripción : 
//		Visualiza las acciones pendientes y finalizadas con los resultados de estatus y horas de inicio y finalización
// Cambio en la linea 73 la cantidad de días predeterminado por 180 (antes 3)
// *************************************************************************************************************************************************
	include_once("../includes/ctrlacc.php");
	include_once("../clases/AdoPhp.php");
	include_once("../clases/MenuContextual.php");
	include_once("../includes/constantes.php");
	include_once("../includes/comunes.php");
	include_once("../includes/RecopilaIpesMacs.php");
	include_once("../includes/InvFecha.php");
	include_once("../clases/XmlPhp.php");
	include_once("../includes/HTMLSELECT.php");
	include_once("../includes/HTMLCTESELECT.php");
	include_once("../includes/TomaDato.php");	
	include_once("../includes/CreaComando.php");
	include_once("../idiomas/php/".$idioma."/colasacciones_".$idioma.".php");
	//________________________________________________________________________________________________________
	//
	// Captura de parámetros	
	//________________________________________________________________________________________________________
	
	$ambito=""; 
	$idambito=0;
	$nombreambito="";

	$fechainicio="";
	$fechafin="";
	$horainicio="";
	$horafin="";
	$tipoaccion="";
	$estado="";
	$resultado="";
	$porcendesde="";
	$porcenhasta="";
	$swPOST="";
	$tiposacciones="";
	$estados="";
	$resultados="";
	$visupro="";
	$visuprm="";
	$visucmd="";
	$sesion="";
	$urlimg="";
	$textambito="";

	if (isset($_GET["ambito"]))	$ambito=$_GET["ambito"]; 
	if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
	if (isset($_GET["nombreambito"])) $nombreambito=$_GET["nombreambito"]; 

	if (isset($_POST["ambito"]))	$ambito=$_POST["ambito"]; 
	if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
	if (isset($_POST["nombreambito"])) $nombreambito=$_POST["nombreambito"]; 

	if (isset($_POST["tipoaccion"])) $tipoaccion=$_POST["tipoaccion"]; 
	if (isset($_POST["estado"])) $estado=$_POST["estado"]; 
	if (isset($_POST["resultado"])) $resultado=$_POST["resultado"]; 

	if (isset($_POST["fechainicio"])) $fechainicio=$_POST["fechainicio"]; 
	if (isset($_POST["fechafin"])) $fechafin=$_POST["fechafin"]; 
	if (isset($_POST["horainicio"])) $horainicio=$_POST["horainicio"]; 
	if (isset($_POST["horafin"])) $horafin=$_POST["horafin"]; 

	if (isset($_POST["swPOST"])) $swPOST=$_POST["swPOST"]; 
	if (isset($_POST["visuprm"])) $visuprm=$_POST["visuprm"]; 
	if (isset($_POST["visupro"])) $visupro=$_POST["visupro"]; 
	if (isset($_POST["visucmd"])) $visucmd=$_POST["visucmd"]; 

	if (isset($_POST["sesion"])) $sesion=$_POST["sesion"]; 

	if (function_exists('date_default_timezone_set')) {
		date_default_timezone_set('UTC');
	}
	if(empty($swPOST)){ // Valores por defecto 
		$wfechainicio=mktime(0, 0, 0, date("m")  , date("d")-180, date("Y")); // Acciones desde tres días antes
		$wfechafin=mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
		$fechainicio=date("d/m/Y",$wfechainicio);
		$fechafin=date("d/m/Y ",$wfechafin);
		$estado=0;
		$resultado=0;
		$tipoaccion=0;
		$visuprm=0;
		$visupro=0;
		$visucmd=1;
	}

	if (isset($_POST["porcendesde"])) $porcendesde=$_POST["porcendesde"]; 
	if (isset($_POST["porcenhasta"])) $porcenhasta=$_POST["porcenhasta"]; 
	if($porcendesde=="") $porcendesde=0;
	if($porcenhasta=="") $porcenhasta=100;
	
	//________________________________________________________________________________________________________

	$cmd=CreaComando($cadenaconexion);
	if (!$cmd)
		Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.

	$tbParametros=CreaTablaParametros($cmd); // Crea tabla en mezmmoria para acceder a detalles de comandos 

	//________________________________________________________________________________________________________
	//
	// Clausula WHERE ( construcción )
	//________________________________________________________________________________________________________
	
	$ClausulaWhere="";

	// Cuestion de fechas 
	$WhereFechaInicio="";
	$WhereFechaFin="";
	$WhereFechaReg="";
	if($fechainicio!="")
		$WhereFechaInicio="acciones.fechahorareg>='".InvFecha($fechainicio).' ' .$horainicio."'";
	if($fechafin!="")
		$WhereFechaFin.=" acciones.fechahorareg<='".InvFecha($fechafin).' ' .$horafin."'";
	if($WhereFechaInicio!=""){
		if($WhereFechaFin!="")
			$WhereFechaReg=" (".$WhereFechaInicio." AND ".$WhereFechaFin.") ";
		else
			$WhereFechaReg=" (".$WhereFechaInicio.") ";
	}
	else{
			if($WhereFechaFin!="")
				$WhereFechaReg=" (".$WhereFechaFin.") ";
	}
	$ClausulaWhere.=$WhereFechaReg;
	
	// Cuestion tipos de acciones
	if(!empty($tipoaccion)) $ClausulaWhere.=" AND acciones.tipoaccion=".$tipoaccion;

	// Cuestion identificador del Centro que ha ejecutado la acción
	$WhereCentroAccion="";
	$WhereCentroAccion='acciones.idcentro='.$idcentro;
	$ClausulaWhere.=" AND (".$WhereCentroAccion.")";
	//________________________________________________________________________________________________________
	?>
	<HTML>
	<HEAD>
		<TITLE>Administración web de aulas</TITLE>
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		<LINK rel="stylesheet" type="text/css" href="../estilos.css">
		<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
		<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
		<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>
		<SCRIPT language="javascript" src="../jscripts/colasacciones.js"></SCRIPT>
		<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>				
		<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/colasacciones_'.$idioma.'.js"></SCRIPT>'?>
	</HEAD>
	<BODY oncontextmenu="return false">
	<?php
	echo '<P align=center class=cabeceras><img src="../images/iconos/acciones.gif">&nbsp;'.$TbMsg[0].'&nbsp;</P>';

	echo '<FORM name="fdatos" action="colasacciones.php" method="post">';
		echo '<INPUT type="hidden" name="ambito" value="'.$ambito.'">';
		echo '<INPUT type="hidden" name="idambito" value="'.$idambito.'">';
		echo '<INPUT type="hidden" name="nombreambito" value="'.$nombreambito.'">';
		echo '<INPUT type="hidden" name="swPOST" value="1">';
		echo '<INPUT type="hidden" name="tiposacciones" value="'.$tiposacciones.'">';
		echo '<INPUT type="hidden" name="estados" value="'.$estados.'">';
		echo '<INPUT type="hidden" name="resultados" value="'.$resultados.'">';
		echo '<INPUT type="hidden" name="resultados" value="'.$fechainicio.'">';
		echo '<INPUT type="hidden" name="resultados" value="'.$fechafin.'">';
		echo '<INPUT type="hidden" name="resultados" value="'.$horainicio.'">';
		echo '<INPUT type="hidden" name="resultados" value="'.$horafin.'">';
		echo '<INPUT type="hidden" name="resultados" value="'.$porcendesde.'">';
		echo '<INPUT type="hidden" name="resultados" value="'.$porcenhasta.'">';
		echo '<INPUT type="hidden" name="sesion" value="'.$sesion.'">';
		

	$HTMLCriterios=""; 
	$HTMLCriterios.='<TABLE class=tabla_busquedas align=center border="0">'; // Filtro de búsquedas
	$HTMLCriterios.='	<TR>';
	$HTMLCriterios.='		<TD HEIGHT="30px" style="BORDER-BOTTOM:#5a86b5 1px solid;" colspan=2 align="center">';
	$HTMLCriterios.='			<SPAN style="FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; 
													FONT-SIZE: 11px;COLOR:#5a86b5;FONT-WEIGHT: 700;">____ '.$TbMsg[1].'____</SPAN></TD></TR>';

	$HTMLCriterios.='<TR>';
	$HTMLCriterios.='	<TD>'; // Desplegables de tipo de acciones y estados
	$HTMLCriterios.='		<TABLE class=tabla_standar align=center border="0">';
	$HTMLCriterios.='			<TR>';
	$HTMLCriterios.='				<TH align=center>&nbsp;'.$TbMsg[2].'&nbsp;</TH></TR>';

	// Desplegable con los tipos de acciones
	$parametros="0=".$TbMsg[61].chr(13);
	$parametros.=$EJECUCION_COMANDO."=".$LITEJECUCION_COMANDO.chr(13);
	$parametros.=$EJECUCION_PROCEDIMIENTO."=".$LITEJECUCION_PROCEDIMIENTO.chr(13);
	$parametros.=$EJECUCION_TAREA."=".$LITEJECUCION_TAREA;
	$HTMLCriterios.='			<TR>';
	$HTMLCriterios.='				<TD>'.HTMLCTESELECT($parametros,"tipoaccion","estilodesple","",$tipoaccion,100).'</TD></TR>';
		
	// Desplegable con los distintos estados
	$HTMLCriterios.='			<TR>';
	$HTMLCriterios.='				<TH align=center>&nbsp;'.$TbMsg[4].'&nbsp;</TH></TR>';
	$HTMLCriterios.=			'<TR>';
	$parametros="0=".$TbMsg[60].chr(13);	
	$parametros.=$ACCION_INICIADA."=".$LITACCION_INICIADA.chr(13);
	$parametros.=$ACCION_DETENIDA."=".$LITACCION_DETENIDA.chr(13);
	$parametros.=$ACCION_FINALIZADA."=".$LITACCION_FINALIZADA.chr(13);;
	$parametros.=$ACCION_PROGRAMADA."=".$LITACCION_PROGRAMADA;
	$HTMLCriterios.='				<TD colspan=3>'.HTMLCTESELECT($parametros,"estado","estilodesple","",$estado,100,"chgdespleestados").'</TD></TR>';
	$HTMLCriterios.=		'</TABLE>';
	$HTMLCriterios.='	</TD>'; // Fin Desplegables de tipo de acciones y estados
	
	$HTMLCriterios.='	<TD valign=top>'; // Desplegables de resultados y porcentajes
	// Desplegable con los distintos resultados 
	$HTMLCriterios.='		<TABLE class=tabla_standar align=center border="0">';
	$HTMLCriterios.='			<TR>';
	$HTMLCriterios.='				<TH align=center>&nbsp;'.$TbMsg[3].'&nbsp;</TH></TR>';
	$HTMLCriterios.='			<TR>';

	$parametros=$ACCION_SINRESULTADO."=".$TbMsg[60].chr(13);
	$parametros.=$ACCION_EXITOSA."=".$LITACCION_EXITOSA.chr(13);
	$parametros.=$ACCION_FALLIDA."=".$LITACCION_FALLIDA;
	$HTMLCriterios.='				<TD colspan=3>'.HTMLCTESELECT($parametros,"resultado","estilodesple","",$resultado,250,"chgdespleresultados").'</TD></TR>';
	// Porcentajes
	$HTMLCriterios.='			<TR>';
	$HTMLCriterios.='				<TH>&nbsp;'.$TbMsg[5].':&nbsp;<INPUT size=1 name="porcendesde" value="'.$porcendesde.'">&nbsp;'.$TbMsg[6].':&nbsp;
														<INPUT size =1 name="porcenhasta" value="'.$porcenhasta.'"></TH></TR>';
	$HTMLCriterios.='		</TABLE>';
	$HTMLCriterios.='	</TD>'; // Fin Desplegables de resultados y porcentajes
	$HTMLCriterios.='</TR>';		
		
		
	$HTMLCriterios.='<TR>'; 
	$HTMLCriterios.='	<TD  style="BORDER-BOTTOM:#5a86b5 1px solid;" colspan=2>'; // Fechas y horas
	// Fechas
	$HTMLCriterios.='		<TABLE WIDTH=100% class=tabla_standar align=center border="0">';
	$HTMLCriterios.='			<TR>';
	$HTMLCriterios.='				<TH>&nbsp;'.$TbMsg[7].':&nbsp;</TH>';
	$HTMLCriterios.='				<TD><INPUT class="cajatexto" onclick="vertabla_calendario(this)" style="WIDTH:80" name="fechainicio" value="'.$fechainicio.'"></TD>';
	$HTMLCriterios.='				<TH align=right>&nbsp;'.$TbMsg[8].':&nbsp;&nbsp;</TH>';
	$HTMLCriterios.='				<TD align=right><INPUT class="cajatexto" onclick="vertabla_calendario(this)" style="WIDTH:80" name="fechafin" value="'.$fechafin.'"></TD>';
	$HTMLCriterios.='			</TR>';
	$HTMLCriterios.='			<TR>';
	$HTMLCriterios.='				<TH>&nbsp;'.$TbMsg[9].':&nbsp;</TH>';
	$HTMLCriterios.='				<TD><INPUT class="cajatexto" onclick="vertabla_horario(this)" style="WIDTH:80" name="horainicio" value="'.$horainicio.'"></TD>';
	$HTMLCriterios.='				<TH align=right>&nbsp;'.$TbMsg[10].':&nbsp;&nbsp;</TH>';
	$HTMLCriterios.='				<TD align=right><INPUT class="cajatexto" onclick="vertabla_horario(this)" style="WIDTH:80" name="horafin" value="'.$horafin.'"></TD>';
	$HTMLCriterios.='			</TR>';
	$HTMLCriterios.='		</TABLE>';
	$HTMLCriterios.='	</TD>';
	$HTMLCriterios.='</TR>';
	$HTMLCriterios.='</TABLE>';// Fin filtro de búsquedas

	$HTMLCriterios.='<BR>';
	//	_________________________________________________________________________
	//	
	// Tabla de checkbox para elegir visualizar detalles 
	//	_________________________________________________________________________
	
	$HTMLCriterios.='<TABLE class="tabla_busquedas" align=center border=0 cellPadding=0 cellSpacing=0>';
	$HTMLCriterios.='	<TR>';
	$HTMLCriterios.='	<TH height=15 align="center" colspan=14>&nbsp;'.$TbMsg[47].'&nbsp;</TH>';
	$HTMLCriterios.='	</TR>';
	$HTMLCriterios.='	<TR>';
	$HTMLCriterios.='		<TD align=right>'.$TbMsg[48].'</TD>';
	$HTMLCriterios.='		<TD align=center><INPUT type="checkbox" value="1" name="visuprm"';
	if($visuprm==1) $HTMLCriterios.=' checked ';
	$HTMLCriterios.='></TD>';
	$HTMLCriterios.='		<TD width="20" align=center>&nbsp;</TD>';
	$HTMLCriterios.='		<TD align=right>'.$TbMsg[49].'</TD>';
	$HTMLCriterios.='		<TD align=center><INPUT type="checkbox" value="1" name="visupro"';
	if($visupro==1) $HTMLCriterios.=' checked ';
	$HTMLCriterios.='></TD>';
	$HTMLCriterios.='		<TD width="20" align=center>&nbsp;</TD>';
	$HTMLCriterios.='		<TD align=right>'.$TbMsg[50].'</TD>';
	$HTMLCriterios.='		<TD align=center><INPUT type="checkbox" value="1" name="visucmd"';
	if($visucmd==1) $HTMLCriterios.=' checked ';
	$HTMLCriterios.='></TD>';
	$HTMLCriterios.='	</TR>';
	$HTMLCriterios.='</TABLE>';

	$HTMLCriterios.='<BR>';
		
	$HTMLCriterios.='<TABLE class="tabla_busquedas" align=center border="0">';
	$HTMLCriterios.='	<TR>';
	$HTMLCriterios.='		<TD>';	// Lupa
	$HTMLCriterios.= '			<A href="#busca"><IMG border=0 src="../images/iconos/busquedas.gif" onclick="fdatos.submit();" alt="Buscar"></A>';
	$HTMLCriterios.='		</TD>';
	$HTMLCriterios.='</TR>';
	$HTMLCriterios.='</TABLE>';

	echo $HTMLCriterios;	
	echo '</FORM>'; // Fin formulario de criterios de busquedas
	
	/* Cabeceras */
	tomaAmbito($ambito,$urlimg,$textambito);
	echo '<DIV align=center>'; // Cabecera
	echo '<span align=center class=subcabeceras><U>'.$TbMsg[11].':'.$textambito.'</U>,
				&nbsp;'.$nombreambito.'</span>&nbsp;&nbsp;<IMG src="'.$urlimg.'"></span>';
	if(!empty($sesion))
		echo '<BR><span align=center class="presentaciones">'.$TbMsg[51].'</span>&nbsp;&nbsp;
			<IMG src="../images/iconos/filtroaccion.gif">';
	?>
	<BR>
	<BR>
	<?php 
	//	_________________________________________________________________________
	//	
	// Tabla de opciones que afectan a todas las acciones mostradas 
	//	_________________________________________________________________________
	?>
	<TABLE  align=center border=0 cellPadding=2 cellSpacing=5 >
		<TR>
			<?php // Eliminar ?>
			<TD onclick="eleccion(1);">&nbsp;
				<TABLE class="filtros" >
					<TR>
						<TD><A href="#op"><IMG border=0 src="../images/iconos/eliminar.gif"></A>&nbsp;</TD>
						<TD><A style="text-decoration:none;COLOR:#999999;" href="#op">
							<span onmouseout="desresaltar(this);" onmouseover="resaltar(this);"><?php echo $TbMsg[12]?><span></A></TD>
					</TR>
				</TABLE>
			</TD>		
			
			<TD onclick="eleccion(2);">&nbsp;
				<TABLE class=filtros>
					<TR>
						<?php // Resaltar ?>
						<TD><A href="#op"><IMG border=0 src="../images/iconos/reiniciar.gif"></A>&nbsp;</TD>
						<TD><A style="text-decoration:none;COLOR:#999999;" href="#op">
								<span onmouseout="desresaltar(this);" onmouseover="resaltar(this);"><?php echo $TbMsg[13]?><span></A></TD>
					</TR>
				</TABLE>
			</TD>				

			<TD onclick="eleccion(3);">&nbsp;
				<TABLE class=filtros>
					<TR>
						<?php // Parar ?>
						<TD><A href="#op"><IMG border=0 src="../images/iconos/acDetenida.gif"></A>&nbsp;</TD>
						<TD><A style="text-decoration:none;COLOR:#999999;" href="#op">
									<span onmouseout="desresaltar(this);" onmouseover="resaltar(this);"><?php echo $TbMsg[14]?><span></A></TD>
						
					</TR>
				</TABLE>
			</TD>	
			
			<TD onclick="eleccion(4);">&nbsp;
				<TABLE class=filtros>
					<TR>
						<?php // Seguir ?>
						<TD><A href="#op"><IMG border=0 src="../images/iconos/acIniciada.gif"></A>&nbsp;</TD>
						<TD>&nbsp;<A style="text-decoration:none;COLOR:#999999;" href="#op">
							<span onmouseout="desresaltar(this);" onmouseover="resaltar(this);"><?php echo $TbMsg[15]?></span></A>&nbsp;</TD>
					</TR>
				</TABLE>
			</TD>	
			
			<TD onclick="eleccion(5);">&nbsp;
				<TABLE class=filtros>
					<TR>
						<?php // Seguir ?>
						<TD><A href="#op"><IMG border=0 src="../images/iconos/acExitosa.gif"></A>&nbsp;</TD>
						<TD>&nbsp;<A style="text-decoration:none;COLOR:#999999;" href="#op">
							<span onmouseout=desresaltar(this); onmouseover=resaltar(this)><?php echo $TbMsg[55]?></span></A>&nbsp;</TD>
					</TR>
				</TABLE>
			</TD>
			<TD onclick="eleccion(6);">&nbsp;
				<TABLE class=filtros>
					<TR>
						<?php // Seguir ?>
						<TD><A href="#op"><IMG border=0 src="../images/iconos/acFallida.gif"></A>&nbsp;</TD>
						<TD>&nbsp;<A style="text-decoration:none;COLOR:#999999;" href="#op">
							<span onmouseout=desresaltar(this); onmouseover=resaltar(this)><?php echo $TbMsg[56]?></span></A>&nbsp;</TD>
					</TR>
				</TABLE>
			</TD>			
		</TR>
	</TABLE>
	<?php
	//	_________________________________________________________________________
	//	
	// Tabla de registros de acciones 
	//	_________________________________________________________________________
	?>	
	<TABLE  border=0 class="tabla_listados" cellspacing=1 cellpadding=0 >
	<TBODY id="tbAcciones">
	<?php
		cabeceraAcciones();
		listaAcciones($ambito,$idambito);

	?>
	</TBODY> 
	</TABLE>
	</DIV>		
	<FORM name="facciones">
		<INPUT type="hidden" name=acciones value="<?php echo $acciones?>">
		<INPUT type="hidden" name=localaccion value="">
		<INPUT type="hidden" name="sesion" value="<?php echo $sesion?>">		
	</FORM>
	<?php
		$flotante=new MenuContextual(); // Crea objeto MenuContextual
		$XMLcontextual=ContextualXMLAcciones(); // Crea contextual de las acciones
		echo $flotante->CreaMenuContextual($XMLcontextual); 

		$XMLcontextual=ContextualXMLNotificaciones(); // Crea contextual de las acciones
		echo $flotante->CreaMenuContextual($XMLcontextual); 
	?>
</BODY>
</HTML>
<?php
/********************************************************************/
//	Escribe la cabecera de los registros de acciones
//	_________________________________________________________________________

function cabeceraAcciones()
{
	global $TbMsg;
	
	$html="";
	$html.='<TR height=20>';
	$html.='<TH colspan=2>&nbsp;</TH>';	
	$html.='<TH>&nbsp;R&nbsp;</TH>';		
	$html.='<TH>&nbsp;'.$TbMsg[19].'&nbsp;</TH>';	
	$html.='<TH>&nbsp;'.$TbMsg[20].'&nbsp;</TH>';
	$html.='<TH>&nbsp;'.$TbMsg[21].'&nbsp;</TH>';
	$html.='<TH>&nbsp;'.$TbMsg[22].'&nbsp;</TH>';
	$html.='<TH>&nbsp;</TH>';
	$html.='<TH>&nbsp;'.$TbMsg[23].'&nbsp;</TH>';
	$html.='<TH>&nbsp;'.$TbMsg[57].'&nbsp;</TH>';
	$html.='<TH>&nbsp;S&nbsp;</TH>';
	$html.='<TH>&nbsp;%&nbsp;</TH>';				
	$html.='</TR>';
	echo $html;
}
//	_________________________________________________________________________

function listaAcciones($ambito,$idambito)
{
	global $cmd;
	global $ClausulaWhere;
	global $cadenaid;
	global $cadenaip;
	global $cadenamac;	
	global $EJECUCION_COMANDO; 	
	global $EJECUCION_PROCEDIMIENTO; 	
	global $EJECUCION_TAREA; 	
	global $ACCION_PROGRAMADA; 	
	global $acciones;
	global $sesion;
	global $estado;
	
	$cadenaid="";
	$cadenaip="";
	$cadenamac="";
	RecopilaIpesMacs($cmd,$ambito,$idambito); // Recopila Ipes del ámbito		
 	$cadenasesion="(SELECT DISTINCT sesion FROM acciones WHERE idordenador NOT IN (".$cadenaid."))";
 
 	$cmd->texto="SELECT acciones.*, comandos.descripcion AS comando, acciones.parametros,
			    comandos.visuparametros, ordenadores.nombreordenador,
			    procedimientos.descripcion AS procedimiento,
			    tareas.descripcion AS tarea, programaciones.sesion AS sesionprog
			FROM acciones
 			INNER JOIN comandos ON comandos.idcomando=acciones.idcomando
 			INNER JOIN ordenadores ON ordenadores.idordenador=acciones.idordenador
 			LEFT OUTER JOIN procedimientos ON procedimientos.idprocedimiento=acciones.idprocedimiento
 			LEFT OUTER JOIN tareas ON tareas.idtarea=acciones.idtarea
 			LEFT OUTER JOIN programaciones ON programaciones.sesion=acciones.sesion";
	if(!empty($sesion)) // Filtro por acción
		$cmd->texto.=" WHERE acciones.sesion =".$sesion;
	else
		$cmd->texto.=" WHERE acciones.sesion NOT IN (".$cadenasesion.")";
		
	if($estado==$ACCION_PROGRAMADA)
		$cmd->texto.=" AND (acciones.idprogramacion=0 AND programaciones.sesion>0)"; // Comando programado
	else{
		if(!empty($ClausulaWhere)) 
			$cmd->texto.=" AND (".$ClausulaWhere.")";
	}	
	$cmd->texto.=" ORDER BY acciones.idaccion DESC, acciones.sesion DESC";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset

	$acciones=""; 	// Variable que recogerá las acciones que cumplan los criterios
			// con formato "ambito,idambito" concadenando con ";" a otro identificador
			// Esta variable se usara para las operaciones globales de Eliminar, etc...

	// Recorre acciones
	$html="";
	while (!$rs->EOF){
		switch($rs->campos["tipoaccion"]){
			case $EJECUCION_COMANDO:
					$html.=listaComado($rs,$rs->campos["sesion"]);
					break;
			case $EJECUCION_PROCEDIMIENTO:
					$html.=listaProcedimiento($rs,$rs->campos["sesion"]);	
					break;							
			case $EJECUCION_TAREA:
					$html.=listaTarea($rs,$rs->campos["sesion"]);	
					break;					
		}	
	}
	echo $html;
}
//	_________________________________________________________________________

function listaTarea($rs,$sesion)
{
	global $acciones;
	
	$oA=new clsAccion; // Crea objeto acción para procesar comandos
	iniAccion($rs,$oA);	
	$html=recorreTarea($rs,$sesion,$oA);	
	if(cumpleCriterios($oA)){
		$acciones.=$oA->tipoaccion.",".$oA->idtipoaccion.",".$oA->sesion.",0;";	// Concadena identificador 
		$html=cabeceraSesion($oA).$html; // Escribe la cabecera del comando
	}
	else
		$html=""; // No cumple con los criterios	
	return($html);		
}		
//	_________________________________________________________________________

function recorreTarea($rs,$sesion,$oA)
{
	$html="";
	do{
		$html.=listaProcedimiento($rs,$sesion,$rs->campos["idtarea"],$oA);
	}while(!$rs->EOF
			&& $rs->campos["sesion"]==$sesion);
	return($html);	
}
//	_________________________________________________________________________

function listaProcedimiento($rs,$sesion,$idtarea=0,$oA=null)
{
	global $acciones;
	
	if($oA!=null){ // Si la función es invocada por una tarea ...
		$html=recorreProcedimiento($rs,$sesion,$idtarea,$oA);
	}
	else{
		$oA=new clsAccion; // Crea objeto acción para procesar comandos
		iniAccion($rs,$oA);	
		$html=recorreProcedimiento($rs,$sesion,$idtarea,$oA);	
		if(cumpleCriterios($oA)){
			$acciones.=$oA->tipoaccion.",".$oA->idtipoaccion.",".$oA->sesion.",0;";	// Concadena identificador 
			if($rs->campos["sesion"]!=$sesion 
				|| $rs->campos["idtarea"]!=$idtarea 
				|| $rs->EOF)
				$html=cabeceraSesion($oA).$html; // Escribe la cabecera del procedimiento
		}
		else
			$html=""; // No cumple con los criterios			
	}
	return($html);		
}		
//	_________________________________________________________________________

function recorreProcedimiento($rs,$sesion,$idtarea,$oA)
{
	$html="";
	do{
		$html.=listaComado($rs,$sesion,$idtarea,$rs->campos["idprocedimiento"],$oA);
	}while(!$rs->EOF
			&& $rs->campos["sesion"]==$sesion
			&& $rs->campos["idtarea"]==$idtarea);
	return($html);	
}
//	_________________________________________________________________________

function listaComado($rs,$sesion,$idtarea=0,$idprocedimiento=0,$oA=null)
{
	global $acciones;
	global $visupro;
	
	if($oA!=null){ // Si la función es invocada por un procedimiento...
		$html=recorreComando($rs,$sesion,$idtarea,$idprocedimiento,$oA);
	}
	else{
		$oA=new clsAccion; // Crea objeto acción para procesar comandos
		iniAccion($rs,$oA);	
		$html=recorreComando($rs,$sesion,$idtarea,$idprocedimiento,$oA);
	
		$acciones.=$oA->tipoaccion.",".$oA->idtipoaccion.",".$oA->sesion.",0;";	// Concadena identificador 
		if($rs->campos["sesion"]!=$sesion 
			|| $rs->campos["idtarea"]!=$idtarea 
			|| $rs->campos["idprocedimiento"]!=$idprocedimiento 
			|| $rs->EOF)
			if($oA->linot>0)				
				$html=cabeceraSesion($oA).$html; // Escribe la cabecera del comando
	}
	return($html);
}
//	_________________________________________________________________________

function recorreComando($rs,$sesion,$idtarea,$idprocedimiento,$oA)
{
	$html="";
	do{
		$html.=listaNotificacion($rs,$sesion,$idtarea,$idprocedimiento,$rs->campos["idcomando"],$oA);
	}while(!$rs->EOF
			&& $rs->campos["sesion"]==$sesion
			&& $rs->campos["idtarea"]==$idtarea
			&& $rs->campos["idprocedimiento"]==$idprocedimiento);
	return($html);	
}
//	_________________________________________________________________________
//	
//	Recorre todas las notificaciones de un mismo comando registrando
//  los datos que servirán para resumen de la Acción
//	_________________________________________________________________________

function listaNotificacion($rs,$sesion,$idtarea,$idprocedimiento,$idcomando,$oA)
{	
	global $ACCION_EXITOSA; 
	global $ACCION_FALLIDA; 
	global $ACCION_SINRESULTADO; 

	global $ACCION_DETENIDA;
	global $ACCION_INICIADA;
	global $ACCION_FINALIZADA;
			
	global $EJECUCION_TAREA; 
	global $visuprm; 
	global $visucmd; 
	global $visupro; 

	$html="";
	
	if($visupro==1)
		$html.=cambiaAmbito($rs,$oA); // Escribe cambio de ámbito	
		
	if($visuprm==1)
		$html.=escribeParametros($rs->campos["comando"],$rs->campos["parametros"],$rs->campos["visuparametros"],$oA);
	
	

	do{
		if(cumpleCriteriosNot($rs,$oA)){
			if($visucmd==1){
				$html.=escribeNotificacion($rs,$oA);
				$oA->linot++; // Contador de lineas de notificaciones escritas cumpliendo criterios
			}				
		}
		/* Fechas y horas */
		$fechahorareg=strtotime($rs->campos["fechahorareg"]);
		if($fechahorareg>0)
			if($oA->fechahorareg>$fechahorareg) $oA->fechahorareg=$fechahorareg;
		$fechahorafin=strtotime($rs->campos["fechahorafin"]);
		if($fechahorafin>0)
			if($oA->fechahorafin<$fechahorafin) $oA->fechahorafin=$fechahorafin;

		$oA->notif++; // Contador de notificaciones en el comando
		switch($rs->campos["estado"]){
			case $ACCION_INICIADA:
				$oA->notini++; // Incrementa contador de comandos con estado de finalizado
				break;
			case $ACCION_DETENIDA:
				$oA->notdet++; // Incrementa contador de comandos con estado de finalizado
				break;
			case $ACCION_FINALIZADA:
				$oA->noter++; // Incrementa contador de comandos con estado de finalizado
				break;				
		}
		/* Cuestión resultados */
		/* Si existe al menos una notificación de error, la acción tiene ya resultado de error */
		if($rs->campos["resultado"]==$ACCION_FALLIDA){
			$oA->resultado=$ACCION_FALLIDA;
		}
		/* Si existe aún alguna notificación pendiente, la acción no tiene resultado global */
		if($rs->campos["resultado"]==$ACCION_SINRESULTADO){
			if($oA->resultado==$ACCION_EXITOSA)		
				$oA->resultado=$ACCION_SINRESULTADO;
		} 		
		$rs->Siguiente();	
		
	}while(!$rs->EOF 
			&& $rs->campos["sesion"]==$sesion
			&& $rs->campos["idtarea"]==$idtarea
			&& $rs->campos["idprocedimiento"]==$idprocedimiento
			&& $rs->campos["idcomando"]==$idcomando);
	
	if($oA->notif>0)
		$oA->porcen=floor($oA->noter*100/$oA->notif); // Calcula porcentaje de finalización

	if($oA->notif==$oA->noter)
		$oA->estado=$ACCION_FINALIZADA;  // Todas las acciones finalizadas
	else{
		if($oA->notif==$oA->notdet) 
			$oA->estado=$ACCION_DETENIDA;  // Todas las acciones detenidas
		else
			$oA->estado=$ACCION_INICIADA; 
	}
	if(cumpleCriterios($oA)){
		if($rs->campos["sesion"]!=$sesion && !$rs->EOF ) // Separación entre sesiones distintas
			$html.='<TR id="'.$oA->sesion.'" value="A"><TD colspan=12 style="BACKGROUND-COLOR:white;BORDER-BOTTOM:#999999 1px solid;">&nbsp;</TD></TR>';
	}
	return($html);
}
//	_________________________________________________________________________

function escribeNotificacion($rs,$oA)
{
		global $ACCION_EXITOSA; 
		global $ACCION_FALLIDA; 
		global $ACCION_SINRESULTADO; 

		global $ACCION_DETENIDA;
		global $ACCION_INICIADA;
		global $ACCION_FINALIZADA;
		
		global $TbMsg;
		global $visupro;
		global $visuprm;
		
		$html="";	
		$html.='<TR id="'.$oA->sesion.'" value="D">';
		if($visupro==0 )
			$html.='<TD align=right colspan=2>'.$rs->campos["comando"].'&nbsp;</TD>';
		else
			$html.='<TD align=right colspan=2>&nbsp;</TD>';
			
		/* Resultado */
		switch($rs->campos["resultado"]){
			case $ACCION_EXITOSA:
				$html.='<TD align=center><IMG value="'.$ACCION_EXITOSA.'" src="../images/iconos/acExitosa.gif" width=16 height=16></TD>';
				break;
			case $ACCION_FALLIDA:
				$html.='<TD align=center><IMG value="'.$ACCION_FALLIDA.'" src="../images/iconos/acFallida.gif" width=16 height=16></TD>';
				break;
			case $ACCION_SINRESULTADO:
				$html.='<TD align=center><IMG value="'.$ACCION_SINRESULTADO.'" src="../images/iconos/nada.gif" width=16 height=16></TD>';
				break;
		}	
		if($oA->swcp){ // Comando programado
			$html.='<TD align=center>&nbsp;</TD>';
			$html.='<TD align=center>&nbsp;</TD>';
			$html.='<TD align=center>&nbsp;</TD>';
			$html.='<TD align=center>&nbsp;</TD>';
		}
		else{
			/* Fechas y horas */
			list($fecha,$hora)=explode(" ",substr($rs->campos["fechahorafin"],0));
			if ($fecha=="1970-01-01") $hora="";
			$html.='<TD align=center>&nbsp;'.InvFecha($fecha).'&nbsp;</TD>';
			$html.='<TD align=center>&nbsp;'.$hora.'&nbsp;</TD>';
				
			list($fecha,$hora)=explode(" ",substr($rs->campos["fechahorareg"],0));
			if ($fecha=="1970-01-01") $hora="";
			$html.='<TD align=center>&nbsp;'.InvFecha($fecha).'&nbsp;</TD>';
			$html.='<TD align=center>&nbsp;'.$hora.'&nbsp;</TD>';
		}	
			
		/* Ámbito de aplicación */
		$urlimg='../images/iconos/ordenador.gif';
		$accion=$oA->tipoaccion.",".$oA->idtipoaccion.",".$oA->sesion.",".$rs->campos["idaccion"].";"; // Tripla clave	
		$oncontxt="document.facciones.localaccion.value='".$accion."';";
		$oncontxt.="menu_contextual(null,'flo_notificaciones');";
		
		$html.='<TD id="'.$rs->campos["idaccion"].'" align=center><A href="#cmd"><IMG border=0 src="'.$urlimg.'" 
				oncontextmenu="'.$oncontxt.'" ></A></TD>';
		$html.='<TD align=left>&nbsp;'.$rs->campos["nombreordenador"].'&nbsp;</TD>';	
		
		/* Descripción de la notificación (Descripción del error si se ha producido alguno) */	
		$html.='<TD>&nbsp;'.$rs->campos["descrinotificacion"].'&nbsp;</TD>';
			
		/* Estado */
		if($oA->swcp) // Comando programado
				$html.='<TD align=center><IMG value="'.$ACCION_DETENIDA.'" 
				src="../images/iconos/reloj.gif" width=16 height=16 style="cursor:pointer" 
				onclick="programacion('.$rs->campos["idtipoaccion"].','.$rs->campos["sesion"].',\''.$rs->campos["comando"].'\')"></TD>';
		else{
			switch($rs->campos["estado"]){
				case $ACCION_DETENIDA:
					$html.='<TD align=center><IMG value="'.$ACCION_DETENIDA.'" src="../images/iconos/acDetenida.gif" width=16 height=16></TD>';
					break;
				case $ACCION_INICIADA:
					$html.='<TD align=center><IMG value="'.$ACCION_INICIADA.'" src="../images/iconos/acIniciada.gif" width=16 height=16></TD>';
					break;
				case $ACCION_FINALIZADA:
					$html.='<TD align=center><IMG value="'.$ACCION_FINALIZADA.'" src="../images/iconos/acFinalizada.gif" width=16 height=16></TD>';
					break;
			}
		}	

		/* Porcentaje */
		$html.='<TD align=center>&nbsp;</TD>';		
		$html.='</TR>';	
		return($html);
}	
//	_________________________________________________________________________
//	
//	Inicializa la clase acción
//
//	Parámetros:
//		oA: Objeto acción a inicializar
//	_________________________________________________________________________

function iniAccion($rs,$oA)
{
	global $ACCION_EXITOSA; 
	global $ACCION_DETENIDA; 
	
	$oA->ambito=$rs->campos["ambito"];
	$oA->idambito=$rs->campos["idambito"];
	$oA->tipoaccion=$rs->campos["tipoaccion"];
	$oA->idtipoaccion=$rs->campos["idtipoaccion"];
	$oA->descriaccion=$rs->campos["descriaccion"];
	$oA->sesion=$rs->campos["sesion"];
	$oA->fechahorareg=strtotime($rs->campos["fechahorareg"]);
	$oA->fechahorafin=0;
	$oA->estado=$ACCION_DETENIDA; 
	$oA->resultado=$ACCION_EXITOSA;
	$oA->notif=$oA->noter=$oA->notini=$oA->notdet=$oA->linot=$oA->porcen=0;
	if(empty($rs->campos["idprogramacion"]) && !empty($rs->campos["sesionprog"])) // switch de Comando programado
		$oA->swcp=true;
	else
		$oA->swcp=false; 
}
//	_________________________________________________________________________

function cabeceraSesion($oA)
{
	global $EJECUCION_COMANDO;
	global $EJECUCION_PROCEDIMIENTO;
	global $EJECUCION_TAREA;

	$html="";		
	$html.='<TR id="'.$oA->sesion.'" value="C">';
	
	$accion=$oA->tipoaccion.",".$oA->idtipoaccion.",".$oA->sesion.",0;"; // Tripla clave	
	$oncontxt="document.facciones.sesion.value='".$oA->sesion."';";
	$oncontxt.="document.facciones.localaccion.value='".$accion."';menu_contextual(null,'flo_acciones');";
	switch($oA->tipoaccion){
		case $EJECUCION_COMANDO:
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center><IMG style="cursor:pointer" border=0
					oncontextmenu="'.$oncontxt.'" 
					src="../images/iconos/comandos.gif"></TD>';
			break;
		case $EJECUCION_PROCEDIMIENTO:
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center><IMG style="cursor:pointer" border=0 
					oncontextmenu="'.$oncontxt.'" 
					src="../images/iconos/procedimiento.gif"></TD>';
			break;				
		case $EJECUCION_TAREA:
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center><IMG style="cursor:pointer" border=0  
					oncontextmenu="'.$oncontxt.'" 
					src="../images/iconos/tareas.gif"></TD>';
			break;					
	}
	/* Cabeceras */
	$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=left><b>'.$oA->descriaccion.'</b></TD>';
	$html.=escribeResumen($oA);
	$html.='</TR>';

	return($html);
}
//	_________________________________________________________________________

function escribeResumen($oA)
{
		global $cmd;
		global $TbMsg;
		global $ACCION_EXITOSA; 
		global $ACCION_FALLIDA; 
		global $ACCION_SINRESULTADO;
		global $ACCION_DETENIDA;
		global $EJECUCION_TAREA;
			
		$html="";		
		
		if($oA->swcp){ // Comando programado
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center>&nbsp;</TD>';
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center>&nbsp;</TD>';
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center>&nbsp;</TD>';
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center>&nbsp;</TD>';
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center>&nbsp;</TD>';
			/* Ámbito de aplicación */
			tomaAmbito($oA->ambito,$urlimg,$textambito);
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center><IMG src="'.$urlimg.'"></TD>';
			tomaDescriAmbito($cmd,$oA->ambito,$oA->idambito,$textambito);
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=left>&nbsp;'.$textambito.'&nbsp;</TD>';	
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center>&nbsp;</TD>';
			
				$html.='<TH align=center><IMG value="'.$ACCION_DETENIDA.'" 
				src="../images/iconos/reloj.gif" width=16 height=16 style="cursor:pointer" 
				onclick="programacion('.$oA->idtipoaccion.','.$oA->sesion.',\''.$oA->descriaccion.'\')"></TH>';
				
			
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center>&nbsp;</TD>';
			return($html);
		}
		
		/* Resultado */
		switch($oA->resultado){
			case $ACCION_EXITOSA:
				$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center><IMG value="'.$ACCION_EXITOSA.'" src="../images/iconos/acExitosa.gif" width=16 height=16></TD>';
				break;
			case $ACCION_FALLIDA:
				$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center><IMG value="'.$ACCION_FALLIDA.'" src="../images/iconos/acFallida.gif" width=16 height=16></TD>';
				break;
			case $ACCION_SINRESULTADO:
				$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center><IMG value="'.$ACCION_SINRESULTADO.'" src="../images/iconos/nada.gif" width=16 height=16></TD>';
		}	
		/* Fechas y horas */
		if($oA->porcen==100){ // Si está acabada la acción
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center>&nbsp;'.strftime("%d-%m-%Y",$oA->fechahorafin).'&nbsp;</TD>';
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center>&nbsp;'.strftime("%H:%M:%S",$oA->fechahorafin).'&nbsp;</TD>';
		}
		else
		{
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center>&nbsp;</TD>';
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center>&nbsp;</TD>';
		}

		$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center>&nbsp;'.strftime("%d-%m-%Y",$oA->fechahorareg).'&nbsp;</TD>';
		$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center>&nbsp;'.strftime("%H:%M:%S",$oA->fechahorareg).'&nbsp;</TD>';

		if($oA->tipoaccion==$EJECUCION_TAREA){
			$html.='<TD  style="BACKGROUND-COLOR: #b5daad" align=left>&nbsp;</TD>';
			$html.='<TD  style="BACKGROUND-COLOR: #b5daad" align=left>&nbsp;</TD>';
		}
		else{
			/* Ámbito de aplicación */
			tomaAmbito($oA->ambito,$urlimg,$textambito);
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center><IMG src="'.$urlimg.'"></TD>';
			tomaDescriAmbito($cmd,$oA->ambito,$oA->idambito,$textambito);
			$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=left>&nbsp;'.$textambito.'&nbsp;</TD>';	
		}	
		
		/* Descripción de la notificación (Descripción del error si se ha producido alguno) */	
		$html.='<TD style="BACKGROUND-COLOR: #b5daad" >&nbsp;'.@$rs->campos["descrinotificacion"].'&nbsp;</TD>';

		/* Estado */
		$html.='<TD style="BACKGROUND-COLOR: #b5daad"  align=center>&nbsp;</TD>';
		
		/* Porcentaje */
		$html.='<TD style="BACKGROUND-COLOR: #b5daad" align=center>'.$oA->porcen.'%</TD>';
		
	
		return($html);
}
//	_________________________________________________________________________

function cambiaAmbito($rs,$oA)
{
	global $cmd;
	global $TbMsg;
	
	$bgcolor="#f6c8f5";
	$html="";
	$html.='<TR id="'.$oA->sesion.'" value="A">';
	$procedimiento=TomaDato($cmd,0,'procedimientos',$rs->campos["idprocedimiento"],'idprocedimiento','descripcion');
	$html.='<TD colspan=2 align="right" style="BACKGROUND-COLOR:'.$bgcolor.'">'.$rs->campos["comando"].'&nbsp;</TD>';
	$html.='<TD style="BACKGROUND-COLOR:'.$bgcolor.'"><IMG style="cursor:pointer" src="../images/iconos/nada.gif">&nbsp;</TD>';
	$html.='<TD colspan=4 style="BACKGROUND-COLOR:'.$bgcolor.'" align=right>'.$procedimiento.'&nbsp;</TD>';
	
	/* Ámbito de aplicación */
	tomaAmbito($rs->campos["ambito"],$urlimg,$textambito);
	$html.='<TD style="BACKGROUND-COLOR:'.$bgcolor.'" align=center><IMG src="'.$urlimg.'"></TD>';
	tomaDescriAmbito($cmd,$rs->campos["ambito"],$rs->campos["idambito"],$textambito);
	$html.='<TD style="BACKGROUND-COLOR:'.$bgcolor.'" align=left>&nbsp;'.$textambito.'&nbsp;</TD>';	
	$html.='<TD colspan=3 style="BACKGROUND-COLOR:'.$bgcolor.'" align=center>&nbsp;</TD>';
	$html.='</TR>';
	return($html);
}
//	_________________________________________________________________________
//
//	Comprueba si se cumplen los criterios para visualizar la acción
//	_________________________________________________________________________

function cumpleCriterios($oA)
{
	global $porcendesde;
	global $porcenhasta;
	global $estado;
	global $resultado;
	global $ACCION_PROGRAMADA;

	if($estado==$ACCION_PROGRAMADA){	
		if(!$oA->swcp)
			return(false); // Comandos programados
		else
			return(true);
	}
	if($oA->swcp){
		if($estado!=$ACCION_PROGRAMADA && $estado>0)	
			return(false); // Comandos programados
		else
			return(true);
	}

	// Cuestion estados
	if(!empty($estado))
		if($oA->estado!=$estado) return(false);

	if(!empty($resultado))		
		if($oA->resultado!=$resultado) return(false);
	
	if($oA->porcen<$porcendesde || $oA->porcen>$porcenhasta) return(false);
	return(true);
}
//	_________________________________________________________________________
//
//	Comprueba si se cumplen los criterios para visualizar la notificación
//	_________________________________________________________________________
function cumpleCriteriosNot($rs,$oA)
{
	global $porcendesde;
	global $porcenhasta;
	global $estado;
	global $resultado;
	global $ACCION_PROGRAMADA;

	if($estado==$ACCION_PROGRAMADA){	
		if(!$oA->swcp)
			return(false); // Comandos programados
		else
			return(true);
	}
	if($oA->swcp){
		if($estado!=$ACCION_PROGRAMADA && $estado>0)	
			return(false); // Comandos programados
		else
			return(true);
	}
	// Cuestion estados
	if(!empty($estado))
		if($rs->campos["estado"]!=$estado) return(false);

	if(!empty($resultado))		
		if($rs->campos["resultado"]!=$resultado) return(false);

	return(true);
}
//	_________________________________________________________________________
//
//	Clase para procesar las acciones
//	_________________________________________________________________________

class clsAccion
{
	var $ambito;
	var $idambito;
	var $tipoaccion;
	var $idtipoaccion;	
	var $descriaccion;
	var $sesion;
	var $fechahorafin;
	var $fechahorareg;
	var $estado;
	var $resultado;
	var $porcen;
	var $notif; 
	var $noter;
	var $notdet;
	var $notini;
	var $linot;
	var $swcp;
	function __construct(){  // Constructor

	}
}
//________________________________________________________________________________________________________

function escribeParametros($comando,$parametros,$visuparametros,$oA)
{	
	global $cmd;
	global $visupro;
	global $visupro;
	global $visucmd;

	$html="";
	$tbParametrosValor=array();
	ParametrosValor($cmd,$parametros,$tbParametrosValor); // Toma valores de cada parámetro
	$vprm=explode(";",$visuparametros);

	if($visupro==1 || ($visupro=0 && $visucmd==0)) $comando="&nbsp;"; // No se muestra el nombre del comando
	for($i=0;$i<sizeof($vprm);$i++){
		$nemo=$vprm[$i]; // Para cada parámetro visualizable ...
		if(isset($tbParametrosValor[$nemo])){
			for($j=0;$j<sizeof($tbParametrosValor[$nemo])-1;$j++){
				$descripcion=$tbParametrosValor[$nemo]["descripcion"];
				if(sizeof($tbParametrosValor[$nemo])>2)
					$valor=$tbParametrosValor[$nemo][$j]["valor"];
				else
					$valor=$tbParametrosValor[$nemo]["valor"];
				$html.=escribiendoParametros($comando,$descripcion,$valor,$oA);
			}	
		}	
	}
	if(empty($visuparametros)){ // Sin parametros
		$bgcolor="#cedcec";
		$html.='<TR id="'.$oA->sesion.'" value="A">';
		$html.='<TD align=right style="BACKGROUND-COLOR: '.$bgcolor.';" colspan=2>'.$comando.'</TD>';
		$html.='<TD style="BACKGROUND-COLOR: '.$bgcolor.';">&nbsp;</TD>';
		$html.='<TD style="BACKGROUND-COLOR: '.$bgcolor.';" colspan=9>&nbsp;</TD>';
		$html.='</TR>';	
	}
	return($html);
}
//________________________________________________________________________________________________________

function escribiendoParametros($comando,$descripcion,$valor,$oA)
{
	$sw=true;
	$html="";
	
	$bgcolor="#cedcec";
	$html.='<TR id="'.$oA->sesion.'" value="A">';
	if($sw){
		$html.='<TD align=right style="BACKGROUND-COLOR: '.$bgcolor.';" colspan=2>'.$comando.'&nbsp;&nbsp;&nbsp;</TD>';
		$sw=false;
	}
	else
		$html.='<TD style="BACKGROUND-COLOR: '.$bgcolor.';" colspan=2>&nbsp;</TD>';
	$html.='<TD style="BACKGROUND-COLOR: '.$bgcolor.';">&nbsp;</TD>';
	$html.='<TD style="BACKGROUND-COLOR: '.$bgcolor.';" colspan=8><b>'.$descripcion.'</b>: 
	'.$valor.'</TD>';
	$html.='<TD style="BACKGROUND-COLOR: '.$bgcolor.';" colspan=1 >&nbsp;</TD>';
	$html.='</TR>';	
	return($html);	
}
//________________________________________________________________________________________________________

function escribeCheck()
{
	echo'
	<TABLE class="tabla_busquedas" align=center border=0 cellPadding=0 cellSpacing=0>
		<TR>
		TH height=15 align="center" colspan=14><?php echo $TbMsg[18]?></TH>
		</TR>
		<TR>
			<TD align=right><?php echo $TbMsg[30]?></TD>
			<TD align=center><INPUT type="checkbox" checked></TD>
			<TD width="20" align=center>&nbsp;</TD>
		</TR>
	</TABLE>';	
}
//________________________________________________________________________________________________________

function ContextualXMLAcciones()
{
	global $TbMsg;
	global $sesion;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_acciones"';
	$layerXML.=' maxanchu=140';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';
	
	$layerXML.='<ITEM';
	if(empty($sesion)){
		$layerXML.=' alpulsar="filtroAccion(1)"';
		$layerXML.=' imgitem="../images/iconos/filtroaccion.gif"';
		$layerXML.=' textoitem='.$TbMsg[41];
		}
	else{
		$layerXML.=' alpulsar="filtroAccion(0)"';
		$layerXML.=' imgitem="../images/iconos/filtro_off.gif"';
		$layerXML.=' textoitem='.$TbMsg[43];		
	}	
		
	$layerXML.=' textoitem='.$TbMsg[41];
	$layerXML.='></ITEM>';
	
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	$layerXML.=ContextualXMLComun();
	return($layerXML);	
}	
//________________________________________________________________________________________________________

function ContextualXMLNotificaciones()
{	

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_notificaciones"';
	$layerXML.=' maxanchu=140';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';
	
	$layerXML.=ContextualXMLComun();
	return($layerXML);	
}	
//________________________________________________________________________________________________________

function ContextualXMLComun()
{	
	global $TbMsg;
	global $idcmdtskwrk;
	global $codtipoaccion;
	global $accionid;
	global $EJECUCION_TAREA;
	global $sesion;
	
	$layerXML ='<ITEM';
	$layerXML.=' alpulsar="eleccion(1,document.facciones.localaccion)"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[46];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eleccion(2,document.facciones.localaccion)"';
	$layerXML.=' imgitem="../images/iconos/reiniciar.gif"';
	$layerXML.=' textoitem='.$TbMsg[45];
	$layerXML.='></ITEM>';
	
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eleccion(3,document.facciones.localaccion)"';
	$layerXML.=' imgitem="../images/iconos/acDetenida.gif"';
	$layerXML.=' textoitem='.$TbMsg[14];
	$layerXML.='></ITEM>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eleccion(4,document.facciones.localaccion)"';
	$layerXML.=' imgitem="../images/iconos/acIniciada.gif"';
	$layerXML.=' textoitem='.$TbMsg[15];
	$layerXML.='></ITEM>';	

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';	
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eleccion(5,document.facciones.localaccion)"';
	$layerXML.=' imgitem="../images/iconos/acExitosa.gif"';
	$layerXML.=' textoitem="'.$TbMsg[55].'"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eleccion(6,document.facciones.localaccion)"';
	$layerXML.=' imgitem="../images/iconos/acFallida.gif"';
	$layerXML.=' textoitem="'.$TbMsg[56].'"';
	$layerXML.='></ITEM>';	
	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}

