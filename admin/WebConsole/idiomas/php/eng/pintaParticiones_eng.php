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

$TbMsg["VARIABLE"]='Variable';
$TbMsg["CACHE_CONTENT"]='Contenido cach&eacute;';
$TbMsg["CACHE_FREESPACE"]='Cach&eacute; libre';
$TbMsg["CACHE_COMPLETE"]='FULL CACHE';

$TbMsg["SAMESYSTEM_IMAGE"]='Imagen (mismo tipo partici&oacute;n)';
$TbMsg["DIFFERENTSYSTEM_IMAGE"]='Imagen (distinto tipo partici&oacute;n)';
$TbMsg["RESTORE_METHOD"]='M&eacute;todo';

$TbMsg["SO_NAME"]='Nombre S.O.';
$TbMsg["IMAGE_TO_CREATE"]='Imagen a crear';
$TbMsg["DESTINATION_REPOSITORY"]='Repositorio de destino';

$TbMsg["IMAGE_REPOSITORY"]='Imagen / Repositorio';
$TbMsg["INCREMENTAL_IMAGE_REPOSITORY"]='Imagen Incremental / Repositorio';

$TbMsg["CONFIG_NOCONFIG"]='No configuration: client does not connect to server.';
$TbMsg["CONFIG_NODISK1MSDOS"]='Warning: this command only uses disk 1 with a MSDOS partition table.';

$TbMsg["SYNC_METHOD"]='Method';
$TbMsg["SYNC1_DIR"]='Based on directory';
$TbMsg["SYNC2_FILE"]='Based on file';

$TbMsg["TITLE_W"]='Rsync option:  delta-transfer algorithm is not used and the whole file is sent as-is instead. This is the default when both the source and  destination   are   specified  as  local  paths.';
$TbMsg["TITLE_E"]="Rsync option: delete extraneous files from  the  receiving side  (ones  that  aren't on the sending side).";
$TbMsg["TITLE_C"]='Rsync option: compresses the file data as it  is  sent to  the  destination  machine,  which reduces the amount of data being transmitted.';
$TbMsg["SEND"]='Protocol';

?>

