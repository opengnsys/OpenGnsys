#!/bin/bash 
  source /var/EAC/admin/librerias/Settings.lib 
  source /var/EAC/admin/librerias/ATA.lib 
  source /var/EAC/admin/librerias/FileSystem.lib 
  source /var/EAC/admin/librerias/Boot.lib 
  source /var/EAC/admin/librerias/Deploy.lib 
  source /var/EAC/admin/librerias/PostConf.lib 
  CreateImageFromPartition 1 1 $IPservidor hdimages/XPBasico/ XPbasico.lzop
  MakePhotoConsole 
  SetDefaultBoot 1 $IP  
  SetDefaultStartpage default.sh $IP 
  reboot