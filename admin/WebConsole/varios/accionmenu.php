<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: accionmenu.php
// Descripción :
//		Administra la inclusión de items en menus (procedimientos,tareas y trabajos de un determinado Centro)
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
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

$idmenu=0;
$descripcionmenu="";

if (isset($_GET["idtipoaccion"])) $idtipoaccion=$_GET["idtipoaccion"]; 
if (isset($_GET["descripcionaccion"])) $descripcionaccion=$_GET["descripcionaccion"]; 
if (isset($_GET["tipoaccion"])) $tipoaccion=$_GET["tipoaccion"]; 

if (isset($_GET["idmenu"])) $idmenu=$_GET["idmenu"]; 
if (isset($_GET["descripcionmenu"])) $descripcionmenu=$_GET["descripcionmenu"]; 

if(empty($idmenu)) $op=1; else $op=2; // Viene de "acciones" o de "menus"
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
if($op==1){ // Viene de "acciones"
	switch($tipoaccion){
			case $EJECUCION_PROCEDIMIENTO :
				$urlimg='../images/iconos/procedimiento.gif';
				$litcabecera=$TbMsg[2];
				$litacion=$TbMsg[2];
				$litdescri=$descripcionaccion;
				break;
			case $EJECUCION_TAREA :
				$urlimg='../images/iconos/tareas.gif';
				$litcabecera=$TbMsg[3];
				$litacion=$TbMsg[3];
				$litdescri=$descripcionaccion;
				break;
	}
}
else{ // Viene de menus
				$urlimg='../images/iconos/menu.gif';
				$litcabecera=$TbMsg[4];
				$litacion=$TbMsg[4];
				$litdescri=$descripcionmenu;
}
//________________________________________________________________________________________________________
	?>
	<HTML>
	<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/accionmenu.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>	
	<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>	
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/accionmenu_'.$idioma.'.js"></SCRIPT>'?>
	</HEAD>
	<BODY>
	<FORM  name="fdatos"> 
		<input type=hidden value="<?php echo $idcentro?>" id=idcentro>	 
		<input type=hidden value="<?php echo $idtipoaccion?>" id=idtipoaccion>	 
		<input type=hidden value="<?php echo $tipoaccion?>" id=tipoaccion>	 
	</FORM>
	<P align=center class=cabeceras><?php echo $litcabecera ?><br>
		<span align=center class=subcabeceras><?php echo $TbMsg[1]?></span>&nbsp;<img src="../images/iconos/menus.gif"><br><br>
		<span align=center class=presentaciones>
			<img src="<?php echo $urlimg?>">&nbsp;&nbsp;&nbsp;
			<u><?php echo $litacion?></u>:&nbsp;<?php echo $litdescri?></span>	
	</P>
	<DIV align=center id="Layer_items">
		<TABLE width="90%" class="tabla_listados" cellspacing=1 cellpadding=0 >
			 <TR id="TR_menus">
				<TH>&nbsp;</TH>
				<?php
					if($op==1) // Si viene desde "acciones" ...
						echo '<TH>'.$TbMsg[9].'</TH>';
					else		
						echo '<TH align=center>'.$TbMsg[15].'</TH>';	
				?>	
				<TH><?php echo $TbMsg[10]?></TH>
				<TH><?php echo $TbMsg[11]?></TH>
				<TH><?php echo $TbMsg[12]?></TH>
				<TH><?php echo $TbMsg[13]?></TH>				
				<?php
					if($op==2){ // Si viene desde "menus" ...					
						echo '<TH style="visibility:hidden">&nbsp;</TH>';						
						echo '<TH style="visibility:hidden">&nbsp;</TH>';						
					}				
				?>
			</TR>
<?php	
//________________________________________________________________________________________________________
	
if(!empty($idmenu)) // Viene de la página de menús
{ 
		$cmd->texto="SELECT  menus.idmenu, menus.descripcion AS descripcionmenu,
				     acciones_menus.idtipoaccion, acciones_menus.tipoaccion,
				     acciones_menus.tipoitem, acciones_menus.idurlimg,
				     acciones_menus.descripitem, acciones_menus.orden
				FROM menus 
				INNER JOIN acciones_menus ON acciones_menus.idmenu=menus.idmenu 
				WHERE acciones_menus.idmenu=".$idmenu."
				ORDER BY acciones_menus.tipoitem, menus.descripcion";

		pintaMenus($cmd,$idmenu,0,2);						
}
else
{
		$cmd->texto="SELECT  menus.idmenu, menus.descripcion AS descripcionmenu,
				     acciones_menus.idtipoaccion,acciones_menus.tipoaccion,
				     acciones_menus.tipoitem,acciones_menus.idurlimg,
				     acciones_menus.descripitem,acciones_menus.orden
				FROM menus 
				INNER JOIN acciones_menus ON acciones_menus.idmenu=menus.idmenu 
				WHERE (acciones_menus.idtipoaccion=".$idtipoaccion." AND acciones_menus.tipoaccion=".$tipoaccion.")
				ORDER BY menus.descripcion";
							
		$idmenus=pintaMenus($cmd,$idtipoaccion,$tipoaccion,1)."0"; // Añade el identificador 0
		$cmd->texto="SELECT  menus.idmenu, menus.descripcion AS descripcionmenu,
				     0 as idtipoaccion, 0 AS tipoaccion,
				     0 AS tipoitem, '' AS idurlimg,
				     '' AS descripitem,0 AS orden
				FROM menus 
				WHERE idmenu NOT IN (".$idmenus.")
				ORDER BY menus.descripcion";	

		pintaMenus($cmd,$idtipoaccion,$tipoaccion,1);	
}
//________________________________________________________________________________________________________
?>
		</TABLE>
	</DIV>
	<BR>	
