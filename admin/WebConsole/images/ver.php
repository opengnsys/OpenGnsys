<?
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
	//if (eregi("..", $archivo)){$contador--;}
	$palabra=preg_quote('log');
        if (eregi("gif", $archivo) || eregi("jpg", $archivo) || eregi("png", $archivo)){
		if (eregi("login", $archivo))
		{}else{
            echo '<TD align="center"><img src="'.$directory."/".$archivo.'"><br>'.$archivo.'</br><a href="ver.php?eliminar=si&archivo='.$archivo.'">Eliminar Imagen</a></TD>';
			}$contador++;
        	}
	}else{
	//Si contador es 4 salta aqui
	//if (eregi(".", $archivo) || eregi("..", $archivo)){$contador--;}
	$palabra=preg_quote('log');
        if (eregi("gif", $archivo) || eregi("jpg", $archivo) || eregi("png", $archivo)){
		if (eregi("login", $archivo))
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