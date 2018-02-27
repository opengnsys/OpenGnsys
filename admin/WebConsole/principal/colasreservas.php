<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero: colasreservas.php
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
include_once("../idiomas/php/".$idioma."/colasreservas_".$idioma.".php");
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
	$auxP=explode(";",$estadoreserva);
	$cont=0;
	for ($i=0;$i<sizeof($auxP)-1;$i++){
		$dualparam=explode("=",$auxP[$i]);
		$westadoreserva[$cont++]=$dualparam[0];
	}
}
if (isset($_POST["situacion"])){
	$situacion=$_POST["situacion"]; 
	$auxP=explode(";",$situacion);
	$cont=0;
	for ($i=0;$i<sizeof($auxP)-1;$i++){
		$dualparam=explode("=",$auxP[$i]);
		$wsituacion[$cont++]=$dualparam[0];
	}
}
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/colasreservas.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/colasreservas_'.$idioma.'.js"></SCRIPT>'?>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>

</HEAD>
<BODY>
<?php
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
}
echo '<p align=center class=cabeceras><img src="../images/iconos/reservas.gif">&nbsp;'.$TbMsg[0].'&nbsp;';
echo '<FORM name="fdatos" action="colasreservas.php" method="post">'.chr(13);
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
// Localiza las aulas pertenecientes al ámbito

$cadenaaulas="";
$cont_aulas=0; 

$sw=false;
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
}
//________________________________________________________________________________________________________
// Proceso de selección de reservas
$ClausulaWhere="";
//________________________________________________________________________________________________________
// Cuestion identificador del Centro que ha ejecutado la acción
$WhereCentroAccion="";
$WhereCentroAccion='reservas.idcentro='.$idcentro;
$ClausulaWhere.=" AND (".$WhereCentroAccion.")";
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
$cadenaaulas=substr($cadenaaulas,0,strlen($cadenaaulas)-1); // Quita la coma
$ClausulaWhere.=" AND idaula in(".$cadenaaulas.")";
//________________________________________________________________________________________________________
// Cuestion de fechas 
if(empty($fechainicio)) $fechainicio=date("d/m/Y",mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
if(empty($fechafin))	$fechafin=date("d/m/Y",mktime(0, 0, 0, date("m")+1  , date("d"), date("Y")));

list($dia_i,$mes_i,$anno_i)=explode("/",$fechainicio);
list($dia_f,$mes_f,$anno_f)=explode("/",$fechafin);

// Elimina registros en tabla temporal
$cmd->texto="DELETE FROM  reservastemporal where idcentro=".$idcentro." AND usuario='".$usuario."'"; // Elimina todos los registros de la tabla temporal
$resul=$cmd->Ejecutar();

//Recorre de fecha inicio a fecha fin
$calendario=new Calendario("tabla_reservas");
$anno_c=$anno_i;
$mes_c=$mes_i;

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
			$HEXmeses=$HEXmeses | $calendario->nombre_mes[$mes_c][1];
			$mes_c++;
	}
	// Cadena SQL para seleccionar reservas
	$cmd->texto="SELECT reservas.idreserva,reservas.descripcion,programaciones.idprogramacion, programaciones.nombrebloque,programaciones.annos, programaciones.meses, programaciones.diario, programaciones.dias, programaciones.semanas, programaciones.horasini, programaciones.ampmini, programaciones.minutosini, programaciones.horasfin, programaciones.ampmfin, programaciones.minutosfin FROM   reservas";
	$cmd->texto.=" INNER JOIN programaciones ON reservas.idreserva = programaciones.identificador";
	$cmd->texto.=" WHERE (programaciones.tipoaccion = ".$EJECUCION_RESERVA.") ".$ClausulaWhere ;
	$cmd->texto.=" AND (programaciones.annos & ".$HEXanno."<>0)";
	$cmd->texto.=" AND (programaciones.meses & ".$HEXmeses."<>0)" ;
	CreaReservasTemporal($cmd,$anno_c,$mes_desde,$mes_hasta,$dia_i,$dia_f,$mes_i,$mes_f,$anno_i,$anno_f);
	$anno_c++;
}
$cadenaXML="";
$cont_a=0;
$cont_m=0;
$cont_d=0;
$HTMLannos="";
$HTMLmeses="";
$HTMLdias="";
$HTMLhoras="";

$sw=true;
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
}

