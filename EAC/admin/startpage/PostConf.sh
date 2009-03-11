#!/bin/bash 
                              source /var/EAC/admin/librerias/Settings.lib 
                              source /var/EAC/admin/librerias/ATA.lib 
                              source /var/EAC/admin/librerias/FileSystem.lib 
                              source /var/EAC/admin/librerias/Boot.lib 
                              source /var/EAC/admin/librerias/Deploy.lib 
                              source /var/EAC/admin/librerias/PostConf.lib 
     
                  
                  export computername=`hostname`
     		export domainname=
     		export domainadmin=
     		export domainpass=
                     CrearPatron computername domainname domainadmin domainpass
                     ParseaSysprep  /var/EAC/admin/gestion/SYSPREP.INF.0809domain 1 1  
                     SetDefaultBoot 11 $IP
                     SetDefaultStartpage default.sh $IP
              	reboot 
                    
