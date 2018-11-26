<?php
// ********************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Agosto-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: barramenu.php
// Descripción :Este fichero implementa el menu general de la Aplicación
// ********************************************************************************************************
// Compatibilidad
$device="";$device = strtolower($_SERVER['HTTP_USER_AGENT']);
if(stripos($device,'iphone') !== false ){$device="iphone";}
elseif  (stripos($device,'ipad') !== false) {$device="ipad";}
elseif (stripos($device,'android') !== false){$device="android";}
else{$device=0;}
$version=@json_decode(file_get_contents("/opt/opengnsys/doc/VERSION.json"))->version;
if(preg_match("/1.0.4/",$version) == TRUE ){$version=4;}
// ********************************************************************************************************
include_once("./includes/ctrlacc.php");
include_once("./includes/constantes.php");
include_once("./includes/CreaComando.php");
include_once("./clases/AdoPhp.php");
include_once("./idiomas/php/".$idioma."/barramenu_".$idioma.".php");
//________________________________________________________________________________________________________

//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexi�n con servidor B.D.
//___________________________________________________________________________________________________
?>
<HTML>
	<HEAD>
		<TITLE>Administración web de aulas</TITLE>
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		<LINK rel="stylesheet" type="text/css" href="estilos.css">
		<SCRIPT language="javascript">
			var currentOp=null;
		//________________________________________________________________________________________________________
		function resaltar(o){
				if (o==currentOp) return;
				o.style.borderBottomColor="#808080";
				o.style.borderRightColor="#808080";
				o.style.borderTopColor="#ffffff";
				o.style.borderLeftColor="#ffffff"
		}
		//________________________________________________________________________________________________________
		function desresaltar(o){
				if (o==currentOp) return;
				o.style.borderBottomColor="#d4d0c8";
				o.style.borderRightColor="#d4d0c8";
				o.style.borderTopColor="#d4d0c8";
				o.style.borderLeftColor="#d4d0c8"
		}
		//________________________________________________________________________________________________________
		function eleccion(o,op){
				var opadre=window.parent; // Toma frame padre
				opadre.frames["frame_contenidos"].document.location.href="nada.php";
				var href;
				var 	href2="nada.php";
				var 	href3="./api/tree.html";
				var 	href4="./api/main.html";
				var 	href5="./principal/ayuda.php";
				var	device="<?php echo $device;?>";
				var	version="<?php echo $version;?>";


				switch(op){
					case 1: 
							if (device!="0"){
							href="./principal/aulas.device.php";
							break;}
							else{href="./principal/aulas.php"; 
							break;}
							
					case 2:
							if (device!="0"){
							href="./principal/acciones.device.php";
							break;}
							else{href="./principal/acciones.php";
							break;}
					case 3:
							if (device!="0"){
								if (version=="4"){
								href="./principal/imagenes.device4.php";
								break;}
								else{href="./principal/imagenes.device.php";
								break;}
							}else{href="./principal/imagenes.php";
							break;}
					case 4:
							if (device!="0"){
							href="./principal/hardwares.device.php";
							break;}
							else{href="./principal/hardwares.php";
							break;}
					case 5:
							if (device!="0"){
							href="./principal/softwares.device.php";
							break;}
							else{href="./principal/softwares.php";
							break;}
					case 6:
							if (device!="0"){
							href="./principal/repositorios.device.php";
							break;}
							else{href="./principal/repositorios.php";
							break;}
					case 7:
							if (device!="0"){
							href="./principal/menus.device.php";
							break;}
							else{href="./principal/menus.php";
							break;}
					case 8:
							href="./principal/reservas.php";
							break;
					case 9:
							if (device!="0"){
							href="./principal/administracion.device.php";
							break;}
							else{href="./principal/administracion.php";
							break;}
					case 10:
							href="./images/L_Iconos.php";
							href2="./images/M_Iconos.php";
							break;
					case 11:
							if (device!="0"){
							href="./principal/administracion.device.php";
							href2="./principal/boot_grub4dos.php";
							break;}
							else{href="./principal/administracion.php";
							href2="./principal/boot_grub4dos.php";
							break;}
					case 13:
							href="./principal/usuarios.php";
							break;
					case 14:
						href="./principal/aulas.php";
						href2="./varios/buscar.php";
						break;
				}
				var oldOp=currentOp;
				currentOp=o;
				if (oldOp) desresaltar(oldOp);
				currentOp.style.borderBottomColor="#ffffff";
				currentOp.style.borderRightColor="#ffffff";
				currentOp.style.borderTopColor="#808080";
				currentOp.style.borderLeftColor="#808080";
				if(op<20){
					opadre.frames["frame_arbol"].document.location.href=href;
					opadre.frames["frame_contenidos"].document.location.href=href2
				}
				else{
						switch(op){
							case 21: 				
									window.top.location.href="acceso.php";
									break;
							case 22: 		
									opadre.frames["frame_contenidos"].document.location.href=href5;
									break;
						}
				}
		}
	//________________________________________________________________________________________________________
		</SCRIPT>
	</HEAD>
	<BODY style="background-color: #d4d0c8;">
		<FORM name=fdatos>
			<TABLE border=0 width=100% style="POSITION:absolute;LEFT:0px;TOP:0px" cellPadding=2 cellSpacing=0>
				<TR>
					<TD align=left>
						<TABLE  class=menupral align=left cellPadding=1 cellSpacing=0 >
						  <TR valign=baseline>
							<TD width=10><IMG src="./images/iconos/pomo.gif"></TD>
							<?php if($idtipousuario!=$SUPERADMINISTRADOR){?>
								<TD onclick=eleccion(this,1); onmouseout=desresaltar(this); onmouseover=resaltar(this)>
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/centros.gif">&nbsp;<SPAN class="menupral"><?php echo $TbMsg[0]?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
								<TD onclick=eleccion(this,2); onmouseout=desresaltar(this); onmouseover=resaltar(this); align=middle>
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/acciones.gif">&nbsp;<SPAN class=menupral ><?php echo $TbMsg[1]?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
								<TD onclick=eleccion(this,3); onmouseout=desresaltar(this); onmouseover=resaltar(this); align=middle>
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/imagenes.gif">&nbsp;<SPAN class=menupral ><?php echo $TbMsg[2]?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
								<TD onclick=eleccion(this,4); onmouseout=desresaltar(this); onmouseover=resaltar(this); align=middle>
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/confihard.gif">&nbsp;<SPAN class=menupral ><?php echo  $TbMsg[3] ?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
								<TD onclick=eleccion(this,5); onmouseout=desresaltar(this); onmouseover=resaltar(this); align=middle>
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/confisoft.gif">&nbsp;<SPAN class=menupral ><?php echo  $TbMsg[4] ?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
								<TD onclick=eleccion(this,6); onmouseout=desresaltar(this); onmouseover=resaltar(this); align=middle>
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/repositorio.gif">&nbsp;<SPAN class=menupral ><?php echo  $TbMsg[5] ?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
								<TD onclick=eleccion(this,7); onmouseout=desresaltar(this); onmouseover=resaltar(this); align=middle>
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/menus.gif">&nbsp;<SPAN class=menupral ><?php echo  $TbMsg[6] ?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
								<td onclick="eleccion(this,14)" onmouseout="desresaltar(this)" onmouseover="resaltar(this)" align="middle">
									&nbsp;<a href="#" style="text-decoration: none"><img border="0" src="./images/iconos/busquedas.gif">&nbsp;<span class="menupral"><?php echo  $TbMsg[14] ?></span></a>&nbsp;</td>
								<td width="4" align="middle"><img src="./images/iconos/separitem.gif"></td>

								<!--TD  onclick=eleccion(this,8) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/reservas.gif">&nbsp;<SPAN class=menupral ><?php echo  $TbMsg[7] ?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD-->

							<?php }
							else{
									if($idtipousuario==$SUPERADMINISTRADOR){?>
											<TD onclick=eleccion(this,9); onmouseout=desresaltar(this); onmouseover=resaltar(this); align=middle>&nbsp;
											<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/administracion.gif">
											<SPAN class=menupral ><?php echo  $TbMsg[8] ?></SPAN></A>&nbsp;</TD>
											<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>

											<TD onclick=eleccion(this,10); onmouseout=desresaltar(this); onmouseover=resaltar(this); align=middle>&nbsp;
											<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/iconos.gif">
											<SPAN class=menupral ><?php echo  $TbMsg[9] ?></SPAN></A>&nbsp;</TD>
											<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>

                                            <TD onclick=eleccion(this,11); onmouseout=desresaltar(this); onmouseover=resaltar(this); align=middle>&nbsp;
                                            <A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/tablas.gif">
                                            <SPAN class=menupral ><?php echo  $TbMsg[15] ?></SPAN></A>&nbsp;</TD>
                                            <TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>


									
									<?php }?>
							<?php }?>

											<TD onclick=eleccion(this,22); onmouseout=desresaltar(this); onmouseover=resaltar(this); align=middle>
											&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/ayuda.gif">&nbsp;<SPAN class=menupral ><?php echo  $TbMsg[11] ?></SPAN></A>&nbsp;</TD>
 											<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>

											<TD onclick=eleccion(this,21); onmouseout=desresaltar(this); onmouseover=resaltar(this); align=middle>
											&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/usuarioslog.gif">&nbsp;<SPAN class=menupral ><?php echo  $TbMsg[10] ?></SPAN></A>&nbsp;</TD>
											<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>


