<?php
listar_directorios_ruta("./");
function listar_directorios_ruta($ruta){ 
   // abrir un directorio y listarlo recursivo 
   if (is_dir($ruta)) { 
      if ($dh = opendir($ruta)) { 
         while (($file = readdir($dh)) !== false) { 
			if($file !=".svn" && $file!="." && $file!=".."){
				//esta línea la utilizaríamos si queremos listar todo lo que hay en el directorio 
				//mostraría tanto archivos como directorios 
				//echo "<br>Nombre de archivo: $file : Es un: " . filetype($ruta . $file); 
				if (is_dir($ruta . $file) && $file!="." && $file!=".."){ 
				   //solo si el archivo es un directorio, distinto que "." y ".." 
				   echo "<br>Directorio: $ruta$file"; 
				   listar_directorios_ruta($ruta . $file . "/"); 
				} 
				else{
					//echo "<br>Archivp:$file"; 
					//if($file=="aulas.php")
						procesaarchivo($ruta,$file);
				}	
			} 
		}
      closedir($dh); 
      } 
   }else 
      echo "<br>No es ruta valida"; 
} 
 function procesaarchivo($ruta,$file){ 
			$meta='<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">';
			$archivo=realpath($ruta.$file);
			echo "<br>Procesando Archivo:".$file; 

			$tam=filesize($archivo);
			$fp = fopen($archivo, "rb"); 
			$buffer = fread($fp, $tam); 
			fclose($fp);
			
			$pos = strpos($buffer,'<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">');
			if($pos==0)
				$pos = strpos($buffer,'<head>');
			if($pos==0)
				return;
				
			$dpl=strlen('<HEAD>');	
			$prebuffer=substr($buffer,0,$pos+$dpl);
			$posbuffer=substr($buffer,$pos+$dpl);
			
			$buffer=$prebuffer."\n\t".$meta.$posbuffer;

			$fp = fopen($archivo,"w"); 
			fwrite($fp, $buffer,strlen($buffer)); 
			fclose($fp); 
}

