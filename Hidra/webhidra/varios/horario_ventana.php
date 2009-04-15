<?
include_once("../includes/ctrlacc.php");
include_once("../idiomas/php/".$idioma."/clases/Almanaque_".$idioma.".php");
$hora=""; 
if (isset($_GET["hora"])) $hora=$_GET["hora"]; 
if($hora=="") $hora="8:00";
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

var currentHora=null;

//-------------------------------------------------------------------
function ItemSeleccionado(o){
	return(o.style.backgroundColor==azul) 
}
//-------------------------------------------------------------------
function Resalta(o){
	o.style.color=blanco 
	o.style.backgroundColor=azul 
}
//-------------------------------------------------------------------
function Desmarca(o){
	o.style.color=colororiginal
	o.style.backgroundColor=fondooriginal

}
//-------------------------------------------------------------------
function sobre(o){
	if (currentHora!=null)
		Desmarca(currentHora)
	Resalta(o);
	currentHora=o;
}
//-------------------------------------------------------------------
function fuera(o){
		Desmarca(o);
}
//-------------------------------------------------------------------
function clic(o){
	window.opener.anade_hora(o.id)
	self.close()
}
//_________________________________________________________
function cerrar_ventana(){
	self.close()
}
//_________________________________________________________
function borrar_fecha(){
	window.opener.anade_hora("")
	self.close()
}
//_________________________________________________________
</SCRIPT>
</HEAD>
<BODY>
	<?
$mialmanaque=new Almanaque("tabla_meses");
echo '<DIV style="position:absolute;top:5px;left:10px">';
echo '<img style="cursor:hand" SRC="../images/iconos/salir_on.gif" onclick="cerrar_ventana()">';
echo '</DIV>';

echo '<DIV style="position:absolute;top:25px;left:5px">';
echo '<FORM action="calendario_ventana.php" name="fdatos" method="post">';
echo '<table align=center border=0>';
echo '<tr>';
echo '<td colspan=2 valign=top width=100>'.$mialmanaque->Horas_Completas().'</td>';
echo '</tr>';
echo '</table>';
echo '</FORM>';
echo '</DIV>';

echo '<SCRIPT language="JavaScript">';
echo '	var o=document.getElementById("'.$hora.'");';
echo '   if(o!=null) sobre(o);';
echo '</SCRIPT>';
?>

</body>
</html>


