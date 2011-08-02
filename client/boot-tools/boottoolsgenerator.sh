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


#funciones especificas del cliente.
source $PROGRAMDIR/boottoolsfunctions.lib


echoAndLog "OpenGnSys CLIENT installation begins at $(date)"

##########################################################################
## FASE 1 -  Instalación de software adicional.
DEPENDENCIES=( debootstrap subversion schroot squashfs-tools)
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
########### FIN FASE 1

##### FASE 2   - Asignación de variables
#obtenemos las variables necesarias.
btogGetVar
#obtenemos la información del host.
btogGetOsInfo
##### FIN fase 2

############# FASE 3: Creación del Sistema Root (Segundo Sistema archivos (img))
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


############### FASE 4: Configuración el acceso al Segundo Sistema de archivos (img), para schroot
cat /etc/schroot/schroot.conf | grep $BTROOTFSIMG || btogSetFsAccess




############### FASE 5: Configuración del Segundo Sistema de archivos (img) con la estructura especial de OpenGnsys
cp ${BTSVNBOOTTOOLS}/includes/root/* /tmp/
chmod 777 /tmp/*.sh
schroot -p -c IMGogclient -- /tmp/importSVNboot-tools.sh 






############# FASE6: Ejecutamos los scripts de personalización del 2º sistema de archivos (img) desde la jaula schroot
### 6.1 instalacion de software con apt-get
schroot -p -c IMGogclient -- /root/InstallSoftware.sh 
echo "saltando" 
 if [ $? -ne 0 ]; then
	errorAndLog "Instalando sofware adicional OG : ERROR"
	exit 
else
	echoAndLog "Instalando sofware adicional OG: OK"
fi
#### 6.2 compilación de software.
cd /
schroot -p -c IMGogclient -- /root/CompileSoftware.sh
cd -

### 6.3 configuracion hostname passroot securety
cd /
schroot -c IMGogclient -- /root/ConfFS.sh
cd -
#schroot -c IMGogclient -- echo -ne "og1\nog1\n" | passwd root
# schroot -c IMGogclient -- passwd root | echo "root"


### 6.4 incorporamos la clave publica del servidor
cd /
ssh-keygen -q -f /root/.ssh/id_rsa -N ""
cp /root/.ssh/id_rsa.pub /tmp
schroot -p -c IMGogclient -- /root/importSshKeys.sh
cd -
############ y la del propio cliente.
schroot -c IMGogclient -- /root/generateSshKeysClient.sh

## configuramos los locales.
schroot -c IMGogclient -- /root/ReconfigureLocales.sh

exit 99
################## FIN fase 6.   Fin de comfiguración del segundo sistema de archivos (img)












################## FASE 7.  Generamos el 1er sistema de archivos. INITRD
btogFsInitrd


################## FASE 8. convertimos el 2ºFS(img) en 2ºFS(sqfs)
# generamos el 2sistema de archivos en squashfs
ogClient2ndSqfs
################## FIN FASE 8. convertimos el 2ºFS(img) en 2ºFS(sqfs)


##################### FASE 9. algunos detallas del pxe
#dejamos ficheros de ejemplo para el pxe y el nfs
#ogClientConfpxe
##################### FIN FASE 9. algunos detallas del pxe


# Mostrar sumario de la instalaciÃ³n e instrucciones de post-instalaciÃ³n.
installationSummary

echoAndLog "OpenGnSys installation finished at $(date)"
