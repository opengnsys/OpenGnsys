<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Copyright 200-2005 José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: imagenincremental.php
// Descripción : 
//		Administra los componentes software incluidos en un software incremental
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/imagenincremental_".$idioma.".php");
//________________________________________________________________________________________________________
$idimagen=0; 
$descripcionimagen=""; 
if (isset($_GET["idimagen"])) $idimagen=$_GET["idimagen"]; // Recoge parametros
if (isset($_GET["descripcionimagen"])) $descripcionimagen=$_GET["descripcionimagen"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="../jscripts/imagenincremental.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/imagenincremental_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<INPUT type=hidden value="<? echo $idcentro?>" id=idcentro>	 
	<INPUT type=hidden value="<? echo $idimagen?>" id=idimagen>	 
	<P align=center class=cabeceras><?echo $TbMsg[0]?><BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/incremental.gif"></P>
	<BR>
	<DIV align=center id="Layer_componentes">
		<SPAN align=center class=presentaciones><B><U><?echo $TbMsg[2]?></U>:&nbsp;<? echo $descripcionimagen?></B></SPAN></P>
		<TABLE width="100%" class="tabla_listados" cellspacing=1 cellpadding=0 >
			 <TR>
				<TH>&nbsp</TH>
				<TH><?echo $TbMsg[3]?></TH>
			</TR>
		<?
			$rs=new Recordset; 
			$cmd->texto='SELECT softincrementales.idsoftincremental,softincrementales.descripcion FROM softincrementales INNER JOIN imagenes_softincremental ON softincrementales.idsoftincremental=imagenes_softincremental.idsoftincremental WHERE imagenes_softincremental.idimagen='.$idimagen.' ORDER BY softincrementales.descripcion';
			$rs->Comando=&$cmd; 
			if ($rs->Abrir()){ 
				$rs->Primero();
				$A_W=" WHERE ";
				$strex="";
				while (!$rs->EOF){
						 echo '<TR>';
						 echo '<TD align=center width="10%" ><INPUT type=checkbox onclick="gestion_componente('.$rs->campos["idsoftincremental"].',this)" checked ></INPUT></TD>';
						 echo '<TD  width="80%" >&nbsp;'.$rs->campos["descripcion"].'</TD>';
						 echo '</TR>';
						 $strex.= $A_W."softincrementales.idsoftincremental<>".$rs->campos["idsoftincremental"];
						$A_W=" AND ";
						$rs->Siguiente();
				}
			}
			if(empty($strex))
				$strex="WHERE";
			else
				$strex.= $A_W;
			$rs->Cerrar();
			$cmd->texto='SELECT softincrementales.idsoftincremental,softincrementales.descripcion FROM softincrementales  '.$strex.'  softincrementales.idcentro='.$idcentro.' ORDER BY softincrementales.descripcion';
			$rs->Comando=&$cmd; 
			if ($rs->Abrir()){
				$rs->Primero();
				while (!$rs->EOF){
						 echo '<TR>';
						 echo '<TD align=center width="10%" ><INPUT type=checkbox onclick="gestion_componente('.$rs->campos["idsoftincremental"].',this)"  ></INPUT></TD>';
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
