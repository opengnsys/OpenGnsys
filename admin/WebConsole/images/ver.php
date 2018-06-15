<?php
$eliminar=$_GET["eliminar"] ;
$archivo=$_GET["archivo"] ;
if ($eliminar=="si")
{
$archivo="./fotos/".$archivo;
unlink($archivo);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////
////////	MOSTRAR EL DIRECTORIO DE IMAGENES	/////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////
echo '<TABLE width="100%" border="0"><TR>';
    $directory="./fotos";
    $contador=1;
    $dirint = dir($directory);
    while (($archivo = $dirint->read()) !== false)
    {
	if ($contador < 3)
	{
	//Si contador es menor de 4 muestra 3 imagenes
	//Si contador es 4 salta
	$palabra=preg_quote('log');
        if (preg_match("/gif/i", $archivo) or preg_match("/jpg/i", $archivo) or preg_match("/png/i", $archivo)){
		if (preg_match("/login/i", $archivo))
		{}else{
            echo '<TD align="center"><img src="'.$directory."/".$archivo.'"><br>'.$archivo.'</br><a href="ver.php?eliminar=si&archivo='.$archivo.'">Eliminar Imagen</a></TD>';
			}$contador++;
        	}
	}else{
	//Si contador es 4 salta aqui
	$palabra=preg_quote('log');
        if (preg_match("/gif/i", $archivo) or preg_match("/jpg/i", $archivo) or preg_match("/png/i", $archivo)){
		if (preg_match("/login/i", $archivo))
		{}else{
            echo '<TD align="center"><img src="'.$directory."/".$archivo.'"><br>'.$archivo.'</br><a href="ver.php?eliminar=si&archivo='.$archivo.'">Eliminar Imagen</a></TD>';
			}$contador++;
        	}
		$contador=1; echo '</TR><TR>';
	     }
    }
    $dirint->close();

echo '</TR></TABLE>';
/////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////
?>
