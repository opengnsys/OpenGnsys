<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: Alberto García Padilla (UMA - Universidad de Málaga)
// Fecha Creación: Año 2020
// Fecha Última modificación: Mayo-2020
// Nombre del fichero: MoverordenadoresAulas.php
// Descripción : 
//		Implementación del Reubicador de masivo de ordenadores entre Aulas
// fecha 2020/05/01
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../includes/TomaDato.php");
include_once("../includes/RecopilaIpesMacs.php");
include_once("../includes/opcionesprotocolos.php");
include_once("../idiomas/php/".$idioma."/comandos/moverordenadoresAulas_".$idioma.".php");
//________________________________________________________________________________________________________
//include_once("./includes/capturaacciones.php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
//
// Captura parámetros
//________________________________________________________________________________________________________
$ambito=0;
$idambito=0;
$nombreambito=0;
$movordaul=0;
$moverordenadoresAulas=0;
$confmovord="no";

if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
$ambito="4";
if (isset($_GET["nombreambito"])) $nombreambito=$_GET["nombreambito"]; 
if ($_POST["funcion"] == "si"){$confmovord=$_POST["funcion"];} 
if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
if (isset($_POST["ambito"])) $ambito=$_POST["ambito"]; 
if (isset($_POST["nombreambito"])) $nombreambito=$_POST["nombreambito"]; 


//________________________________________________________________________________________________________

// Buscamos el idcentro
$cmd->texto="SELECT idcentro FROM aulas WHERE idaula=$idambito";
$rs=new Recordset;
$rs->Comando=&$cmd; 
if (!$rs->Abrir()) return(true); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){	$idcentro=$rs->campos["idcentro"];}
	$rs->Cerrar();
//________________________________________________________________________________________________________
// Buscamos el idcentro
$cmd->texto="SELECT netmask FROM aulas WHERE idaula=$idambito";
$rs=new Recordset;
$rs->Comando=&$cmd; 
if (!$rs->Abrir()) return(true); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){	$netmaskaulaori=$rs->campos["netmask"];}
	$rs->Cerrar();
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
<TITLE>Administración web de aulas</TITLE>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<STYLE TYPE="text/css"></STYLE>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>

<script type="text/javascript">

function aceptar() {document.fdatos.submit();}
function comprobaridaula(){	alert( "<?php echo $TbMsg[4]?>" );}
function comprobarord(){	alert( "<?php echo $TbMsg[5]?>" );}
function actualiza_frame_principal(){
	window.parent.frames[2].location="../nada.php"
	window.parent.frames[1].location="../principal/aulas.php"
}
function comprobarnetmask(){
	alert( "<?php echo $TbMsg[6]?>" );
}

</script> 
</HEAD>
<BODY>
<?php
	//________________________________________________________________________________________________________
	echo '<BR>';
	echo '<p align=center><span class=cabeceras>'.$TbMsg[0].'&nbsp;</span><br>';
	//________________________________________________________________________________________________________
?>
		<FORM action="MoverordenadoresAulas.php" name="fdatos" method="POST">
		<?php
		//________________________________________________________________________________________________________
		include_once("./includes/FiltradoAmbitoMovAulas.php");
		//________________________________________________________________________________________________________
		?>
<?php
// Recorremos todos los checkbox del FiltroAmbito
// ##############################################
// Comprobamos si hay algun equipo seleccionado
for ( $i=0; $i<$num; $i++){
	$idordmov=$_POST["chk-".$i];
	if ( isset($idordmov) ){$sihaysel="si";break;}
}

//________________________________________________________________________________________________________

if ( isset($_POST['select_idaula']) ){
	$selectidaula=$_POST["select_idaula"]; 
	// Buscamos el idcentro
	$cmd->texto="SELECT netmask FROM aulas WHERE idaula=$selectidaula";
	$rs=new Recordset;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(true); // Error al abrir recordset
		$rs->Primero(); 
		if (!$rs->EOF){	$netmaskauladest=$rs->campos["netmask"];}
		$rs->Cerrar();
		
}

