<?php
// *************************************************************************************************************************************************
// Autor: Ramón M. Gómez, ETSII Universidad de Sevilla
// Fecha Creación:
// Fecha Última modificación: junio 2018
// Nombre del fichero: gestor_proyectores.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de proyectores
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
//include_once("./relaciones/proyectores_eliminacion.php");
include_once("../includes/opciones.php");
//include_once("../idiomas/php/".$idioma."/gestor_proyectores_".$idioma.".php");

//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idaula=0; 
$idproyector=0; 
$nombreproyector="";
$modelo="";
$tipo="";
$ip="";
$datosduplicados="";
if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros
if (isset($_POST["idaula"])) $idaula=$_POST["idaula"];
if (isset($_POST["idproyector"])) $idproyector=$_POST["idproyector"];
if (isset($_POST["nombreproyector"])) $nombreproyector=$_POST["nombreproyector"];
if (isset($_POST["modelo"])) $modelo=$_POST["modelo"];
if (isset($_POST["tipo"])) $tipo=$_POST["tipo"];
if (isset($_POST["ip"])) $ip=$_POST["ip"];
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
if($opcion!=$op_movida){
	echo '<HTML>';
	echo '<HEAD>';
	echo '	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">';
	echo '<BODY>';
	echo '<P><SPAN style="visibility:hidden" id="arbol_nodo">'.$tablanodo.'</SPAN></P>';
	echo '	<SCRIPT language="javascript" src="../jscripts/propiedades_ordenadores.js"></SCRIPT>';
	echo '<SCRIPT language="javascript">'.chr(13);
	if ($resul){
		echo 'var oHTML'.chr(13);
		echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
		echo 'o=cTBODY.item(1);'.chr(13);
	}
}
//________________________________________________________________________________________________________
	$literal="";
	switch($opcion){
		case $op_alta :
			$literal="resultado_insertar_proyectores";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_proyectores";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_proyectores";
			break;
		case $op_movida :
			$literal="resultado_cambiar_proyectores";
			break;
		default:
			break;
	}
if ($resul){
	if ($opcion==$op_alta ) {
	    if ( $datosduplicados != '') {
		echo $literal."(0,'".$TbMsg["DUPLICADO"].$datosduplicados." ',".$idproyector.",o.innerHTML);".chr(13);
	    } else {  
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idproyector.",o.innerHTML);".chr(13);
	    }
	}
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$nombreproyector."');".chr(13);
}
else
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idproyector.")";

if($opcion!=$op_movida){
	echo '	</SCRIPT>';
	echo '</BODY>	';
	echo '</HTML>';	
}
/*________________________________________________________________________________________________________
	Inserta, modifica o elimina datos en la tabla proyectores
________________________________________________________________________________________________________*/
function Gestiona(){
	global $cmd;
	global $opcion;
	global $idproyector;
	global $nombreproyector;
	global $modelo;
	global $tipo;
	global $ip;
	global $idaula;

	global $op_alta;
	global $op_modificacion;
	global $op_eliminacion;
	global $op_movida;
	global $tablanodo;

	global $datosduplicados;

	$cmd->CreaParametro("@idaula",$idaula,1);
	$cmd->CreaParametro("@idproyector",$idproyector,1);
	$cmd->CreaParametro("@nombreproyector",$nombreproyector,0);
	$cmd->CreaParametro("@modelo",$modelo,0);
	$cmd->CreaParametro("@tipo",$tipo,0);
	$cmd->CreaParametro("@ip",$ip,0);

	switch($opcion){
		case $op_alta :
                        // Comprueba que no existan duplicados
                        $ipduplicada='no';
                        $nombreduplicado='no';
                        $cmd->texto=<<<EOD
SELECT *
  FROM projectors
 WHERE name=@nombreproyector OR ipddr=@ip;
EOD;
                        $rs=new Recordset;
                        $rs->Comando=&$cmd;
                        if (!$rs->Abrir()) return(0); // Error al abrir recordset
                        $rs->Primero();
                        while (!$rs->EOF){
                           if ( $nombreproyector == $rs->campos["nombreproyector"]) $datosduplicados ="nombre: $nombreproyector,";
                           if ( $ip == $rs->campos["ip"]) $datosduplicados .=" ip: $ip,";
                           $rs->Siguiente();
                        }
                        $rs->Cerrar();
                        // quitamos última coma
                        $datosduplicados = trim($datosduplicados, ',');

                        // Si no hay datos duplicados insertamos el proyector;
                        if ( $datosduplicados == "" ) {
			     $cmd->texto = <<<EOD
INSERT INTO projectors (name, model, type, ipaddr)
     VALUES (@nombreproyector, @modelo, @tipo, @ip);
EOD;
			}
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
			    $idproyector=$cmd->Autonumerico();
			    // Insertar datos en el árbol de configuración.
			    $arbolXML=SubarbolXML_proyectores($idproyector,$nombreproyector);
			    $baseurlimg="../images/signos"; // Url de las imagenes de signo
			    $clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
			    $arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
			    $tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto=<<<EOD
UPDATE projectors
   SET name=@nombreordenador, model=@modelo, type=@tipo, ipaddr=@ip
 WHERE id=@idproyector;
EOD;
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaProyectores($cmd,$idproyector,"idproyector");// Eliminación en cascada
			break;
		case $op_movida :
			$cmd->texto=<<<EOD
UPDATE projectors
   SET lab_id=@idaula
 WHERE id=@idproyector;
EOD;
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}
/*________________________________________________________________________________________________________
	Crea un arbol XML para el nuevo nodo insertado 
________________________________________________________________________________________________________*/
function SubarbolXML_proyectores($idproyector,$nombreproyector){
		global $LITAMBITO_ORDENADORES;
		$cadenaXML='<PROYECTOR';
		// Atributos			
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_PROYECTORES."'" .')"';
		$cadenaXML.=' imagenodo="../images/iconos/proyector.gif"';
		$cadenaXML.=' infonodo="'.$nombreproyector.'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_PROYECTORES.'-'.$idproyector;
		$cadenaXML.='></PROYECTOR>';
		return($cadenaXML);
} 
