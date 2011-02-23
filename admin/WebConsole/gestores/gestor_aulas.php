<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
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
$idproautoexec=0;
$idrepositorio=0;
$idperfilhard=0;
$cache=0;
$modomul=0;
$ipmul="";
$pormul=0;
$velmul=0;
############## ADV
$router=0;
$netmask=0;
$modp2p=0;
$timep2p=0;
############ ADV
if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros

if (isset($_POST["grupoid"])) $grupoid=$_POST["grupoid"];
if (isset($_POST["idaula"])) $idaula=$_POST["idaula"];
if (isset($_POST["identificador"])) $idaula=$_POST["identificador"];

if (isset($_POST["nombreaula"])) $nombreaula=$_POST["nombreaula"]; 
if (isset($_POST["urlfoto"])) $urlfoto=$_POST["urlfoto"]; 
if (isset($_POST["cagnon"])) $cagnon=$_POST["cagnon"]; 
if (isset($_POST["pizarra"])) $pizarra=$_POST["pizarra"];
if (isset($_POST["ubicacion"])) $ubicacion=$_POST["ubicacion"]; 
if (isset($_POST["comentarios"])) $comentarios=$_POST["comentarios"];
if (isset($_POST["puestos"])) $puestos=$_POST["puestos"]; 
if (isset($_POST["horaresevini"])) $horaresevini=$_POST["horaresevini"]; 
if (isset($_POST["horaresevfin"])) $horaresevfin=$_POST["horaresevfin"]; 
if (isset($_POST["idmenu"])) $idmenu=$_POST["idmenu"]; 
if (isset($_POST["idprocedimiento"])) $idproautoexec=$_POST["idprocedimiento"]; 
if (isset($_POST["idrepositorio"])) $idrepositorio=$_POST["idrepositorio"]; 
if (isset($_POST["idperfilhard"])) $idperfilhard=$_POST["idperfilhard"]; 
if (isset($_POST["cache"])) $cache=$_POST["cache"]; 
if (isset($_POST["modomul"])) $modomul=$_POST["modomul"]; 
if (isset($_POST["ipmul"])) $ipmul=$_POST["ipmul"]; 
if (isset($_POST["pormul"])) $pormul=$_POST["pormul"]; 
if (isset($_POST["velmul"])) $velmul=$_POST["velmul"]; 
############## ADV
if (isset($_POST["router"])) $router=$_POST["router"];
if (isset($_POST["netmask"])) $netmask=$_POST["netmask"]; 
if (isset($_POST["modp2p"])) $modp2p=$_POST["modp2p"]; 
if (isset($_POST["timep2p"])) $timep2p=$_POST["timep2p"]; 
################# ADV

$gidmenu=0;
$gidproautoexec=0;
$gidrepositorio=0;
$gidperfilhard=0;
$gcache=0;
	
if (isset($_POST["gidmenu"])) $gidmenu=$_POST["gidmenu"]; 
if (isset($_POST["gidprocedimiento"])) $gidproautoexec=$_POST["gidprocedimiento"]; 
if (isset($_POST["gidrepositorio"])) $gidrepositorio=$_POST["gidrepositorio"]; 
if (isset($_POST["gidperfilhard"])) $gidperfilhard=$_POST["gidperfilhard"]; 
if (isset($_POST["gcache"])) $gcache=$_POST["gcache"]; 

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
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<BODY>
	<SCRIPT language="javascript" src="../jscripts/propiedades_aulas.js"></SCRIPT>
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
echo '<p><span style="visibility:hidden" id="arbol_nodo">'.$tablanodo.'</span></p>';
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo 'var oHTML'.chr(13);
	echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
	echo 'o=cTBODY.item(1);'.chr(13);
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idaula.",o.innerHTML);".chr(13);
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$nombreaula."');".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idaula.")";
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
	global	$idproautoexec;
	global	$idrepositorio;
	global	$idperfilhard;
	global	$cache;
	
	global $gidmenu;
	global $gidproautoexec;
	global $gidrepositorio;
	global $gidperfilhard;
	global $gcache;
	
	global	$modomul;
	global	$ipmul;
	global	$pormul;
	global	$velmul;
######################### ADV	
	global  $router;
	global	$netmask;
	global  $modp2p;
	global  $timep2p;
########################## ADV

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
	$cmd->CreaParametro("@idproautoexec",$idproautoexec,1);
	$cmd->CreaParametro("@idrepositorio",$idrepositorio,1);
	$cmd->CreaParametro("@idperfilhard",$idperfilhard,1);
	$cmd->CreaParametro("@cache",$cache,1);
	$cmd->CreaParametro("@modomul",$modomul,1);
	$cmd->CreaParametro("@ipmul",$ipmul,0);
	$cmd->CreaParametro("@pormul",$pormul,1);
	$cmd->CreaParametro("@velmul",$velmul,1);
############ ADV
	$cmd->CreaParametro("@netmask",$netmask,0);
	$cmd->CreaParametro("@router",$router,0);
	$cmd->CreaParametro("@modp2p",$modp2p,0);
	$cmd->CreaParametro("@timep2p",$timep2p,1);
############### ADV

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO aulas(idcentro,grupoid,nombreaula,urlfoto,cagnon,pizarra,ubicacion,comentarios,
			puestos,horaresevini,horaresevfin,modomul,ipmul,pormul,velmul,router,netmask,modp2p,timep2p) 
			VALUES (@idcentro,@grupoid,@nombreaula,@urlfoto,@cagnon,@pizarra,@ubicacion,@comentarios,
			@puestos,@horaresevini,@horaresevfin,@modomul,@ipmul,@pormul,@velmul,@router,@netmask,@modp2p,@timep2p)";
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
			$cmd->texto="UPDATE aulas SET nombreaula=@nombreaula,urlfoto=@urlfoto,cagnon=@cagnon,pizarra=@pizarra,
			ubicacion=@ubicacion,comentarios=@comentarios,puestos=@puestos,horaresevini=@horaresevini,
			horaresevfin=@horaresevfin,modomul=@modomul,ipmul=@ipmul,pormul=@pormul,velmul=@velmul,router=@router,netmask=@netmask,modp2p=@modp2p,timep2p=@timep2p WHERE idaula=@idaula";
			$resul=$cmd->Ejecutar();
			//echo $cmd->texto;
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$clsUpdate="";	
				if($idmenu>0 || $gidmenu>0)	
					$clsUpdate.="idmenu=@idmenu,";
				if($idproautoexec>0 || $gidproautoexec>0)	
					$clsUpdate.="idproautoexec=@idproautoexec,";					
				if($idrepositorio>0 || $gidrepositorio>0)	
					$clsUpdate.="idrepositorio=@idrepositorio,";
				if($idperfilhard>0 || $gidperfilhard>0)	
					$clsUpdate.="idperfilhard=@idperfilhard,";
				if($cache!=0 || $gcache>0)	
					$clsUpdate.="cache=@cache,";
					
				if(!empty($clsUpdate)){				
					$clsUpdate=substr($clsUpdate,0,strlen($clsUpdate)-1); // Quita última coma
					$cmd->texto="UPDATE ordenadores SET ".$clsUpdate." WHERE idaula=@idaula";
					$resul=$cmd->Ejecutar();
					//echo $cmd->texto;
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
