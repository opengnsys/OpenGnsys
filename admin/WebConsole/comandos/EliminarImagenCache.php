<?php
// *************************************************************************************************************************************************
// Nombre del fichero: EliminarImagenCache.php
// DescripciÃ³n : 
//		ImplementaciÃ³nï¿œ del comando "Eliminar Imagen Cache"
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/TomaDato.php");
include_once("../idiomas/php/".$idioma."/comandos/eliminarimagencache_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");
//________________________________________________________________________________________________________
include_once("./includes/capturaacciones.php");
$funcion=EjecutarScript;

//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//___________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="./jscripts/EliminarImagenCache.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/eliminarimagencache_'.$idioma.'.js"></SCRIPT>'?>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
</HEAD>
<BODY>
<?php
switch($ambito){
		case $AMBITO_CENTROS :
			$urlimg='../images/iconos/centros.gif';
			$textambito=$TbMsg[0];
			break;
		case $AMBITO_GRUPOSAULAS :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[1];
			break;
		case $AMBITO_AULAS :
			$urlimg='../images/iconos/aula.gif';
			$textambito=$TbMsg[2];
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[3];
			break;
		case $AMBITO_ORDENADORES :
			$urlimg='../images/iconos/ordenador.gif';
			$textambito=$TbMsg[4];
			break;
	}
	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>';
	echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras><U>'.$TbMsg[6].': '.$textambito.','.$nombreambito.'</U></span>&nbsp;&nbsp;</span></p>';
?>
	<P align=center>
	<SPAN align=center class=subcabeceras><? echo $TbMsg[7] ?></SPAN>
	</BR>
<form  align=center name="fdatos"> 
	<TABLE  id="tabla_conf" align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;<? echo $TbMsg[11] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<? echo $TbMsg[12] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<? echo $TbMsg[10] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<? echo $TbMsg[13] ?>&nbsp;</TH>


		</TR>
			<?php
				echo tabla_configuraciones($cmd,$idambito);
			?>
	</TABLE>
</FORM>
<?php
	//________________________________________________________________________________________________________
	include_once("./includes/formularioacciones.php");
	//________________________________________________________________________________________________________
	include_once("./includes/opcionesacciones.php");
	//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?php
