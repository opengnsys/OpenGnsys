#!/bin/bash 
   source /var/EAC/admin/librerias/Settings.lib 
   source /var/EAC/admin/librerias/ATA.lib 
   source /var/EAC/admin/librerias/FileSystem.lib 
   source /var/EAC/admin/librerias/Boot.lib 
   source /var/EAC/admin/librerias/Deploy.lib 
   source /var/EAC/admin/librerias/PostConf.lib 
   RestorePartitionFromImage 1 1 $IPservidor hdimages/XPBasico/ XPbasico.lzop-1.mcast
   MakePhotoConsole 
   SetDefaultBoot 11 $IP  
   SetDefaultStartpage default.sh $IP 
   HDBoot 1 1
