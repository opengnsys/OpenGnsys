<?
include_once("../includes/ctrlacc.php");

include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");

$identificador=0;
$ambicom=""; // Ambito del comando
$literalnodo="";

if (isset($_GET["identificador"]))	$identificador=$_GET["identificador"]; 
if (isset($_GET["ambicom"]))	$ambicom=$_GET["ambicom"]; 
if (isset($_GET["literalnodo"]))	$literalnodo=$_GET["literalnodo"]; 

$literal="";
switch($ambicom){
			case $LITAMBITO_GRUPOSAULAS:
				$literal="Comando aplicado al grupo de aulas:";
				break;
			case $LITAMBITO_AULAS:
				$literal="Comando aplicado al aula:";
				break;
			case $LITAMBITO_GRUPOSORDENADORES:
				$literal="Comando aplicado al grupo de ordenadores:";
				break;
			case $LITAMBITO_ORDENADORES:
				$literal="Comando aplicado al ordenador:";
				break;
}
$literal=$literal.$literalnodo;
?>
<HTML>
<HEAD>
<LINK rel="stylesheet" type="text/css" href="../hidra.css">
</HEAD>
<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/comandos.js"></SCRIPT>
<BODY>
	<input type=hidden value="<? echo $identificador?>" id=identificador> 
	<input type=hidden value="<? echo $ambicom?>" id=ambicom> 
	
	<p align=center class=cabeceras>COMANDOS&nbsp<img src="../images/iconos/comandos.gif"><br>
	<span align=center class=subcabeceras><? echo $literal ?></span></p>
<?
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if ($cmd){
	$rs=new Recordset; 
	$cmd->texto="SELECT idcomando,descripcion,urlimg FROM comandos ORDER BY descripcion";
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$rs->Primero(); 
		echo '<TABLE align=center>';
		echo ' <TR bgcolor="#003300">';
		echo '<TD><strong><font color="#FFFFFF" size="1" face="Arial, Helvetica,sans-serif">Comando</font></strong></TD>';
		echo '<TD><strong><font color="#FFFFFF" size="1" face="Arial, Helvetica,sans-serif">Descripcion</font></strong></TD>';
		echo '</TR>';
		$swcolor=true;
		while (!$rs->EOF){
			if ($swcolor){ 
					$swcolor=false;
					$bgcolor="#EEEECC";
					$color="#003300";
			}									
			else{
					$swcolor=true;
					//$bgcolor="#999999";
					//$color="#FFFFFF";
					$bgcolor="#EEEECC";
					$color="#003300";
			}
			echo '<TR bgcolor="'.$bgcolor.'">';
			echo '<TD><INPUT  class="formulariodatos" id='.$rs->campos["idcomando"].'  type=radio onclick="SeleccionaComando(this)"></TD>'; 
			echo '<TD  id="comando-'.$rs->campos["idcomando"].'">';
			echo '	<font color="#003300" size="1" face="Arial, Helvetica, sans-serif">'.$rs->campos["descripcion"].'</font>';
			echo '</TR>';
				echo '<TR>';
				echo ' <TD></TD>';
				echo '<TD> ';
				echo '</TD>';
				echo '</TR>';
			//}
			echo '<TR bgcolor="#999999"><td colspan=3></td></tr>';
			$rs->Siguiente();
		}
		echo '</TABLE>';
	}
}
?>
<br>
<?
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotones.php");
//________________________________________________________________________________________________________

//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?	$cmd->Conexion->Cerrar(); // Cierra la conexión ?>
<?
/******************************************************************
	Devuelve una objeto comando totalmente operativo (con la conexión abierta)
	Parametros: 
		- cadenaconexion: Una cadena con los datos necesarios para la conexión: nombre del servidor
		usuario,password,base de datos,etc separados por coma
---------------------------------------------------------------------------------------------*/
function CreaComando($cadenaconexion){
	$strcn=split(";",$cadenaconexion);
	$cn=new Conexion; 
	$cmd=new Comando;	
	$cn->CadenaConexion($strcn[0],$strcn[1],$strcn[2],$strcn[3],$strcn[4]);
	if (!$cn->Abrir()) return (false); 
	$cmd->Conexion=&$cn; 
	return($cmd);
}
/* -------------------------------------------------------------------------------------------
	Crea la etiqueta html <SELECT> de cualquier tabla
		Parametros: 
		- cmd:Una comando ya operativo (con conexión abierta)  
		- nombretabla: El nombre de la tabla origen de los datos
		- identificador: Un identificador de la tabla ( el que aparecerá seleccionado)
		- nombreid: El nombre del identificador de la tabla
		- nombreliteral: El nombre del literal de la tabla
		- largo: longitud del desplegable
---------------------------------------------------------------------------------------------*/
function HTMLSELECT($cmd,$nombretabla,$identificador,$nombreid,$nombreliteral,$largo){
	$SelectHtml="";
	$rs=new Recordset; 
	$cmd->texto='SELECT * FROM '.$nombretabla.' WHERE '. $nombreid.'='.$identificador.' ORDER BY '.$nombreliteral;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$SelectHtml.= '<SELECT class="formulariodatos" name="'.$nombreid.'" style="WIDTH: '.$largo.'">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';
	$rs->Primero(); 
	while (!$rs->EOF){
		$SelectHtml.='<OPTION value="'.$rs->campos[$nombreid].'"';
		If ($rs->campos[$nombreid]==$identificador)  $SelectHtml.= ' selected ' ;
		$SelectHtml.= '>'.$rs->campos[$nombreliteral].'</OPTION>';
		$rs->Siguiente();
	}$SelectHtml.= '</SELECT>';
	$rs->Cerrar();
	return($SelectHtml);
}
?>