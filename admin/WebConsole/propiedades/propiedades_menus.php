<? 
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaciónn: A�o 2003-2004
// Fecha �ltima modificaci�n: Marzo-2005
// Nombre del fichero: propiedades_menus.php
// Descripciónn : 
//		 Presenta el formulario de captura de datos de un menu para insertar,modificar y eliminar
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_menus_".$idioma.".php");
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idmenu=0; 
$descripcion="";
$titulo="";
$coorx=0;
$coory=0;
$modalidad=0;
$scoorx=0;
$scoory=0;
$smodalidad=0;
$comentarios="";
$grupoid=0;
$htmlmenupub="";
$htmlmenupri="";
$resolucion="";

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"];  // Recoge parametros
if (isset($_GET["idmenu"])) $idmenu=$_GET["idmenu"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idmenu=$_GET["identificador"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexi�n con servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idmenu);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperaci�n de datos.
}
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administraci�n web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_menus.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_menus_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<INPUT type=hidden name=idmenu value=<?=$idmenu?>>
	<INPUT type=hidden name=grupoid value=<?=$grupoid?>>
	<P align=center class=cabeceras><?echo $TbMsg[4]?><BR>
	<SPAN align=center class=subcabeceras><? echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos >
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TD>
			<?if ($opcion==$op_eliminacion)
					echo '<TD style="width:300">'.$descripcion.'</TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=descripcion style="width:300" type=text value="'.$descripcion.'"></TD>';?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH  align=center>&nbsp;<?echo $TbMsg[6]?>&nbsp;</TH>
			<?if ($opcion==$op_eliminacion)
					echo '<TD  style="width:300">'.$titulo.'</TD>';
				else
					echo '<TD ><INPUT  class="formulariodatos" name=titulo style="width:300" type=text value="'.$titulo.'"></TD>';?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH  align=center>&nbsp;<?echo $TbMsg[17]?>&nbsp;</TH>
			<?
				if ($opcion==$op_eliminacion){
					$tbresolucion[1]="800x600";
					$tbresolucion[2]="1024x768";
					echo '<TD style="width:150">'.$tbresolucion[$resolucion].'</TD>';
				}
				else{
					$parametros="1=800x600".chr(13);
					$parametros.="2=1024x768";
					echo '<TD>'.HTMLCTESELECT($parametros,"resolucion","estilodesple","",$resolucion,100).'</TD>';
				}
			?>
		</TR>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[7]?>&nbsp;</TH>
			<?if ($opcion==$op_eliminacion)
					echo '<TD >'.$comentarios.'&nbsp</TD>';
				else
					echo '<TD><TEXTAREA   class="formulariodatos" name=comentarios rows=3 cols=55>'.$comentarios.'</TEXTAREA></TD>';
			?>
		</TR>	
