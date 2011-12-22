#!/bin/bash
#@file    boottoolsgenerator.sh
#@brief   Script generación del sistema opertativo cliente OpenGnSys
#@warning 
#@version 0.9 - Prototipo de sistema operativo multiarranque de opengnsys.
#@author  Antonio J. Doblas Viso. Universidad de Malaga.
#@date    2010/05/24
#@version 1.0 - Compatibilidad OpengGnsys X.
#@author  Antonio J. Doblas Viso. Universidad de Malaga.
#@date    2011/08/03
#*/

 #mkdir -p /tmp/opengnsys_installer/opengnsys;
 #mkdir -p /tmp/opengnsys_installer/opengnsys2;
 #cp -prv /home/administrador/workspace/OpenGnsys/branches/version2/* /tmp/opengnsys_installer/opengnsys2/;
 #cp -prv /home/administrador/workspace/OpenGnsys/branches/version1.0/client/ /tmp/opengnsys_installer/opengnsys/;
#find /tmp/opengnsys_installer/ -name .svn -type d -exec rm -fr {} \; 2>/dev/null;


#Variables
TYPECLIENT=host
WORKDIR=/tmp/opengnsys_installer
INSTALL_TARGET=/opt/opengnsys
PROGRAMDIR=$(readlink -e $(dirname "$0"))

# Solo ejecutable por usuario root
if [ "$(whoami)" != 'root' ]
then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi

for i in `mount | grep IMGogclient | grep /var | cut -f3 -d" "`; do echo $i; umount $i; done
for i in `mount | grep IMGogclient | grep /var | cut -f3 -d" "`; do echo $i; umount $i; done
for i in `mount | grep IMGogclient | grep /var | cut -f3 -d" "`; do echo $i; umount $i; done



#funciones especificas del cliente.
source $PROGRAMDIR/boottoolsfunctions.lib


echoAndLog "OpenGnSys CLIENT installation begins at $(date)"

##########################################################################
## FASE 1 -  Instalación de software adicional.
cat /etc/apt/sources.list | grep "http://free.nchc.org.tw/drbl-core" || echo "deb http://free.nchc.org.tw/drbl-core drbl stable " >> /etc/apt/sources.list
apt-get update
apt-get -y --force-yes install debootstrap subversion schroot squashfs-tools syslinux genisoimage gpxe qemu

##### FASE 2   - Asignación de variables
#obtenemos las variables necesarias y la información del host.
btogGetVar && btogGetOsInfo

############# FASE 3: Creación del Sistema raiz RootFS (Segundo Sistema archivos (img))
##3.1 creación y formateo del disco virtual. generamos el dispositivo loop.
file $BTROOTFSIMG | grep "partition 1: ID=0x83"
if [ $? == 1 ]
then
	btogSetFsVirtual || exit 2
fi
#3.2 generamos el Sistema de archivos con debootstrap 
schroot -p -c IMGogclient -- touch /tmp/ogclientOK
if [ -f /tmp/ogclientOK ] 
then 
	rm /tmp/ogclientOK 
else
	btogSetFsBase || exit 3
fi

# FASE 4: Configuración el acceso al Segundo Sistema de archivos (img), para schroot
cat /etc/schroot/schroot.conf | grep $BTROOTFSIMG || btogSetFsAccess

# FASE 5: Incorporando con ficheros OG el sistema raiz rootfs 
cp -prv ${BTSVNBOOTTOOLS}/includes/usr/bin/* /tmp/
chmod 777 /tmp/boot-tools/*.sh
schroot -p -c IMGogclient -- /tmp/boot-tools/boottoolsFsOpengnsys.sh 

# FASE6: Instalacion de software
# 6.1 instalacion de software con apt-get
#cp  /etc/apt/sources.list /tmp
schroot -p -c IMGogclient -- /usr/bin/boot-tools/boottoolsSoftwareInstall.sh 


# 6.2 compilación de software.
cd /
schroot -p -c IMGogclient -- /usr/bin/boot-tools/boottoolsSoftwareCompile.sh
schroot -p -c IMGogclient -- /usr/bin/boot-tools/boottoolsSoftwareCompile.sh

cd -

#Fase 7. Personalizando

schroot -p -c IMGogclient -- /usr/bin/boot-tools/boottoolsFsLocales.sh


### 7.1 incorporamos la clave publica del servidor
cd /
ssh-keygen -q -f /root/.ssh/id_rsa -N ""
cp /root/.ssh/id_rsa.pub /tmp
schroot -p -c IMGogclient -- /usr/bin/boot-tools/boottoolsSshServer.sh
cd -
### 7.2 y la del propio cliente.
schroot -c IMGogclient -- /usr/bin/boot-tools/boottoolsSshClient.sh

## 7.3 configuramos los locales.
schroot -c IMGogclient -- /usr/bin/boot-tools/boottoolsFsLocales.sh

for i in `mount | grep IMGogclient | grep /var | cut -f3 -d" "`; do echo $i; umount $i; done
for i in `mount | grep IMGogclient | grep /var | cut -f3 -d" "`; do echo $i; umount $i; done
for i in `mount | grep IMGogclient | grep /var | cut -f3 -d" "`; do echo $i; umount $i; done





#Fase 7. Generando la ISO.
#7.1 el initrd
btogFsInitrd


#7.2 Convertivos el sistema raiz img en formato sqfs
btogFsSqfs
#7.3 Generamos la iso
btogIsoGenerator


# Mostrar sumario de la instalaciÃ³n e instrucciones de post-instalaciÃ³n.
installationSummary

echoAndLog "OpenGnSys installation finished at $(date)"



