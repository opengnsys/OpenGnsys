<?php
// *******************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_usuarios.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de usuarios
// *******************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("./relaciones/usuarios_eliminacion.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idusuario=0; 
$usuario="";
$pasguor="";
$nombre="";
$email="";
$idambito=0;
$ididioma=0;
$idtipousuario=0;

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"];

if (isset($_POST["idusuario"])) $idusuario=$_POST["idusuario"];
if (isset($_POST["usuario"])) $usuario=$_POST["usuario"];
if (isset($_POST["pasguor"])) $pasguor=$_POST["pasguor"];
if (isset($_POST["nombre"])) $nombre=$_POST["nombre"];
if (isset($_POST["email"])) $email=$_POST["email"];
if (isset($_POST["idambito"])) $idambito=$_POST["idambito"];
if (isset($_POST["ididioma"])) $ididioma=$_POST["ididioma"];
if (isset($_POST["idtipousuario"])) $idtipousuario=$_POST["idtipousuario"];

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
	<SCRIPT language="javascript" src="../jscripts/propiedades_usuarios.js"></SCRIPT>
<?php
	$literal="";
	switch($opcion){
		case $op_alta :
			$literal="resultado_insertar_usuarios";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_usuarios";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_usuarios";
			break;
		case $op_movida :
			$literal="resultado_cambiar_usuarios";
			break;
		default:
			break;
	}
	echo '<P><SPAN style="visibility:hidden" id="arbol_nodo">'.$tablanodo.'</SPAN></P>';
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo 'var oHTML'.chr(13);
	echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
	echo 'o=cTBODY.item(1);'.chr(13);
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idusuario.",o.innerHTML);";
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$nombre."');";
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idusuario.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?php
/*________________________________________________________________________________________________________
	Inserta, modifica o elimina datos en la tabla usuarios
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;
	global $OPERADOR;
	global $ADMINISTRADOR;

	global $idusuario;
	global $usuario;
	global $pasguor;
	global $nombre;
	global $email;
	global $idambito;
	global $ididioma;
	global $idtipousuario;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;
	global	$tablanodo;

	$cmd->CreaParametro("@idusuario",$idusuario,1);
	$cmd->CreaParametro("@usuario",$usuario,0);
	$cmd->CreaParametro("@pasguor",$pasguor,0);
	$cmd->CreaParametro("@nombre",$nombre,0);
	$cmd->CreaParametro("@email",$email,0);
	$cmd->CreaParametro("@idambito",$idambito,1);
	$cmd->CreaParametro("@ididioma",$ididioma,1);
	$cmd->CreaParametro("@idtipousuario",$idtipousuario,1);
	// Generar clave de acceso a la API REST.
	$apikey=md5(uniqid(rand(), true));
	$cmd->CreaParametro("@apikey",$apikey,0);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO usuarios (usuario,pasguor,nombre,email,ididioma,idtipousuario,apikey) VALUES (@usuario,SHA2(@pasguor,224),@nombre,@email,@ididioma,@idtipousuario,@apikey);";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idusuario=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_usuarios($idusuario,$nombre,$idtipousuario);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE usuarios SET usuario=@usuario,pasguor=SHA2(@pasguor,224),nombre=@nombre,email=@email,ididioma=@ididioma WHERE idusuario=@idusuario";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaUsuarios($cmd,$idusuario,"idusuario");
			break;
		default:
			break;
	}
	return($resul);
}
/*________________________________________________________________________________________________________
	Busca los datos de un usuario 
		Parametros: 
		- cmd:Una comando ya operativo (con conexión abierta)  
		- idusuario:El identificador del usuario
________________________________________________________________________________________________________*/
function toma_usuario($cmd,$idusuario){
	global $usuario;
	global $idambito;
	global $idtipousuario;

	$rs=new Recordset; 
	$cmd->texto="SELECT usuario, idambito, idtipousuario FROM usuarios WHERE idusuario=".$idusuario;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$usuario=$rs->campos["usuario"];
		$idambito=$rs->campos["idambito"];
		$idtipousuario=$rs->campos["idtipousuario"];
		return(true);
	}
	else
		return(false);
}
/*________________________________________________________________________________________________________
	Crea un arbol XML para el nuevo nodo insertado 
________________________________________________________________________________________________________*/
function SubarbolXML_usuarios($idusuario,$nombre,$idtipousuario){
		global $LITAMBITO_USUARIOS;
		global $SUPERADMINISTRADOR;
		global $ADMINISTRADOR;
		global $OPERADOR;

		switch($idtipousuario){
			case $SUPERADMINISTRADOR:
				$urlimg="../images/iconos/superadministradores.gif";
				break;
			case $ADMINISTRADOR:
				$urlimg="../images/iconos/administradores.gif";
				break;
			case $OPERADOR:
				$urlimg="../images/iconos/operadores.gif";
				break;
		}
		$cadenaXML='<USUARIO';
		// Atributos			
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_USUARIOS."'" .')"';
		$cadenaXML.=' imagenodo="'.$urlimg.'"';
		$cadenaXML.=' infonodo="'.$nombre.'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_USUARIOS.'-'.$idusuario;
		$cadenaXML.='></USUARIO>';
		return($cadenaXML);
} 
?>
