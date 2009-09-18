<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero: programacionesaulas.php
// Descripción : 
//		Visualiza las reservas pendientes, confirmadas, denegadas y fecha y hora de la reserva
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLCTEMULSELECT.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../idiomas/php/".$idioma."/clases/Calendario_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/programacionesaulas_".$idioma.".php");
//________________________________________________________________________________________________________
$ambito=""; 
$idambito=0;
$nombreambito="";
$fechainicio="";
$fechafin="";
$estadoreserva="";
$situacion="";

if (isset($_GET["ambito"]))	$ambito=$_GET["ambito"]; 
if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if (isset($_GET["nombreambito"])) $nombreambito=$_GET["nombreambito"]; 
if (isset($_GET["tipocola"])) $tipocola=$_GET["tipocola"]; 

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
// Criterios por defecto
if($ambito!="" && $idambito!="" && $nombreambito!="" && $tipocola!=""){ 
	$wfechainicio=mktime(0, 0, 0, date("m")  , date("d"), date("Y")); // Reservas desde un mes anterior
	$wfechafin=mktime(0, 0, 0, date("m")+6 , date("d"), date("Y"));
	$fechainicio=date("d/m/Y",$wfechainicio);
	$fechafin=date("d/m/Y ",$wfechafin);
	// Tipos de reservas
	switch($tipocola){
		case 0:
			$westadoreserva[0]=$RESERVA_CONFIRMADA;
			$westadoreserva[1]=$RESERVA_PENDIENTE;
			$westadoreserva[2]=$RESERVA_DENEGADA;

			$estadoreserva=$RESERVA_CONFIRMADA."=".$LITRESERVA_CONFIRMADA.";";
			$estadoreserva.=$RESERVA_PENDIENTE."=".$LITRESERVA_PENDIENTE.";";
			$estadoreserva.=$RESERVA_DENEGADA."=".$LITRESERVA_DENEGADA.";";
			break;
		case $RESERVA_CONFIRMADA:
			$westadoreserva[0]=$RESERVA_CONFIRMADA;
			$estadoreserva=$RESERVA_CONFIRMADA."=".$LITRESERVA_CONFIRMADA.";";
			break;
		case $RESERVA_PENDIENTE:
			$westadoreserva[0]=$RESERVA_PENDIENTE;
			$estadoreserva=$RESERVA_PENDIENTE."=".$LITRESERVA_PENDIENTE.";";
			break;
		case $RESERVA_DENEGADA:
			$westadoreserva[0]=$RESERVA_DENEGADA;
			$estadoreserva=$RESERVA_DENEGADA."=".$LITRESERVA_DENEGADA.";";
			break;
	}
		// Estados default
	$wsituacion[0]=$RESERVA_PARADA;
	$wsituacion[1]=$RESERVA_ACTIVA;
	$situacion=$RESERVA_PARADA."=".$LITRESERVA_PARADA.";";
	$situacion.=$RESERVA_ACTIVA."=".$LITRESERVA_ACTIVA.";";
}
//________________________________________________________________________________________________________
// Recupera parametros del formulario
if (isset($_POST["ambito"]))	$ambito=$_POST["ambito"]; 
if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
if (isset($_POST["nombreambito"])) $nombreambito=$_POST["nombreambito"]; 
if (isset($_POST["fechainicio"])) $fechainicio=$_POST["fechainicio"]; 
if (isset($_POST["fechafin"])) $fechafin=$_POST["fechafin"]; 

if (isset($_POST["estadoreserva"])) {
	$estadoreserva=$_POST["estadoreserva"]; 
	$auxP=split(";",$estadoreserva);
	$cont=0;
	for ($i=0;$i<sizeof($auxP)-1;$i++){
		$dualparam=split("=",$auxP[$i]);
		$westadoreserva[$cont++]=$dualparam[0];
	}
}
if (isset($_POST["situacion"])){
	$situacion=$_POST["situacion"]; 
	$auxP=split(";",$situacion);
	$cont=0;
	for ($i=0;$i<sizeof($auxP)-1;$i++){
		$dualparam=split("=",$auxP[$i]);
		$wsituacion[$cont++]=$dualparam[0];
	}
}
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../hidra.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/programacionesaulas.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/programacionesaulas_'.$idioma.'.js"></SCRIPT>'?>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<?
switch($ambito){
		case $AMBITO_CENTROS :
			$urlimg='../images/iconos/centros.gif';
			$textambito=$TbMsg[12];
			break;
		case $AMBITO_GRUPOSAULAS :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[13];
			break;
		case $AMBITO_AULAS :
			$urlimg='../images/iconos/aula.gif';
			$textambito=$TbMsg[14];
			break;
		case $AMBITO_GRUPOSRESERVAS :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[30];
			break;
}
echo '<p align=center class=cabeceras><img src="../images/iconos/reservas.gif">&nbsp;'.$TbMsg[0].'&nbsp;';
echo '<FORM name="fdatos" action="programacionesaulas.php" method="post">'.chr(13);
// Campos ocultos 
echo '<INPUT type=hidden name=ambito value="'.$ambito.'">';
echo '<INPUT type=hidden name=idambito value="'.$idambito.'">';
echo '<INPUT type=hidden name=nombreambito value="'.$nombreambito.'">';
echo '<INPUT type=hidden name=estadoreserva value="'.$estadoreserva.'">';
echo '<INPUT type=hidden name=situacion value="'.$situacion.'">';
echo CriteriosBusquedas(); // Opciones de búsqueda
echo '</FORM>'.chr(13);

