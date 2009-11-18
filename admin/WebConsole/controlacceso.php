<? 
// ********************************************************************
// Aplicación WEB: ogAdmWebCon 
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla 
// Fecha Creación: Diciembre-2003 
// Fecha Última modificación: Febrero-2005 
// Nombre del fichero: controlacceso.php 
// Descripción :Este fichero implementa el control de acceso a la aplicación 
// *********************************************************************
 if(isset($_SESSION)){     // Si existe algua sesión ... 
     session_unset(); // Elimina variables 
     session_destroy(); // Destruye sesión 
 } 
 session_start(); // Activa variables de sesión 
  
 include_once("./clases/AdoPhp.php"); 
  
 $usu=""; 
 $pss=""; 
 $iph=""; // Switch menu cliente 
  
 if (isset($_POST["usu"])) $usu=$_POST["usu"];  
 if (isset($_POST["pss"])) $pss=$_POST["pss"];  
 if (isset($_GET["iph"])) $iph=$_GET["iph"];  
  
/* 
 //======================================================================================================== 
 // Variables de sessión de configuración de servidor y base de datos( Modificar aquípara cambio global)  
 $cnx="localhost;usuog;passusuog;ogBDAdmin;mysql"; // Cadena de conexión a la base de datos 
 $ips="SERVERIP"; // IP del servidor de Administración 
 $prt="2008"; // Puerto de comunicación con el servidor 
 $wer="OPENGNSYSURL/pagerror.php"; // Página de redireccionamiento de errores 
 $wac="OPENGNSYSURL/acceso.php"; // Página de login de la aplicación 
 //======================================================================================================== 
 */ 
 //======================================================================================================== 
 // Variables de sessión de configuración de servidor y base de datos( Modificar aquípara cambio global)  
 $cnx="localhost;usuog;passusuog;ogBDAdmin;mysql"; // Cadena de conexión a la base de datos 
 $ips="10.1.15.3"; // IP del servidor de Administración 
 $prt="2008"; // Puerto de comunicación con el servidor 
 $wer="http://localhost/WebConsole/pagerror.php"; // Página de redireccionamiento de errores 
 $wac="http://localhost/WebConsole/acceso.php"; // Página de login de la aplicación 
 //======================================================================================================== 
 $cmd=CreaComando($cnx); // Crea objeto comando 
 $resul=false; 
 $idc=0; 
 $nmc=""; 
 $idi=""; 
 if(!empty($iph)){ // LLamada del browser del cliente 
     list($wip,$wusu,$wpwd,$wbd,$tbd)=split(";",$cnx); 
     $usu=$wusu; 
     $pss=$wpwd; 
     $_SESSION["ogCliente"]=$iph; 
 } 
 if ($cmd){ 
     $resul=toma_datos($cmd,&$idc,&$nmc,&$idi,$usu,&$tsu,$pss); 
 } 
 if(!$resul) 
     Header("Location: ".$wac."?herror=4"); // Error de conexión con servidor B.D. 
  
 if(!empty($iph)){ 
     $wurl="./varios/menucliente.php?iph=".trim($iph); 
     Header("Location:".$wurl); // Accede a la página de menus 
 } 
 $_SESSION["widcentro"]=$idc;  
 $_SESSION["wnombrecentro"]=$nmc;  
 $_SESSION["wusuario"]=$usu;  
 $_SESSION["widtipousuario"]=$tsu;  
 $_SESSION["widioma"]=$idi; 
 $_SESSION["wcadenaconexion"]=$cnx; 
 $_SESSION["wservidorhidra"]=$ips; 
 $_SESSION["whidraport"]=$prt; 
 $_SESSION["wpagerror"]=$wer; 
 $_SESSION["wurlacceso"]=$wac; 
 
 // ************************************************************************************************************************************************* 
 //    Devuelve una objeto comando totalmente operativo (con la conexión abierta) 
 //    Parametros:  
 //        - cadenaconexion: Una cadena con los datos necesarios para la conexión: nombre del servidor 
 //        usuario,password,base de datos,etc separados por coma 
 //________________________________________________________________________________________________________ 
 function CreaComando($cadenaconexion){ 
     $strcn=split(";",$cadenaconexion); 
     $cn=new Conexion;  
     $cmd=new Comando;     
     $cn->CadenaConexion($strcn[0],$strcn[1],$strcn[2],$strcn[3],$strcn[4]); 
     if (!$cn->Abrir()) return (false);  
     $cmd->Conexion=&$cn;  
     return($cmd); 
 } 
 //________________________________________________________________________________________________________ 
 //    Busca datos del usuario que intenta acceder a la aplicación  
 //        Parametros:  
 //        - cmd:Una comando ya operativo (con conexión abierta)   
 //        - usuario: Nombre del usuario   
 //        - pasguor: Password del uuario   
 // 
 //    Devuelve el identificador del centro, el nombre y el idioma utilizado por el usuario  
 //_______________________________________________________________________________________________________ 
 function toma_datos($cmd,$idcentro,$nombrecentro,$idioma,$usuario,$idtipousuario,$pasguor){ 
     $rs=new Recordset;  
  
     $cmd->texto="SELECT usuarios.idtipousuario,usuarios.idambito,centros.nombrecentro,idiomas.nemonico AS idioma FROM usuarios"; 
     $cmd->texto.=" LEFT OUTER JOIN centros ON usuarios.idambito=centros.idcentro"; 
     $cmd->texto.=" INNER JOIN idiomas ON usuarios.ididioma=idiomas.ididioma"; 
     $cmd->texto.=" WHERE idtipousuario<>3 AND usuarios.usuario='".$usuario."' AND usuarios.pasguor='".$pasguor."'"; 
  
     $rs->Comando=&$cmd; 
     $resul=false; 
     if (!$rs->Abrir()) return($resul); // Error al abrir recordset 
     $rs->Primero();  
     if (!$rs->EOF){ 
         $idcentro=$rs->campos["idambito"]; 
         $nombrecentro=$rs->campos["nombrecentro"]; 
         $idtipousuario=$rs->campos["idtipousuario"]; 
         $idioma=$rs->campos["idioma"]; 
         return(true); 
     } 
     return($resul); 
 } 
?> 
 <HTML> 
     <TITLE> Administración web de aulas</TITLE> 
     <HEAD> 
     <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"> 
         <LINK rel="stylesheet" type="text/css" href="estilos.css"> 
     </HEAD> 
     <BODY> 
         <DIV id="mensaje" style="Position:absolute;TOP:250;LEFT:330; visibility:visible"> 
         <SPAN  align=center class=subcabeceras>Acceso permitido. Espere por favor ...</SPAN></P> 
         <SCRIPT LANGUAGE="JAVASCRIPT"> 
             var vez=0; 
             setTimeout("acceso();",300); 
             function acceso(){ 
                 o=document.getElementById("mensaje"); 
                 var s=o.style.visibility; 
                 if(s=="hidden") 
                     o.style.visibility="visible"; 
                 else 
                     o.style.visibility="hidden"; 
                 if(vez>5){ 
                     var w=window.top; 
                     w.location="frames.php"; 
                 } 
                 vez++; 
                 setTimeout("acceso();",300); 
             } 
     </SCRIPT> 
  </BODY> 
 </HTML> 