</TABLE>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<BR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TD align=center colspan=6>&nbsp;<b><?echo $TbMsg[8]?></b>&nbsp;</TD>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp; <?echo $TbMsg[9]?>&nbsp;</TH>
			<?if ($opcion==$op_eliminacion)
				echo '<TD style="width:50">'.$coorx.'</TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=coorx style="width:50" type=text value="'.$coorx.'"></TD>';?>
			<TH align=center>&nbsp;<?echo $TbMsg[10]?>&nbsp;</TH>
			<?if ($opcion==$op_eliminacion)
					echo '<TD style="width:50">'.$coorx.'</TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=coory style="width:50" type=text value="'.$coory.'"></TD>';?>
			<TH align=center>&nbsp;<?echo $TbMsg[11]?>&nbsp;</TH>
			<?
				if ($opcion==$op_eliminacion){
					$tbmodalidad[1]=$TbMsg[13];
					$tbmodalidad[2]=$TbMsg[14];
					echo '<TD style="width:100">'.$tbmodalidad[$modalidad].'</TD>';
				}
				else{
					$parametros="1=1".chr(13);
					$parametros.="2=2".chr(13);
					$parametros.="3=3".chr(13);
					$parametros.="4=4".chr(13);
					$parametros.="5=5";
					echo '<TD>'.HTMLCTESELECT($parametros,"modalidad","estilodesple","",$modalidad,100).'</TD>';
				}
			?>
		</TR>

		<TR>
			<TH align=center>&nbsp; <?echo $TbMsg[15]?>&nbsp;</TH>
			<?if ($opcion==$op_eliminacion)
				echo '<TD colspan=5 style="width:350">'.$htmlmenupub.'</TD>';
				else
					echo '<TD colspan=5><INPUT  class="formulariodatos" name=htmlmenupub style="width:350" type=text value="'.$htmlmenupub.'"></TD>';?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TD align=center colspan=6>&nbsp;<b><?echo $TbMsg[12]?></b>&nbsp;</TD>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[9]?>&nbsp;</TH>
			<?if ($opcion==$op_eliminacion)
				echo '<TD style="width:50">'.$scoorx.'</TD>';
			else
				echo '<TD><INPUT  class="formulariodatos" name=scoorx style="width:50" type=text value="'.$scoorx.'"></TD>';?>
			<TH align=center>&nbsp;<?echo $TbMsg[10]?>&nbsp;</TH>
				<?if ($opcion==$op_eliminacion)
						echo '<TD style="width:50">'.$scoorx.'</TD>';
					else
						echo '<TD><INPUT  class="formulariodatos" name=scoory style="width:50" type=text value="'.$scoory.'"></TD>';?>
			<TH align=center>&nbsp;<?echo $TbMsg[11]?>&nbsp;</TH>
				<?
					if ($opcion==$op_eliminacion){
						$tbmodalidad[1]=$TbMsg[13];
						$tbmodalidad[2]=$TbMsg[14];
						echo '<TD style="width:100">'.$tbmodalidad[$smodalidad].'</TD>';
					}
					else{
						$parametros="1=1".chr(13);
						$parametros.="2=2".chr(13);
						$parametros.="3=3".chr(13);
						$parametros.="4=4".chr(13);
						$parametros.="5=5";
						echo '<TD>'.HTMLCTESELECT($parametros,"smodalidad","estilodesple","",$smodalidad,100).'</TD>';
					}
			?>
		</TR>
		<TR>
			<TH align=center>&nbsp; <?echo $TbMsg[16]?>&nbsp;</TH>
			<?if ($opcion==$op_eliminacion)
				echo '<TD colspan=5 style="width:350">'.$htmlmenupri.'</TD>';
				else
					echo '<TD colspan=5><INPUT  class="formulariodatos" name=htmlmenupri style="width:350" type=text value="'.$htmlmenupri.'"></TD>';?>
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
//	Recupera los datos de un menu 
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexi�n abierta)  
//		- id: El identificador del menu 
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global	$descripcion;
	global   $titulo;
	global   $coorx;
	global   $coory;
	global   $modalidad;
	global   $scoorx;
	global   $scoory;
	global   $smodalidad;
	global	$comentarios;
	global	$htmlmenupub;
	global	$htmlmenupri;
	global	$resolucion;
	
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM menus WHERE idmenu=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$descripcion=$rs->campos["descripcion"];
		$titulo=$rs->campos["titulo"];
		$coorx=$rs->campos["coorx"];
		$coory=$rs->campos["coory"];
		$modalidad=$rs->campos["modalidad"];
		$scoorx=$rs->campos["scoorx"];
		$scoory=$rs->campos["scoory"];
		$smodalidad=$rs->campos["smodalidad"];
		$comentarios=$rs->campos["comentarios"];
		$htmlmenupub=$rs->campos["htmlmenupub"];
		$htmlmenupri=$rs->campos["htmlmenupri"];
		$resolucion=$rs->campos["resolucion"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
?>