echo '<DIV align=center >';
echo '<span align=center class=subcabeceras><U>'.$TbMsg[11].':'.$textambito.'</U>,&nbsp'.$nombreambito.'</span>&nbsp;&nbsp;<IMG src="'.$urlimg.'"></span></DIV></p>';
//________________________________________________________________________________________________________
// Proceso de selección de reservas
$ClausulaWhere="";
//________________________________________________________________________________________________________
// Cuestion identificador del ámbito
$WhereCentroAccion="";
$WhereCentroAccion='reservas.idcentro='.$idcentro;
$ClausulaWhere.=" AND (".$WhereCentroAccion.")";

$cadenaaulas="";
$cadenareservas="";
$swa=false; // Para saber que ámbitos se han seleccionado
switch($ambito){
		case $AMBITO_CENTROS :
			$cmd->texto="SELECT idcentro,nombrecentro FROM centros WHERE idcentro=".$idambito;
 			RecorreCentro($cmd);
			break;
		case $AMBITO_GRUPOSAULAS :
			$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE idgrupo=".$idambito." AND tipo=".$AMBITO_GRUPOSAULAS;
			RecorreGruposAulas($cmd);
			break;
		case $AMBITO_AULAS :
			$cmd->texto="SELECT idaula,nombreaula,horaresevfin  FROM aulas WHERE idaula=".$idambito;
			RecorreAulas($cmd);
			break;
		case $AMBITO_GRUPOSRESERVAS :
			$swa=true;
			if(!empty($idambito)){
				$cmd->texto="SELECT idgrupo FROM grupos WHERE idgrupo=".$idambito." AND tipo=".$AMBITO_GRUPOSRESERVAS;
				RecorreGruposReservas($cmd);
			}
			else{
				$cmd->texto="SELECT idgrupo FROM grupos WHERE grupoid=0 AND tipo=".$AMBITO_GRUPOSRESERVAS;
				RecorreGruposReservas($cmd);
				$cmd->texto="SELECT idreserva FROM reservas WHERE grupoid=0";
				RecorreReservas($cmd);
			}
			break;
}
if(strlen($cadenaaulas)>0){
	$cadenaaulas=substr($cadenaaulas,0,strlen($cadenaaulas)-1); // Quita la coma
	$ClausulaWhere.=" AND aulas.idaula IN(".$cadenaaulas.")";
}
if(strlen($cadenareservas)>0){
	$cadenareservas=substr($cadenareservas,0,strlen($cadenareservas)-1); // Quita la coma
	$ClausulaWhere.=" AND idreserva in(".$cadenareservas.")";
}
//________________________________________________________________________________________________________
// Cuestion estado de las reservas ( Confirmadas,Pendientes o Denegadas )
$WhereEstadosReservas="";
for($i=0;$i<sizeof($westadoreserva);$i++){
		if (isset($westadoreserva[$i]))
			$WhereEstadosReservas.=" reservas.estado=".$westadoreserva[$i]." OR ";
}
if($WhereEstadosReservas!=""){
	$WhereEstadosReservas=substr($WhereEstadosReservas,0,strlen($WhereEstadosReservas)-3); 
	$ClausulaWhere.=" AND (".$WhereEstadosReservas.")";
}
//________________________________________________________________________________________________________
// Cuestion situación de la programación
$WhereSituaciones="";
for($i=0;$i<sizeof($wsituacion);$i++){
	if (isset($wsituacion[$i]))
		$WhereSituaciones.=" programaciones.suspendida=".$wsituacion[$i]." OR ";
}
if($WhereSituaciones!=""){
	$WhereSituaciones=substr($WhereSituaciones,0,strlen($WhereSituaciones)-3); 
	$ClausulaWhere.=" AND (".$WhereSituaciones.")";
}
//________________________________________________________________________________________________________
// Cuestion de fechas 
$calendario=new Calendario("tabla_reservas");

