<? 
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: propiedades_entornos.php
// Descripción : 
//		 Presenta el formulario de captura de datos de entorno para insertar,modificar y eliminar
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_entornos_".$idioma.".php");
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________


$identorno=1; 
$ipserveradm="";
$portserveradm="";
$protoclonacion="";


if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["identorno"])) $identorno=$_GET["identorno"]; 

//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando


if (!$cmd){
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
}
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$identorno);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.
}


//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_entornos.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_entornos_'.$idioma.'.js"></SCRIPT>'?>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<DIV  align=center>
<FORM  name="fdatos"  name="fdatos" action="../gestores/gestor_entornos.php" method="post">
	<INPUT type=hidden name=identorno value="<?=$identorno?>">

	<P align=center class=cabeceras><?echo $TbMsg[4]?><BR>
	<SPAN align=center class=subcabeceras><? echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos >

	

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TD>
			<?
				if ($opcion==$op_eliminacion){
					echo '<TD>'. $ipserveradm.'</TD>';
			}
			else{
					echo '<TD><INPUT type=tex class="formulariodatos" name=ipserveradm size="50" value="'. $ipserveradm.'"></TD>';
					
			}
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[6]?>&nbsp;</TD>
			<?if ($opcion==$op_eliminacion)
					echo '<TD>'.$portserveradm.'&nbsp; </TD>';
				else
					echo '<TD><INPUT type=text class="formulariodatos" name=portserveradm size="50" value="'.$portserveradm.'"></TD>';
			?>
		</TR>	
		
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[7]?>&nbsp;</TD>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD>'.$protoclonacion.'</TD>';
				else{
						$metodos="UNICAST=UNICAST".chr(13);
						$metodos.="MULTICAST=MULTICAST".chr(13);
						$metodos.="TORRENT=TORRENT";
					echo '<TD>'.HTMLCTESELECT($metodos,"protoclonacion","estilodesple","",$protoclonacion,100).'</TD>'.chr(13);
			}  
			?>
		</TR>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	</TABLE>
</FORM>
</DIV>
<?
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
//________________________________________________________________________________________________________
//	Recupera los datos de entorno
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- id: El identificador del entorno.
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){

  global $identorno;	
	global $ipserveradm;
	global $portserveradm;
	global $protoclonacion;
	

	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM entornos WHERE identorno=".$id;

	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$ipserveradm=$rs->campos["ipserveradm"];
		$portserveradm=$rs->campos["portserveradm"];
		$protoclonacion=$rs->campos["protoclonacion"];
		
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
?>
