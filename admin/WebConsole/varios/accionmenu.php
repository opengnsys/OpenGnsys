<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: accionmenu.php
// Descripción :
//		Administra la inclusión de items en menus (procedimientos,tareas y trabajos de un determinado Centro)
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/accionmenu_".$idioma.".php");
//________________________________________________________________________________________________________
$idtipoaccion=0; 
$descripcionaccion=""; 
$tipoaccion=0;
$tipoitem=0;
$idmenu=0; 
$idaccionmenu=0; 

if (isset($_GET["idtipoaccion"])) $idtipoaccion=$_GET["idtipoaccion"]; // Recoge parametros
if (isset($_GET["descripcionaccion"])) $descripcionaccion=$_GET["descripcionaccion"]; // Recoge parametros
if (isset($_GET["tipoaccion"])) $tipoaccion=$_GET["tipoaccion"]; // Recoge parametros
if (isset($_GET["tipoitem"])) $tipoitem=$_GET["tipoitem"]; // Recoge parametros
if (isset($_GET["idmenu"])) $idmenu=$_GET["idmenu"]; // Recoge parametros
if (isset($_GET["idaccionmenu"])) $idaccionmenu=$_GET["idaccionmenu"]; // Recoge parametros

$EDICIONACCION=1;
$EDICIONITEMS=2;
$EDICIONITEM=3;
$tipoedicion=0;
if(empty($idmenu)) 
	$tipoedicion=$EDICIONACCION; // Edición desde Acciones
