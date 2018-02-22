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

$TbMsg["PARTITION"]='Partition';
$TbMsg["INSTALLED_OS"]='Installed Operating System';
$TbMsg["INST_SO"]='Installed O.S.';
$TbMsg["SIZE_KB"]='Size (KB)';
$TbMsg["PARTITION_TYPE"]='Type';
$TbMsg["IMAGE"]='Image';
$TbMsg["SOFT_PROFILE"]='Software Profile';
$TbMsg["FILESYSTEM"]='Filesystem';
$TbMsg["FILESYSTEM_SHORT"]='F.S.';

$TbMsg["REFORMAT"]='Reformat';
$TbMsg["REMOVE"]='Remove';

$TbMsg["DISK"]='Disk';
$TbMsg["CONFIG_PARTTABLE"]='Partition Table';

$TbMsg["USAGE"]='Usage';

$TbMsg["VARIABLE"]='Variable';
$TbMsg["CACHE_CONTENT"]='Date/Cache Content';
$TbMsg["CACHE_FREESPACE"]='Cach&eacute; libre';
$TbMsg["CACHE_COMPLETE"]='CACHE IS FULL';

$TbMsg["SAMESYSTEM_IMAGE"]='Image (same partition type)';
$TbMsg["DIFFERENTSYSTEM_IMAGE"]='Image (different partition type)';
$TbMsg["RESTORE_METHOD"]='Method';

$TbMsg["SO_NAME"]='O.S. Name';
$TbMsg["IMAGE_TO_CREATE"]='Image to create';
$TbMsg["DESTINATION_REPOSITORY"]='Destination Repository';

$TbMsg["IMAGE_REPOSITORY"]='Image / Repository';
$TbMsg["INCREMENTAL_IMAGE_REPOSITORY"]='Incremental Image / Repository';

$TbMsg["CONFIG_NOCONFIG"]='No configuration: client does not connect to server.';
$TbMsg["CONFIG_NODISK1MSDOS"]='Warning: this command only uses disk 1 with a MSDOS partition table.';

$TbMsg["SYNC_METHOD"]='Method';
$TbMsg["SYNC1_DIR"]='Based on directory';
$TbMsg["SYNC2_FILE"]='Based on file';

$TbMsg["TITLE_W"]='Rsync option:  delta-transfer algorithm is not used and the whole file is sent as-is instead. This is the default when both the source and  destination   are   specified  as  local  paths.';
$TbMsg["TITLE_E"]="Rsync option: delete extraneous files from  the  receiving side  (ones  that  aren't on the sending side).";
$TbMsg["TITLE_C"]='Rsync option: compresses the file data as it  is  sent to  the  destination  machine,  which reduces the amount of data being transmitted.';
$TbMsg["SEND"]='Protocol';
// WARNINGS.
$TbMsg["WARN_PROTOCOL"]='La opción "protocolo" sólo se utiliza en las sincronizadas tipo archivo la primera vez que se envía la imagen a caché. <br>En otro caso el protocolo es RSYNC.';
$TbMsg["WARN_DIFFIMAGE"]='There is a new image version (showing revision difference).';
$TbMsg["WARN_DIFFDISKSIZE"]='ATENTION: a group of computers with different disk sizes can not be partitioned.<br>Check &quotUngroup by partition size&quot option and press &quot;Accept&quot; in the top menu to apply this operation properly.';
?>

