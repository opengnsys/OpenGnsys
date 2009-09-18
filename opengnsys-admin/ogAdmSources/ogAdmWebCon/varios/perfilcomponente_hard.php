<?
// *************************************************************************************************************************************************
// Aplicaci� WEB: Hidra
// Copyright 200-2005 Jos�Manuel Alonso. Todos los derechos reservados.
// Fecha Creaci�: A� 2003-2004
// Fecha �tima modificaci�: Febrero-2005
// Nombre del fichero: perfilcomponente_hard.php
// Descripci� : 
//		Administra los componentes hardware incluidos en un perfil harware
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/perfilcomponente_hard_".$idioma.".php");
//________________________________________________________________________________________________________
$idperfilhard=0; 
$descripcionperfil=""; 
if (isset($_GET["idperfilhard"])) $idperfilhard=$_GET["idperfilhard"]; // Recoge parametros
if (isset($_GET["descripcionperfil"])) $descripcionperfil=$_GET["descripcionperfil"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexi� con servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
<LINK rel="stylesheet" type="text/css" href="../hidra.css">
<SCRIPT language="javascript" src="../jscripts/perfilcomponente_hard.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/perfilcomponente_hard_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<INPUT type=hidden value="<? echo $idcentro?>" id=idcentro>	 
	<INPUT type=hidden value="<? echo $idperfilhard?>" id=idperfilhard>	 
	<P align=center class=cabeceras><?echo $TbMsg[0]?><BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/confihard.gif"></P>
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
			$cmd->texto='SELECT hardwares.idhardware,hardwares.descripcion,tipohardwares.descripcion as hdescripcion,tipohardwares.urlimg,fabricantes.nombre as nombrefabricante,tipohardwares.pci FROM hardwares INNER JOIN perfileshard_hardwares ON hardwares.idhardware=perfileshard_hardwares.idhardware INNER JOIN tipohardwares ON hardwares.idtipohardware=tipohardwares.idtipohardware LEFT OUTER JOIN fabricantes  ON fabricantes.codigo=hardwares.codigo1 WHERE perfileshard_hardwares.idperfilhard='.$idperfilhard.' ORDER BY tipohardwares.idtipohardware,hardwares.descripcion';
			$rs->Comando=&$cmd; 
			if ($rs->Abrir()){ 
				$rs->Primero();
				$A_W=" WHERE ";
				$strex="";
				while (!$rs->EOF){
						 echo '<TR>';
						 echo '<TD align=center width="10%" ><INPUT type=checkbox onclick="gestion_componente('.$rs->campos["idhardware"].',this)" checked ></INPUT></TD>';
						 echo '<TD align=center width="10%" ><IMG alt="'. $rs->campos["hdescripcion"].'"src="'.$rs->campos["urlimg"].'"></TD>';
						if ($rs->campos["pci"]>0)
							$fabricante="(".trim($rs->campos["nombrefabricante"]).")";
						else
							$fabricante="";
						 echo '<TD  width="80%" >&nbsp;'.$fabricante.$rs->campos["descripcion"].'</TD>';
						 echo '</TR>';
						 $strex.= $A_W."hardwares.idhardware<>".$rs->campos["idhardware"];
						$A_W=" AND ";
						$rs->Siguiente();
				}
			}
			$rs->Cerrar();
			$cmd->texto='SELECT hardwares.idhardware,hardwares.descripcion,tipohardwares.descripcion as hdescripcion,tipohardwares.urlimg,fabricantes.nombre as nombrefabricante,tipohardwares.pci  FROM hardwares  INNER JOIN tipohardwares ON hardwares.idtipohardware=tipohardwares.idtipohardware  LEFT OUTER JOIN fabricantes   ON fabricantes.codigo=hardwares.codigo1  '.$strex.' AND hardwares.idcentro='.$idcentro.'  ORDER BY tipohardwares.idtipohardware,hardwares.descripcion';
			$rs->Comando=&$cmd; 
			if ($rs->Abrir()){
				$rs->Primero();
				while (!$rs->EOF){
						 echo '<TR>';
						 echo '<TD align=center width="10%" ><INPUT type=checkbox onclick="gestion_componente('.$rs->campos["idhardware"].',this)"  ></INPUT></TD>';
						 echo '<TD align=center width="10%" ><IMG alt="'. $rs->campos["hdescripcion"].'"src="'.$rs->campos["urlimg"].'"></TD>';
						if ($rs->campos["pci"]>0)
							$fabricante="(".trim($rs->campos["nombrefabricante"]).")";
						else
							$fabricante="";
						 echo '<TD width="80%" >&nbsp;'.$fabricante.$rs->campos["descripcion"].'</TD>';
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
