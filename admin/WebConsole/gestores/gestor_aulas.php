<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_aulas.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de aulas
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("./relaciones/aulas_eliminacion.php");
include_once("./relaciones/ordenadores_eliminacion.php");
include_once("../includes/opciones.php");
include_once("./relaciones/gruposordenadores_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idaula=0; 
$nombreaula="";
$grupoid=0; 
$urlfoto="";
$cagnon=false;
$pizarra=false;
$ubicacion="";
$comentarios="";
$puestos=0;
$horaresevini=0;
$horaresevfin=0;
$idmenu=0;

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros

if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"];
if (isset($_GET["idaula"])) $idaula=$_GET["idaula"];
if (isset($_GET["identificador"])) $idaula=$_GET["identificador"];

if (isset($_GET["nombreaula"])) $nombreaula=$_GET["nombreaula"]; 
if (isset($_GET["urlfoto"])) $urlfoto=$_GET["urlfoto"]; 
if (isset($_GET["cagnon"])) $cagnon=$_GET["cagnon"]; 
if (isset($_GET["pizarra"])) $pizarra=$_GET["pizarra"];
if (isset($_GET["ubicacion"])) $ubicacion=$_GET["ubicacion"]; 
if (isset($_GET["comentarios"])) $comentarios=$_GET["comentarios"];
if (isset($_GET["puestos"])) $puestos=$_GET["puestos"]; 
if (isset($_GET["horaresevini"])) $horaresevini=$_GET["horaresevini"]; 
if (isset($_GET["horaresevfin"])) $horaresevfin=$_GET["horaresevfin"]; 
if (isset($_GET["idmenu"])) $idmenu=$_GET["idmenu"]; 

$tablanodo=""; // Arbol para nodos insertados
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
// *************************************************************************************************************************************************
?>
<HTML>
<HEAD>
<BODY>
<?
	$literal="";
	switch($opcion){
		case $op_alta :
			$literal="resultado_insertar_aulas";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_aulas";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_aulas";
			break;
		default:
			break;
	}
echo '<p><span id="arbol_nodo">'.$tablanodo.'</span></p>';
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo 'var oHTML'.chr(13);
	echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
	echo 'o=cTBODY.item(1);'.chr(13);
	if ($opcion==$op_alta )
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idaula.",o.innerHTML);".chr(13);
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$nombreaula."');".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idaula.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla aulas
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$grupoid;

	global	$idaula;
	global	$nombreaula;
	global	$urlfoto;
	global	$cagnon;
	global	$pizarra;
	global	$ubicacion;
	global	$comentarios;
	global	$puestos;
	global	$horaresevini;
	global	$horaresevfin;
	global	$idmenu;
	
	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$tablanodo;

	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@idcentro",$idcentro,1);

	$cmd->CreaParametro("@idaula",$idaula,1);
	$cmd->CreaParametro("@nombreaula",$nombreaula,0);
	$cmd->CreaParametro("@urlfoto",$urlfoto,0);
	$cmd->CreaParametro("@cagnon",$cagnon,1);
	$cmd->CreaParametro("@pizarra",$pizarra,1);
	$cmd->CreaParametro("@ubicacion",$ubicacion,0);
	$cmd->CreaParametro("@comentarios",$comentarios,0);
	$cmd->CreaParametro("@puestos",$puestos,1);
	$cmd->CreaParametro("@horaresevini",$horaresevini,1);
	$cmd->CreaParametro("@horaresevfin",$horaresevfin,1);
	$cmd->CreaParametro("@idmenu",$idmenu,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO aulas(idcentro,grupoid,nombreaula,urlfoto,cagnon,pizarra,ubicacion,comentarios,puestos,horaresevini,horaresevfin) VALUES (@idcentro,@grupoid,@nombreaula,@urlfoto,@cagnon,@pizarra,@ubicacion,@comentarios,@puestos,@horaresevini,@horaresevfin)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idaula=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_aulas($idaula,$nombreaula);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE aulas SET nombreaula=@nombreaula,urlfoto=@urlfoto,cagnon=@cagnon,pizarra=@pizarra,ubicacion=@ubicacion,comentarios=@comentarios,puestos=@puestos,horaresevini=@horaresevini,horaresevfin=@horaresevfin WHERE idaula=@idaula";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				if($idmenu>0){
					$cmd->texto="UPDATE ordenadores SET idmenu=@idmenu WHERE idaula=@idaula";
					$resul=$cmd->Ejecutar();
				}
			}
			break;
		case $op_eliminacion :
			$resul=EliminaAulas($cmd,$idaula,"idaula");// Eliminación en cascada
			break;
		default:
			break;
	}
	return($resul);
}
/*________________________________________________________________________________________________________
	Crea un arbol XML para el nuevo nodo insertado 
________________________________________________________________________________________________________*/
function SubarbolXML_aulas($idaula,$nombreaula){
	global 	$LITAMBITO_AULAS;
	$cadenaXML='<AULAS ';
	// Atributos		
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_AULAS."'" .')"';
	$cadenaXML.=' imagenodo="../images/iconos/aula.gif"';
	$cadenaXML.=' infonodo="'.$nombreaula.'"';
	$cadenaXML.=' nodoid='.$LITAMBITO_AULAS.'-'.$idaula;
	$cadenaXML.='>';
	$cadenaXML.='</AULAS>';
	return($cadenaXML);
}
?>