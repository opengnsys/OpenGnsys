<?php
//______________________________________________________
//
//	Fichero de idiomas php: pintaParticiones_esp.php 
//	Idioma: Español
//______________________________________________________
// Si ya existe TbMsg, solo agregamos lo nuevo
if(!isset($TbMsg)){
	$TbMsg=array();
}

$TbMsg["PARTITION"]='Partici&oacute;n';
$TbMsg["INST_SO"]='S.O. Instalado';
$TbMsg["SIZE_KB"]='Tama&ntilde;o (KB)';
$TbMsg["PARTITION_TYPE"]='Tipo';
$TbMsg["IMAGE"]='Imagen';
$TbMsg["SOFT_PROFILE"]='Perfil Software';
$TbMsg["FILESYSTEM_SHORT"]='S.F.';

$TbMsg["DISK"]='Disco';
$TbMsg["CONFIG_PARTTABLE"]='Tabla de particiones';

$TbMsg["USAGE"]='Uso';

$TbMsg["VARIABLE"]='Variable';
$TbMsg["CACHE_CONTENT"]='Data/Caché';
$TbMsg["CACHE_FREESPACE"]='Cach&eacute; libre';
$TbMsg["CACHE_COMPLETE"]='CACHE COMPLETA';

$TbMsg["SAMESYSTEM_IMAGE"]='Imagen (mismo tipo partici&oacute;n)';
$TbMsg["DIFFERENTSYSTEM_IMAGE"]='Imagen (distinto tipo partici&oacute;n)';
$TbMsg["RESTORE_METHOD"]='M&eacute;todo';

$TbMsg["SO_NAME"]='Nombre S.O.';
$TbMsg["IMAGE_TO_CREATE"]='Imagen a crear';
$TbMsg["DESTINATION_REPOSITORY"]='Repositorio de destino';

$TbMsg["IMAGE_REPOSITORY"]='Imagen / Repositorio';
$TbMsg["INCREMENTAL_IMAGE_REPOSITORY"]='Imagen Incremental / Repositorio';

$TbMsg["CONFIG_NOCONFIG"]='Sense configuració: client no connectat al servidor.';
$TbMsg["CONFIG_NODISK1MSDOS"]='Avís: aquest comandament sol tracta el disc 1 amb taula de particions MSDOS.';

$TbMsg["SYNC_METHOD"]='Método';
$TbMsg["SYNC1_DIR"]='Basada en directorio';
$TbMsg["SYNC2_FILE"]='Basada en archivo';

$TbMsg["TITLE_W"]='Opción de rsync: El algoritmo incremental rsync no se usa y se envía todo el archivo. Rsync lo usa por defecto cuando el origen y destino locales. ';
$TbMsg["TITLE_E"]='Opción de rsync: Se compara el destino con el origen y se borran los ficheros que no existen en el primero.';
$TbMsg["TITLE_C"]='Opción de rsync: Comprime los archivos de datos que se envían a la máquina de destino, lo que reduce la cantidad de datos que se transmiten. ';
$TbMsg["SEND"]='Protocolo';

$TbMsg["WARN_PROTOCOL"]='La opción "protocolo" sólo se utiliza en las sincronizadas tipo archivo la primera vez que se envía la imagen a caché. <br>En otro caso el protocolo es RSYNC.';
?>

