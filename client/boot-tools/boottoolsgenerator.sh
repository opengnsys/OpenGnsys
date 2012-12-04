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

####################################################################3
echo "FASE 1 - Asignación de variables"
#obtenemos las variables necesarias y la información del host.
btogGetVar
echoAndLog "OpenGnSys CLIENT installation begins at $(date)"
btogGetOsInfo
##########################################################################
echo "FASE 2 - Instalación de software adicional."
cat /etc/apt/sources.list | grep "http://free.nchc.org.tw/drbl-core" || echo "deb http://free.nchc.org.tw/drbl-core drbl stable " >> /etc/apt/sources.list
apt-get update; apt-get -y --force-yes install debootstrap subversion schroot squashfs-tools syslinux genisoimage gpxe qemu
###################################################################3
echo "FASE 3 - Creación del Sistema raiz RootFS (Segundo Sistema archivos (img)) "
echo "Fase 3.1 Generar y formatear el disco virtual. Generar el dispositivo loop."
file $BTROOTFSIMG | grep "partition 1: ID=0x83"
if [ $? == 1 ]
then
	btogSetFsVirtual || exit 2
fi
echo "Fase 3.2 Generar sistema de archivos con debootstrap" 
schroot -p -c IMGogclient -- touch /tmp/ogclientOK
if [ -f /tmp/ogclientOK ] 
then 
	rm /tmp/ogclientOK 
else
	btogSetFsBase || exit 3
fi
###################################################################3
echo "FASE 4 - Configurar acceso schroot al Segundo Sistema de archivos (img)"
cat /etc/schroot/schroot.conf | grep $BTROOTFSIMG || btogSetFsAccess
###########################################################################
echo "FASE 5 - Incorporando ficheros OpenGnSys el sistema raiz rootfs "
cp -av ${BTSVNBOOTTOOLS}/includes/usr/bin/* /tmp/
chmod 777 /tmp/boot-tools/*.sh
umount $BTROOTFSMNT 2>/dev/null
schroot -p -c IMGogclient -- /tmp/boot-tools/boottoolsFsOpengnsys.sh 
############################################################################################
echo "FASE 6 - Instalar software"
echo "Fase 6.1 instalar paquetes deb con apt-get"
schroot -p -c IMGogclient -- /usr/bin/boot-tools/boottoolsSoftwareInstall.sh 
echo "Fase 6.2 compilar software."
cd /
schroot -p -c IMGogclient -- /usr/bin/boot-tools/boottoolsSoftwareCompile.sh
schroot -p -c IMGogclient -- /usr/bin/boot-tools/boottoolsSoftwareCompile.sh
cd -

echo "FASE 7 - Personalizar el sistema creado"
echo "Fase 7.1 Incorporar la clave publica del servidor"
cd /
ssh-keygen -q -f /root/.ssh/id_rsa -N ""
cp /root/.ssh/id_rsa.pub /tmp
schroot -p -c IMGogclient -- /usr/bin/boot-tools/boottoolsSshServer.sh
cd -
echo "Fase 7.2. Incorpoar la clave publica del propio  cliente"
schroot -p -c IMGogclient -- /usr/bin/boot-tools/boottoolsSshClient.sh

echo "Fase 7.3. Configurando las locales"
schroot -p -c IMGogclient -- /usr/bin/boot-tools/boottoolsFsLocales.sh


for i in `mount | grep IMGogclient | grep /var | cut -f3 -d" "`; do echo $i; umount $i; done
for i in `mount | grep IMGogclient | grep /var | cut -f3 -d" "`; do echo $i; umount $i; done
for i in `mount | grep IMGogclient | grep /var | cut -f3 -d" "`; do echo $i; umount $i; done

#########################################################################
echo "FASE 8 - Generar distribucion"
echo "Fase 8.1 Generar el initrd"
btogFsInitrd
echo "Fase 8.2 Generar fichero sqfs a partir del fichero img"
btogFsSqfs
echo "Fase 8.3 Generar la ISO" 
btogIsoGenerator
######################################################################3
########################################################################
# Mostrar sumario de la instalaciÃ³n e instrucciones de post-instalaciÃ³n.
installationSummary
echoAndLog "OpenGnSys installation finished at $(date)"

