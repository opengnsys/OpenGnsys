<? 
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: propiedades_aulas.php
// Descripción : 
//		 Presenta el formulario de captura de datos de un aula para insertar,modificar y eliminar
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../includes/HTMLSELECT.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_aulas_".$idioma.".php");
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idaula=0; 
$nombreaula="";
$urlfoto="";
$cagnon=false;
$pizarra=false;
$ubicacion="";
$comentarios="";
$idmenu=0;
$ordenadores=0;
$puestos=0;
$horaresevini=0;
$horaresevfin=0;
$grupoid=0;

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idaula"])) $idaula=$_GET["idaula"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idaula=$_GET["identificador"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idaula);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.
}
else
	$urlfoto="../images/aula.jpg";
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_aulas.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_aulas_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<DIV  align=center>
<FORM  name="fdatos"> 
	<INPUT type=hidden name=idaula value=<?=$idaula?>>
	<INPUT type=hidden name=grupoid value=<?=$grupoid?>>
	<INPUT type=hidden name=ordenadores value=<?=$ordenadores?>>
	<P align=center class=cabeceras><?echo $TbMsg[4]?><BR>
	<SPAN align=center class=subcabeceras><? echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos >
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TD>
			<?
				if ($opcion==$op_eliminacion){
					echo '<TD>'. $nombreaula.'</TD>';
					echo '<TD colspan=2 valign=top align=center rowspan=2><IMG border=3 style="border-color:#63676b" src="'.$urlfoto.'"<br><center>&nbsp;Computers:&nbsp;'. $ordenadores.'</center></TD>';
			}
			else{
					echo '<TD><INPUT  class="formulariodatos" name=nombreaula style="width:215" type=text value="'. $nombreaula.'"></TD>';
					echo'<TD colspan=2 valign=top align=left rowspan=2><IMG border=3 style="border-color:#63676b" src="'.$urlfoto.'"<br><center>&nbsp;Computers:&nbsp;'. $ordenadores.'</center></TD>';
			}
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[6]?>&nbsp;</TD>
			<?if ($opcion==$op_eliminacion)
					echo '<TD>'.$ubicacion.'&nbsp; </TD>';
				else
					echo '<TD><TEXTAREA   class="formulariodatos" name=ubicacion rows=3 cols=42>'.$ubicacion.'</TEXTAREA></TD>';
			?>
		</TR>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[7]?>&nbsp;</TD>
			<?
			if ($opcion==$op_eliminacion){
					echo '<TD colspan=3><INPUT  class="formulariodatos" name=cagnon type=checkbox  onclick="desabilita(this)" ';
					if ($cagnon) echo ' checked ';
					echo '></TD>';
			}
			else{
					echo '<TD colspan=3><INPUT  class="formulariodatos" name=cagnon type=checkbox  ';
					if ($cagnon) echo ' checked ';
					echo '></TD>';
			}
			?>
			</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[8]?>&nbsp;</TD>
			<?
			if ($opcion==$op_eliminacion){
					echo '<TD colspan=3><INPUT  class="formulariodatos" name=pizarra type=checkbox  onclick="desabilita(this)" ';
					if ($pizarra) echo ' checked ';
					echo '></TD>';
			}
			else{
					echo '<TD colspan=3><INPUT  class="formulariodatos" name=pizarra type=checkbox  ';
					if ($pizarra) echo ' checked ';
					echo '></TD>';
			}
			?>
		</TR	>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center&nbsp;><?echo $TbMsg[9]?>&nbsp;</TD>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.$puestos.'</TD>';
				else
					echo '<TD colspan=3><INPUT  class="formulariodatos" name=puestos style="width:30" type=text value='.$puestos.'></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center&nbsp;><?echo $TbMsg[13]?>&nbsp;</TD>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.$TbMsg[14].$horaresevini.'&nbsp;&nbsp;&nbsp&nbsp;'.$TbMsg[15].$horaresevfin.'</TD>';
				else
					echo '<TD colspan=3>'.$TbMsg[14].'&nbsp<INPUT  class="formulariodatos" onclick="vertabla_horas(this)"  name=horaresevini style="width:30" type=text value='.$horaresevini.'>&nbsp;&nbsp;&nbsp&nbsp;'.$TbMsg[15].'&nbsp<INPUT  class="formulariodatos" onclick="vertabla_horas(this)" name=horaresevfin style="width:30" type=text value='.$horaresevfin.'></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[10]?>&nbsp;</TD>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD  colspan=3>'.$urlfoto.'</TD>';
				else
					echo '<TD colspan=3><INPUT  class="formulariodatos" name=urlfoto style="width:330" type=text value='.$urlfoto.'></TD>';
			?>
		</TR>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[11]?>&nbsp;</TD>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.TomaDato($cmd,$idcentro,'menus',$idmenu,'idmenu','descripcion').'&nbsp;</TD>';
				else
					echo '<TD colspan=3>'.HTMLSELECT($cmd,$idcentro,'menus',$idmenu,'idmenu','descripcion',330).'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[12]?>&nbsp;</TD>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD colspan=3>'.$comentarios.'</TD>';
				else
					echo '<TD colspan=3><TEXTAREA   class="formulariodatos" name=comentarios rows=3 cols=65>'.$comentarios.'</TEXTAREA></TD>';
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
//	Recupera los datos de un aula
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- id: El identificador del aula
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global $idaula;
	global $nombreaula;
	global $urlfoto;
	global $cagnon;
	global $pizarra;
	global $ubicacion;
	global $comentarios;
	global $ordenadores;
	global $puestos;
	global $horaresevini;
	global $horaresevfin;
	global $idmenu;
	global $grupoid;
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM aulas WHERE idaula=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombreaula=$rs->campos["nombreaula"];
		$urlfoto=$rs->campos["urlfoto"];
		if ($urlfoto=="" )
			$urlfoto="../images/aula.jpg";
		$cagnon=$rs->campos["cagnon"];
		$pizarra=$rs->campos["pizarra"];
		$ubicacion=$rs->campos["ubicacion"];
		$comentarios=$rs->campos["comentarios"];
		$puestos=$rs->campos["puestos"];
		$horaresevini=$rs->campos["horaresevini"];
		$horaresevfin=$rs->campos["horaresevfin"];
		$grupoid=$rs->campos["grupoid"];
		$rs->Cerrar();
		$cmd->texto="SELECT count(*) as numordenadores FROM ordenadores WHERE idaula=".$id;
		$rs->Comando=&$cmd; 
		if (!$rs->Abrir()) return(false); // Error al abrir recordset
		$rs->Primero(); 
		if (!$rs->EOF)
			$ordenadores=$rs->campos["numordenadores"];
		$cmd->texto="SELECT idmenu FROM ordenadores WHERE idaula=".$id." group by idmenu";
		$rs->Comando=&$cmd; 
		if (!$rs->Abrir()) return(false); // Error al abrir recordset
		$rs->Primero(); 
		if (!$rs->EOF){
			if($rs->numeroderegistros==1) // Un sólo menu para todos los ordenadores
				$idmenu=$rs->campos["idmenu"];
		}
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
?>