if(empty($fechainicio)) $fechainicio=date("d/m/Y",mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
if(empty($fechafin))	$fechafin=date("d/m/Y",mktime(0, 0, 0, date("m")+1  , date("d"), date("Y")));

$sumahoras=0;
$JDif=0;
$TBfechas="";
$TOTfechas="";
$cadenaXML=ProcesoAnual($fechainicio,$fechafin,$swa);
// Creación del árbol
$baseurlimg="../images/tsignos"; // Url de las imágenes de signo
$clasedefault="tabla_listados_sin";
$titulotabla=$TbMsg[0];  
$arbol=new ArbolVistaXml($cadenaXML,0,$baseurlimg,$clasedefault,1,20,270,2,$titulotabla);
$salidaHTML=$arbol->CreaArbolVistaXml();  // Muestra  árbol
echo "<BR>";
echo "<DIV align=center width=100%>";
echo "	 <TABLE align=center width=100%>";
echo "		<TR><TD>";
echo urldecode($salidaHTML);
echo "		</TD></TR><TABLE></DIV>";
?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
function ProcesoAnual($fechainicio,$fechafin,$swa){
	global $EJECUCION_RESERVA;
	global $calendario;
	global $cmd;
	global $ClausulaWhere;
	global $sumahoras;
	global $TbMsg;
	global $TOTfechas;
	global $TBfechas;
	global $JDif;

	list($sdia,$smes,$sanno)=split("/",$fechainicio);
	$dia_i=(int)$sdia;
	$mes_i=(int)$smes;
	$anno_i=(int)$sanno;

	list($sdia,$smes,$sanno)=split("/",$fechafin);
	$dia_f=(int)$sdia;
	$mes_f=(int)$smes;
	$anno_f=(int)$sanno;

	$udm=$calendario->dias_meses[(int)$mes_f]; // Último día del mes
	if($calendario->bisiesto($anno_f) && $mes_f==2) $udm++;

	$JDif=$calendario->juliana("1/".$mes_i."/".$anno_i); // calcula valor de resta para indices de fechas en tabla de memoria
	$JDesde=0;
	$JHasta=$calendario->juliana($udm."/".$mes_f."/".$anno_f)-$JDif;
	$TOTfechas=""; // tabla en memoria para acumulado de horas por fecha
	$TBfechas=""; // tabla en memoria para acumulado de horas por fecha

	$cmd->texto="SELECT   SUM(horaresevfin - horaresevini) as sumahoras FROM  aulas";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) $sumahoras=0; // Error al abrir recordset
	if(!$rs->EOF){
		$sumahoras=$rs->campos["sumahoras"]*60;
	}

	//Recorre de fecha inicio a fecha fin
	$anno_c=$anno_i;
	$mes_c=$mes_i;

	$cadenaXML="";

	// Calcula el rango de meses dependiendo del intervalo de fechas solicitado
	while($anno_c<=$anno_f){
		if($anno_c==$anno_i){
			$mes_c=$mes_i;
			if($anno_f>$anno_c)
				$mes_t=12;
			else
				$mes_t=$mes_f;
		}
		else{
				if($anno_c>$anno_i){
					if($anno_c==$anno_f){
						$mes_c=1;
						$mes_t=$mes_f;
					}
					else{
						$mes_c=1;
						$mes_t=12;
					}
				}
		}
		$HEXanno=$calendario->numero_annos[$anno_c-2003][1];  // Primera referencia: 2004
		$HEXmeses=0;
		$mes_desde=$mes_c;
		$mes_hasta=$mes_t;
		while($mes_c<=$mes_t){
				$HEXmeses=$HEXmeses | $calendario->nombre_mes[(int)$mes_c][1];
				$mes_c++;
		}
		// Cadena SQL para seleccionar reservas
		$cmd->texto="SELECT programaciones.idprogramacion, programaciones.nombrebloque,programaciones.annos, programaciones.meses, programaciones.diario, programaciones.dias, programaciones.semanas, programaciones.horasini, programaciones.ampmini, programaciones.minutosini, programaciones.horasfin, programaciones.ampmfin, programaciones.minutosfin,";
		$cmd->texto.="trabajos.idtrabajo,tareas.idtarea,trabajos.descripcion AS nombretrabajo,tareas.descripcion AS nombretarea,";
		$cmd->texto.="reservas.idreserva,reservas.descripcion,reservas.solicitante,reservas.email,reservas.estado,reservas.idaula,";
		$cmd->texto.="aulas.horaresevfin ,aulas.horaresevini ,aulas.nombreaula as nombreaula,imagenes.idimagen ,imagenes.descripcion as nombreimagen";
		$cmd->texto.=" FROM   reservas";
		$cmd->texto.=" INNER JOIN programaciones ON reservas.idreserva = programaciones.identificador";
		$cmd->texto.=" INNER JOIN  aulas ON reservas.idaula = aulas.idaula";
		$cmd->texto.=" LEFT OUTER JOIN imagenes ON reservas.idimagen = imagenes.idimagen";
		$cmd->texto.=" LEFT OUTER JOIN tareas ON reservas.idtarea = tareas.idtarea";
		$cmd->texto.=" LEFT OUTER JOIN trabajos ON reservas.idtrabajo = trabajos.idtrabajo";
		$cmd->texto.=" WHERE (programaciones.tipoaccion = ".$EJECUCION_RESERVA.") ".$ClausulaWhere ;
		$cmd->texto.=" AND (programaciones.annos & ".$HEXanno."<>0)";
		$cmd->texto.=" AND (programaciones.meses & ".$HEXmeses."<>0)" ;
		$cmd->texto.=" ORDER BY programaciones.annos,programaciones.meses,"; 
		if($swa)
			$cmd->texto.="programaciones.ampmini,programaciones.horasini,programaciones.minutosini"; 
		else
			$cmd->texto.="aulas.idaula,programaciones.ampmini,programaciones.horasini,programaciones.minutosini"; 

		$AuxcadenaXML=ListaReservas($cmd,$anno_c,$mes_desde,$mes_hasta,$dia_i,$dia_f,$mes_i,$mes_f,$anno_i,$anno_f,$swa);

		$cadenaXML.='<TBANNO ';
		// Atributos		
		$cadenaXML.=' imagenodo="../images/iconos/reloj.gif"';
		$cadenaXML.=' clickimg="AnnoReserva('.$anno_c.');"';
		$cadenaXML.=' infonodo="%3Cb%3E&nbsp; '.$TbMsg[15].': %3C/b%3E'.$anno_c.'"';
		$cadenaXML.=' nodoid=anno-'.$anno_c;
		$cadenaXML.='>';
			$cadenaXML.='<ANNO ';
			// Atributos		
			$cadenaXML.=' imagenodo="../images/iconos/nada.gif"';
			//___________________________________________________________________________
			$HTMLannos="<TABLE><TR>";
				for ($i=$mes_desde;$i<=$mes_hasta;$i++){
						if($i%7==0) 	$HTMLannos.="</TR><TR>";
						$HTMLannos.='<TD style="BACKGROUND-COLOR:#FFFFFF" valign=top>';
						$HTMLannos.=$calendario->JMesAnno($i,$anno_c,$JDif,$TOTfechas,$sumahoras);
						$HTMLannos.='</TD>';
				}
			$HTMLannos.="</TR></TABLE>";
			//___________________________________________________________________________
			$cadenaXML.=' infonodo='.urlencode($HTMLannos);
			$cadenaXML.=' nodoid=tablameses-'.$anno_c;
			$cadenaXML.=' fondonodo='."#FFFFFF";
			$cadenaXML.='>';
			$cadenaXML.='</ANNO> ';
			$cadenaXML.=$AuxcadenaXML;
		$cadenaXML.='</TBANNO> ';

		$anno_c++;
	}
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function CriteriosBusquedas(){
	global $idcentro;
	global $TbMsg;

	global $RESERVA_CONFIRMADA;
	global $RESERVA_PENDIENTE;
	global $RESERVA_DENEGADA;
	global $LITRESERVA_CONFIRMADA;
	global $LITRESERVA_PENDIENTE;
	global $LITRESERVA_DENEGADA;

	global $RESERVA_PARADA;  // reserva momentanemente parada
	global $RESERVA_ACTIVA; // Reserva activa
	global $LITRESERVA_PARADA;
	global $LITRESERVA_ACTIVA;

	global $fechainicio;
	global $fechafin;
	
	global $westadoreserva;
	global $wsituacion;

	$HTMLCriterios="";
	$HTMLCriterios.='<TABLE class=tabla_busquedas align=center border="0">'.chr(13);
	$HTMLCriterios.='<TR HEIGHT=30>'.chr(13);
		$HTMLCriterios.='<TD colspan=4 align="center" >'.chr(13);
		$HTMLCriterios.='<SPAN align=center style="FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; FONT-SIZE: 11px;COLOR:#5a86b5;FONT-WEIGHT: 700;">_______________ '.$TbMsg[1].' _______________</SPAN>'.chr(13);
		$HTMLCriterios.='</TR>'.chr(13);
		$HTMLCriterios.='</TD>'.chr(13);

	// Desplegable con los tipos de reservas
	$HTMLCriterios.='<TR>'.chr(13);
		$HTMLCriterios.='<TD>'.chr(13);
			$HTMLCriterios.='<TABLE class=tabla_standar align=center border="0">'.chr(13);
			$HTMLCriterios.='<TR>'.chr(13);
			$HTMLCriterios.='	<TH align=center>&nbsp;'.$TbMsg[2].'&nbsp;</TH>'.chr(13);
			$HTMLCriterios.='</TR>'.chr(13);
			$HTMLCriterios.='<TR>'.chr(13);
			$parametros=$RESERVA_CONFIRMADA."=".$LITRESERVA_CONFIRMADA.chr(13);
			$parametros.=$RESERVA_PENDIENTE."=".$LITRESERVA_PENDIENTE.chr(13);
			$parametros.=$RESERVA_DENEGADA."=".$LITRESERVA_DENEGADA;
			$HTMLCriterios.='<TD colspan=3>'.HTMLCTEMULSELECT($parametros,"westadoreserva",$westadoreserva,"estilodesple","chgdesplereservas",100,3).'</TD>';
			$HTMLCriterios.='</TR>'.chr(13);
			$HTMLCriterios.='</TABLE>'.chr(13);
		$HTMLCriterios.='</TD>'.chr(13);
	// Desplegable con los distintos situacion
		$HTMLCriterios.='<TD>'.chr(13);
			$HTMLCriterios.='<TABLE class=tabla_standar align=center border="0">'.chr(13);
			$HTMLCriterios.='<TR>'.chr(13);
			$HTMLCriterios.='	<TH align=center>&nbsp;'.$TbMsg[4].'&nbsp;</TH>'.chr(13);
			$HTMLCriterios.='</TR>'.chr(13);
			$HTMLCriterios.='<TR>'.chr(13);
			$parametros=$RESERVA_PARADA."=".$LITRESERVA_PARADA.chr(13);
			$parametros.=$RESERVA_ACTIVA."=".$LITRESERVA_ACTIVA;
			$HTMLCriterios.='<TD colspan=3>'.HTMLCTEMULSELECT($parametros,"wsituacion",$wsituacion,"estilodesple","chgdesplesituacion",100,3).'</TD>';
			$HTMLCriterios.='</TR>'.chr(13);
			$HTMLCriterios.='</TABLE>'.chr(13);
	$HTMLCriterios.='</TD>'.chr(13);

	// Fechas
		$HTMLCriterios.='<TD  COLSPAN=2>'.chr(13);
			$HTMLCriterios.='<TABLE WIDTH=100% class=tabla_standar align=center border="0">'.chr(13);
			$HTMLCriterios.='<TR>'.chr(13);
			$HTMLCriterios.='<TH>&nbsp;'.$TbMsg[7].':&nbsp;</TH>'.chr(13);
			$HTMLCriterios.='<TD><INPUT class="cajatexto" onclick="vertabla_calendario(this)" style="WIDTH:80" name="fechainicio" value="'.$fechainicio.'"></TD>'.chr(13);
			$HTMLCriterios.='</TR>'.chr(13);
			$HTMLCriterios.='<TR>'.chr(13);
			$HTMLCriterios.='<TH align=right>&nbsp;'.$TbMsg[8].':&nbsp;&nbsp;</TH>'.chr(13);
			$HTMLCriterios.='<TD> <INPUT class="cajatexto" onclick="vertabla_calendario(this)" style="WIDTH:80" name="fechafin" value="'.$fechafin.'"></TD>'.chr(13);
			$HTMLCriterios.='</TR>'.chr(13);
			$HTMLCriterios.='</TABLE>'.chr(13);		
		$HTMLCriterios.='</TD>'.chr(13);
	$HTMLCriterios.='</TR>'.chr(13);

	$HTMLCriterios.='<TR height=5>'.chr(13);
		$HTMLCriterios.='<TD colspan=4 align="center" >'.chr(13);
		$HTMLCriterios.='<SPAN style="FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; FONT-SIZE: 11px;COLOR:#5a86b5;FONT-WEIGHT: 700;">__________________________________________________</SPAN>'.chr(13);
	$HTMLCriterios.='</TR>'.chr(13);

	// Lupa
	$HTMLCriterios.='<TR>'.chr(13);
		$HTMLCriterios.='<TD  COLSPAN=4>'.chr(13);
		$HTMLCriterios.='<TABLE class=tabla_busquedas align=center border="0">'.chr(13);
		$HTMLCriterios.='<TR>'.chr(13);
		$HTMLCriterios.='<TD>';

		$HTMLCriterios.='<IMG src="../images/iconos/busquedas.gif" onclick="javascript:fdatos.submit()" style="cursor:hand" alt="Buscar">';
		$HTMLCriterios.='</TD>';
		$HTMLCriterios.='<TD>';
		$HTMLCriterios.='</TD>'.chr(13);
		$HTMLCriterios.='</TR>'.chr(13);
		$HTMLCriterios.='</TABLE>';
		$HTMLCriterios.='</TD>'.chr(13);
	$HTMLCriterios.='</TR>'.chr(13);
$HTMLCriterios.='</TABLE>';
return($HTMLCriterios);
}
//________________________________________________________________________________________________________
function ListaReservas($cmd,$anno_c,$mes_desde,$mes_hasta,$dia_i,$dia_f,$mes_i,$mes_f,$anno_i,$anno_f,$swa){
	global $calendario;
	global $JDif;
	global $TBfechas;
	global $TOTfechas;
	global $sumahoras;
	global $TbMsg;

	$cadenaXML="";

	$udm=$calendario->dias_meses[(int)$mes_hasta]; // Último día del mes
	if($calendario->bisiesto($anno_f) && $mes_hasta==2) $udm++;
	$fechaminima=mktime(0, 0, 0, $mes_i, 1, $anno_i);
	$fechamaxima=mktime(0, 0, 0, $mes_f,$udm, $anno_f);

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	$gidaula=0;

	if($swa)
			$wsumahoras=$sumahoras;

	// Recorre reservas
	while (!$rs->EOF){
		if(!$swa){
			if($gidaula!=$rs->campos["idaula"]){
				$wsumahoras=($rs->campos["horaresevfin"]-$rs->campos["horaresevini"])*60;;
				if($gidaula>0)
					$cadenaXML.='</AULA>';
				$gidaula=$rs->campos["idaula"];
				$nombreaula=$rs->campos["nombreaula"];
				$cadenaXML.='<AULA ';
				// Atributos		
				$cadenaXML.=' imagenodo="../images/iconos/aula.gif"';
				$cadenaXML.=' infonodo="%3Cb%3E'.$TbMsg[24].':%3C/b%3E '.$rs->campos["nombreaula"].'"';;
				$cadenaXML.=' nodoid=aula-'.$rs->campos["idaula"];
				$cadenaXML.=' colornodo='."#000000";
				$cadenaXML.=' fondonodo='."#B5DAAD;";
				$cadenaXML.='>';
			}
		}
		$swr=false; // detecta si la reserva es válida
		$TBfechas=""; // tabla en memoria para acumulado de horas por fecha de cada reserva
		$cf=$calendario->Fechas($anno_c,$mes_desde,$mes_hasta,$rs->campos["meses"],$rs->campos["diario"],$rs->campos["dias"],$rs->campos["semanas"]);
		$fechas_reservas=split(";",$cf);
		for ($i=0;$i<sizeof($fechas_reservas)-1;$i++){
				list($auxdia,$auxmes,$auxanno)=split("/",$fechas_reservas[$i]);
				$auxfecha=mktime(0, 0, 0, $auxmes,$auxdia, $auxanno);
				if($auxfecha>=$fechaminima &&  $auxfecha<=$fechamaxima){
					$swr=true;
					$Jreserva=$calendario->juliana($fechas_reservas[$i]);
					$idx=$Jreserva-$JDif;
					// Cálculo de los minutos
					$sOcupacion=CalculaMinutos($rs);
					if (!isset($TBfechas[$idx])) $TBfechas[$idx]=0;
					if (!isset($TOTfechas[$idx])) $TOTfechas[$idx]=0;
					$TBfechas[$idx]+=$sOcupacion;
					$TOTfechas[$idx]+=$sOcupacion;
				}
		}
		if($swr)
			$cadenaXML.=TomaReserva($rs,$mes_desde,$mes_hasta,$anno_c,$wsumahoras);
		$rs->Siguiente();
	}
	if(!$swa){
		if($gidaula>0)
			$cadenaXML.='</AULA>';
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function TomaReserva($rs,$mes_desde,$mes_hasta,$anno_c,$wsumahoras){
	global $TbMsg;
	global $calendario;
	global $RESERVA_CONFIRMADA;
	global $RESERVA_PENDIENTE;
	global $RESERVA_DENEGADA;
	global $EJECUCION_RESERVA;
	global $TBfechas;
	global $sumahoras;
	global $JDif;

	 $AuxcadenaXML="";

	$tbimg[$RESERVA_CONFIRMADA]='../images/iconos/confirmadas.gif';
	$tbimg[$RESERVA_PENDIENTE]='../images/iconos/pendientes.gif';
	$tbimg[$RESERVA_DENEGADA]='../images/iconos/denegadas.gif';

	$tbampm[0]="a.m.";
	$tbampm[1]="p.m.";

	// Descripción de la reserva
	$AuxcadenaXML.='<RESERVAS ';
	// Atributos		
	$AuxcadenaXML.=' imagenodo="../images/iconos/reservas.gif"';

	// Construye tabla de ocupación
	
	$AuxcadenaXML.=' infonodo="%3CIMG border=0 src='.$tbimg[$rs->campos["estado"]].'%3E&nbsp;%3Cb%3E'.$rs->campos["descripcion"].' %3C/b%3E';
	$AuxcadenaXML.='&nbsp;(%3Cb%3E'.$TbMsg[15].":%3C/b%3E".$anno_c.')"';

	$AuxcadenaXML.=' nodoid=reserva-'.$rs->campos["idreserva"];
	$AuxcadenaXML.=' colornodo='."#000000";
	$AuxcadenaXML.=' fondonodo='."#EEEECC;";
	$AuxcadenaXML.='>';

	$AuxcadenaXML.='<OCUPACION ';
	// Atributos		
	$AuxcadenaXML.=' imagenodo="../images/iconos/nada.gif"';
	//___________________________________________________________________________
	$HTMLannos="<TABLE><TR>";
		for ($i=$mes_desde;$i<=$mes_hasta;$i++){
				if($i%7==0) 	$HTMLannos.="</TR><TR>";
				$HTMLannos.='<TD style="BACKGROUND-COLOR:#FFFFFF" valign=top>';
				$HTMLannos.=$calendario->JMesAnno($i,$anno_c,$JDif,$TBfechas,$wsumahoras);
				$HTMLannos.='</TD>';
		}
	$HTMLannos.="</TR></TABLE>";
	//___________________________________________________________________________
	$AuxcadenaXML.=' infonodo='.urlencode($HTMLannos);
	$AuxcadenaXML.=' nodoid=opcupacion';
	$AuxcadenaXML.=' colornodo='."#000000";
	$AuxcadenaXML.=' fondonodo='."#FFFFFF;";
	$AuxcadenaXML.='>';
	$AuxcadenaXML.='</OCUPACION>';

/*
	$AuxcadenaXML.='<RESERVA ';
		// Atributos		
	$AuxcadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$AuxcadenaXML.=' infonodo="%3Cb%3E'.$TbMsg[30].':%3C/b%3E "';
	$AuxcadenaXML.=' nodoid=reserva';
	$AuxcadenaXML.=' colornodo='."#000000";
	$AuxcadenaXML.='>';
*/

// Cuestion Ocupación
		$hexhoradesde=$rs->campos["horasini"];
		$minutosdesde=$rs->campos["minutosini"];
		if($minutosdesde==0) $minutosdesde="00";
		$minutosdesde.=" ".$tbampm[$rs->campos["ampmini"]];
		$hexhorahasta=$rs->campos["horasfin"];
		$minutoshasta=$rs->campos["minutosfin"];
		if($minutoshasta==0) $minutoshasta="00";
		$minutoshasta.=" ".$tbampm[$rs->campos["ampmfin"]];
		$cont=0;
		while($hexhoradesde>0){
			$cont++;
			$hexhoradesde=$hexhoradesde>>1;
		}
		$horadesde=$cont-1;
		$cont=0;
		while($hexhorahasta>0){
			$cont++;
			$hexhorahasta=$hexhorahasta>>1;
		}
		$horahasta=$cont-1;

		$mulmin=floor($minutosdesde/15);
		$currentminutos=$mulmin*15;
		$CntDia[(int)$rs->campos["ampmini"]][(int)$horadesde][(int)$currentminutos]=1;
		$mulmin=floor($minutoshasta/15);
		$currentminutos=$mulmin*15;
		$CntDia[(int)$rs->campos["ampmfin"]][(int)$horahasta][(int)$currentminutos]=0;

		$AuxcadenaXML.='<OCUPACION ';
		// Atributos		
		$AuxcadenaXML.=' imagenodo="../images/iconos/reloj.gif"';
		$AuxcadenaXML.=' infonodo="%3Cb%3E'.$TbMsg[19].':%3C/b%3E '.$horadesde.":".$minutosdesde." - ".$horahasta.":".$minutoshasta.'"';
		$AuxcadenaXML.=' nodoid=opcupacion-'.$horadesde.":".$minutosdesde." - ".$horahasta.":".$minutoshasta;
		$AuxcadenaXML.=' colornodo='."#000000";
		$AuxcadenaXML.=' fondonodo='."#FBECFA;";
		$AuxcadenaXML.='>';
		$AuxcadenaXML.='</OCUPACION>';

		$idaula=$rs->campos["idaula"];
		$nombreaula=$rs->campos["nombreaula"];
		$AuxcadenaXML.='<AULA ';
			// Atributos		
		$AuxcadenaXML.=' imagenodo="../images/iconos/aula.gif"';
		$AuxcadenaXML.=' infonodo="%3Cb%3E'.$TbMsg[24].':%3C/b%3E '.$nombreaula.'"';;
		$AuxcadenaXML.=' nodoid=aula-'.$rs->campos["idaula"];
		$AuxcadenaXML.=' colornodo='."#000000";
		$AuxcadenaXML.=' fondonodo='."#FBECFA;";
		$AuxcadenaXML.='>';
		$AuxcadenaXML.='</AULA>';

		$nombreimagen=$rs->campos["nombreimagen"];
		if(empty($nombreimagen) ) $nombreimagen=$TbMsg[23];
		// Descripción de la imagen a restaurar
		$AuxcadenaXML.='<IMAGEN ';
		// Atributos		
		$AuxcadenaXML.=' imagenodo="../images/iconos/imagenes.gif"';
		$AuxcadenaXML.=' infonodo="%3Cb%3E'.$TbMsg[20].':  %3C/b%3E'.$nombreimagen.'"';
		$AuxcadenaXML.=' nodoid=imagen-'.$rs->campos["idimagen"];
		$AuxcadenaXML.=' colornodo='."#000000";
		$AuxcadenaXML.=' fondonodo='."#FBECFA;";
		$AuxcadenaXML.='>';
		$AuxcadenaXML.='</IMAGEN>';

		$nombretarea=$rs->campos["nombretarea"];
		if(!empty($nombretarea) ){
			// Descripción de la tarea a restaurar
			$AuxcadenaXML.='<TAREA ';
			// Atributos		
			$AuxcadenaXML.=' imagenodo="../images/iconos/tareas.gif"';
			$AuxcadenaXML.=' infonodo="%3Cb%3E'.$TbMsg[28].':  %3C/b%3E'.$nombretarea.'"';
			$AuxcadenaXML.=' nodoid=tarea-'.$rs->campos["idtarea"];
			$AuxcadenaXML.=' colornodo='."#000000";
			$AuxcadenaXML.=' fondonodo='."#FBECFA;";
			$AuxcadenaXML.='>';
			$AuxcadenaXML.='</TAREA>';
		}
		$nombretrabajo=$rs->campos["nombretrabajo"];
		if(!empty($nombretrabajo) ){
			// Descripción del  trabajo a restaurar
			$AuxcadenaXML.='<TRABAJO ';
			// Atributos		
			$AuxcadenaXML.=' imagenodo="../images/iconos/trabajos.gif"';
			$AuxcadenaXML.=' infonodo="%3Cb%3E'.$TbMsg[29].':  %3C/b%3E'.$nombretrabajo.'"';
			$AuxcadenaXML.=' nodoid=trabajo-'.$rs->campos["idtrabajo"];
			$AuxcadenaXML.=' colornodo='."#000000";
			$AuxcadenaXML.=' fondonodo='."#FBECFA;";
			$AuxcadenaXML.='>';
			$AuxcadenaXML.='</TRABAJO>';
		}
		$AuxcadenaXML.='<EMAIL ';
		// Atributos		
		$AuxcadenaXML.=' imagenodo="../images/iconos/email.gif"';
		$AuxcadenaXML.=' infonodo="%3Cb%3E'.$TbMsg[25].':%3C/b%3E&nbsp;'.$rs->campos["solicitante"].' &nbsp;(%3CA href=mailto:'.$rs->campos["email"].'%3E'.$rs->campos["email"].'%3C/A%3E)"';
		$AuxcadenaXML.=' nodoid=email-'.$idaula;
		$AuxcadenaXML.=' colornodo='."#000000";
		$AuxcadenaXML.=' fondonodo='."#FBECFA;";
		$AuxcadenaXML.='>';
		$AuxcadenaXML.='</EMAIL>';


	//$AuxcadenaXML.='</RESERVA> ';
	$AuxcadenaXML.='</RESERVAS>';
	return($AuxcadenaXML);
}
//________________________________________________________________________________________________________
function CalculaMinutos($rs){
	$hexhoradesde=$rs->campos["horasini"];
	$hexhorahasta=$rs->campos["horasfin"];
	$cont=0;
	while($hexhoradesde>0){
		$cont++;
		$hexhoradesde=$hexhoradesde>>1;
	}
	$shorasini=$cont-1;
	$cont=0;
	while($hexhorahasta>0){
		$cont++;
		$hexhorahasta=$hexhorahasta>>1;
	}
	$shorasfin=$cont-1;
	$sminutosini=$rs->campos["minutosini"];
	$sminutosfin=$rs->campos["minutosfin"];
	if($rs->campos["ampmini"]==1) $shorasini+=12;
	if($rs->campos["ampmfin"]==1) $shorasfin+=12;
	$socupacion=($shorasfin-$shorasini)*60+($sminutosfin-$sminutosini);
	return($socupacion);
}
/*________________________________________________________________________________________________________
	Recorrea los distintos ámbitos
________________________________________________________________________________________________________*/
function RecorreCentro($cmd){
	global $AMBITO_CENTROS;
	global $LITAMBITO_CENTROS;
	global $AMBITO_GRUPOSAULAS;
	global $idambito,$nombreambito;

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	if(!$rs->EOF){
		$idcentro=$rs->campos["idcentro"];
		$cmd->texto="SELECT idgrupo FROM grupos WHERE idcentro=".$idcentro." AND grupoid=0  AND tipo=".$AMBITO_GRUPOSAULAS;
		RecorreGruposAulas($cmd);
		$cmd->texto="SELECT idaula FROM aulas WHERE idcentro=".$idcentro." AND grupoid=0";
		RecorreAulas($cmd);
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreGruposAulas($cmd){
	global $AMBITO_GRUPOSAULAS;
	global $LITAMBITO_GRUPOSAULAS;

	$rs=new Recordset; 
	$cmd->texto.="ORDER by nombregrupo"; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$idgrupo=$rs->campos["idgrupo"];
		$cmd->texto="SELECT idgrupo FROM grupos WHERE grupoid=".$idgrupo ." AND tipo=".$AMBITO_GRUPOSAULAS;
		RecorreGruposAulas($cmd);
		$cmd->texto="SELECT idaula FROM aulas WHERE  grupoid=".$idgrupo;
		RecorreAulas($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreAulas($cmd){
	global $AMBITO_AULAS;
	global $LITAMBITO_AULAS;
	global $cadenaaulas;

	$rs=new Recordset; 
	$cmd->texto.="ORDER by nombreaula"; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$idaula=$rs->campos["idaula"];
		$cadenaaulas.=$idaula.",";
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreGruposReservas($cmd){
	global $AMBITO_GRUPOSRESERVAS;
	global $LITAMBITO_GRUPOSRESERVAS;

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$idgrupo=$rs->campos["idgrupo"];
		$cmd->texto="SELECT idgrupo FROM grupos WHERE grupoid=".$idgrupo ." AND tipo=".$AMBITO_GRUPOSRESERVAS;
		RecorreGruposReservas($cmd);
		$cmd->texto="SELECT idreserva FROM reservas WHERE  grupoid=".$idgrupo;
		RecorreReservas($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreReservas($cmd){
	global $AMBITO_RESERVAS;
	global $LITAMBITO_RESERVAS;
	global $cadenareservas;

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$idreserva=$rs->campos["idreserva"];
		$cadenareservas.=$idreserva.",";
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
?>