<?php if($idtipousuario!=$SUPERADMINISTRADOR){ ?>
<TD>
<?php
	$usuarioactual=$_SESSION["wusuario"];
	$cmd->texto="SELECT * FROM centros
				INNER JOIN administradores_centros ON administradores_centros.idcentro=centros.idcentro
				INNER JOIN usuarios ON usuarios.idusuario=administradores_centros.idusuario
				WHERE usuarios.usuario='".$usuarioactual."'
				AND centros.identidad=".$_SESSION["widentidad"];
	$rs=new Recordset;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(true); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$identidad=$rs->campos["identidad"];
		$idcentro=$rs->campos["idcentro"];
		$nombrecentro=$rs->campos["nombrecentro"];
		$numidcentro[]=$idcentro;$numnombrecentro[]=$nombrecentro;
	$rs->Siguiente();
					  }//Cierre
	$rs->Cerrar();
echo '<form></form>';
if (count($numidcentro) > 1)
{
?>
<form name="fcentros" action="frames.php" target="_parent" method="POST">
<select name="idmicentro" id="idmicentro" >
<option value=""> -- <?php echo $_SESSION["wnombrecentro"] ;?> -- </option>
<?php
for ($i=0;$i<count($numidcentro);$i++)
	{
		if ($_SESSION["wnombrecentro"] == $numnombrecentro[$i])
		{}else{
		echo '<option value="'.$numidcentro[$i].','.$numnombrecentro[$i].'"># - '.$numnombrecentro[$i].'</option>';
			}
	}
?>

</select>
<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
<TD width=4 align=middle><input name="submit" type="submit" value="Cambiar" ></input></TD>

</form>
<TD width=0 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
<TD><?php echo "Usuario.:.".ucwords($_SESSION["wusuario"]); ?></TD>

</TD>
<?php } }?>






						   </TR>
						 </TABLE>
				</TR>
			 </TABLE>
		</FORM>
	</BODY>
</HTML>
