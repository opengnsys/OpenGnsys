<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 200-2005 José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero: calendario_ventana.php
// Descripción :
//		Muestra un calendario para elegir una fecha
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../idiomas/php/".$idioma."/clases/Almanaque_".$idioma.".php");
//_________________________________________________________________________________________________________
$anno_elegido=2004; 
$mes_elegido=1; 
$dia_elegido=1; 

if (isset($_GET["fecha"])){
	$fecha=$_GET["fecha"]; 
	if ($fecha!=""){
		list($dia_p,$mes_p,$anno_p)=split("/",$fecha);
		$mes_elegido=(int)($mes_p);
		$anno_elegido=(int)($anno_p);
		$dia_elegido=(int)($dia_p);
	}
}

if (isset($_POST["dia_elegido"])) $dia_elegido=$_POST["dia_elegido"]; 
if (isset($_POST["mes_elegido"])) $mes_elegido=$_POST["mes_elegido"]; 
if (isset($_POST["anno_elegido"])) $anno_elegido=$_POST["anno_elegido"]; 
//_________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../hidra.css">
<SCRIPT language="JavaScript">

var rojo="#cc3366"
var negro="#ffffff"
var verde="lightseagreen"
var gris="#bbbcb9"
var blanco="#eeeeee"
var azul= "#0000cc"
var fondooriginal="#EEEECC";
var colororiginal="#003300";

var currentDia=null;

dias_meses=new Array(12);

dias_meses[1]=31;
dias_meses[2]=28;
dias_meses[3]=31;
dias_meses[4]=30;
dias_meses[5]=31;
dias_meses[6]=30;
dias_meses[7]=31;
dias_meses[8]=31;
dias_meses[9]=30;
dias_meses[10]=31;
dias_meses[11]=30;
dias_meses[12]=31;
//_________________________________________________________________________________________________________
function ItemSeleccionado(o){
	return(o.style.backgroundColor==azul) 
}
//_________________________________________________________________________________________________________
function elige_anno(){
	document.forms.fdatos.dia_elegido.value=1
	document.forms.fdatos.mes_elegido.value=1
	document.forms.fdatos.anno_elegido.value=document.forms.fdatos.despleanno.value
	document.forms.fdatos.submit()
}
//_________________________________________________________________________________________________________
function mes_siguiente(){
	var wmes=parseInt(document.forms.fdatos.mes_elegido.value)
	var wanno=parseInt(document.forms.fdatos.anno_elegido.value)
	wmes++;
	if(wmes>12){
		wmes=1
		wanno=wanno+1
		if(wanno>2014){
			wanno=2014
		}
	}
	document.forms.fdatos.dia_elegido.value=1
	document.forms.fdatos.mes_elegido.value=wmes
	document.forms.fdatos.anno_elegido.value=wanno
	document.forms.fdatos.submit()
}
//_________________________________________________________________________________________________________
function mes_anterior(){
	var wmes=parseInt(document.forms.fdatos.mes_elegido.value)
	var wanno=parseInt(document.forms.fdatos.anno_elegido.value)
	wmes--;
	if(wmes<1){
		wmes=12
		wanno=wanno-1
		if(wanno<2004){
			wanno=2004
		}
	}
	var swbi=0;
	if (wanno%4==0 && wmes==2) swbi=1;

	document.forms.fdatos.dia_elegido.value=dias_meses[wmes]+swbi;
	document.forms.fdatos.mes_elegido.value=wmes;
	document.forms.fdatos.anno_elegido.value=wanno;
	document.forms.fdatos.submit()

}
//____________________________ ____________________________________________________________________________
function Resalta(o){
	o.style.color=blanco 
	o.style.backgroundColor=azul 
}
//____________________________ ____________________________________________________________________________
function Desmarca(o){
	o.style.color=colororiginal
	o.style.backgroundColor=fondooriginal

}
//____________________________ ____________________________________________________________________________
function sobre(o){
	if (currentDia!=null)
		Desmarca(currentDia)
	Resalta(o);
	currentDia=o;
}
//____________________________ ____________________________________________________________________________

function fuera(o){
		Desmarca(o);
}
//____________________________ ____________________________________________________________________________
function clic(o){
	window.opener.anade_fecha(o.id)
	self.close()
}
//____________________________ ____________________________________________________________________________
function cerrar_ventana(){
	self.close()
}
//____________________________ ____________________________________________________________________________
function borrar_fecha(){
	window.opener.anade_fecha("")
	self.close()
}
//____________________________ ____________________________________________________________________________
</SCRIPT>
</HEAD>
<BODY>
<FORM action="calendario_ventana.php" name="fdatos" method="post">
	<INPUT type=hidden name=dia_elegido value="<? echo $dia_elegido?>">
	<INPUT type=hidden name=mes_elegido value="<? echo $mes_elegido?>">
	<INPUT type=hidden name=anno_elegido value="<? echo $anno_elegido?>">
	<?
$mialmanaque=new Almanaque("tabla_meses");

echo '<TABLE align="center">';
echo '	<TR>';
echo '	<TD align="left"><img style="cursor:hand" SRC="../images/iconos/salir_on.gif" onclick="cerrar_ventana()"></TD>';
echo '<TD style="COLOR: MediumBlue;FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE:12">&nbsp;&nbsp;Año:&nbsp;';
echo '<SELECT name="despleanno" onchange="elige_anno()" style="width=60">';
for($i=2004;$i<2015;$i++){
		echo '<OPTION value='.$i;
		if($anno_elegido==$i) echo" selected ";
		echo '>'.$i.'</OPTION>';
}
echo '</SELECT>';
echo '</TD>';
echo '</TR>';

echo '<table align=center border=0>';
echo '<tr>';
echo '<td onclick="javascript:mes_anterior();" style="cursor:hand;COLOR: MediumBlue;FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE:10" align=left><<</td>';
echo '<td onclick="javascript:mes_siguiente()" style="cursor:hand;COLOR: MediumBlue;FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE:10" align=right>>></td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan=2 valign=top width=100>'.$mialmanaque->MesAnno($mes_elegido,$anno_elegido).'</td>';
echo '</tr>';
echo '</table>';
echo '</FORM>';

echo '<SCRIPT language="JavaScript">';
echo '	var o=document.getElementById("'.$dia_elegido."/".$mes_elegido."/".$anno_elegido.'");';
echo '   sobre(o);';
echo '</SCRIPT>';
?>
</BODY>
</HTML>


