<?  
// *************************************************************************************************************************************************
// Aplicaciónn WEB: Hidra
// Copyright 2003-2005  Jos� Manuel Alonso. Todos los derechos reservados.
// Fecha Creaciónn: A�o 2003-2004
// Fecha �ltima modificaci�n: Marzo-2005
// Nombre del fichero: propiedades_servidoresrembo.php
// Descripciónn : 
//		 Presenta el formulario de captura de datos de un servidor rembo para insertar,modificar y eliminar
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_servidoresrembo_".$idioma.".php"); 
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idservidorrembo=0; 
$nombreservidorrembo="";
$ip="";
$puertorepo="2002";
$pathrembod="/usr/local/hidra";
$grupoid=0;
$comentarios="";
$ordenadores=0; // N�mero de ordenador a los que da servicio

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idservidorrembo"])) $idservidorrembo=$_GET["idservidorrembo"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idservidorrembo=$_GET["identificador"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexi�n con servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idservidorrembo);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperaci�n de datos.
}
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administraci�n web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_servidoresrembo.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_servidoresrembo_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<DIV  align=center>
<FORM  name="fdatos"> 
	<INPUT type=hidden name=idservidorrembo value=<?=$idservidorrembo?>>
	<INPUT type=hidden name=grupoid value=<?=$grupoid?>>
	<INPUT type=hidden name=ordenadores value=<?=$ordenadores?>>
	<IMPUT type=hidden name=pathrembod value=<?=$pathrembod?>>
	<P align=center class=cabeceras><?echo $TbMsg[4]?><BR>
	<SPAN align=center class=subcabeceras><? echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos >
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TD>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD>'.$nombreservidorrembo.'</TD>';
				else	
					echo '<TD><INPUT  class="formulariodatos" name=nombreservidorrembo style="width:200" type=text value="'.$nombreservidorrembo.'"></TD>';
			?>
			<TD colspan=2 valign=top align=left rowspan=4	><CENTER><IMG border=3 style="border-color:#63676b" src="../images/aula.jpg"><BR>&nbsp;Ordenadores:&nbsp;<? echo $ordenadores?><BR>&nbsp;</CENTER></TD>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[6]?>&nbsp;</TD>
			<?
			if ($opcion==$op_eliminacion)
					echo '<TD>'.$ip.'</TD>';
			else	
				echo'<TD><INPUT  class="formulariodatos" name=ip type=text style="width:200" value="'.$ip.'"></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;Puerto de Repo&nbsp;</TD>
		<?
			if ($opcion==$op_eliminacion)
					echo '<TD>'.$puertorepo.'</TD>';
			else	
				echo'<TD><INPUT  class="formulariodatos" name=puertorepo type=text style="width:200" value="'.$puertorepo.'"></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[7]?>&nbsp;</TD>
			<?
			if ($opcion==$op_eliminacion)
					echo '<TD>'.$comentarios.'</TD>';
			else	
				echo '<TD><TEXTAREA   class="formulariodatos" name=comentarios rows=2 cols=50>'.$comentarios.'</TEXTAREA></TD>';
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
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
//________________________________________________________________________________________________________
//	Recupera los datos de un servidor rembo
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexi�n abierta)  
//		- id: El identificador del servidor
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global $nombreservidorrembo;
	global $ip;
	global $comentarios;
	global $puertorepo;
	global $pathrembod;
	global $ordenadores;

	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM servidoresrembo WHERE idservidorrembo=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombreservidorrembo=$rs->campos["nombreservidorrembo"];
		$ip=$rs->campos["ip"];
		$comentarios=$rs->campos["comentarios"];
		$puertorepo=$rs->campos["puertorepo"];
		$pathrembod=$rs->campos["pathrembod"];
		$rs->Cerrar();
		$cmd->texto="SELECT count(*) as numordenadores FROM ordenadores WHERE idservidorrembo=".$id;
		$rs->Comando=&$cmd; 
		if (!$rs->Abrir()) return(0); // Error al abrir recordset
		$rs->Primero(); 
		if (!$rs->EOF)
			$ordenadores=$rs->campos["numordenadores"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
?>
