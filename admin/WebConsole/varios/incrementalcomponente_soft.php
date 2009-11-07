<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla

// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: incrementalcomponente_soft.php
// Descripción : 
//		Administra los componentes software incluidos en un software incremental
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/incrementalcomponente_soft_".$idioma.".php");
//________________________________________________________________________________________________________
$idsoftincremental=0; 
$descripcionincremental=""; 
if (isset($_GET["idsoftincremental"])) $idsoftincremental=$_GET["idsoftincremental"]; // Recoge parametros
if (isset($_GET["descripcionincremental"])) $descripcionincremental=$_GET["descripcionincremental"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="../jscripts/incrementalcomponente_soft.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/incrementalcomponente_soft_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<INPUT type=hidden value="<? echo $idcentro?>" id=idcentro>	 
	<INPUT type=hidden value="<? echo $idsoftincremental?>" id=idsoftincremental>	 
	<P align=center class=cabeceras><?echo $TbMsg[0]?><BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/confisoft.gif"></P>
	<BR>
	<DIV align=center id="Layer_componentes">
		<SPAN align=center class=presentaciones><B><U><?echo $TbMsg[2]?></U>:&nbsp;<? echo $descripcionincremental?></B></SPAN></P>
		<TABLE width="100%" class="tabla_listados" cellspacing=1 cellpadding=0 >
			 <TR>
				<TH>&nbsp</TH>
				<TH>T</TH>
				<TH><?echo $TbMsg[3]?></TH>
			</TR>
		<?
			$rs=new Recordset; 
			$cmd->texto='SELECT softwares.idsoftware,softwares.descripcion,tiposoftwares.descripcion as hdescripcion,tiposoftwares.urlimg FROM softwares INNER JOIN softincremental_softwares ON softwares.idsoftware=softincremental_softwares.idsoftware INNER JOIN tiposoftwares ON softwares.idtiposoftware=tiposoftwares.idtiposoftware WHERE softincremental_softwares.idsoftincremental='.$idsoftincremental.' ORDER BY tiposoftwares.idtiposoftware,softwares.descripcion';
			$rs->Comando=&$cmd; 
			if ($rs->Abrir()){ 
				$rs->Primero();
				$A_W=" WHERE ";
				$strex="";
				while (!$rs->EOF){
						 echo '<TR>';
						 echo '<TD align=center width="10%" ><INPUT type=checkbox onclick="gestion_componente('.$rs->campos["idsoftware"].',this)" checked ></INPUT></TD>';
						 echo '<TD align=center width="10%" ><img alt="'. $rs->campos["hdescripcion"].'"src="'.$rs->campos["urlimg"].'"></TD>';
						 echo '<TD  width="80%" >&nbsp;'.$rs->campos["descripcion"].'</TD>';
						 echo '</TR>';
						 $strex.= $A_W."softwares.idsoftware<>".$rs->campos["idsoftware"];
						$A_W=" AND ";
						$rs->Siguiente();
				}
			}
			$rs->Cerrar();
			if(empty($strex))
				$strex="WHERE";
			else
				$strex.= $A_W;
//  Para que no salga la opción de elegir un sistema operativo
//			$cmd->texto='SELECT softwares.idsoftware,softwares.descripcion,tiposoftwares.descripcion as hdescripcion,tiposoftwares.urlimg  FROM softwares  INNER JOIN tiposoftwares ON softwares.idtiposoftware=tiposoftwares.idtiposoftware  '.$strex.'  softwares.idtiposoftware<>1 AND softwares.idcentro='.$idcentro.' ORDER BY tiposoftwares.idtiposoftware,softwares.descripcion';

// Se deja elegir componente que sea un sistema operativo sólo para para incluir en el desplegable, en la creación de software incremental
			$cmd->texto='SELECT softwares.idsoftware,softwares.descripcion,tiposoftwares.descripcion as hdescripcion,tiposoftwares.urlimg  FROM softwares  INNER JOIN tiposoftwares ON softwares.idtiposoftware=tiposoftwares.idtiposoftware  '.$strex.'   softwares.idcentro='.$idcentro.' ORDER BY tiposoftwares.idtiposoftware,softwares.descripcion';
			$rs->Comando=&$cmd; 
			if ($rs->Abrir()){
				$rs->Primero();
				while (!$rs->EOF){
						 echo '<TR>';
						 echo '<TD align=center width="10%" ><INPUT type=checkbox onclick="gestion_componente('.$rs->campos["idsoftware"].',this)"  ></INPUT></TD>';
						 echo '<TD align=center width="10%" ><img alt="'. $rs->campos["hdescripcion"].'"src="'.$rs->campos["urlimg"].'"></TD>';
						 echo '<TD width="80%" >&nbsp;'.$rs->campos["descripcion"].'</TD>';
						 echo '</TR>';
						$rs->Siguiente();
				}
			}
			$rs->Cerrar();
		?>
		</TABLE>
	</DIV>		
	<DIV id="Layer_nota" align=center >
		<BR>
		<SPAN align=center class=notas><I><?echo $TbMsg[4]?></I></SPAN>
	</DIV>
</FORM>
<?
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
