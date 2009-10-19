<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Copyright 200-2005 José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero: programaciones.php
// Descripción :
//		Gestiona la programación de tareas , trabajos y reservas
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/clases/Almanaque_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/programaciones_".$idioma.".php");
//________________________________________________________________________________________________________
$idtarea=0; 
$idtrabajo=0; 
$idreserva=0; 
$descripciontarea=""; 
$descripciontrabajo=""; 
$descripcionreserva=""; 
$tipoaccion=""; 
$identificador=0; 

if (isset($_GET["idtarea"])) $idtarea=$_GET["idtarea"]; // Recoge parametros
if (isset($_GET["idtrabajo"])) $idtrabajo=$_GET["idtrabajo"]; // Recoge parametros
if (isset($_GET["idreserva"])) $idreserva=$_GET["idreserva"]; // Recoge parametros
if (isset($_GET["descripciontrabajo"])) $descripciontrabajo=$_GET["descripciontrabajo"]; // Recoge parametros
if (isset($_GET["descripciontarea"])) $descripciontarea=$_GET["descripciontarea"]; // Recoge parametros
if (isset($_GET["descripcionreserva"])) $descripcionreserva=$_GET["descripcionreserva"]; // Recoge parametros
if (isset($_GET["tipoaccion"])) $tipoaccion=$_GET["tipoaccion"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
$rs=new Recordset; // Recupero acciones anidadas
$rs->Comando=&$cmd; 
switch($tipoaccion){
	case $EJECUCION_TAREA :
	$textoaccion=$LITEJECUCION_TAREA;
	$urlimg='../images/iconos/tareas.gif';
	$identificador=$idtarea;
	$descripcion=$descripciontarea;
	$cmd->texto="SELECT * FROM programaciones WHERE identificador=".$identificador." AND tipoaccion=".$EJECUCION_TAREA;
	break;
case $EJECUCION_TRABAJO :
	$textoaccion=$LITEJECUCION_TRABAJO;
	$urlimg='../images/iconos/trabajos.gif';
	$identificador=$idtrabajo;
	$descripcion=$descripciontrabajo;
	$cmd->texto="SELECT * FROM programaciones WHERE identificador=".$identificador." AND tipoaccion=".$EJECUCION_TRABAJO;
	break;
case $EJECUCION_RESERVA :
	$textoaccion=$LITEJECUCION_RESERVA;
	$urlimg='../images/iconos/reservas.gif';
	$identificador=$idreserva;
	$descripcion=$descripcionreserva;
	$cmd->texto="SELECT * FROM programaciones WHERE identificador=".$identificador." AND tipoaccion=".$EJECUCION_RESERVA;
	break;
}
$numreg=0;
if (!$rs->Abrir()){
	$numreg=0;
	$wsw_sus="";
}
else{
	$numreg=$rs->numeroderegistros;
	if($rs->campos["suspendida"]==1)
		$wsw_sus="checked";
	else
		$wsw_sus="";
}
$mialmanaque= new Almanaque("tabla_meses");
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/programaciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/programaciones_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<P align=center class=cabeceras><?echo $TbMsg[0]?> <?=$textoaccion?>&nbsp;<img src="../images/iconos/reloj.gif"><br>
<IMG src=<?=$urlimg?>>&nbsp;<SPAN align=center class=subcabeceras><?=$descripcion?></SPAN>&nbsp;</p>
<FORM name="fprogramaciones" method="post">
<INPUT type=hidden name=tipoaccion value="<? echo $tipoaccion?>">
<CENTER>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!-- Tabla Tabla con los tiempos y los bloques de programación  -->
<TABLE align=center border=0 cellPadding=0 cellSpacing=0 width=100%>
	<TR>
		<TD>
			<!--  Tabla con los años dias y semanas  -->
			<TABLE  border=0 cellPadding=0 cellSpacing=0 width="100%"  style="height: 27px">
				  <TR>
					<TD valign=top>
					<!-- Tabla con la suspension y los años dias y semanas -->
						<TABLE border=0 cellPadding=0 cellSpacing=0>
							<TR  class=opciones_ejecucion>
								<TD colspan=6><INPUT   id=sw_sus type=checkbox<? echo $wsw_sus?> onclick="suspender_programacion(<?=$identificador?>,<?=$tipoaccion?>)"><SPAN style="COLOR:#999999"><?echo $TbMsg[1]?></SPAN></TD>
							</TR>
							<TR>
								<TD>&nbsp;</TD>
							</TR>
							<TR>
								<TD valign=top>
									<? 	$annodesde=date("Y");
										echo $mialmanaque->Annos(($annodesde-4),($annodesde+4)); // Años?>
								</TD>
								<TD width="150">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>
								<TD  valign=top width="164">
										<? echo $mialmanaque->Meses(); // Meses del año?>
								</TD>
								<TD width="150">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>
								<TD  valign=top width="155">
									<TABLE id="fechassimples" style="visibility:visible" border=0 cellPadding=0 cellSpacing=0 >
										<TR>
											<TD id=tbmesanno>
												<? $tmarray=getdate();
													$anoactual=$tmarray["year"];
													$mesacutal=$tmarray["mon"];
												  echo $mialmanaque->MesAnno($mesacutal,$anoactual); // Calendario del mes?>
											</TD>
										</TR>
									</TABLE>
								</TD>
								<TD width="150">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>
							</TR>
						</TABLE>
					</TD>
					<TD>
					<!-- Tabla Dias semenas y dias del mes -->
						<TABLE id="fechasmultiples" style="visibility:hidden" border=0 cellPadding=0 cellSpacing=0 >
							<TR>
								<TD>
									<? echo $mialmanaque->Dias(); // Dias de la semana?>
								</TD>
							</TR>
							<TR>
								<TD>
									<? echo $mialmanaque->Semanas(); // Orden de la semanas?>
								</TD>
							</TR>
							<TR>
								<TD>
									<? echo $mialmanaque->DiasMes(); // Días del mes?>
								</TD>
							</TR>
						</TABLE>
					</TD>
				</TR>
			</TABLE>
		</TD>
	</TR>
	<TR>
		<TD>
			<!-- Tabla Horas-->
			<TABLE border=0 cellPadding=0 cellSpacing=0 style="HEIGHT: 24px; WIDTH: 279px">
				<TR>
					<TD>
						<? echo "<BR>".$mialmanaque->Horas(); // Horas?>
						<? if ($tipoaccion==$EJECUCION_RESERVA){
								echo $mialmanaque->HorasReserva("1","tabla_horasini","ampmini","minutosini"); // Horas desde;
								echo $mialmanaque->HorasReserva("2","tabla_horasfin","ampmfin","minutosfin"); // Horas hasta;
							}
						?>
						<br>
					</TD>
				</TR>
			</TABLE>
		</TD>
	</TR>
	<TR>
		<TD>
			 <!-- Tabla Bloques-->
			<TABLE   class="tablaprogramacion" border=0 cellPadding=0 cellSpacing=0   width="521">
				<TR>
					<TD valign=top >
						<TABLE border=0 cellPadding=0 cellSpacing=0  >
							<TR>
								<TD >
									<TABLE   align=center class=tablaprogramacion border=0 cellPadding=0 cellSpacing=2  width="413">
										<TR>
											<TH align=center width="409">&nbsp;<?echo $TbMsg[2]?></TH>
										</TR>
										<TR>
											<TD width="407"><input type=text  class="cajatexto" id="nombrebloque" style="width: 410; height: 20" size="20" ></TD>
										</TR>
									</TABLE>
								</TD>
							</TR>
							<TR>
								<TD>
									<?
										$HTMLSELECT="";
										$HTMLSELECT.='<SELECT onclick=consulta_programacion(); class=estilodesple id="lista_programaciones" size=2 style="height:100; width: 412">' ;
										if ($numreg>0){
											while (!$rs->EOF){
												$HTMLSELECT.='<OPTION value="'.$rs->campos["idprogramacion"].'"';
												$HTMLSELECT.= '>'.$rs->campos["nombrebloque"].'</OPTION>';
												$rs->Siguiente();
											}
										}
										$HTMLSELECT.= '</SELECT>';
										$rs->Cerrar();
										echo $HTMLSELECT;
									?>
								</TD>
							</TR>
						</TABLE>
					</TD>
					<TD valign=top width="80">
						<TABLE  border=0 class=tablaprogramacion border=0 cellPadding=1 cellSpacing=6  width="103">
							<TR>
								<TD  class=botonprogramacion id=bt_insertar style="cursor:hand;" onclick="alta_programacion(<?=$identificador?>,<?=$tipoaccion?>)" align=center height="17" width="83">Añadir</TD>
							</TR>
							<TR>
								<TD class=botonprogramacion id=bt_modificar style="color:#bbbcb9" onclick="modifica_programacion(<?=$identificador?>,<?=$tipoaccion?>)" align=center height="17" width="83">Modificar</TD>
							</TR>

								<TD class=botonprogramacion id=bt_duplicar style="color:#bbbcb9" onclick="duplicar_programacion()" align=center height="17" width="83">Duplicar</TD>
							</TR>

							<TR>
								<TD  class=botonprogramacion id=bt_eliminar style="color:#bbbcb9" onclick="elimina_programacion()"align=center height="17" width="83">Eliminar</TD>
							</TR>
							<TR>
								<TD  class=botonprogramacion id=bt_cancelar style="color:#bbbcb9" onclick="cancela_programacion()" align=center height="17" width="83">Cancelar</TD>
							</TR>
						</TABLE>
					</TD>
				</TR>
			</TABLE>
		</TD>
	</TR>
</TABLE>
</FORM>
<?
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
<SCRIPT language="javascript">
	var lista=document.getElementById("lista_programaciones");
	var numblo=lista.options.length
	if(numblo>0){
		lista.selectedIndex=0;
		consulta_programacion()
	}
	else{
			nuevo_bloque();
	}
</SCRIPT>
</BODY>
</HTML>

