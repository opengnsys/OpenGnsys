#!/bin/bash 
 source /var/EAC/admin/librerias/Settings.lib 
 source /var/EAC/admin/librerias/ATA.lib 
 source /var/EAC/admin/librerias/FileSystem.lib 
 source /var/EAC/admin/librerias/Boot.lib 
 source /var/EAC/admin/librerias/Deploy.lib 
 source /var/EAC/admin/librerias/PostConf.lib 
 FormatCACHE 
 DeployPartitionFromImage 1 1 $IPservidor hdimages/curso0809/ xp6-73-sysprep.lzop-1.torrent
 DeployPartitionFromImage 1 2 $IPservidor hdimages/curso0809/ xp4-sysprep4.gzip-2.torrent
   MakePhotoConsole 
     SetDefaultBoot 11 $IP  
     SetDefaultStartpage default.sh $IP
     /var/EAC/admin/startpage/PostConf.sh
     reboot