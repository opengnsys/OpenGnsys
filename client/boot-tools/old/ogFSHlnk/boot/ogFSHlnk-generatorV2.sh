#!/bin/bash
#Definicion de variables
# TODO: Pendiente Definir directorio base del 2FS
OGBASEDIR=/opt/opengnsys/client/
OGFSFILE=${OGBASEDIR}og2ndFS
OGFSMOUNT=${OGBASEDIR}ogfsmount
OGLIB=/opt/opengnsys/client/lib





#/**
#         ogFSHMount [str_program]
#@brief   Acceso al 2nd FS del cliente desde el Servidor Opengnsys
#@param 1 Opciona: scripts o programa a ejecutar para automatizaciones
#@return  Si no hay parametros: login de acceso.
#@return  con un parametro: La salida del programa ejecutado
#@exception 
#@note    
#@todo    
#@version 0.9 - Primera versión para OpenGnSys
#@author  Antonio J. Doblas Viso, Universidad de Málaga
#@date    2010/02/15
#*/ ##
function ogFSHMount ()
{
mount $OGFSFILE $OGFSMOUNT -t ext3 -o loop
mount --bind /proc $OGFSMOUNT/proc
mount --bind /sys $OGFSMOUNT/sys
mount --bind /tmp $OGFSMOUNT/tmp
mount --bind /dev $OGFSMOUNT/dev
mount --bind /dev/pts $OGFSMOUNT/dev/pts
[ $# = 0 ] && $(chroot $OGFSMOUNT /sbin/getty 38400 `tty`)
[ $# = 1 ] && chroot $OGFSMOUNT $1
}

#/**
#         ogFSHUnmount 
#@brief   Desmonta el 2nd FS del cliente desde el Servidor Opengnsys
#@param   
#@return  
#@exception 
#@note    
#@todo    
#@version 0.9 - Primera versión para OpenGnSys
#@author  Antonio J. Doblas Viso, Universidad de Málaga
#@date    2010/02/15
#*/ ##

function ogFSHUnmount ()
{
cd /tmp
umount -d -f -l $OGFSMOUNT/proc
umount -d -f -l $OGFSMOUNT/sys
umount -d -f -l $OGFSMOUNT/tmp
umount -d -f -l $OGFSMOUNT/dev
umount -d -f -l $OGFSMOUNT/dev/pts
umount -d -f -l $OGFSMOUNT 
}


#/**
#         ogFSHCreate str_versionUbuntu 
#@brief   Crea el 2nd FS del cliente desde el Servidor Opengnsys
#@param 1 Versión de ubuntu a generar, jaunty karmic
#@return  
#@exception 
#@note    
#@todo    
#@version 0.9 - Primera versión para OpenGnSys
#@author  Antonio J. Doblas Viso, Universidad de Málaga
#@date    2010/02/15
#*/ ##

function ogFSHCreate () 
{
#Definicion de variables.
local SCRIPT RUNME FSCLIENTSIZEMB PASSROOT LASTDEBOOTSTRAP OGMUSTCOMPILE OGFSLABEL
FSCLIENTSIZEMB=1000
PASSROOT=og
SCRIPT=/root/configure.sh
RUNME=$OGFSMOUNT$SCRIPT
LASTDEBOOTSTRAP=http://archive.ubuntu.com/ubuntu/pool/main/d/debootstrap/debootstrap_1.0.20_all.deb
OGMUSTCOMPILE="http://www.informatica.us.es:8080/opengnsys/browser/trunk/client/engine/ToolsGNU.c?format=txt -O /root/ToolsGNU.c"
OGFSLABEL=og2FS
#TODO comprobar la compatibilidad del SO host

if [ $# != 1 ]
then
echo Debes introducir como argumento: jaunty karmic lucid
return
fi

#TODO: configurar la version lucid
#TODO: introducir un nuevo case para la vesion
case $1 in
     jaunty|JAUNTY)
     	VERSION=jaunty
     	RELEASE=2.6.28-11-generic
     ;;
     karmic|KARMIC)
     	VERSION=karmic
     	RELEASE=2.6.31-14-generic
     ;;
     lucid|LUCID)
     	VERSION=lucid
     	#RELEASE=2.6.32-19-generic
     	RELEASE=2.6.32-21-generic-pae
     ;;
esac

# instalamos el ultimo debotstrap para permitir instalar versiones superiores a nuestro sistema
apt-get install gdebi-core
wget $LASTDEBOOTSTRAP
gdebi -n debootstrap_1.0.20_all.deb

#Creamos el disco virtual con el filesystem del cliente.
dd if=/dev/zero of=$OGFSFILE bs=1048576 count=$FSCLIENTSIZEMB
mkfs.ext3 -b 4096 -L $OGFSLABEL $OGFSFILE -F
#Creamos el directorio donde montaremos el disco virtual
mkdir -p $OGFSMOUNT
#Montamos el dispositivo virtual en su punto de montaje.
mount $OGFSFILE $OGFSMOUNT -t ext3 -o loop

#TODO Comprobar arquitectura
#Iniciamos la creación del sistema en el directorio de clientes.
echo debootstrap --include=linux-image-$RELEASE --arch=i386 --components=main,universe $VERSION $OGFSMOUNT http://es.archive.ubuntu.com/ubuntu/ 
debootstrap --include=linux-image-$RELEASE --arch=i386 --components=main,universe $VERSION $OGFSMOUNT http://es.archive.ubuntu.com/ubuntu/ 

# preparamos el etc.sources.
cat << FIN > ${OGFSMOUNT}/etc/apt/sources.list
deb http://es.archive.ubuntu.com/ubuntu/ $VERSION main restricted
deb-src http://es.archive.ubuntu.com/ubuntu/ $VERSION main restricted
## Major bug fix updates produced after the final release of the
## distribution.
deb http://es.archive.ubuntu.com/ubuntu/ $VERSION-updates main restricted
deb-src http://es.archive.ubuntu.com/ubuntu/ $VERSION-updates main restricted
## N.B. software from this repository is ENTIRELY UNSUPPORTED by the Ubuntu
## team. Also, please note that software in universe WILL NOT receive any
## review or updates from the Ubuntu security team.
deb http://es.archive.ubuntu.com/ubuntu/ $VERSION universe
deb-src http://es.archive.ubuntu.com/ubuntu/ $VERSION universe
deb http://es.archive.ubuntu.com/ubuntu/  $VERSION-updates universe
deb-src http://es.archive.ubuntu.com/ubuntu/ $VERSION-updates universe

## N.B. software from this repository is ENTIRELY UNSUPPORTED by the Ubuntu 
## team, and may not be under a free licence. Please satisfy yourself as to 
## your rights to use the software. Also, please note that software in 
## multiverse WILL NOT receive any review or updates from the Ubuntu
## security team.
deb http://es.archive.ubuntu.com/ubuntu/ $VERSION multiverse
deb-src http://es.archive.ubuntu.com/ubuntu/ $VERSION multiverse
deb http://es.archive.ubuntu.com/ubuntu/ $VERSION-updates multiverse
deb-src http://es.archive.ubuntu.com/ubuntu/ $VERSION-updates multiverse

## Uncomment the following two lines to add software from the 'backports'
## repository.
## N.B. software from this repository may not have been tested as
## extensively as that contained in the main release, although it includes
## newer versions of some applications which may provide useful features.
## Also, please note that software in backports WILL NOT receive any review
## or updates from the Ubuntu security team.
# deb http://es.archive.ubuntu.com/ubuntu/ $VERSION-backports main restricted universe multiverse
# deb-src http://es.archive.ubuntu.com/ubuntu/ $VERSION-backports main restricted universe multiverse

## Uncomment the following two lines to add software from Canonical's
## 'partner' repository.
## This software is not part of Ubuntu, but is offered by Canonical and the
## respective vendors as a service to Ubuntu users.
# deb http://archive.canonical.com/ubuntu $VERSION partner
# deb-src http://archive.canonical.com/ubuntu $VERSION partner


deb http://security.ubuntu.com/ubuntu $VERSION-security main restricted
deb-src http://security.ubuntu.com/ubuntu $VERSION-security main restricted
deb http://security.ubuntu.com/ubuntu $VERSION-security universe
deb-src http://security.ubuntu.com/ubuntu $VERSION-security universe
deb http://security.ubuntu.com/ubuntu $VERSION-security multiverse
deb-src http://security.ubuntu.com/ubuntu $VERSION-security multiverse


deb http://archive.ubuntu.com/ubuntu $VERSION main
deb http://free.nchc.org.tw/drbl-core drbl stable
deb http://free.nchc.org.tw/ubuntu $VERSION-security main restricted universe multiverse
deb http://ppa.launchpad.net/freenx-team/ubuntu/ $VERSION main
deb http://ppa.launchpad.net/randomaction/ppa/ubuntu $VERSION main
deb-src http://ppa.launchpad.net/randomaction/ppa/ubuntu $VERSION main
FIN

#TODO: fichero etc/hosts
#TODO: fichero etc/resolv.conf
echo "2ndFSHclient" > ${OGFSMOUNT}/etc/hostname
#TODO: introducir mas consoluas para el acceso como root.
echo "pts/0" >> ${OGFSMOUNT}/etc/securetty
echo "pts/1" >> ${OGFSMOUNT}/etc/securetty
echo "pts/2" >> ${OGFSMOUNT}/etc/securetty
echo "pts/3" >> ${OGFSMOUNT}/etc/securetty

# copiamos algunas cosas del nfsexport
echo "Linking fonts for browser1 $OGLIB"
mkdir -p $OGFSMOUNT/usr/local/Trolltech/QtEmbedded-4.5.1/lib/
cp -pr $OGLIB/fonts $OGFSMOUNT/usr/local/Trolltech/QtEmbedded-4.5.1/lib/fonts

echo "Linking fonts for browser2 $OGLIB"
mkdir -p $OGFSMOUNT/usr/local/QtEmbedded-4.6.2/lib/
cp -pr $OGLIB/fonts $OGFSMOUNT/usr/local/QtEmbedded-4.6.2/lib/fonts

echo "Coping pci.ids"
cp -pr $OGLIB/pci.ids $OGFSMOUNT/etc

# Guión de ejecución y personalización final.
cat << FIN > $RUNME
#!/bin/bash
################################################################
##################### SOFTWARE #####################
################################################################
export LANGUAGE=C
export LC_ALL=C
export LANG=C

dpkg-divert --local --rename --add /sbin/initctl 
ln -s /bin/true /sbin/initctl


apt-get -y update

# software system
apt-get -y --force-yes install linux-image-$RELEASE wget dialog man-db htop
#
# software to compile code
apt-get -y --force-yes install build-essential libattr* attr make m4 gettext libmhash-dev gdebi-core gawk
wget $OGMUSTCOMPILE 
source /root/ToolsGNU.c
#
# sofware networking
apt-get -y --force-yes install netpipes nfs-common sshfs smbfs smbclient davfs2 
ctorrent install
udpcast install
#
# software services
apt-get -y --force-yes install openssh-server bittornado trickle
#
# software disk and filesystem
apt-get -y --force-yes install drbl-ntfsprogs ntfsprogs parted 
apt-get -y --force-yes install dmraid dmsetup mdadm lvm2 e2fsprogs jfsutils reiserfsprogs xfsprogs unionfs-fuse mhddfs squashfs-tools
ntfs-3g install
#
# software cloning
apt-get -y --force-yes install drbl-partimage fsarchiver pv kexec-tools
wget -O partclone_0.2.8_i386.deb http://downloads.sourceforge.net/project/partclone/stable/0.2.8/partclone_0.2.8_i386.deb?use_mirror=ovh
gdebi -n partclone_0.2.8_i386.deb 
mbuffer install
#
## software postconf
apt-get -y --force-yes install drbl-chntpw chntpw ethtool lshw gawk
ms-sys install
#
# software compressor
apt-get -y --force-yes install lzma zip unzip gzip lzop drbl-lzop

apt-get -y --force-yes remove busybox
apt-get -y --force-yes install busybox-static  bash-static 
#
####################################################################
###################### Preparando el entorno ######################
###################################################################
mkdir -p /var/lock
dpkg-reconfigure passwd
echo "root:$PASSROOT" | chpasswd
mv /etc/fstab /etc/fstab.original 2>/dev/null 
mv /etc/mtab /etc/mtab.original 2>/dev/null 
FIN

chmod +x $RUNME

#chroot hacia el punto de montaje.
ogFSHMount $SCRIPT
### Desmontamos y salimos.
ogFSHUnmount 2>/dev/null
ogFSHUnmount 2>/dev/null
ogFSHUnmount 2>/dev/null
}


