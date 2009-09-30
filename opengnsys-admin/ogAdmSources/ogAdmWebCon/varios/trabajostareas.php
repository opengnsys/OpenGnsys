<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Copyright 200-2005 José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: trabajostareas.php
// Descripción : 
//		Muestra las tareas que forman parte de un trabajo y sus comandos
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/trabajostareas_".$idioma.".php");
//________________________________________________________________________________________________________
$idtrabajo=0; 

$descripciontrabajo=""; 
if (isset($_GET["idtrabajo"])) $idtrabajo=$_GET["idtrabajo"]; // Recoge parametros
if (isset($_GET["descripciontrabajo"])) $descripciontrabajo=$_GET["descripciontrabajo"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="../jscripts/trabajostareas.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/trabajostareas_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<INPUT type=hidden value="<? echo $idcentro?>" id=idcentro>	 
	<INPUT type=hidden value="<? echo $idtrabajo?>" id=idtrabajo>	 
	<p align=center class=cabeceras><IMG src="../images/iconos/trabajos.gif">&nbsp;<?echo $TbMsg[0]?><br>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/tareas.gif"><BR><BR>
	<SPAN align=center class=presentaciones><B><?echo $TbMsg[2]?>:&nbsp;</B><? echo $descripciontrabajo?></SPAN></P>
	<DIV align=center id="Layer_componentes">
		<TABLE width="100%" class="tabla_listados" cellspacing=1 cellpadding=0 >
			 <TR>
				<TH></TH>
				<TH align=left>&nbsp;<?echo $TbMsg[3]?></TH>
				<TH>Ord.</TH>
				<TH>A</TH>
			</TR>
		<?
			$rs=new Recordset; 
			$cmd->texto='SELECT tareas.idtarea,tareas.descripcion,trabajos_tareas.orden FROM tareas INNER JOIN trabajos_tareas ON tareas.idtarea=trabajos_tareas.idtarea  WHERE trabajos_tareas.idtrabajo='.$idtrabajo.' ORDER BY trabajos_tareas.orden';
			$rs->Comando=&$cmd; 
			if ($rs->Abrir()){ 
				$rs->Primero();
				$A_W=" AND ";
				$strex="";
				while (!$rs->EOF){
						 echo '<TR>';
						 echo '<TD align=center width="10%" ><INPUT  id=checkbox-'.$rs->campos["idtarea"].' type=checkbox						 onclick="gestion_tareas('.$rs->campos["idtarea"].',this)" checked ></INPUT></TD>';
						// Descripcion de la tarea
						 echo '<TD>&nbsp;'.$rs->campos["descripcion"].'</TD>';
						// Orden del item del item
						echo '<TD align=center >&nbsp;<INPUT class="formulariodatos" id=orden-'.$rs->campos["idtarea"].' style="WIDTH:20px" type=text value="'.$rs->campos["orden"].'"></INPUT></TD>';
						echo '<TD align=center id="imgact-'.$rs->campos["idtarea"].'"><IMG src="../images/iconos/actualizar.gif" style="cursor:hand" onclick="ActualizarAccion('.$rs->campos["idtarea"].')"></TD>';
						echo '</TR>';
						 $strex.= $A_W."tareas.idtarea<>".$rs->campos["idtarea"];
						$rs->Siguiente();
				}
			}
			$rs->Cerrar();
			$cmd->texto='SELECT tareas.idtarea,tareas.descripcion  FROM tareas INNER JOIN tareas_comandos ON tareas.idtarea=tareas_comandos.idtarea GROUP BY  tareas.idcentro,tareas.idtarea,tareas.descripcion HAVING tareas.idcentro='.$idcentro.' '.$strex.' ORDER BY tareas.descripcion';
			$rs->Comando=&$cmd; 
			if ($rs->Abrir()){
				$rs->Primero();
				while (!$rs->EOF){
						 echo '<TR>';
						 echo '<TD align=center width="10%" ><INPUT id=checkbox-'.$rs->campos["idtarea"].' type=checkbox onclick="gestion_tareas('.$rs->campos["idtarea"].',this)"  ></INPUT></TD>';
 						// Descripcion de la tarea
						echo '<TD >&nbsp;'.$rs->campos["descripcion"].'</TD>';
						// Orden del item del item
						echo '<TD align=center >&nbsp;<INPUT class="formulariodatos" id=orden-'.$rs->campos["idtarea"].' style="WIDTH:20px" type=text value=0></INPUT></TD>';
						echo '<TD align=center  id="imgact-'.$rs->campos["idtarea"].'"><IMG src="../images/iconos/nada.gif" ></TD>';
						echo '</TR>';
						$rs->Siguiente();
				}
			}
			$rs->Cerrar();
		?>
		</TABLE>
	</DIV>		
	<DIV id="Layer_nota" align=center >
		<br>
		<SPAN align=center class=notas><I><?echo $TbMsg[4]?>.</I></SPAN>
	</DIV>
</FORM>
<?
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
