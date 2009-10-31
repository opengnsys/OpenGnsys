<?
// *******************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_usuarios.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de usuarios
// *******************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/SockHidra.php");
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

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"];

if (isset($_GET["idusuario"])) $idusuario=$_GET["idusuario"];
if (isset($_GET["usuario"])) $usuario=$_GET["usuario"];
if (isset($_GET["pasguor"])) $pasguor=$_GET["pasguor"];
if (isset($_GET["nombre"])) $nombre=$_GET["nombre"];
if (isset($_GET["email"])) $email=$_GET["email"];
if (isset($_GET["idambito"])) $idambito=$_GET["idambito"];
if (isset($_GET["ididioma"])) $ididioma=$_GET["ididioma"];
if (isset($_GET["idtipousuario"])) $idtipousuario=$_GET["idtipousuario"];

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
echo '<p><span id="arbol_nodo">'.$tablanodo.'</span></p>';
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo 'var oHTML'.chr(13);
	echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
	echo 'o=cTBODY.item(1);'.chr(13);
	if ($opcion==$op_alta )
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idusuario.",o.innerHTML);";
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$nombre."');";
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idusuario.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
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

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO usuarios (usuario,pasguor,nombre,email,idambito,ididioma,idtipousuario ) VALUES (@usuario,@pasguor,@nombre,@email,@idambito,@ididioma,@idtipousuario);";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idusuario=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_usuarios($idusuario,$nombre,$idtipousuario);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
				if($idtipousuario==$OPERADOR)
					GestionLogin($cmd,$usuario,$pasguor,$idusuario,$op_alta,$idambito);
				else{
					if($idtipousuario==$ADMINISTRADOR){
						GestionLogin($cmd,$usuario,$pasguor,$idusuario,$op_alta,0);
					}
				}
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE usuarios SET usuario=@usuario,pasguor=@pasguor,nombre=@nombre,email=@email,idambito=@idambito,ididioma=@ididioma WHERE idusuario=@idusuario";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			if(toma_usuario($cmd,$idusuario)){
				if($idtipousuario==$OPERADOR)
					GestionLogin($cmd,$usuario,$pasguor,$idusuario,$op_eliminacion,$idambito);
				else{
					if($idtipousuario==$ADMINISTRADOR){
						GestionLogin($cmd,$usuario,$pasguor,$idusuario,$op_eliminacion,0);
					}
				}
				$resul=EliminaUsuarios($cmd,$idusuario,"idusuario");
			}
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
	global $pasguor;
	global $idambito;
	global $idtipousuario;

	$rs=new Recordset; 
	$cmd->texto="SELECT usuario, pasguor,idambito,idtipousuario FROM usuarios WHERE idusuario=".$idusuario;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$usuario=$rs->campos["usuario"];
		$pasguor=$rs->campos["pasguor"];
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
/*________________________________________________________________________________________________________
	Envía un comando al servidor para que cree el fichero de login de este operador
________________________________________________________________________________________________________*/
function GestionLogin($cmd,$usuario,$pasguor,$idusuario,$op,$idambito){
	global $servidorhidra;
	global $hidraport;
	
	$nombrefuncion="FicheroOperador"; 
	$ejecutor="1"; 
	$cadenaip=TomaIpesServidores($cmd,$idusuario,$idambito);
	$shidra=new SockHidra($servidorhidra,$hidraport); 

	$parametros=$ejecutor;
	$parametros.="nfn=".$nombrefuncion.chr(13);
	$parametros.="amb=".$op.chr(13);
	$parametros.="usu=".$usuario.chr(13);
	$parametros.="psw=".$pasguor.chr(13);
	$parametros.="ida=".$idambito.chr(13);
	$auxIP=split(";",$cadenaip);
	for ($i=0;$i<sizeof($auxIP)-1;$i++){
		$auxparametros=$parametros."rmb=".$auxIP[$i].chr(13);
		$resul=manda_trama($shidra,$auxparametros);
	}
	return(false);
}
/*________________________________________________________________________________________________________
	Devuelve una cadena con las Ipes de los servidores rembo implicados
________________________________________________________________________________________________________*/
function TomaIpesServidores($cmd,$idusuario,$idambito){
	if($idambito>0)
			$cmd->texto="SELECT DISTINCT servidoresrembo.ip FROM aulas INNER JOIN ordenadores ON aulas.idaula = ordenadores.idaula INNER JOIN servidoresrembo ON ordenadores.idservidorrembo = servidoresrembo.idservidorrembo INNER JOIN   usuarios ON aulas.idaula = usuarios.idambito Where usuarios.idusuario=".$idusuario;
	else
			$cmd->texto="SELECT DISTINCT servidoresrembo.ip FROM aulas INNER JOIN ordenadores ON aulas.idaula = ordenadores.idaula INNER JOIN servidoresrembo ON ordenadores.idservidorrembo = servidoresrembo.idservidorrembo INNER JOIN  centros  ON aulas.idcentro = centros.idcentro	INNER JOIN   usuarios ON centros.idcentro = usuarios.idambito Where usuarios.idusuario=".$idusuario;

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir())	return(""); // Error al abrir recordset
	$rs->Primero(); 
	$cadenaip="";
	while(!$rs->EOF){
		$cadenaip.=trim($rs->campos["ip"]).";";
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaip);
}
//________________________________________________________________________________________________________
//
//	Manda una trama del comando Actualizar
//________________________________________________________________________________________________________
function manda_trama($shidra,$parametros){
	if ($shidra->conectar()){ // Se ha establecido la conexión con el servidor hidra
		$shidra->envia_comando($parametros);
		$shidra->desconectar();
		return(true);
	}
	return(false);
}
?>