###### Funciones para los clientes offline 
function agregarOG {
cd $NEWROOT
mkdir -p opt/opengnsys
cp -prv /opt/opengnsys/client/* $NEWROOT/opt/opengnsys
echo "agregando OG al newinitrd"
}

function finalizarISO
{
    cd $ANTERIORPWD
    mv $TMPINITRD/new-initrd.gz $DEST/initrdISO.gz
    if [ $LINUX ] ; then
        mv $TMPINITRD/linux $DEST/linuxISO
    fi
}


function CrearISO {
mkdir -p tmp/iso/isolinux
#cd tmp/iso/
cp -prv /usr/lib/syslinux/* tmp/iso/isolinux/
cp -prv /usr/share/gpxe/* tmp/iso/isolinux/
cp -prv /tmp/linux tmp/iso/isolinux/linuxISO
cp -prv /tmp/initrd.gz tmp/iso/isolinux/
cp -prv /opt/opengnsys/client/og2ndFS tmp/iso/isolinux/


cat << FIN > tmp/iso/isolinux/isolinux.cfg
DEFAULT menu.c32
PROMPT 0
ALLOWOPTIONS 1

MENU TITLE FuTuR3 Live Collection

LABEL gpxe
MENU LABEL gpxe
KERNEL /clonezilla/live/vmlinuz1
APPEND initrd=/clonezilla/live/initrd1.img boot=live union=aufs noswap vga=788 ip=frommedia


#default 0
#prompt 1
#timeout 100

#display mensaje.txt

LABEL 0
MENU LABEL ogClient
KERNEL linuxISO
APPEND initrd=initrd.gz ro vga=788 irqpoll acpi=on boot=admin status=offline

LABEL 1
MENU LABEL ogClient1 sin vga
KERNEL linuxISO
APPEND initrd=initrd.gz ro  irqpoll acpi=on boot=admin status=offline

LABEL 2
MENU LABEL ogClient2 sin irqpoll
KERNEL linuxISO
APPEND initrd=initrd.gz ro acpi=on boot=admin status=offline

LABEL 3
MENU LABEL ogClient3 acpi=off
KERNEL linuxISO
APPEND initrd=initrd.gz ro acpi=off boot=admin status=offline


#LABEL ogclient
#KERNEL /ogclient/linuxISO
#APPEND initrd=/ogclient/initrdISO.img

#KERNEL linuxISO
#APPEND initrd=initrdISO.img

LABEL 4
MENU LABEL local
localboot 0x80
append -


label 5
MENU LABEL Network boot via gPXE lkrn
KERNEL gpxe.lkrn

label 5
MENU LABEL Network boot via gPXE usb
KERNEL gpxe.usb

label 5
MENU LABEL Network boot via gPXE  pxe
KERNEL gpxe.pxe

label 5
MENU LABEL Network boot via gPXE  iso
KERNEL gpxe.iso
FIN
#### /tmp/iso# 
mkisofs -V ogClient -o ogClient.iso -b isolinux/isolinux.bin -c isolinux/boot.cat -no-emul-boot -boot-load-size 4 -boot-info-table tmp/iso

}

function probarISO {
#/tmp/iso
qemu -m 256 -boot d -cdrom ogClient.iso 
}




