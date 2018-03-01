<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla

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
$idcomando=0; 
$sesion=0; 
$idreserva=0; 
$descripciontarea=""; 
$descripcioncomando=""; 
$descripcionreserva=""; 
$tipoaccion=""; 
$identificador=0; 

if (isset($_GET["idtarea"])) $idtarea=$_GET["idtarea"]; // Recoge parametros
if (isset($_GET["idcomando"])) $idcomando=$_GET["idcomando"]; // Recoge parametros
if (isset($_GET["sesion"])) $sesion=$_GET["sesion"]; // Recoge parametros
if (isset($_GET["idreserva"])) $idreserva=$_GET["idreserva"]; // Recoge parametros
if (isset($_GET["descripcioncomando"])) $descripcioncomando=$_GET["descripcioncomando"]; // Recoge parametros
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
case $EJECUCION_COMANDO :
	$textoaccion=$LITEJECUCION_COMANDO;
	$urlimg='../images/iconos/comandos.gif';
	$identificador=$idcomando;
	$descripcion=$descripcioncomando;
	$cmd->texto="SELECT * FROM programaciones 
			WHERE identificador=".$identificador." AND sesion=".$sesion." AND tipoaccion=".$EJECUCION_COMANDO;
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
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/programaciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/programaciones_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<P align=center class=cabeceras><?php echo $TbMsg[0]?> <?php echo $textoaccion?>&nbsp;<img src="../images/iconos/reloj.gif"><br>
<IMG src=<?php echo $urlimg?>>&nbsp;<SPAN align=center class=subcabeceras><?php echo $descripcion?></SPAN>&nbsp;</p>
<FORM name="fprogramaciones" method="post">
<INPUT type=hidden name=tipoaccion value="<?php echo $tipoaccion?>">
<CENTER>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!-- Tabla Tabla con los tiempos y los bloques de programación  -->
<TABLE align=center border=0 cellPadding=0 cellSpacing=0>
	<TR>
		<TD>
			<!--  Tabla con los años dias y semanas  -->
			<TABLE  border=0 cellPadding=0 cellSpacing=0  style="height: 27px">
				  <TR>
					<TD valign=top>
					<!-- Tabla con la suspension y los años dias y semanas -->
						<TABLE border=0 cellPadding=0 cellSpacing=0>
							<TR  class=opciones_ejecucion>
								<TD colspan=6><INPUT   id=sw_sus type="checkbox" <?php echo $wsw_sus?> 
									onclick="suspender_programacion(<?php echo $identificador?>,<?php echo $tipoaccion?>,<?php echo $sesion?>);)">
									<SPAN style="COLOR:#999999"><?php echo $TbMsg[1]?></SPAN></TD>
							</TR>
							<TR>
								<TD colspan=6>&nbsp;</TD>
							</TR>
							<TR>
								<TD valign=top>
									<?php 	$annodesde=date("Y");
										echo $mialmanaque->Annos(($annodesde-3),($annodesde+3)); // Años?>
								</TD>
								<TD width=10>&nbsp;</TD>
								<TD  valign=top>
										<?php echo $mialmanaque->Meses(); // Meses del año?>
								</TD>
								<TD width=10>&nbsp;</TD>
								<TD  valign=top width="155">
									<TABLE id="fechassimples" style="visibility:visible" border=0 cellPadding=0 cellSpacing=0>
										<TR>
											<TD id=tbmesanno>
												<?php $tmarray=getdate();
													$anoactual=$tmarray["year"];
													$mesacutal=$tmarray["mon"];
												  echo $mialmanaque->MesAnno($mesacutal,$anoactual); // Calendario del mes?>
											</TD>
										</TR>
									</TABLE>
								</TD>
								<TD width=10>&nbsp;</TD>
							</TR>
						</TABLE>
					</TD>
					<TD>
					<!-- Tabla Dias semenas y dias del mes -->
						<TABLE id="fechasmultiples" style="visibility:hidden" border=0 cellPadding=0 cellSpacing=0 >
							<TR>
								<TD>
									<?php echo $mialmanaque->Dias(); // Dias de la semana?>
								</TD>
							</TR>
							<TR>
								<TD>
									<?php echo $mialmanaque->Semanas(); // Orden de la semanas?>
								</TD>
							</TR>
							<TR>
								<TD>
									<?php echo $mialmanaque->DiasMes(); // Días del mes?>
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
						<?php echo "<BR>".$mialmanaque->Horas(); // Horas?>
						<?php if ($tipoaccion==$EJECUCION_RESERVA){
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
			<TABLE  border=0 cellPadding=0 cellSpacing=0 >
				<TR>
					<TD valign=top >
						<TABLE border=0 cellPadding=0 cellSpacing=0  >
							<TR>
								<TD >
									<TABLE   align=center class="tabla_meses" border=0 cellPadding=0 cellSpacing=2>
										<TR>
											<TH align=center>&nbsp;<?php echo $TbMsg[2]?></TH>
										</TR>
										<TR>
											<TD><input type=text  class="cajatexto" id="nombrebloque"
											style="width: 350; height: 20" size="20" ></TD>
										</TR>
									</TABLE>
								</TD>
							</TR>
							<TR>
								<TD>
									<?php
										$HTMLSELECT="";
										$HTMLSELECT.='<SELECT onclick=consulta_programacion(); 
										class=estilodesple id="lista_programaciones" size=2 style="height:100; width: 350">' ;
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
					<TD valign=top width=6>&nbsp;</TD>
					<TD valign=top>					
						<TABLE  border=0 class=tablaprogramacion border=0 cellPadding=1 cellSpacing=6  width="103">
							<TR>
								<TD align=center class=botonprogramacion>
									<SPAN id=bt_insertar style="cursor:pointer;visibility:visible;color:#bbbcb9"
									onmouseover="sobreboton(this)" 	onmouseout="fueraboton(this)"
									onclick="alta_programacion(<?php echo $identificador?>,<?php echo $tipoaccion?>,<?php echo $sesion?>)" 
									align=center height="17" width="83">Añadir</SPAN></TD>
							</TR>
							<TR>
								<TD  align=center class=botonprogramacion >
									<SPAN id=bt_modificar style="cursor:pointer;visibility:visible;color:#bbbcb9"
									onmouseover="sobreboton(this)" 	onmouseout="fueraboton(this)"
									onclick="modifica_programacion(<?php echo $identificador?>,<?php echo $tipoaccion?>,<?php echo $sesion?>)" align=center height="17" width="83">Modificar</SPAN></TD>
							</TR>

								<TD align=center class=botonprogramacion >
									<SPAN id=bt_duplicar style="cursor:pointer;visibility:visible;color:#bbbcb9" 
									onmouseover="sobreboton(this)" 	onmouseout="fueraboton(this)"
									onclick="duplicar_programacion()" align=center height="17" width="83">Duplicar</SPAN></TD>
							</TR>

							<TR>
								<TD align=center class=botonprogramacion >
									<SPAN id=bt_eliminar style="cursor:pointer;color:#bbbcb9;visibility:visible" 
									onmouseover="sobreboton(this)" 	onmouseout="fueraboton(this)"
									onclick="elimina_programacion()"align=center height="17" width="83">Eliminar</SPAN></TD>
							</TR>
							<TR>
								<TD align=center class=botonprogramacion >
									<SPAN id=bt_cancelar style="cursor:pointer;visibility:visible;color:#bbbcb9" 
									onmouseover="sobreboton(this)" 	onmouseout="fueraboton(this)"
									onclick="cancela_programacion()" align=center height="17" width="83">Cancelar</SPAN></TD>
							</TR>
						</TABLE>
					</TD>
				</TR>
			</TABLE>
		</TD>
	</TR>
</TABLE>
</FORM>
<SCRIPT language="javascript">
	var lista=document.getElementById("lista_programaciones");
	var numblo=lista.options.length;
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
