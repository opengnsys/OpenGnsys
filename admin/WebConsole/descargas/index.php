<?php
// Warning: Don't left any character outside PHP code.
//
// Choose a file on this directory to download via Apache.

include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/descargas_".$idioma.".php");
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.

// Security tip: change to local directory.
$oldpwd=getcwd();
chdir(dirname(__FILE__));
if (isset($_POST['file'])) {
	// Send file.
	sendFile ($_POST['file']);
} else {
	// Show list of files.
	echo '<!DOCTYPE html>'."\n";
	echo '<html><head>'."\n";
	echo '  <link rel="stylesheet" type="text/css" href="../estilos.css" />'."\n";
	echo '</head><body>'."\n";
	echo '<div align="center" class="tabla_datos">'."\n";
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n";
	echo '  <table>'."\n";
	echo '    <tr><th>'.$TbMsg['DOWNLOADS'].':</th></tr>'."\n";
	$filelist = glob("*");
	$data = "";
	foreach ($filelist as $f) {
		// Get only readable files, except this one.
		if ($f !== basename(__FILE__) and is_file($f) and is_readable($f)) {
			$data .= '      <option value="'.$f.'">'.$f.'</option>'."\n";
		}
	}
	if (empty($data)) {
		// Show warning message if there is no files to download.
		echo '    <tr><td>'.$TbMsg['NOFILES'].'</td></tr>'."\n";
	} else {
		// Show available files.
		echo '    <tr><td><select name="file">'."\n";
		echo $data;
		echo '      </select>'."\n";
		echo '      <input type="submit" value="" style="width:20px; background:url(../images/boton_confirmar.gif);"></td></tr>'."\n";
	}
	echo '</table>'."\n";
	echo '</form>'."\n";
	echo '</body></html>'."\n";
}
// Change again to source directory.
chdir($oldpwd);


// Send a file.
function sendFile($file) {
	// Check if file exists in current directory and it isn't this file.
	if (file_exists($file) and strpos($file,"/") === false and $file !== basename(__FILE__)) {
		header('Content-Type: ' . mime_content_type($file));
		header('Content-Length: ' . filesize($file));
		header('Content-Disposition: attachment; filename="' . $file . '"');
		readfile($file);
	}
}

// Warning: Don't left any character outside PHP code.

