<? 
// ********************************************************************
// Aplicación WEB: ogAdmWebCon 
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla 
// Fecha Creación: Diciembre-2003 
// Fecha Última modificación: Febrero-2005 
// Nombre del fichero: controlacceso.php 
// Descripción :Este fichero implementa el control de acceso a la aplicación 
// *********************************************************************
include_once("controlacceso.php");
include_once("./includes/CreaComando.php");
include_once("./clases/AdoPhp.php");
#include_once("idiomas/php/$idi/acceso_$idi.php");
//________________________________________________________________________________________________________
 $usu=""; 
 $pss=""; 
 $idc=0; 
 $iph=""; // Switch menu cliente 
  
 if (isset($_POST["usu"])) $usu=mysql_escape_string($_POST["usu"]);  
 if (isset($_POST["pss"])) $pss=mysql_escape_string($_POST["pss"]);  
 if (isset($_POST["idcentro"])) $idc=mysql_escape_string($_POST["idcentro"]); 

 if (isset($_GET["iph"])) $iph=$_GET["iph"];  
//________________________________________________________________________________________________________
 $cmd=CreaComando($cnx); // Crea objeto comando 
 if (!$cmd)
  die($TbMsg["ACCESS_ERROR"]);
//________________________________________________________________________________________________________

 $nmc=""; 
 $idi=""; 

 if(!empty($iph)){ // LLamada del browser del cliente 
     list($wip,$wusu,$wpwd,$wbd,$tbd)=split(";",$cnx); 
     $usu=$wusu; 
     $pss=$wpwd; 
 }

 $resul=toma_datos($cmd,$idc,$nmc,$idi,$usu,$tsu,$pss); 
 // Antes la variable idioma no es la correcta
 include_once("idiomas/php/$idi/acceso_$idi.php");
 if(!$resul) 
     Header("Location: ".$wac."?herror=4"); // Error de conexión con servidor B.D. 
  
 if(!empty($iph)){ 
     $wurl="./varios/menucliente.php"; 
     Header("Location:".$wurl); // Accede a la página de menus 
 } 


 session_start(); // Activa variables de sesión

 $_SESSION["widcentro"]=$idc;  
 $_SESSION["wnombrecentro"]=$nmc;  
 $_SESSION["wusuario"]=$usu;  
 $_SESSION["widtipousuario"]=$tsu;  
 $_SESSION["widioma"]=$idi; 
 $_SESSION["wcadenaconexion"]=$cnx; 
 $_SESSION["wpagerror"]=$wer; 
 $_SESSION["wurlacceso"]=$wac; 

// Variables de entorno
 $resul=toma_entorno($cmd,$ips,$prt,$pclo,$rep); 
 if(!$resul) 
     Header("Location: ".$wac."?herror=4"); // Error de conexión con servidor B.D. 

 $_SESSION["wservidorhidra"]=$ips; 
 $_SESSION["whidraport"]=$prt; 
 $_SESSION["protclonacion"]=$pclo; 
 $_SESSION["repcentralizado"]=$rep; 

/*
echo "<BR>Cadena=".$_SESSION["wcadenaconexion"];
echo "<BR>servidorhidra=".$_SESSION["wservidorhidra"];
echo "<BR>hidraport=".$_SESSION["whidraport"];
echo "<BR>usuario=".$_SESSION["wusuario"];
echo "<BR>idtipousuario=".$_SESSION["widtipousuario"];
*/

 //________________________________________________________________________________________________________ 
 //    Busca datos del usuario que intenta acceder a la aplicación  
 //        Parametros:  
 //        - cmd:Una comando ya operativo (con conexión abierta)   
 //        - usuario: Nombre del usuario   
 //        - pasguor: Password del uuario   
 // 
 //    Devuelve el identificador del centro, el nombre y el idioma utilizado por el usuario  
 //_______________________________________________________________________________________________________ 
 function toma_datos($cmd,$idcentro,&$nombrecentro,&$idioma,$usuario,&$idtipousuario,$pasguor){ 
	$rs=new Recordset;  
	if(!empty($idcentro)){
		 $cmd->texto="SELECT usuarios.idtipousuario, centros.nombrecentro,
				     idiomas.nemonico AS idioma
				FROM usuarios
				INNER JOIN administradores_centros ON administradores_centros.idusuario=usuarios.idusuario
				INNER JOIN centros ON centros.idcentro=administradores_centros.idcentro
				INNER JOIN idiomas ON usuarios.ididioma=idiomas.ididioma
				WHERE idtipousuario <> 3
				  AND usuarios.usuario='".$usuario."'
				  AND usuarios.pasguor='".$pasguor."'
				  AND administradores_centros.idcentro=".$idcentro; 
	}			
	else{
		 $cmd->texto="SELECT usuarios.idtipousuario, idiomas.nemonico AS idioma
				FROM usuarios
				INNER JOIN idiomas ON usuarios.ididioma=idiomas.ididioma
				WHERE idtipousuario <> 3
				  AND usuarios.usuario='".$usuario."'
				  AND usuarios.pasguor='".$pasguor."'"; 
				
	}
	$rs->Comando=&$cmd; 
		 //echo $cmd->texto;
	if (!$rs->Abrir()) return($false); // Error al abrir recordset 
	if(!$rs->EOF){
		$idtipousuario=$rs->campos["idtipousuario"]; 
		$idioma=$rs->campos["idioma"]; 
		$usuario=$rs->campos["usuario"]; 
		if(!empty($idcentro)){
			$nombrecentro=$rs->campos["nombrecentro"]; 
			$idtipousuario=2; // Fuerza al acceso como administrador de UNidad organizativa
			return(true);
		}
		else{
			$nombrecentro="";
			if($idtipousuario<>1) // Si NO es superadminsitrador
				return(false); 	
			else
		       		return(true); 					
		}
	} 
	return(false); 
 } 
//________________________________________________________________________________________________________ 
 //    Busca datos de configuración del sistema  
 //        Parametros:  
 //        - cmd:Una comando ya operativo (con conexión abierta)   
 //        - ips: Dirección IP del servidor de administración   
 //        - prt: Puerto de comunicaciones
 //        - pclo: Protocolo de clonación
 //	     - rep: Uso de repositorio centralizado
 // 
 //    Devuelve datos generales de configuración del sistema
 //_______________________________________________________________________________________________________ 
 function toma_entorno($cmd,&$ips,&$prt,&$pclo,&$rep){ 
 	$rs=new Recordset;  
	$cmd->texto="SELECT * FROM entornos"; 
	$rs->Comando=&$cmd; 
	//echo $cmd->texto;
	if (!$rs->Abrir()) return($false); // Error al abrir recordset 
	if(!$rs->EOF){
		$ips=$rs->campos["ipserveradm"]; 
		$prt=$rs->campos["portserveradm"];
		$pclo=$rs->campos["protoclonacion"];
		$rep=$rs->campos["repositorio"];
	}
	return(true); 
 } 
 //_______________________________________________________________________________________________________ 
?> 
<html> 
<head> 
     <title><?php echo $TbMsg["ACCESS_TITLE"] ?></title>
     <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"> 
     <link rel="stylesheet" type="text/css" href="estilos.css"> 
</head> 

<body> 
<div id="mensaje" style="position:absolute;TOP:250;LEFT:330; visibility:visible"> 
     <span align="center" class="subcabeceras"><?php echo $TbMsg["ACCESS_ALLOWED"] ?></span>
</div>
     <script language="javascript"> 
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
     </script> 
</body> 
</html> 

