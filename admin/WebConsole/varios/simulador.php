<?
    include_once("../clases/SockHidra.php");
		
	$shidra=new SockHidra("192.168.0.100",2005); 
	$parametros="1"; // Ejecutor
	$parametros.="nfn=inclusion_cliRMB".chr(13);

	$tiposo="tiposo=Windows";
	$tipopart="tipopart=FAT32";
	$tamapart="tamapart=5000000"; 
	$numpart="numpart=1";  
	$nombreso="nombreso=Windos 98,SE,Millenium";
	$parametroscfg="@cfg".chr(10).$tiposo.chr(10).$tipopart.chr(10).$tamapart.chr(10).$numpart.chr(10).$nombreso.chr(9);

	$parametros.="cfg=".$parametroscfg.chr(13);
	$parametros.="nau=".chr(13);
	$parametros.="nor=".chr(13);
	$parametros.="mac=000102B44EB2".chr(13);
	$parametros.="ipd=192.168.0.100".chr(13);
	$parametros.="ipr=192.168.0.100".chr(13);
	$parametros.="iph=10.1.15.11".chr(13);
	$parametros.="ido=23".chr(13);

	$resul=$shidra->conectar(); // Se ha establecido la conexión con el servidor hidra
	if($resul){
		$resul=$shidra->envia_comando($parametros);
		echo $parametros;
		$shidra->desconectar();
	}
?>