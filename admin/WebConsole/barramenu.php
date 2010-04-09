<?
// ********************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Diciembre-2003
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: barramenu.php
// Descripción :Este fichero implementa el menu general de la Aplicación
// ********************************************************************************************************
include_once("./includes/ctrlacc.php");
include_once("./includes/constantes.php");
include_once("./idiomas/php/".$idioma."/barramenu_".$idioma.".php");
//________________________________________________________________________________________________________
?>
<HTML>
	<TITLE>Administración web de aulas</TITLE>
	<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html;charset=ISO-8859-1"> 
		<LINK rel="stylesheet" type="text/css" href="estilos.css">
		<SCRIPT language="javascript">
			var currentOp=null;
		//________________________________________________________________________________________________________
		function resaltar(o){
				if (o==currentOp) return
				o.style.borderBottomColor="#808080"
				o.style.borderRightColor="#808080"
				o.style.borderTopColor="#ffffff"
				o.style.borderLeftColor="#ffffff"
		}
		//________________________________________________________________________________________________________
		function desresaltar(o){
				if (o==currentOp) return
				o.style.borderBottomColor="#d4d0c8"
				o.style.borderRightColor="#d4d0c8"
				o.style.borderTopColor="#d4d0c8"
				o.style.borderLeftColor="#d4d0c8"
		}
		//________________________________________________________________________________________________________
		function eleccion(o,op){
				opadre=window.parent // Toma frame padre
				opadre.frames["frame_contenidos"].document.location.href="nada.php"
				var href;
				var 	href2="nada.php"
				var 	href3="./api/tree.html"
				var 	href4="./api/main.html"
				var 	href5="./api/index.html"

				switch(op){
					case 1: 
						href="./principal/aulas.php"
						break;
					case 2:
							href="./principal/acciones.php"
							break;
					case 3:
							href="./principal/imagenes.php"
							break;
					case 4:
							href="./principal/hardwares.php"
							break;
					case 5:
							href="./principal/softwares.php"
							break;
					case 6:
							href="./principal/servidores.php"
							break;
					case 7:
							href="./principal/menus.php"
							break;
					case 8:
							href="./principal/reservas.php"
							break;
					case 9:
							href="./principal/administracion.php"
							break;
					case 10:
							href="./images/L_Iconos.php"
							href2="./images/M_Iconos.php"
							break;
					case 13:
							href="./principal/usuarios.php"
							break;
				}
				var oldOp=currentOp
				currentOp=o;
				if (oldOp) desresaltar(oldOp);
				currentOp.style.borderBottomColor="#ffffff"
				currentOp.style.borderRightColor="#ffffff"
				currentOp.style.borderTopColor="#808080"
				currentOp.style.borderLeftColor="#808080"
				if(op<20){
					opadre.frames["frame_arbol"].document.location.href=href
					opadre.frames["frame_contenidos"].document.location.href=href2
				}
				else{
						switch(op){
							case 21: 				
									window.top.location.href="acceso.php";
									break;
							case 22: 		
									opadre.frames["frame_contenidos"].document.location.href=href4
									break;
						}
				}
		}

	//________________________________________________________________________________________________________
		</SCRIPT>
	</HEAD>
	<BODY bgcolor="#d4d0c8">
		<FORM name=fdatos>
			<TABLE border=0  style="POSITION:absolute;LEFT:0px;TOP:0px" cellPadding=2 cellSpacing=0>
				<TR>
					<TD align=left>
						<TABLE  class=menupral align=left cellPadding=1 cellSpacing=0 >
						  <TR valign=baseline>
							<TD width=10><IMG src="./images/iconos/pomo.gif"></TD>
							<? if($idtipousuario!=$SUPERADMINISTRADOR){?>
								<TD  onclick=eleccion(this,1)  onmouseout=desresaltar(this) onmouseover=resaltar(this) >
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/centros.gif">&nbsp;<SPAN class="menupral"><?echo $TbMsg[0]?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
								<TD  onclick=eleccion(this,2) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>		
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/acciones.gif">&nbsp;<SPAN class=menupral ><?echo $TbMsg[1]?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
								<TD   onclick=eleccion(this,3) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/imagenes.gif">&nbsp;<SPAN class=menupral ><?echo $TbMsg[2]?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
								<TD  onclick=eleccion(this,4) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/confihard.gif">&nbsp;<SPAN class=menupral ><?echo  $TbMsg[3] ?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
								<TD  onclick=eleccion(this,5) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/confisoft.gif">&nbsp;<SPAN class=menupral ><?echo  $TbMsg[4] ?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
								<TD  onclick=eleccion(this,6) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/servidores.gif">&nbsp;<SPAN class=menupral ><?echo  $TbMsg[5] ?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
								<TD  onclick=eleccion(this,7) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/menus.gif">&nbsp;<SPAN class=menupral ><?echo  $TbMsg[6] ?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
								<TD  onclick=eleccion(this,8) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>
									&nbsp;<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/reservas.gif">&nbsp;<SPAN class=menupral ><?echo  $TbMsg[7] ?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
							<? }
							else{
									if($idtipousuario==$SUPERADMINISTRADOR){?>
											<TD  onclick=eleccion(this,9) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>&nbsp;
											<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/administracion.gif">
											<SPAN class=menupral ><?echo  $TbMsg[8] ?></SPAN></A>&nbsp;</TD>
											<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>

											<TD  onclick=eleccion(this,10) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>&nbsp;
											<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/iconos.gif">
											<SPAN class=menupral ><?echo  $TbMsg[9] ?></SPAN></A>&nbsp;</TD>
											<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
									<?}?>
							<?}?>

							<TD  onclick=eleccion(this,22) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>&nbsp;
							<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/ayuda.gif">&nbsp;
							<SPAN class=menupral ><?echo  $TbMsg[11] ?></SPAN></A>&nbsp;</TD>
								<TD width=4 align=middle><IMG src="./images/iconos/separitem.gif"></TD>
							<TD  onclick=eleccion(this,21) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>&nbsp;
							<A href="#" style="text-decoration: none"><IMG border=0 src="./images/iconos/usuarioslog.gif">&nbsp;
							<SPAN class=menupral ><?echo  $TbMsg[10] ?></SPAN></A>&nbsp;</TD>
		

						   </TR>
						 </TABLE>
				</TR>
			 </TABLE>
		</FORM>
	</BODY>
</HTML>