// Creación del árbol
$baseurlimg="../images/tsignos"; // Url de las imágenes de signo
$clasedefault="texto_arbol";
$titulotabla=$TbMsg[0];  
$arbol=new ArbolVistaXml($cadenaXML,0,$baseurlimg,$clasedefault,2,20,270,2,$titulotabla);
$salidaHTML=$arbol->CreaArbolVistaXml();  // Muestra  árbol
echo "<BR>";
echo "<DIV align=center width=100%>";
echo "	 <TABLE align=center width=100%>";
echo "		<TR><TD>";
echo urldecode($salidaHTML);
echo "		</TD></TR><TABLE></DIV>";

//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?php
// *************************************************************************************************************************************************
function CreaReservasTemporal($cmd,$anno_c,$mes_desde,$mes_hasta,$dia_i,$dia_f,$mes_i,$mes_f,$anno_i,$anno_f){
	global $usuario;
	global $idcentro;

	$fechaminima=mktime(0, 0, 0, $mes_i, 1, $anno_i);
	$fechamaxima=mktime(0, 0, 0, $mes_f, $dia_f, $anno_f);
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	// Recorre reservas
	while (!$rs->EOF){
		$cadenafechas=FechasReservas($anno_c,$mes_desde,$mes_hasta,$rs->campos["meses"],$rs->campos["diario"],$rs->campos["dias"],$rs->campos["semanas"]);
		$fechas_reservas=explode(";",$cadenafechas);
		for ($i=0;$i<sizeof($fechas_reservas)-1;$i++){
				list($auxdia,$auxmes,$auxanno)=explode("/",$fechas_reservas[$i]);
				$auxfecha=mktime(0, 0, 0, $auxmes,$auxdia, $auxanno);

				if($auxfecha>=$fechaminima &&  $auxfecha<=$fechamaxima){
					$cmd->texto="INSERT INTO reservastemporal(idcentro,usuario,idprogramacion,idreserva,fecha) VALUES (".$idcentro.",'".$usuario."',".$rs->campos["idprogramacion"].",".$rs->campos["idreserva"].",'".$fechas_reservas[$i]."')";
					$resul=$cmd->Ejecutar();
				}
		}
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
// Función : FechasReservas
// Descripción :
//		Devuelve una cadena de fechas separada por comas que son precisamente  las fechas que forman parte de una reserva concreta
//	Parametros: 
//		- anno_c: Un año determinado
//		- mes_desde: El mes desde que se considera la reserva
//		- mes_hasta: El mes hasta que se considera la reserva
//		- meses: Campo con información hexadecimal de los meses de la reserva ( la información contenida en el campo de la tabla con este nombre
//		- diario:  Idem para los dias de un mes
//		- dias: idem para los nombres de los días
//		- semanas: Idem para las semanas
//________________________________________________________________________________________________________
function FechasReservas($anno_c,$mes_desde,$mes_hasta,$meses,$diario,$dias,$semanas){
	global $calendario;

	$cadenafechas="";
	$mascara=0x0001;
	$cadenameses="";
	$meses=$meses>>($mes_desde-1);
	for($i=$mes_desde;$i<=$mes_hasta;$i++){
		if($meses&$mascara>0){
			$cadenameses.=$i.";";
			// Dias de la semAna
			if($dias>0){
				$auxdias=$dias;
				for($j=1;$j<=7;$j++){
					if($auxdias&$mascara>0){
						$cadenadias=$calendario->DiasPorMes($i,$anno_c,$j);
						$tbdias=explode(";",$cadenadias);
						for ($k=0;$k<sizeof($tbdias)-1;$k++)
							$cadenafechas.=$tbdias[$k]."/".$i."/".$anno_c.";";
					}
					$auxdias=$auxdias>>1;
				}
			}
			// Semanas
			if($semanas>0){
				$auxsemanas=$semanas;
				for($j=1;$j<=6;$j++){
					if($auxsemanas&$mascara>0){
						if($j==6){
							$ulse=$calendario->UltimaSemana($i,$anno_c);
							$cadenadias=$calendario->DiasPorSemanas($i,$anno_c,$ulse);
						}
						else
							$cadenadias=$calendario->DiasPorSemanas($i,$anno_c,$j);
						$tbdias=explode(";",$cadenadias);
						for ($k=0;$k<sizeof($tbdias)-1;$k++)
							$cadenafechas.=$tbdias[$k]."/".$i."/".$anno_c.";";
					}
					$auxsemanas=$auxsemanas>>1;
				}
			}
		}
		$meses=$meses>>1;
	}
	$cadenadiario="";
	for($i=1;$i<32;$i++){
			if($diario&$mascara>0) $cadenadiario.=$i.";";
			$diario=$diario>>1;
	}
	$tbmeses=explode(";",$cadenameses);
	$tbdiario=explode(";",$cadenadiario);
	for ($i=0;$i<sizeof($tbmeses)-1;$i++){
		for ($j=0;$j<sizeof($tbdiario)-1;$j++){
			$cadenafechas.=$tbdiario[$j]."/".$tbmeses[$i]."/".$anno_c.";";
		}
	}
	return($cadenafechas);
}
/*________________________________________________________________________________________________________
	Recorrea los distintos ámbitos
________________________________________________________________________________________________________*/
function RecorreCentro($cmd){
	global $AMBITO_CENTROS;
	global $LITAMBITO_CENTROS;
	global $AMBITO_GRUPOSAULAS;
	global $cadenaXML;
	global $sw;
	global $idambito,$nombreambito;

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	if(!$rs->EOF){
		if($sw) {
			$cadenaXML.='<CENTRO';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/centros.gif"';
			$cadenaXML.=' nodoid='.$LITAMBITO_CENTROS."-".$idambito;
			$cadenaXML.=' infonodo='.$nombreambito;
			$cadenaXML.='>';
		}
		$idcentro=$rs->campos["idcentro"];
		$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE idcentro=".$idcentro." AND grupoid=0  AND tipo=".$AMBITO_GRUPOSAULAS;
		RecorreGruposAulas($cmd);
		$cmd->texto="SELECT idaula,nombreaula,horaresevfin FROM aulas WHERE idcentro=".$idcentro." AND grupoid=0";
		RecorreAulas($cmd);
		if($sw) $cadenaXML.='</CENTRO>';

	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreGruposAulas($cmd){
	global $AMBITO_GRUPOSAULAS;
	global $LITAMBITO_GRUPOSAULAS;
	global $cadenaXML;
	global $sw;

	$rs=new Recordset; 
	$cmd->texto.="ORDER by nombregrupo"; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 

	while (!$rs->EOF){
		if($sw) {
			$cadenaXML.='<GRUPOSAULAS';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
			$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSAULAS."-".$rs->campos["idgrupo"];
			$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
			$cadenaXML.='>';
		}
		$idgrupo=$rs->campos["idgrupo"];
		$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE grupoid=".$idgrupo ." AND tipo=".$AMBITO_GRUPOSAULAS;
		RecorreGruposAulas($cmd);
		$cmd->texto="SELECT idaula,nombreaula,horaresevfin FROM aulas WHERE  grupoid=".$idgrupo;
		RecorreAulas($cmd);
		$rs->Siguiente();
		if($sw) $cadenaXML.='</GRUPOSAULAS>';
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreAulas($cmd){
	global $AMBITO_AULAS;
	global $LITAMBITO_AULAS;
	global $cadenaaulas;
	global $cont_aulas;
	global $sw;
	global $cadenaXML;

	$rs=new Recordset; 
	$cmd->texto.="ORDER by nombreaula"; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 

	while (!$rs->EOF){
		if($sw && $rs->campos["horaresevfin"]>0) {
			$cadenaXML.='<AULA ';
			// Atributos		
			$cadenaXML.=' imagenodo="../images/iconos/aula.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["nombreaula"].'"';
			$cadenaXML.=' nodoid='.$LITAMBITO_AULAS.'-'.$rs->campos["idaula"];
			$cadenaXML.=' colornodo='."#000000";
			$cadenaXML.=' fondonodo='."#B5DAAD;";
			$cadenaXML.='>';
		}
		$idaula=$rs->campos["idaula"];
		$cadenaaulas.=$idaula.",";
		$cont_aulas++;
		if($sw && $rs->campos["horaresevfin"]>0) ListaReservas($cmd,$idaula,$rs->campos["nombreaula"]);
		$rs->Siguiente();
		if($sw && $rs->campos["horaresevfin"]>0) $cadenaXML.='</AULA>';
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function ListaReservas($cmd,$idaula,$nombreaula){
	global $idcentro;
	global $usuario;
	global $TbMsg;
	global $calendario;
	global $RESERVA_CONFIRMADA;
	global $RESERVA_PENDIENTE;
	global $RESERVA_DENEGADA;
	global $EJECUCION_RESERVA;

	global $AMBITO_CENTROS;
	global $AMBITO_GRUPOSAULAS;
	global $AMBITO_AULAS;

	global $cadenaXML;

	global $cont_a;
	global $cont_m;
	global $cont_d;
	global $HTMLannos;
	global $HTMLmeses;
	global $HTMLdias;
	global $HTMLhoras;

	$tbimg[$RESERVA_CONFIRMADA]='../images/iconos/confirmadas.gif';
	$tbimg[$RESERVA_PENDIENTE]='../images/iconos/pendientes.gif';
	$tbimg[$RESERVA_DENEGADA]='../images/iconos/denegadas.gif';

	$tbampm[0]="a.m.";
	$tbampm[1]="p.m.";

	$cmd->texto="SELECT  DISTINCT aulas.horaresevini,aulas.horaresevfin,reservastemporal.idreserva, reservastemporal.fecha,programaciones.horasini,programaciones.ampmini,programaciones.minutosini,trabajos.idtrabajo,tareas.idtarea,trabajos.descripcion AS nombretrabajo,tareas.descripcion AS nombretarea,reservas.solicitante,reservas.email,reservas.estado,reservas.idaula,reservas.idreserva,reservas.descripcion,DAY(reservastemporal.fecha) as dia,MONTH(reservastemporal.fecha) as mes,YEAR(reservastemporal.fecha) as anno,reservas.descripcion,aulas.nombreaula as nombreaula,imagenes.idimagen ,imagenes.descripcion as nombreimagen,";
	$cmd->texto.=" programaciones.horasini,programaciones.minutosini,programaciones.horasfin,programaciones.minutosfin,programaciones.ampmini,programaciones.ampmfin";
	$cmd->texto.="	FROM   reservas";
	$cmd->texto.=" INNER JOIN reservastemporal ON reservas.idreserva = reservastemporal.idreserva";
	$cmd->texto.=" INNER JOIN aulas ON reservas.idaula = aulas.idaula";
	$cmd->texto.=" LEFT OUTER JOIN imagenes ON reservas.idimagen = imagenes.idimagen";
	$cmd->texto.=" LEFT OUTER JOIN tareas ON reservas.idtarea = tareas.idtarea";
	$cmd->texto.=" LEFT OUTER JOIN trabajos ON reservas.idtrabajo = trabajos.idtrabajo";
	$cmd->texto.=" INNER JOIN programaciones ON reservastemporal.idprogramacion = programaciones.idprogramacion";
	$cmd->texto.=" WHERE (programaciones.tipoaccion = ".$EJECUCION_RESERVA.") ";
	$cmd->texto.="  AND (aulas.idaula = ".$idaula.") ";
	$cmd->texto.="  AND (reservastemporal.idcentro='".$idcentro."') ";
	$cmd->texto.="  AND (reservastemporal.usuario='".$usuario."') ";
	$cmd->texto.=" ORDER by reservastemporal.fecha,programaciones.ampmini,programaciones.horasini,programaciones.minutosini";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 

	$calendario->aula=$idaula;
	$calendario->horaresevini=$rs->campos["horaresevini"];
	$calendario->horaresevfin=$rs->campos["horaresevfin"];
	$swa=false;
	$swm=false;
	$swd=false;
	$ganno=0;
	$gmes=0;
	$gdia=0;

	$cont_a=0;
	$HTMLannos="";

	// Recorre reservas temporales
	while (!$rs->EOF){
		// Año
		if($ganno<>$rs->campos["anno"]){
			if($swd){
				GuardaHorasDias($ganno,$gmes,$gdia,$CntDia,$CntMes);
				$cadenaXML.='</DIA> ';
			}
			if($swm){
				GuardaMesAnno($ganno,$gmes,$CntMes);
				$cadenaXML.='</MES> ';
			}
			if($swa){
				GuardaAnno($ganno);
				$cadenaXML.='</ANNO> ';
			}
			$ganno=$rs->campos["anno"];
			$cadenaXML.='<ANNO ';
			// Atributos		
			$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
			$cadenaXML.=' infonodo="%3Cb%3E&nbsp; '.$TbMsg[15].': %3C/b%3E'.$ganno.'"';
			$cadenaXML.=' nodoid=nodoanno-'.$ganno;
			$cadenaXML.=' colornodo='."#000000";
			$cadenaXML.=' fondonodo='."#B5B7B9;";
			$cadenaXML.='>';
			$gmes=0;
			$gdia=0;
			$swa=true;
			$swm=false;
			$swd=false;
			PintaAnno($ganno);
			$HTMLmeses="";
			$cont_m=0;
		}
	
		// Mes
		if($gmes<>$rs->campos["mes"]){
			if($swd){
				GuardaHorasDias($ganno,$gmes,$gdia,$CntDia,$CntMes);
				$cadenaXML.='</DIA> ';
			}
			if($swm){
				GuardaMesAnno($ganno,$gmes,$CntMes);
				$cadenaXML.='</MES> ';
			}
			$gmes=$rs->campos["mes"];
			$nombremes=$calendario->nombre_mes[$rs->campos["mes"]][0];

			$cadenaXML.='<MES ';
			// Atributos		
			$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
			$cadenaXML.=' infonodo="%3CA name=anodomes-'.$idaula."-".$gmes.'/'.$ganno.'%3E%3Cb%3E&nbsp; '.$TbMsg[16].': %3C/b%3E'.$nombremes.' %3C/A%3E "';
			$cadenaXML.=' nodoid=nodomes-'.$idaula."-".$gmes.'/'.$ganno;
			$cadenaXML.=' colornodo='."#000000";
			$cadenaXML.=' fondonodo='."#E3D8C6";
			$cadenaXML.='>';
			$gdia=0;
			$swm=true;
			$swd=false;
			$CntMes="";
			PintaMesAnno($ganno,$gmes);
			$HTMLdias="";
			$cont_d=0;
		}
		// Dia
		if($gdia<>$rs->campos["dia"]){
			if($swd){
				GuardaHorasDias($ganno,$gmes,$gdia,$CntDia,$CntMes);
				$cadenaXML.='</DIA> ';
			}
			$gdia=$rs->campos["dia"];
			$nombredia=$rs->campos["dia"]." - ".$nombremes." - ".$rs->campos["anno"];
			$cadenaXML.='<DIA ';
			// Atributos		
			$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
			$cadenaXML.=' infonodo="%3CA name=anododia-'.$idaula."-".$gdia.'/'.$gmes.'/'.$ganno.'%3E%3Cb%3E&nbsp;'.$TbMsg[17].': %3C/b%3E'.$nombredia.' %3C/A%3E "';
			$cadenaXML.=' nodoid=nododia-'.$idaula."-".$gdia.'/'.$gmes.'/'.$ganno;
			$cadenaXML.=' colornodo='."#000000";
			$cadenaXML.=' fondonodo='."#CFDAE6";
			$cadenaXML.='>';
			$swd=true;
			//$CntMes[$gdia]=1;
			$CntDia="";
			PintaHorasDias($ganno,$gmes,$gdia);
			$HTMLhoras="";
		}
		
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


		// Descripción de la reserva
		$cadenaXML.='<RESERVA ';
		// Atributos		
		$cadenaXML.=' imagenodo="../images/iconos/reservas.gif"';
		$cadenaXML.=' infonodo="%3CIMG src='.$tbimg[$rs->campos["estado"]].'%3E&nbsp;%3Cb%3E'.$rs->campos["descripcion"].': %3C/b%3E';
		$cadenaXML.='&nbsp;%3CIMG src="../images/iconos/reloj.gif"%3E&nbsp;('.$horadesde.":".$minutosdesde." - ".$horahasta.":".$minutoshasta.')"';

		$cadenaXML.=' nodoid=reserva-'.$rs->campos["idreserva"];
		$cadenaXML.=' colornodo='."#000000";
		$cadenaXML.=' fondonodo='."#EEEECC;";
		$cadenaXML.='>';

		$cadenaXML.='<OCUPACION ';
		// Atributos		
		$cadenaXML.=' imagenodo="../images/iconos/reloj.gif"';
		$cadenaXML.=' infonodo="%3Cb%3E'.$TbMsg[19].':%3C/b%3E '.$horadesde.":".$minutosdesde." - ".$horahasta.":".$minutoshasta.'"';
		$cadenaXML.=' nodoid=opcupacion-'.$horadesde.":".$minutosdesde." - ".$horahasta.":".$minutoshasta;
		$cadenaXML.=' colornodo='."#000000";
		$cadenaXML.=' fondonodo='."#FBECFA;";
		$cadenaXML.='>';
		$cadenaXML.='</OCUPACION>';


		$cadenaXML.='<AULA ';
		// Atributos		
		$cadenaXML.=' imagenodo="../images/iconos/aula.gif"';
		$cadenaXML.=' infonodo="%3Cb%3E'.$TbMsg[24].':%3C/b%3E '.$nombreaula.'"';;
		$cadenaXML.=' nodoid=aula-'.$idaula;
		$cadenaXML.=' colornodo='."#000000";
		$cadenaXML.=' fondonodo='."#FBECFA;";
		$cadenaXML.='>';
		$cadenaXML.='</AULA>';

		$nombreimagen=$rs->campos["nombreimagen"];
		if(empty($nombreimagen) ) $nombreimagen=$TbMsg[23];
		// Descripción de la imagen a restaurar
		$cadenaXML.='<IMAGEN ';
		// Atributos		
		$cadenaXML.=' imagenodo="../images/iconos/imagenes.gif"';
		$cadenaXML.=' infonodo="%3Cb%3E'.$TbMsg[20].':  %3C/b%3E'.$nombreimagen.'"';
		$cadenaXML.=' nodoid=imagen-'.$rs->campos["idimagen"];
		$cadenaXML.=' colornodo='."#000000";
		$cadenaXML.=' fondonodo='."#FBECFA;";
		$cadenaXML.='>';
		$cadenaXML.='</IMAGEN>';

		$nombretarea=$rs->campos["nombretarea"];
		if(!empty($nombretarea) ){
			// Descripción de la tarea a restaurar
			$cadenaXML.='<TAREA ';
			// Atributos		
			$cadenaXML.=' imagenodo="../images/iconos/tareas.gif"';
			$cadenaXML.=' infonodo="%3Cb%3E'.$TbMsg[28].':  %3C/b%3E'.$nombretarea.'"';
			$cadenaXML.=' nodoid=tarea-'.$rs->campos["idtarea"];
			$cadenaXML.=' colornodo='."#000000";
			$cadenaXML.=' fondonodo='."#FBECFA;";
			$cadenaXML.='>';
			$cadenaXML.='</TAREA>';
		}

		$nombretrabajo=$rs->campos["nombretrabajo"];
		if(!empty($nombretrabajo) ){
			// Descripción del  trabajo a restaurar
			$cadenaXML.='<TRABAJO ';
			// Atributos		
			$cadenaXML.=' imagenodo="../images/iconos/trabajos.gif"';
			$cadenaXML.=' infonodo="%3Cb%3E'.$TbMsg[29].':  %3C/b%3E'.$nombretrabajo.'"';
			$cadenaXML.=' nodoid=trabajo-'.$rs->campos["idtrabajo"];
			$cadenaXML.=' colornodo='."#000000";
			$cadenaXML.=' fondonodo='."#FBECFA;";
			$cadenaXML.='>';
			$cadenaXML.='</TRABAJO>';
		}

		$cadenaXML.='<EMAIL ';
		// Atributos		
		$cadenaXML.=' imagenodo="../images/iconos/email.gif"';
		$cadenaXML.=' infonodo="%3Cb%3E'.$TbMsg[25].':%3C/b%3E&nbsp;'.$rs->campos["solicitante"].' &nbsp;(%3CA href=mailto:'.$rs->campos["email"].'%3E'.$rs->campos["email"].'%3C/A%3E)"';
		$cadenaXML.=' nodoid=email-'.$idaula;
		$cadenaXML.=' colornodo='."#000000";
		$cadenaXML.=' fondonodo='."#FBECFA;";
		$cadenaXML.='>';
		$cadenaXML.='</EMAIL>';



		$cadenaXML.='</RESERVA>';

		$rs->Siguiente();
	}
	if($swd){
				GuardaHorasDias($ganno,$gmes,$gdia,$CntDia,$CntMes);
		$cadenaXML.='</DIA> ';
	}
	if($swm){
		GuardaMesAnno($ganno,$gmes,$CntMes);
		$cadenaXML.='</MES> ';
	}
	if($swa){
		GuardaAnno($ganno);
		$cadenaXML.='</ANNO> ';
	}
}
//________________________________________________________________________________________________________
function PintaAnno($ganno){
	global $cadenaXML;
	global $cont_a;

	$cadenaXML.='<TBANNO ';
	// Atributos		
	$cadenaXML.=' imagenodo="../images/iconos/nada.gif"';
	$cadenaXML.=' infonodo=%anno-'.$ganno.'%';
	$cadenaXML.=' nodoid=anno-'.$ganno;
	$cadenaXML.=' fondonodo='."#FFFFFF";
	$cadenaXML.='>';
	$cadenaXML.='</TBANNO> ';
}
//________________________________________________________________________________________________________
function GuardaAnno($ganno){
	global $cadenaXML;
	global $cont_a;
	global $cont_m;
	global $HTMLmeses;
	global $HTMLannos;
	global $calendario;

$HTMLannos[$cont_a]="<TABLE><TR>";
$j=0;
	for($i=0;$i<$cont_m;$i++){
		if(isset($HTMLmeses[$i])){
			$HTMLannos[$cont_a].='<TD style="BACKGROUND-COLOR:#FFFFFF" valign=top>'.$HTMLmeses[$i].'</TD>';
			$j++;
			if($j==4) {
				$HTMLannos[$cont_a].="</TR><TR>";
				$j=0;
			}
		}
	}
	$HTMLannos[$cont_a].="</TR></TABLE>";
	$cadenaXML=preg_replace("/%anno-".$ganno.'%/',urlencode($HTMLannos[$cont_a]), $cadenaXML );
	$cont_a++;
}
//________________________________________________________________________________________________________
function PintaMesAnno($ganno,$gmes){
	global $cadenaXML;

	$cadenaXML.='<TBMES ';
	// Atributos		
	$cadenaXML.=' imagenodo="../images/iconos/nada.gif"';
	$cadenaXML.=' infonodo=%mes-'.$ganno.'-'.$gmes.'%';
	$cadenaXML.=' nodoid=mes-'.$ganno.'-'.$gmes;
	$cadenaXML.=' fondonodo='."#FFFFFF";
	$cadenaXML.='>';
	$cadenaXML.='</TBMES> ';
}
//________________________________________________________________________________________________________
function GuardaMesAnno($ganno,$gmes,$CntMes){
	global $cadenaXML;
	global $cont_m;
	global $calendario;
	global $HTMLmeses;

	$HTMLmeses[$cont_m]="<TABLE cellspacing=3><TR><TD valign=top>";
	$HTMLmeses[$cont_m].=$calendario->MesAnno($gmes,$ganno,$CntMes);
	$HTMLmeses[$cont_m].="</TD></TR></TABLE>";
	$cadenaXML=preg_replace('/%mes-'.$ganno.'-'.$gmes.'%/',urlencode($HTMLmeses[$cont_m]), $cadenaXML );
	$cont_m++;
}
//________________________________________________________________________________________________________
function PintaHorasDias($ganno,$gmes,$gdia){
	global $cadenaXML;

	$cadenaXML.='<TBDIA ';
	// Atributos		
	$cadenaXML.=' imagenodo="../images/iconos/nada.gif"';
	$cadenaXML.=' infonodo=%horas-'.$ganno.'-'.$gmes.'-'.$gdia.'%';
	$cadenaXML.=' nodoid=horas-'.$ganno.'-'.$gmes.'-'.$gdia;
	$cadenaXML.=' fondonodo='."#FFFFFF";
	$cadenaXML.='>';
	$cadenaXML.='</TBDIA> ';
}
//________________________________________________________________________________________________________
function GuardaHorasDias($ganno,$gmes,$gdia,$CntDia,&$CntMes){
	global $cadenaXML;
	global $calendario;
	global $HTMLhorasdias;

	$HTMLhorasdias="<TABLE cellspacing=3><TR><TD valign=top>";
	$HTMLhorasdias.=$calendario->HorasDias($CntDia,$porcenhoras);
	$HTMLhorasdias.="</TD></TR></TABLE>";

	$CntMes[$gdia]=$porcenhoras;
	$cadenaXML=preg_replace('/%horas-'.$ganno.'-'.$gmes.'-'.$gdia.'%/',urlencode($HTMLhorasdias), $cadenaXML );
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
}?>
