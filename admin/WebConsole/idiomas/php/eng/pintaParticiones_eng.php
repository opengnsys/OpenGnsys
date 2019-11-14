<?php
//______________________________________________________
//
//	Php language file: pintaParticiones_esp.php
//	Language: English
//______________________________________________________
// If exist TbMsg, only the new is added
if(!isset($TbMsg)){
	$TbMsg=array();
}

$TbMsg["PARTITION"]='Partition';
$TbMsg["INSTALLED_OS"]='Installed O.S.';
$TbMsg["INST_SO"]='Installed O.S.';
$TbMsg["SIZE_KB"]='Size (KB)';
$TbMsg["PARTITION_TYPE"]='Type';
$TbMsg["IMAGE"]='Image';
$TbMsg["SOFT_PROFILE"]='Software Profile';
$TbMsg["FILESYSTEM"]='Filesystem';
$TbMsg["FILESYSTEM_SHORT"]='F.S.';

$TbMsg["REFORMAT"]='Reformat';
$TbMsg["REMOVE"]='Delete';

$TbMsg["DISK"]='Disk';
$TbMsg["CONFIG_PARTTABLE"]='Partition Table';

$TbMsg["USAGE"]='Usage';

$TbMsg["VARIABLE"]='Variable';
$TbMsg["CACHE_CONTENT"]='Date/Cache Content';
$TbMsg["CACHE_FREESPACE"]='Free Cache ';
$TbMsg["CACHE_COMPLETE"]='CACHE IS FULL';

$TbMsg["SAMESYSTEM_IMAGE"]='Image (same partition type)';
$TbMsg["DIFFERENTSYSTEM_IMAGE"]='Image (different partition type)';
$TbMsg["RESTORE_METHOD"]='Method';

$TbMsg["SO_NAME"]='O.S. Name';
$TbMsg["IMAGE_TO_CREATE"]='Image to create';
$TbMsg["DESTINATION_REPOSITORY"]='Destination Repository';

$TbMsg["IMAGE_REPOSITORY"]='Image / Repository';
$TbMsg["INCREMENTAL_IMAGE_REPOSITORY"]='Incremental Image / Repository';

$TbMsg["CONFIG_NOCONFIG"]='No configuration: client not connected to server.';
$TbMsg["CONFIG_NODISK1MSDOS"]='Warning: this command only uses disk 1 with MSDOS partition table.';
$TbMsg["CONFIG_NOOS"]='No operating system detected on the computer.';

$TbMsg["SYNC_METHOD"]='Method';
$TbMsg["SYNC1_DIR"]='Directory based';
$TbMsg["SYNC2_FILE"]='File based';

$TbMsg["TITLE_W"]='Rsync option: Rsync incremental algorithm is not used and the whole file is sent as-is instead. This is the default when both the source and destination are specified as local paths.';
$TbMsg["TITLE_E"]="Rsync option: Delete extraneous files from the receiving side (ones  that  aren't on the sending side).";
$TbMsg["TITLE_C"]='Rsync option: Compresses data files sent to  the  destination  machine, which reduces the amount of data being transmitted.';
$TbMsg["SEND"]='Protocol';
// WARNINGS.
$TbMsg["WARN_PROTOCOL"]='"Protocol" option is only used in incremental file-type, the first time that image is sent to cache. <br>In other case protocol is RSYNC.';
$TbMsg["WARN_DIFFIMAGE"]='There is a new version of  the image(showing revision difference).';
$TbMsg["WARN_DIFFDISKSIZE"]='WARNING: A group of computers with different disk sizes can not be partitioned.<br>Check &quotUngroup by partition size&quot option and press &quot;Accept&quot; in the top menu to apply this operation properly.';