//________________________________________________________________________________________________________
if($confmovord == "si" ){
	if ( $netmaskaulaori != $netmaskauladest ){echo "<script>comprobarnetmask();</script>";}
	// ######################################
	// Abrimos la conexion a la base de datos
	$rsm=new Recordset;
	$rsm->Comando=&$cmd;
	//_______________________________________
	if (!$rsm->Abrir()) return; // Error al abrir recordset
	// Si son las Mismas Aulas salimos
		if ( $idambito != $selectidaula )
		{
			// Si NO hay equipos seleccionados salimos
			if ($sihaysel=="si"){
				$idaulamov=$_POST['select_idaula'];
				for ( $i=0; $i<$num; $i++){
					$idordmov=$_POST["chk-".$i];
						if ( isset($idordmov) ){
							###	AGP		######################################################################################
							// ##########################################
							// Aqui actualizamos los ordenadores
							// ##########################################
							$cmd->texto = "UPDATE ordenadores SET idaula=$idaulamov, grupoid=0 WHERE idordenador=$idordmov";
							$resulm=$cmd->Ejecutar();
							###	AGP		######################################################################################
						}
				}
			}else{echo "<script>comprobarord();</script>";}
		}else{
			echo "<script>comprobaridaula();</script>";
		}
$rsm->Cerrar();
$confmovord="no";
echo "<script>actualiza_frame_principal();</script>";
}

//________________________________________________________________________________________________________
?>
				<INPUT type="hidden" name="idambito" value="<?php echo $idambito?>">
				<INPUT type="hidden" name="ambito" value="<?php echo $ambito?>">	
				<INPUT type="hidden" name="cadenaid" value="<?php echo $cadenaid?>">				
				<INPUT type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
				<INPUT type="hidden" name="idcomando" value="<?php echo $idcomando?>">
				<INPUT type="hidden" name="descricomando" value="<?php echo $descricomando?>">
				<INPUT type="hidden" name="gestor" value="<?php echo $gestor;?>">
				<INPUT type="hidden" name="filtro" value="<?php echo $filtro;?>">
				<INPUT type="hidden" name="funcion" value="<?php echo "si";?>">
				<TABLE  name=masivo id=masivo align=center border=7 cellPadding=3 cellSpacing=1 class=tabla_listados >
					<TR>
						<TH align=center>&nbsp;<?php echo $TbMsg[3]?>&nbsp;</TH>
							<?php echo '<TD colspan=3>'.HTMLSELECT_aulas($cmd,$idcentro,$idambito).'</TD>'; ?>
					</TR>
				</TABLE>

				<TABLE align=center>
					<TR><TD width=300></TD></TR>
					<TR><TD width=300></TD></TR>
					<TR><TD width=300></TD></TR>
					<TR>
						<TH height=20 align="left" colspan=14>
						<A href=#><IMG border=0 src="../images/boton_confirmar_<?php echo $idioma ?>.gif" onClick="aceptar();"></A></TD>			
					</TR>
				</TABLE>
		</FORM>

<SCRIPT language="javascript">
	Sondeo();
</SCRIPT>
</BODY>
</HTML>
<?php
/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de las Aulas
// Version 0.1
//      UMA - Alberto García Padilla 30-04-2020
________________________________________________________________________________________________________*/
/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de los repositorios
________________________________________________________________________________________________________*/
function HTMLSELECT_aulas($cmd,$idcentro,$idaula){
	global $idcentro;
	$SelectHtml="";
	$rs=new Recordset; 
	
	$cmd->texto="SELECT nombreaula,idaula FROM  aulas WHERE idcentro=$idcentro";
	$rs->Comando=&$cmd; 

	if (!$rs->Abrir()) return($SelectHtml); // Error al abrir recordset
	$SelectHtml.= '<SELECT class="formulariodatos" name="select_idaula" style="WIDTH: 200">';
	$rs->Primero(); 
	while (!$rs->EOF){
		$SelectHtml.='<OPTION value="'.$rs->campos["idaula"].'"';
		if($rs->campos["idaula"]==$idaula) $SelectHtml.=" selected ";
		$SelectHtml.='>';
		$SelectHtml.= $rs->campos["nombreaula"];
		$SelectHtml.='</OPTION>';
		$rs->Siguiente();
	}
	$SelectHtml.= '</SELECT>';
	$rs->Cerrar();
	return($SelectHtml);
}
?>