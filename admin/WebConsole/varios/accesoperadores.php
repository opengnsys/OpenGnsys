<? 
// ********************************************************************
// Aplicación WEB: ogAdmWebCon 
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla 
// Fecha Creación: Diciembre-2003 
// Fecha Última modificación: Febrero-2005 
// Nombre del fichero: controlacceso.php 
// Descripción :Este fichero implementa el control de acceso de los operadores de aula
// *********************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
  
$usu=""; 
$pss=""; 
$iph=""; // Switch menu cliente 
  
if (isset($_POST["usu"])) $usu=$_POST["usu"];  
if (isset($_POST["pss"])) $pss=$_POST["pss"];  
if (isset($_POST["iph"])) $iph=$_POST["iph"];  

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location:acceso_operador.php?herror=2'); // Error de conexióncon servidor B.D.

$ITEMS_PUBLICOS=1;
$ITEMS_PRIVADOS=2;

// COntrol de acceso del usuario
$rs=new Recordset;  
  
$cmd->texto="SELECT idusuario,idtipousuario,idambito FROM usuarios WHERE usuario='".$usu."' AND pasguor='".$pss."'"; 
$rs->Comando=&$cmd; 
if (!$rs->Abrir()){
	Header('Location:acceso_operador.php?herror=2'); // Error de conexióncon servidor B.D. 
	exit;
}
if ($rs->EOF){
	Header('Location:acceso_operador.php?herror=1'); // Error de acceso, no existe usuario
	exit;
}
if($idcentro!=$rs->campos["idambito"] && $rs->campos["idtipousuario"]!=1 ){
	Header('Location:acceso_operador.php?herror=1'); // Error de acceso, el usuario no pertenece al Centro
	exit;
}
// Acceso al menu de adminitración del aula
$wurl="menucliente.php?iph=".$iph."&tip=".$ITEMS_PRIVADOS;
$_SESSION["swop"]=$usu; 
Header('Location:'.$wurl); 
exit;