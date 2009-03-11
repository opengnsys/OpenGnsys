#!/bin/bash 
source /var/EAC/admin/librerias/Settings.lib 
source /var/EAC/admin/librerias/ATA.lib 
source /var/EAC/admin/librerias/FileSystem.lib 
source /var/EAC/admin/librerias/Boot.lib 
source /var/EAC/admin/librerias/Deploy.lib 
source /var/EAC/admin/librerias/PostConf.lib 
CreatePartitions 1 1:1:NTFS 2:2:NTFS 3:3:NTFS 4:4:NTFS 5:5:NTFS 6:6:NTFS 7:7:NTFS 8:8:NTFS
SetPartitionActive 1 1 
NewMbrXP 1
MakePhotoConsoleSetDefaultStartpage default.sh $IP 
SetDefaultBoot 11 $IP 
reboot