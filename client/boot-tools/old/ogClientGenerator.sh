#!/bin/bash
#@file    ogClientGenerator.sh
#@brief   Script generación del cliente OpenGnSys
#@warning 
#@version 0.9 -
#@author  Antonio J. Doblas Viso.
#@date    2010/05/24
#*/



if [ $# -ne 1 ]; then
		echo ": invalid number of parameters"
		echo " host | lucid | karmic | jaunty | lenny | squeeze "
		exit 1
fi



# Soo ejecutable por usuario root
if [ "$(whoami)" != 'root' ]
then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi

# Comprobar si se ha descargado el paquete comprimido (USESVN=0) o sÃ³lo el instalador (USESVN=1).
PROGRAMDIR=$(readlink -e $(dirname "$0"))
if [ -d "$PROGRAMDIR/../installer" ]; then
    USESVN=0
else
    USESVN=1
    SVN_URL=svn://www.informatica.us.es:3690/opengnsys/branches/ogClient
    #directorio donde se almacenará el codigo temporalmente.
    SVN_DIR="./opengnsys/installer/ogClient"
fi

WORKDIR=/tmp/opengnsys_installer
mkdir -p $WORKDIR
INSTALL_TARGET=/opt/opengnsys
LOG_FILE=/tmp/opengnsys_installation.log

###############################################
pushd $WORKDIR


source `dirname $0`/ogInstaller.lib


if [ "$1" == "host" ] 
then
	OSDISTRIB=$(lsb_release -i | awk -F: '{sub(/\t/,""); print $2}') 2>/dev/null
	#OSCODENAME=$(lsb_release -c | awk -F: '{sub(/\t/,""); print $2}') 2>/dev/null
	OSCODENAME=$(cat /etc/lsb-release | grep CODENAME | awk -F= '{print $NF}')
	OGRELEASE=$(uname -a | awk '{print $3}')
else
	OSCODENAME=`echo $1`
	OGRELEASE=`ogClientGetRelease $OSCODENAME`
fi



OGCLIENTBASEDIR=/var/lib/tftpboot/ogclient/
OGCLIENTFILE=${OGCLIENTBASEDIR}ogclient.img
OGCLIENTMOUNT=${OGCLIENTBASEDIR}ogclientmount

OGLIB=/opt/opengnsys/client/lib



echoAndLog "OpenGnSys CLIENT installation begins at $(date)"


# Datos para la generación del cliente.
DEPENDENCIES=( debootstrap subversion schroot)

## Actualizar repositorios
apt-get update
# InstalaciÃ³n de dependencias (paquetes de sistema operativo).
declare -a notinstalled
checkDependencies DEPENDENCIES notinstalled
if [ $? -ne 0 ]; then
	installDependencies notinstalled
	if [ $? -ne 0 ]; then
		echoAndLog "Error while installing some dependeces, please verify your server installation before continue"
		exit 1
	fi
fi



# Si es necesario, descarga el repositorio especifico de la instalación del cliente
if [ $USESVN -eq 1 ]; then
	#svnExportCode $SVN_URL
	echo svn export "$SVN_URL" $SVN_DIR
	#svn export "$SVN_URL" $SVN_DIR
	#####debug boorrar el cp
	#mkdir -p $SVN_DIR
	#cp -prv /home/administrador/workspace/opengnsys/branches/ogClient/* $SVN_DIR
	find $SVN_DIR/ -name .svn -type d -exec rm -fr {} \; 2>/dev/null
	###TODO si ya esta descargado da error ???
	###if [ $? -ne 0 ]; then
	###	errorAndLog "Error while getting code from svn"
	###	exit 1
	###fi
else
	ln -fs "$(dirname $PROGRAMDIR)" opengnsys
fi

#### Parseo de ficheros descargados del svn.
# parseamos del apt.source
if [ "$1" == host ]
then
    cp /etc/apt/sources.list ${SVN_DIR}/clientstructure/etc/apt/sources.list
else
	sed -e "s/OGVERSION/$OGVERSION/g" ${SVN_DIR}/clientstructure/etc/apt/sources.list.generic > ${SVN_DIR}/clientstructure/etc/apt/sources.list
	#rm ${SVN_DIR}/clientstructure/etc/apt/sources.list.generic
fi
if [ $? -ne 0 ]; then
	errorAndLog "parseando el fichero apt.source: ERROR"
else
	echoAndLog "parseando el fichero apt.source: OK"
fi

#parseamos el scripts de generación del initrd.
sed -e "s/OGRELEASE/$OGRELEASE/g" ${SVN_DIR}/clientstructure/root/GenerateInitrd.generic.sh > ${SVN_DIR}/clientstructure/root/GenerateInitrd.sh
#rm ${SVN_DIR}/clientstructure/root/GenerateInitrd.generic.sh
if [ $? -ne 0 ]; then
	errorAndLog "parseando el fichero de cliente GenerateInitrd: ERROR"
else
	echoAndLog "parseando el fichero de cliente GenerateInitrd: OK"
fi



#damos permiso al directorio de scripts 
chmod 775 ${SVN_DIR}/clientstructure/root/*
if [ $? -ne 0 ]; then
	errorAndLog "Dando permisos de escritura al directorio de scrips para el cliente: ERROR"
else
	echoAndLog "Dando permisos de escritura al directorio de scrips para el cliente: OK"
fi


#####PASO 1. Generamos el 2º sistema de archivos.
echo "generamos el sistema base con debootstrap"
if [ "$1" == host ]
then
	echo ogClientGeneratorDebootstrap $OSCODENAME $OGRELEASE 2>&1 | tee -a $LOG_FILE
#	ogClientGeneratorDebootstrap $OSCODENAME $OGRELEASE 2>&1 | tee -a $LOG_FILE
else
	echo ogClientGeneratorDebootstrap $OSCODENAME $(ogClientGetRelease $OGVERSION) 2>&1 | tee -a $LOG_FILE
	ogClientGeneratorDebootstrap $OSCODENAME $(ogClientGetRelease $OGVERSION) 2>&1 | tee -a $LOG_FILE
fi
if [ $? -ne 0 ]; then
	errorAndLog "Generando 2nd FileSystem: ERROR"
else
	echoAndLog "Generando 2nd FileSystem: OK"
fi

##########################################

cp /etc/schroot/schroot.conf /etc/schroot/schroot.conf.`getDateTime`
cat << EOF > /etc/schroot/schroot.conf
[IMGogclient]
type=loopback
file=/var/lib/tftpboot/ogclient/ogclient.img
description=ogclient ubuntu luc IMGi
priority=1
users=root
groups=root
root-groups=root
mount-options=-o offset=32256
root-users=root
[DIRogclient]
type=directory
directory=/var/lib/tftpboot/ogclient/ogclientmount
description=ogclient ubuntu lucid DIR
priority=2
users=root
groups=root
root-groups=root
root-users=root
EOF



####PASO 2 Insertamos datos en el 2º sistema de archivos
#2.1 montamos para insertar los ficheros necesarios.
mount | grep $OGCLIENTMOUNT || mount $OGCLIENTFILE $OGCLIENTMOUNT -o loop,offset=32256
if [ $? -ne 0 ]; then
	errorAndLog "Montando 2nd FileSystem Para Añadir elementos OG: ERROR"
	exit 1
else
	echoAndLog "Montando 2nd FileSystem Para Añadir elementos OG: OK"
fi

#2.2 copiamos la estrucutra descargada al fichero imagen.
echo "cp -prv ${SVN_DIR}/clientstructure/* $OGCLIENTMOUNT "
cp -prv ${SVN_DIR}/clientstructure/* $OGCLIENTMOUNT
if [ $? -ne 0 ]; then
	errorAndLog "Copiando los elementos del cliente svn: ERROR"
	exit 1
else
	echoAndLog "Copiando los elementos del cliente svn: OK"
fi

#2.3 Copiamos algunas cosas del actual sistema - ver de que manera integrar los lib
# copiamos algunas cosas del nfsexport
echo "Linking fonts for browser1 $OGLIB"
mkdir -p $OGCLIENTMOUNT/usr/local/Trolltech/QtEmbedded-4.5.1/lib/
cp -pr $OGLIB/fonts $OGCLIENTMOUNT/usr/local/Trolltech/QtEmbedded-4.5.1/lib/fonts
echo "Linking fonts for browser2 $OGLIB"
mkdir -p $OGCLIENTMOUNT/usr/local/QtEmbedded-4.6.2/lib/
cp -pr $OGLIB/fonts $OGCLIENTMOUNT/usr/local/QtEmbedded-4.6.2/lib/fonts
echo "Coping pci.ids"
cp -pr $OGLIB/pci.ids $OGCLIENTMOUNT/etc
#copiamos el browser y el ogADMcline al bin
cp /opt/opengnsys/client/bin/browser $OGCLIENTMOUNT/bin
cp /opt/opengnsys/client/bin/ogAdmClient  $OGCLIENTMOUNT/bin
if [ $? -ne 0 ]; then
	errorAndLog "Copiando qt pci.ids fonts: ERROR"
	exit 1
else
	echoAndLog "Copiando qt pci.ids fonts: OK"
fi


## final desmontamos.
mount | grep $OGCLIENTMOUNT && umount $OGCLIENTMOUNT
if [ $? -ne 0 ]; then
	errorAndLog "Desmontando cliente : ERROR"
	exit 1
else
	echoAndLog "desmontando cliente: OK"
fi

################## Fin paso 2

popd

######## instalamos software adicional.
#ogClientMount /root/InstallSoftware.sh 2>&1 | tee -a `echo $LOG_FILE`
cd /
schroot -c IMGogclient -- /root/InstallSoftware.sh 2>&1 | tee -a `echo $LOG_FILE`

if [ $? -ne 0 ]; then
	errorAndLog "Instalando sofware adicional OG : ERROR"
	#exit 1
else
	echoAndLog "Instalando sofware adicional OG: OK"
fi
cd -

#cd /
#schroot -c IMGogclient -- /root/CompileSoftware.sh
#cd -

### configuracion hostname passroot securety
cd /
schroot -c IMGogclient -- /root/ConfFS.sh
cd -

##2.4 claves ssh
sshkeys()
{
##montamos 
mount $OGCLIENTFILE $OGCLIENTMOUNT -o loop,offset=32256
##comprobamos clave rsa en el host,.
if [ ! -f /root/.ssh/id_rsa.pub ] 
then
	ssh-keygen -q -f /root/.ssh/id_rsa -N ""
fi
## copiamos ssh rsa del host al guest como authorized-key2
rm ${OGCLIENTMOUNT}/root/.ssh/authorized-key2
cat /root/.ssh/id_rsa.pub >> ${OGCLIENTMOUNT}/root/.ssh/authorized-key2
#cat ${OGCLIENTMOUNT}/root/.ssh/id_rsa.pub >> ${OGCLIENTMOUNT}/root/.ssh/authorized-key2
mount | grep $OGCLIENTMOUNT || umount $OGCLIENTMOUNT
}

### Generamos el 1er sistema de archivos.
cd /
schroot -c IMGogclient -- /root/GenerateInitrd.sh
cp /tmp/*-${OGRELEASE} $OGCLIENTBASEDIR
cd -



################## DEJAMOS FICHERO DE EJEMPLOS PARA:
#default
cat << FIN >> /var/lib/tftpboot/pxelinux.cfg/defaultNEWClient
LABEL pxe-${OGRELEASE}
KERNEL ogclient/vmlinuz-$OGRELEASE
APPEND initrd=ogclient/initrd.img-$OGRELEASE ip=dhcp ro boot=og vga=788 irqpoll acpi=on reposerver=
FIN
#/etc/hosts
echo "/var/lib/tftpboot   *(ro,no_subtree_check,no_root_squash,sync)" > /etc/exportsNEWClient
/etc/init.d/nfs-kernel-server restart
#####################################################################

# Mostrar sumario de la instalaciÃ³n e instrucciones de post-instalaciÃ³n.
installationSummary

#rm -rf $WORKDIR
echoAndLog "OpenGnSys installation finished at $(date)"