<TABLE align=center>
	<TR>
		<TD><A href="#botones"><IMG border=0 src="../images/boton_confirmar.gif" onclick="confirmar(<?php echo $op?>)" ></A></TD>
	</TR>
</TABLE>
	<BR>		
	<DIV id="Layer_nota" align=center>
		<SPAN align=center class=notas><I><?php echo$TbMsg[14]?></I></SPAN>
	</DIV>
</FORM>


</BODY>
</HTML>
<?php
//________________________________________________________________________________________________________
// Descripción:
//	Muestra la tabla de items a incluir en menús
//	Parámetros:
//		$cmd: Objeto comando (Operativo)
//		$identificador: Identificador de la acción si viene de acciones, identificador del menu si viene de "menus"
//		$tipoaccion: Tipo de acción (Procedimiento o Tarea
//		$op: Indica si esta página se ejecuta desde "acciones" o desde "menus"
//				 1: desde acciones
//				 2: desde menus
//________________________________________________________________________________________________________

function pintaMenus($cmd,$identificador,$tipoaccion,$op)
{ 
	global $pagerror;
	global $TbMsg;
	global $ITEM_PUBLICO;
	global $ITEM_PRIVADO;
	global $op_modificacion;
	global $descripcionaccion;
	global $EJECUCION_PROCEDIMIENTO;
	global $EJECUCION_TAREA;
	
	// echo $cmd->texto;	
	$idmenus=""; // Identificadores de menus para segunda consulta	
	$litcabecera=$TbMsg[0];
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if(!$rs->Abrir())
		Header('Location: '.$pagerror.'?herror=3'); // Error de lectura
		
	while (!$rs->EOF){
		$idmenus.=$rs->campos["idmenu"].",";
		echo '<TR>';
		/* Ckeckbox para incluir o eliminar items en el menú */
		echo '<TD align=center>';
		echo '<INPUT  id='.$rs->campos["idmenu"].' type=checkbox ';
		switch($op){
			case 1: // Se ejecuta desde "acciones"
				if($rs->campos["idtipoaccion"]==$identificador && $rs->campos["tipoaccion"]==$tipoaccion)
					echo ' value="1" checked ';
				else
					echo ' value="0"';
				break;
			case 2:	// Se ejecuta desde "menus"		
				if($rs->campos["idmenu"]==$identificador)
					echo ' value="1" checked ';
				else
					echo ' value="0"';
				break;
		}
		echo '></INPUT></TD>';

		/* Nombre del menú  si viene de "acciones" o tipo de acción si viene desde "menus" */
		if($op==1)
			echo '<TD align=center>&nbsp;'.$rs->campos["descripcionmenu"].'</TD>';		
		else{				
			switch($rs->campos["tipoaccion"]){
				case $EJECUCION_PROCEDIMIENTO :
					$urlimg='../images/iconos/procedimiento.gif';
					break;
				case $EJECUCION_TAREA :
					$urlimg='../images/iconos/tareas.gif';
					break;
			}
			echo '<TD align=center><IMG src="'.$urlimg.'"></TD>';	
		}		
		
		/* Tipo de item */
		$parametros=$ITEM_PUBLICO."=".$TbMsg[5]."".chr(13);
		$parametros.=$ITEM_PRIVADO."=".$TbMsg[6]."";
		echo '<TD align=center>'.HTMLCTESELECT($parametros,"tipositems-".$rs->campos["idmenu"],"estilodesple","",$rs->campos["tipoitem"],70).'</TD>';
		
		/* Imagen del item */
		echo '<TD align=center>';
	 	echo HTMLSELECT($cmd,0,'iconos',$rs->campos["idurlimg"],'idicono','descripcion',160,"","","idtipoicono=2");
	 	echo '</TD>';
	 	
		/* Descripción del item */
		$descripitem=$rs->campos["descripitem"];
		if(empty($descripitem)) $descripitem=$descripcionaccion;	
	 	echo '<TD align=center><INPUT class="formulariodatos" id=descripitem-'.$rs->campos["idmenu"].' 
	 					style="WIDTH:300px" type=text value="'.$descripitem.'"></INPUT></TD>';
		// Orden del item del item
		$orden=$rs->campos["orden"];
		if(empty($orden)) $orden=1;
		echo '<TD align=center><INPUT class="formulariodatos" id=orden-'.$rs->campos["idmenu"].' 
						style="WIDTH:20px" type=text value="'.$orden.'"></INPUT></TD>';
		
		if($op==2){ // Si viene desde "menus" ...					
			echo '<TD style="visibility:hidden" align=center>'.$rs->campos["idtipoaccion"].'</TD>';						
			echo '<TD style="visibility:hidden" align=center>'.$rs->campos["tipoaccion"].'</TD>';	
		}	
		echo '</TR>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($idmenus); // retorna identificadores de menus implicados en la consulta
}
?>