/**************************************************************************************************************************************************
	Recupera los datos de un ordenador
		Parametros: 
		- cmd: Una comando ya operativo (con conexiónabierta)  
		- ido: El identificador del ordenador
________________________________________________________________________________________________________*/
function toma_propiedades($cmd,$idordenador){
	global $nombreordenador;
	global $ip;
	global $mac;
	global $idperfilhard;
	global $idservidordhcp;
	global $idservidorrembo;
	$rs=new Recordset; 
	$cmd->texto="SELECT nombreordenador,ip,mac,idperfilhard FROM ordenadores WHERE idordenador='".$idordenador."'";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombreordenador=$rs->campos["nombreordenador"];
		$ip=$rs->campos["ip"];
		$mac=$rs->campos["mac"];
		$idperfilhard=$rs->campos["idperfilhard"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
/*________________________________________________________________________________________________________
	Crea la tabla de configuraciones y perfiles a crear
________________________________________________________________________________________________________*/

function tabla_configuraciones($cmd,$idambito){

	global $idc;
	$idc=$_SESSION["widcentro"];
	global $ambito;
	global $nombreambito;


	global $AMBITO_CENTROS;
	global $AMBITO_GRUPOSAULAS;
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;

switch($ambito){
		case $AMBITO_CENTROS :
			$urlimg='../images/iconos/centros.gif';
			//echo "ambito - ".$ambito."<br>";
			//echo "idcentro - ".$idc;
			break;

		case $AMBITO_GRUPOSAULAS :

	$cmd->texto="SELECT * FROM grupos WHERE nombregrupo='$nombreambito' AND idcentro='$idc'";
	$rs=new Recordset;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(true); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$identificadorgrupo=$rs->campos["idgrupo"];
	}
	$rs->Cerrar();

			$cmd->texto="SELECT * FROM aulas,grupos
                                        WHERE grupos.nombregrupo='$nombreambito'
					AND aulas.idcentro='$idc'
                                        AND aulas.grupoid='$identificadorgrupo'
                                        AND aulas.grupoid=grupos.idgrupo";


			break;

		case $AMBITO_AULAS :
			$cmd->texto="SELECT * FROM ordenadores,aulas,ordenadores_particiones 
					WHERE ordenadores_particiones.idordenador=ordenadores.idordenador 
					AND ordenadores.idaula=aulas.idaula
					AND aulas.nombreaula='$nombreambito'
                                        AND aulas.idcentro='$idc'
					AND ordenadores_particiones.numpar=4  
					GROUP BY ordenadores_particiones.cache";

			break;

		case $AMBITO_GRUPOSORDENADORES :
			$cmd->texto="SELECT * FROM ordenadores,aulas,ordenadores_particiones,gruposordenadores 
					WHERE ordenadores_particiones.idordenador=ordenadores.idordenador 
					AND ordenadores.idaula=aulas.idaula
                                        AND gruposordenadores.idaula=aulas.idaula
					AND gruposordenadores.nombregrupoordenador='$nombreambito'
                                        AND aulas.idcentro='$idc'
					AND ordenadores_particiones.numpar=4  
					GROUP BY ordenadores_particiones.cache";

			break;
		case $AMBITO_ORDENADORES :
			$cmd->texto="SELECT * FROM ordenadores,ordenadores_particiones 
					WHERE ordenadores_particiones.idordenador=ordenadores.idordenador 
					AND ordenadores.nombreordenador='$nombreambito'
					AND ordenadores_particiones.numpar=4  
					GROUP BY ordenadores_particiones.cache";
			break;
	}

	$tablaHtml="";


	$rs->Comando=&$cmd;  
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($tablaHtml); // Error al abrir recordset
	$rs->Primero(); 

	while (!$rs->EOF){

				$cache=$rs->campos["cache"];
				$idordenador=$rs->campos["idordenador"];
				$ima=split(",",$cache);
				
				for ($x=0;$x<count($ima); $x++)
				{
				    if(ereg(".img",$ima[$x])  ) //si contiene .img
					{
						if (ereg(".img.sum",$ima[$x]) || ereg(".img.torrent",$ima[$x])  )//Si el nombre contiene .img.sum o img.torrent
						  {}else{
							$ima[$x] = str_replace(".img", "", $ima[$x]); //quitar todos los .img
							$ima[$x]=trim($ima[$x]);
							$nombreimagenes[]=$ima[$x];
				   			}
					 }else{}
				 }
	
				 $rs->Siguiente();
			}
			$rs->Cerrar();

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

					$sin_duplicados=array_unique($nombreimagenes);
					$contar=1;
					foreach($sin_duplicados as $value) //imprimimos $sin_duplicados
					{

					$nombrefichero=$value.'.img';
					$tamanofich=exec("du -h /opt/opengnsys/images/$nombrefichero");
					if ($tamanofich==""){$tamanofich=$TbMsg[14];}
					$tamanofich=split("/",$tamanofich);	
						
					$todo=".*";
					$ruta='rm%20/opt/opengnsys/cache/opt/opengnsys/images/'.$value.$todo;

					echo '<TR>'.chr(13);
					echo '<TD align=center>&nbsp;'.$contar.'&nbsp;</TD>'.chr(13);
					echo '<TD align=center ><input type="radio" name="codigo"  value='.$ruta.'></TD>'.chr(13);
					echo '<TD align=center>&nbsp;'.$value.'&nbsp;</TD>'.chr(13);
					echo '<TD align=center>&nbsp;'.$tamanofich[0].'</TD>'.chr(13);
					echo '</TR>'.chr(13);
					$contar++;
					}


			return($tablaHtml);
}

?>

