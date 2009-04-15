<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 200-2005 José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: perfilcomponente_soft.php
// Descripción : 
//		Administra los componentes software incluidos en un perfil software
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/perfilcomponente_soft_".$idioma.".php");
//________________________________________________________________________________________________________
$idperfilsoft=0; 
$descripcionperfil=""; 
if (isset($_GET["idperfilsoft"])) $idperfilsoft=$_GET["idperfilsoft"]; // Recoge parametros
if (isset($_GET["descripcionperfil"])) $descripcionperfil=$_GET["descripcionperfil"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
<LINK rel="stylesheet" type="text/css" href="../hidra.css">
<SCRIPT language="javascript" src="../jscripts/perfilcomponente_soft.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/perfilcomponente_soft_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<INPUT type=hidden value="<? echo $idcentro?>" id=idcentro>	 
	<INPUT type=hidden value="<? echo $idperfilsoft?>" id=idperfilsoft>	 
	<P align=center class=cabeceras><?echo $TbMsg[0]?><BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/confisoft.gif"></P>
	<BR>
	<DIV align=center id="Layer_componentes">
		<SPAN align=center class=presentaciones><B><U><?echo $TbMsg[2]?></U>:&nbsp;<? echo $descripcionperfil?></B></SPAN></P>
		<TABLE width="100%" class="tabla_listados" cellspacing=1 cellpadding=0 >
			 <TR>
				<TH>&nbsp</TH>
				<TH>T</TH>
				<TH><?echo $TbMsg[3]?></TH>
			</TR>
		<?
			$rs=new Recordset; 
			$cmd->texto='SELECT softwares.idsoftware,softwares.descripcion,tiposoftwares.descripcion as hdescripcion,tiposoftwares.urlimg FROM softwares INNER JOIN perfilessoft_softwares ON softwares.idsoftware=perfilessoft_softwares.idsoftware INNER JOIN tiposoftwares ON softwares.idtiposoftware=tiposoftwares.idtiposoftware WHERE perfilessoft_softwares.idperfilsoft='.$idperfilsoft.' ORDER BY tiposoftwares.idtiposoftware,softwares.descripcion';
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
			$cmd->texto='SELECT softwares.idsoftware,softwares.descripcion,tiposoftwares.descripcion as hdescripcion,tiposoftwares.urlimg  FROM softwares  INNER JOIN tiposoftwares ON softwares.idtiposoftware=tiposoftwares.idtiposoftware  '.$strex.' AND softwares.idcentro='.$idcentro.' ORDER BY tiposoftwares.idtiposoftware,softwares.descripcion';
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