else{
	if(!empty($tipoitem))
		$tipoedicion=$EDICIONITEMS; // Edición de todos los items (privados o públicos )
	else
		$tipoedicion=$EDICIONITEM; // Edición de un item
}
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if($cmd){
	switch($tipoedicion){
			case $EDICIONACCION :
					gestiona_edicionaccion($cmd,$tipoedicion);
					break;
			case $EDICIONITEMS :
					gestiona_edicionitems($cmd,$tipoedicion);
					break;
			case $EDICIONITEM :
					gestiona_edicionitem($cmd,$tipoedicion);
					break;
	}
}
//________________________________________________________________________________________________________
function gestiona_edicionaccion($cmd,$tipoedicion){
	global $TbMsg;
	global $idioma;
	global $idtipoaccion;
	global $descripcionaccion;
	global $tipoaccion;
	global $idcentro;
	global $EJECUCION_PROCEDIMIENTO;
	global $EJECUCION_TAREA;
	global $EJECUCION_TRABAJO;
	global $ITEM_PUBLICO;
	global $ITEM_PRIVADO;

	switch($tipoaccion){
			case $EJECUCION_PROCEDIMIENTO :
				$urlimg='../images/iconos/procedimiento.gif';
				$litacion=$TbMsg[2];
				break;
			case $EJECUCION_TAREA :
				$urlimg='../images/iconos/tareas.gif';
				$litacion=$TbMsg[3];
				break;
			case $EJECUCION_TRABAJO :
				$urlimg='../images/iconos/trabajos.gif';
				$litacion=$TbMsg[4];
				break;
	}
	$rs=new Recordset; 
	$cmd->texto='SELECT menus.idmenu,menus.descripcion,acciones_menus.descripitem,acciones_menus.orden,acciones_menus.idurlimg,acciones_menus.tipoitem FROM menus INNER JOIN acciones_menus ON menus.idmenu=acciones_menus.idmenu WHERE acciones_menus.idtipoaccion='.$idtipoaccion.' AND acciones_menus.tipoaccion='.$tipoaccion. ' ORDER BY menus.descripcion';
	$litcabecera=$TbMsg[0];
	$rs->Comando=&$cmd; 
	$resul=$rs->Abrir();
	?>
	<HTML>
	<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/accionmenu.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/accionmenu_'.$idioma.'.js"></SCRIPT>'?>
	</HEAD>
	<BODY>
	<FORM  name="fdatos"> 
		<input type=hidden value="<? echo $idcentro?>" id=idcentro>	 
		<input type=hidden value="<? echo $idtipoaccion?>" id=idtipoaccion>	 
		<input type=hidden value="<? echo $tipoaccion?>" id=tipoaccion>	 
		<input type=hidden value="<? echo $tipoedicion?>" id=tipoedicion>	 
	</FORM>
	<P align=center class=cabeceras>
		<? echo $litcabecera ?><br>
		<span align=center class=subcabeceras><?echo $TbMsg[1]?></span>&nbsp;<img src="../images/iconos/menus.gif"><br><br>
		<span align=center class=presentaciones><img src="<? echo $urlimg?>">&nbsp;&nbsp;&nbsp;<u><? echo $litacion?></u>:&nbsp;<? echo $descripcionaccion?></span>	
	</P>
	<DIV align=center id="Layer_items">
		<TABLE width="90%" class="tabla_listados" cellspacing=1 cellpadding=0 >
			 <TR>
				<TH></TH>
				<TH><?echo $TbMsg[9]?></TH>
				<TH><?echo $TbMsg[10]?></TH>
				<TH><?echo $TbMsg[11]?></TH>
				<TH><?echo $TbMsg[12]?></TH>
				<TH><?echo $TbMsg[13]?></TH>
				<TH>A</TH>
			</TR>
		<?
		if ($resul){ 
				$rs->Primero();
				$A_W=" AND ";
				$strex="";
				while (!$rs->EOF){
						 echo '<TR>';
						 echo '<TD align=center width="10%" ><INPUT  id=checkbox-'.$rs->campos["idmenu"].' type=checkbox onclick="gestion_acciones('.$rs->campos["idmenu"].',this)" checked ></INPUT></TD>';
						 echo '<TD>&nbsp;'.$rs->campos["descripcion"].'</TD>';
						$parametros=$ITEM_PUBLICO."=".$TbMsg[5]."".chr(13);
						$parametros.=$ITEM_PRIVADO."=".$TbMsg[6]."";
						echo '<TD>'.HTMLCTESELECT($parametros,"tipositems-".$rs->campos["idmenu"],"estilodesple","",$rs->campos["tipoitem"],70).'</TD>';
						echo '<TD id=TDurlimagesitems-'.$rs->campos["idmenu"].' >'.HTMLSELECT($cmd,0,'iconos',$rs->campos["idurlimg"],'idicono','descripcion',160,"","","idtipoicono=2").'</TD>';
						echo '<TD >&nbsp;<INPUT class="formulariodatos" id=descripitem-'.$rs->campos["idmenu"].' style="WIDTH:300px" type=text value="'.$rs->campos["descripitem"].'"></INPUT></TD>';
						// Orden del item del item
						echo '<TD>&nbsp;<INPUT class="formulariodatos" id=orden-'.$rs->campos["idmenu"].' style="WIDTH:20px" type=text value="'.$rs->campos["orden"].'"></INPUT></TD>';
						echo '<TD id="imgact-'.$rs->campos["idmenu"].'"><IMG src="../images/iconos/actualizar.gif" style="cursor:hand" onclick="ActualizarAccion('.$rs->campos["idmenu"].')"></TD>';
						echo '</TR>';
						$strex.= $A_W."menus.idmenu<>".$rs->campos["idmenu"];
						$rs->Siguiente();
				}
			}
			$rs->Cerrar();
			$cmd->texto='SELECT menus.idmenu,menus.descripcion FROM menus  WHERE menus.idcentro='.$idcentro.' '.$strex.' ORDER BY menus.descripcion';
			$rs->Comando=&$cmd; 
			if ($rs->Abrir()){
				$rs->Primero();
				while (!$rs->EOF){
						 echo '<TR>';
						 echo '<TD align=center width="10%" ><INPUT id=checkbox-'.$rs->campos["idmenu"].' type=checkbox onclick="gestion_acciones('.$rs->campos["idmenu"].',this)"  ></INPUT></TD>';
						echo '<TD >&nbsp;'.$rs->campos["descripcion"].'</TD>';
						$parametros="1=".$TbMsg[5]."".chr(13);
						$parametros.="2=".$TbMsg[6]."";
						echo '<TD>'.HTMLCTESELECT($parametros,"tipositems-".$rs->campos["idmenu"],"estilodesple","",1,70).'</TD>';
						echo '<TD id=TDurlimagesitems-'.$rs->campos["idmenu"].'>'.HTMLSELECT($cmd,0,'iconos',0,'idicono','descripcion',160,"","","idtipoicono=2").'</TD>';
						echo '<TD >&nbsp;<INPUT class="formulariodatos"  id=descripitem-'.$rs->campos["idmenu"].' style="WIDTH:300px" type=text value="'.$descripcionaccion.'"></INPUT></TD>';
						// Orden del item del item
						echo '<TD>&nbsp;<INPUT class="formulariodatos" id=orden-'.$rs->campos["idmenu"].' style="WIDTH:20px" type=text value=0></INPUT></TD>';
						echo '<TD id="imgact-'.$rs->campos["idmenu"].'"><IMG src="../images/iconos/nada.gif" ></TD>';
						echo '</TR>';
						$rs->Siguiente();
				}
			}
			$rs->Cerrar();
?>
		</TABLE>
	</DIV>		
	<DIV id="Layer_nota" align=center>
		<BR>
		<SPAN align=center class=notas><I><? echo$TbMsg[14]?></I></SPAN>
	</DIV>
</FORM>
<?
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
}
//--------------------------------------------------------------------------------------------------------------------------------------------------------------
function gestiona_edicionitems($cmd,$tipoedicion){
	global $TbMsg;
	global $idioma;
	global $tipoitem;
	global $idmenu;
	global $idcentro;
	global $EJECUCION_PROCEDIMIENTO;
	global $EJECUCION_TAREA;
	global $EJECUCION_TRABAJO;
	global $ITEM_PUBLICO;
	global $ITEM_PRIVADO;

	$rs=new Recordset; 
	$cmd->texto='SELECT acciones_menus.idaccionmenu,acciones_menus.orden,acciones_menus.tipoaccion,acciones_menus.idtipoaccion,menus.idmenu,menus.descripcion,acciones_menus.descripitem,acciones_menus.idurlimg,acciones_menus.tipoitem FROM menus INNER JOIN acciones_menus ON menus.idmenu=acciones_menus.idmenu WHERE acciones_menus.tipoitem='.$tipoitem ;
	switch($tipoitem){
				case $ITEM_PUBLICO :
					$litcabecera=$TbMsg[7];
					break;
				case $ITEM_PRIVADO :
					$litcabecera=$TbMsg[8];
					break;
	}
	 $cmd->texto.=" AND menus.idmenu=".$idmenu;
	 $cmd->texto.=" ORDER BY acciones_menus.orden";
	 $rs->Comando=&$cmd; 
	$resul=$rs->Abrir();
	?>
	<HTML>
	<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/accionmenu.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/accionmenu_'.$idioma.'.js"></SCRIPT>'?>
	</HEAD>
	<BODY>
	<FORM  name="fdatos"> 
		<INPUT type=hidden value="<? echo $idcentro?>" id=idcentro>	 
		<INPUT type=hidden value="<? echo $tipoitem?>" id=tipoitem>	 
		<INPUT type=hidden value="<? echo $idmenu?>" id=idmenu>	 
		<INPUT type=hidden value="<? echo $tipoedicion?>" id=tipoedicion>	 
	</FORM>
	<?
	echo '<P align=center class=cabeceras>'.$litcabecera.'<br>';
	echo '<span align=center class=subcabeceras>'.$TbMsg[1].'</span>&nbsp;<img src="../images/iconos/menus.gif"><br><br>';
	echo '<span align=center class=presentaciones><img src="../images/iconos/menu.gif">&nbsp;&nbsp;<u>Menu</u>:'.$rs->campos["descripcion"].'<br>' ;
	?>
	</P>
	<DIV align=center id="Layer_items">
		<TABLE width="90%" class="tabla_listados" cellspacing=1 cellpadding=0 >
		<?	
			echo'	</TR>';
				echo '<TH>'.$TbMsg[11].'</TH>';
				echo  '<TH>A</TH>';
				echo '<TH>'.$TbMsg[12].'</TH>';
				echo '<TH>'.$TbMsg[13].'</TH>';
				echo '<TH>A</TH>';
			echo'	</TR>';
			if ($resul){ 
				$rs->Primero();
				while (!$rs->EOF){
						 echo '<TR>';
						switch($rs->campos["tipoaccion"]){
								case $EJECUCION_PROCEDIMIENTO :
									$urlimg='../images/iconos/procedimiento.gif';
									break;
								case $EJECUCION_TAREA :
									$urlimg='../images/iconos/tareas.gif';
									break;
								case $EJECUCION_TRABAJO :
									$urlimg='../images/iconos/trabajos.gif';
									break;
						}
						// Nombre de la imagen
						echo '<TD id=TDurlimagesitems-'.$rs->campos["idaccionmenu"].'  >'.HTMLSELECT($cmd,0,'iconos',$rs->campos["idurlimg"],'idicono','descripcion',160,"","","idtipoicono=2").'</TD>';
						echo '<TD><IMG src="'.$urlimg.'">';
						// Literal del item
						echo '<TD >&nbsp;<INPUT class="formulariodatos" id=descripitem-'.$rs->campos["idaccionmenu"].' style="WIDTH:300px" type=text value="'.$rs->campos["descripitem"].'"></INPUT></TD>';
						// Orden del item del item
						echo '<TD>&nbsp;<INPUT class="formulariodatos" id=orden-'.$rs->campos["idaccionmenu"].' style="WIDTH:20px" type=text value="'.$rs->campos["orden"].'"></INPUT></TD>';
						echo '<TD id="imgact-'.$rs->campos["idaccionmenu"].'"><IMG src="../images/iconos/actualizar.gif" style="cursor:hand" onclick="ActualizarItems('.$rs->campos["tipoaccion"].','.$rs->campos["idtipoaccion"].','.$rs->campos["idaccionmenu"].')"></TD>';
						echo '</TR>';
						$rs->Siguiente();
				}
			}
			$rs->Cerrar();
		?>
		</TABLE>
	</DIV>		
	<?
	echo '<br>';
	echo '<TABLE border=0 align=center>';
		echo '<TR>';
			echo '<TD width=20>&nbsp;</TD>';
			echo '<TD align=center><IMG src="../images/boton_cerrar.gif" style="cursor:hand"  onclick="javascript:self.close();"></TD>';
		echo '</TR>';
	echo '</TABLE>';
	?>
</FORM>
<?
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
}
//--------------------------------------------------------------------------------------------------------------------------------------------------------------
function gestiona_edicionitem($cmd,$tipoedicion){
	global $TbMsg;
	global $idioma;
	global $idmenu;
	global $idcentro;
	global $idaccionmenu;
	global $descripcionaccion;
	global $EJECUCION_PROCEDIMIENTO;
	global $EJECUCION_TAREA;
	global $EJECUCION_TRABAJO;
	global $ITEM_PUBLICO;
	global $ITEM_PRIVADO;

	$rs=new Recordset; 
	$cmd->texto='SELECT acciones_menus.idaccionmenu,acciones_menus.orden,acciones_menus.idaccionmenu,acciones_menus.tipoaccion,acciones_menus.idtipoaccion,menus.idmenu,menus.descripcion,acciones_menus.descripitem,acciones_menus.idurlimg,acciones_menus.tipoitem FROM menus INNER JOIN acciones_menus ON menus.idmenu=acciones_menus.idmenu WHERE acciones_menus.idaccionmenu='.$idaccionmenu;
	$litcabecera="Item";
	 $rs->Comando=&$cmd; 
	$resul=$rs->Abrir();
	
	switch($rs->campos["tipoaccion"]){
			case $EJECUCION_PROCEDIMIENTO :
				$urlimg='../images/iconos/procedimiento.gif';
				$litacion=$TbMsg[2];
				break;
			case $EJECUCION_TAREA :
				$urlimg='../images/iconos/tareas.gif';
				$litacion=$TbMsg[3];
				break;
			case $EJECUCION_TRABAJO :
				$urlimg='../images/iconos/trabajos.gif';
				$litacion=$TbMsg[4];
				break;
	}
	?>
	<HTML>
	<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/accionmenu.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/accionmenu_'.$idioma.'.js"></SCRIPT>'?>
	</HEAD>
	<BODY>
	<FORM  name="fdatos"> 
		<INPUT type=hidden value="<? echo $idcentro?>" id=idcentro>	 
		<INPUT type=hidden value="<? echo $idmenu?>" id=idmenu>	 
		<INPUT type=hidden value="<? echo $tipoedicion?>" id=tipoedicion>	 
	</FORM>
	<?
	echo '<P align=center class=cabeceras>'.$litcabecera.'<br>';
	echo '<span align=center class=subcabeceras>'.$TbMsg[1].'</span>&nbsp;<img src="../images/iconos/menus.gif"><br><br>';
	echo '<span align=center class=presentaciones><img src="../images/iconos/menu.gif">&nbsp;&nbsp;<u>Menu</u>:'.$rs->campos["descripcion"].'<br>' ;
	echo '<span align=center class=presentaciones><img src="'. $urlimg.'">&nbsp;&nbsp;&nbsp;<u>'. $litacion.'</u>:&nbsp;'. $descripcionaccion.'</span>	';
	?>
	</P>
	<DIV align=center id="Layer_items">
		<TABLE width="90%" class="tabla_listados" cellspacing=1 cellpadding=0 >
		<?	
			echo'	</TR>';
				echo '<TH>'.$TbMsg[10].'</TH>';
				echo '<TH>'.$TbMsg[11].'</TH>';
				echo '<TH>'.$TbMsg[12].'</TH>';
				echo '<TH>'.$TbMsg[13].'</TH>';
				echo '<TH>A</TH>';
			echo'	</TR>';
			if ($resul){ 
				$rs->Primero();
				while (!$rs->EOF){
						 echo '<TR>';
						// Tipo de item
						$parametros=$ITEM_PUBLICO."=".$TbMsg[5]."".chr(13);
						$parametros.=$ITEM_PRIVADO."=".$TbMsg[6]."";
						echo '<TD>'.HTMLCTESELECT($parametros,"tipositems-".$rs->campos["idaccionmenu"],"estilodesple","",$rs->campos["tipoitem"],70).'</TD>';
						// Nombre de la imagen
						echo '<TD id=TDurlimagesitems-'.$rs->campos["idaccionmenu"].'  >'.HTMLSELECT($cmd,0,'iconos',$rs->campos["idurlimg"],'idicono','descripcion',160,"","","idtipoicono=2").'</TD>';
						// Literal del item
						echo '<TD >&nbsp;<INPUT class="formulariodatos" id=descripitem-'.$rs->campos["idaccionmenu"].' style="WIDTH:300px" type=text value="'.$rs->campos["descripitem"].'"></INPUT></TD>';
						// Orden del item del item
						echo '<TD>&nbsp;<INPUT class="formulariodatos" id=orden-'.$rs->campos["idaccionmenu"].' style="WIDTH:20px" type=text value="'.$rs->campos["orden"].'"></INPUT></TD>';
						echo '<TD id="imgact-'.$rs->campos["idaccionmenu"].'"><IMG src="../images/iconos/actualizar.gif" style="cursor:hand" onclick="ActualizarItem('.$rs->campos["tipoaccion"].','.$rs->campos["idtipoaccion"].','.$rs->campos["idaccionmenu"].')"></TD>';
						echo '</TR>';
						$rs->Siguiente();
				}
			}
			$rs->Cerrar();
		?>
		</TABLE>
	</DIV>		
	<?
	echo '<br>';
	echo '<TABLE border=0 align=center>';
		echo '<TR>';
			echo '<TD width=20>&nbsp;</TD>';
			echo '<TD align=center><IMG src="../images/boton_cerrar.gif" style="cursor:hand"  onclick="javascript:self.close();"></TD>';
		echo '</TR>';
	echo '</TABLE>';
	?>
</FORM>
<?
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
}
?>