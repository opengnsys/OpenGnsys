<?php
// *****************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Agosto-2010
// Nombre del fichero: acceso.php
// Descripción: Pantalla principal de acceso a la consola de administración web.
// Versión 1.0.3: Unificación de ficheros e internacionalización.
// Autor: Ramón Gómez - ETSII, Universidad de Sevilla
// Fecha: 2012-02-07
// *****************************************************************************
session_start();
if(isset($_SESSION)){ 	// Si existe algua sesión ...
	session_unset(); // Elimina variables
	session_destroy(); // Destruye sesi�n
}

// Cargar configuración.
include_once("controlacceso.php");
include_once("./includes/CreaComando.php");
include_once("./clases/AdoPhp.php");
include_once("./includes/HTMLSELECT.php");

// Control de errores.
if (isset($_GET["herror"])) $herror=$_GET["herror"]; 
if (isset($_POST["herror"])) $herror=$_POST["herror"]; 
// Idioma.
if (isset($_POST["nemonico"])) $parmidi=$_POST["nemonico"]; 
if (!empty ($parmidi) and file_exists ("idiomas/php/$parmidi/acceso_$parmidi.php")) {
	$idi=$parmidi;
}
include ("idiomas/php/$idi/acceso_$idi.php");

$busidcentro="";
$cmd=CreaComando($cnx); // Crea objeto comando 
if (!$cmd)
   	die($TbMsg["ACCESS_ERROR"]);

        $rs=new Recordset;
        $cmd->texto="SELECT * FROM centros";
        $rs->Comando=&$cmd;
        if (!$rs->Abrir()) return(false); // Error al abrir recordset
        $rs->Primero();
        if (!$rs->EOF){
        $busidcentro=$rs->campos["identidad"];
        }$rs->Cerrar();	
	
// Valores por defecto.
$herror=0;
if (empty($busidcentro)){
	$idcentro="";
}else{
	$idcentro=$busidcentro;
}
?>
<html>
<head>
<title><?php echo $TbMsg["ACCESS_TITLE"];?></title>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<link rel="shortcut icon" href="images/iconos/logocirculos.png" type="image/png" />
<link rel="stylesheet" type="text/css" href="estilos.css" />
<script language="javascript">
//______________________________________________________________________________
function confirmar(){
	if (comprobar_datos())
		document.fdatos.submit();
}
//______________________________________________________________________________
function comprobar_datos(){
	if (document.fdatos.usu.value==""){
		<?php echo 'alert("'.$TbMsg["ACCESS_NOUSER"].'");' ?>
		document.fdatos.usu.focus();
		return(false)
	}
	if (document.fdatos.pss.value==""){
		<?php echo 'alert("'.$TbMsg["ACCESS_NOPASS"].'");' ?>
		document.fdatos.pss.focus();
		return(false)
	}
	var  p=document.fdatos.idcentro.selectedIndex;
	if (p==0){  
		<?php echo 'var res=confirm("'.$TbMsg["ACCESS_NOUNIT"].'");' ?>
	if(!res)
		return(false)
	}
	return(true)
}
//______________________________________________________________________________
function PulsaEnter(oEvento){ 
    var iAscii; 
    if (oEvento.keyCode) 
        iAscii = oEvento.keyCode; 
    else{
		if (oEvento.which) 
			iAscii = oEvento.which; 
		else 
			return false; 
	}
    if (iAscii == 13)  confirmar();
	return true; 
} 
//______________________________________________________________________________
</script>
</head>

<body>
<div class="acceso">
<h1> <?php echo $TbMsg["ACCESS_HEADING"]; ?> </h1>
<h2> <?php echo $TbMsg["ACCESS_SUBHEAD"]; ?> </h2>
<form action="controlpostacceso.php" name="fdatos" method="post">
    <fieldset>
	<div><label for="usu"><?php echo $TbMsg["ACCESS_USERNAME"]; ?></label>
	   <input name="usu" type="text" onkeypress="PulsaEnter(event)" /></div>
	<div><label for="pss"><?php echo $TbMsg["ACCESS_PASSWORD"]; ?></label>
	   <input name="pss" type="password" onkeypress="PulsaEnter(event)" /></div>
	<div><label for="idcentro"><?php echo $TbMsg["ACCESS_ORGUNIT"]; ?></label>
	   <?php echo HTMLSELECT($cmd,0,'centros',$idcentro,'idcentro','nombrecentro',220); ?></div>
	<div><button type="submit" onclick="confirmar()"><?php echo $TbMsg["ACCESS_OK"]; ?></button></div>
    </fieldset>
</form>
</div>
<div class="pie">
<span><a href="https://opengnsys.es/">
<?php
// Añadir versión en el enlace a la URL del proyecto.
$data = json_decode(@file_get_contents(__DIR__ . '/../doc/VERSION.json'));
if (empty($data->project)) {
    echo "OpenGnsys";
} else {
    echo @$data->project.' '
        .@$data->version.' '
        .(isset($data->codename) ? '('.$data->codename.') ' : '')
        .@$data->release;
}
?>
</a></span>
<form action="#" name="lang" method="post">
      <?php echo HTMLSELECT($cmd,0,'idiomas',$idi,'nemonico','descripcion',80); ?>
      <button type="submit"><?php echo $TbMsg["ACCESS_CHOOSE"]; ?></button>
</form>
</div>
<?php
//______________________________________________________________________________
// Posiciona cursor en campo usuario y muestra mensaje de error si lo hubiera
echo '<script language="javascript">';
if (!empty($herror)) {
	if (!empty($TbErr[$herror])) {
		echo "	alert('".$TbErr[$herror]."');";
	} else {
		echo "	alert('".$TbMsg["ACCESS_UNKNOWNERROR"]."');";
	}
}
echo '  document.fdatos.usu.focus()';
echo '</script>';
//______________________________________________________________________________
?>
</body>
</html>

