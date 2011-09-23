#!/bin/bash
#@file    ogClientGenerator.sh
#@brief   Script generación del cliente OpenGnSys
#@warning 
#@version 0.9 -
#@author  Antonio J. Doblas Viso.
#@date    2010/05/24
#*/


#$1 OSCONENAME  lucid karmic
#if [ $# -ne 1 ]; then
#		echo ": invalid number of parameters"
#		echo " host | lucid | karmic | jaunty | lenny | squeeze "
#		exit 1
#fi
TYPECLIENT=host

# Solo ejecutable por usuario root
if [ "$(whoami)" != 'root' ]
then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi



#FIXME: variables del instalador oficial.
WORKDIR=/tmp/opengnsys_installer
INSTALL_TARGET=/opt/opengnsys
LOG_FILE=/tmp/opengnsys_installation.log
PROGRAMDIR=$(readlink -e $(dirname "$0"))


#funciones especificas del cliente.
source $PROGRAMDIR/ogClientManager.lib
#funciones incluidas dentro del scritps general de instalacion.
source $PROGRAMDIR/ogInstaller.lib

echoAndLog "OpenGnSys CLIENT installation begins at $(date)"

##########################################################################
## FASE 1 -  Instalación de software adicional.
##TO DO Integrar en el instaldor. Actualizar repositorios
# Datos para la generación del cliente.
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
############## FIN DEL TO DO
############################################## FIN FASE 1



############## FASE 2   - Asignación de variables
#obtenemos las variables necesarias.
ogClientVar
#obtenemos la información del host.
ogClientOsInfo
######################## FIN fase 2

############# FASE 3: Segundo Sistema archivos (img) Creación.
#TODO comprobacion de que el fichero esta creado.
file $OGCLIENTFILE | grep "partition 1: ID=0x83"
if [ $? == 1 ]
then
	##3.1 creación y formateo del disco virtual. generamos el dispositivo loop.
	ogClient2ndFile || exit 1
fi


#3.2 generamos el Sistema de archivos con debootstrap 
# Comprobamos que ya tenemos alguno.
schroot -p -c IMGogclient -- touch /tmp/ogclientOK
if [ -f /tmp/ogclientOK ] 
then 
	rm /tmp/ogclientOK 
else
	ogClient2ndFs $TYPECLIENT || exit
fi


############### FASE 4: Configuración el acceso al Segundo Sistema de archivos (img), para schroot
cat /etc/schroot/schroot.conf | grep ogclient || ogClientSchrootConf


############### FASE 5: Configuración del Segundo Sistema de archivos (img) con la estructura especial de OpenGnsys
#ogClient2ndSVN $TYPECLIENT || exit
cp ${SVNCLIENTDIR}/clientstructure/root/* /tmp/
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


################## FIN fase 6.   Fin de comfiguración del segundo sistema de archivos (img)

################## FASE 7.  Generamos el 1er sistema de archivos. INITRD
#nota el parametro es el "tipo" de linux generado en debootstrap. usar solo "host", es decir version,kernel ... del propio host
#nota: hace un schroot, al 2fs (img), ejecuta el fichero generateinitrd.
#nota: deja en el directorio tmp del host el nuevo initrd, y lo copia al tftpboot
ogClientInitrd $TYPECLIENT


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
