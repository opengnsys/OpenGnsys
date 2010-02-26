#!/bin/bash
#/**
#@file    opengnsys_update.sh
#@brief   Script actualización de OpenGnSys
#@warning No se actualiza BD, ni ficheros de configuración.
#@version 0.9 - basado en opengnsys_installer.sh
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2010/01/27
#*/


# Sólo ejecutable por usuario root
if [ "$(whoami)" != 'root' ]
then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi

# Comprobar si se ha descargado el paquete comprimido (USESVN=0) o sólo el instalador (USESVN=1).
PROGRAMDIR=$(readlink -e $(dirname "$0"))
DEPS="rsync gcc"
if [ -d "$PROGRAMDIR/../installer" ]; then
    USESVN=0
else
    USESVN=1
    SVN_URL=svn://www.informatica.us.es:3690/opengnsys/trunk
    DEPS="$DEPS subversion"
fi

WORKDIR=/tmp/opengnsys_update
mkdir -p $WORKDIR

INSTALL_TARGET=/opt/opengnsys
LOG_FILE=/tmp/opengnsys_update.log



#####################################################################
####### Algunas funciones útiles de propósito general:
#####################################################################
function getDateTime()
{
        echo `date +%Y%m%d-%H%M%S`
}

# Escribe a fichero y muestra por pantalla
function echoAndLog()
{
        echo $1
        FECHAHORA=`getDateTime`
        echo "$FECHAHORA;$SSH_CLIENT;$1" >> $LOG_FILE
}

function errorAndLog()
{
        echo "ERROR: $1"
        FECHAHORA=`getDateTime`
        echo "$FECHAHORA;$SSH_CLIENT;ERROR: $1" >> $LOG_FILE
}


#####################################################################
####### Funciones de copia de seguridad y restauración de ficheros
#####################################################################

# Hace un backup del fichero pasado por parámetro
# deja un -last y uno para el día
function backupFile()
{
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local fichero=$1
	local fecha=`date +%Y%m%d`

	if [ ! -f $fichero ]; then
		errorAndLog "${FUNCNAME}(): file $fichero doesn't exists"
		return 1
	fi

    echoAndLog "${FUNCNAME}(): Making $fichero back-up"

	# realiza una copia de la última configuración como last
	cp -p $fichero "${fichero}-LAST"

	# si para el día no hay backup lo hace, sino no
	if [ ! -f "${fichero}-${fecha}" ]; then
		cp -p $fichero "${fichero}-${fecha}"
	fi
}

# Restaura un fichero desde su copia de seguridad
function restoreFile()
{
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local fichero=$1

    echoAndLog "${FUNCNAME}(): restoring $fichero file"
	if [ -f "${fichero}-LAST" ]; then
		cp -p "$fichero-LAST" "$fichero"
	fi
}


#####################################################################
####### Funciones de instalación de paquetes
#####################################################################

# Instalar las deependencias necesarias para el actualizador.
function installDependencies ()
{
	if [ $# = 0 ]; then
		echoAndLog "${FUNCNAME}(): no deps needed."
    else
        while [ $# -gt 0 ]; do
            if ! dpkg -s $1 &>/dev/null; then
                INSTALLDEPS="$INSTALLDEPS $1"
            fi
            shift
        done
        if [ -n "$INSTALLDEPS" ]; then
            apt-get update && apt-get install $INSTALLDEPS
        	if [ $? -ne 0 ]; then
        		errorAndLog "${FUNCNAME}(): cannot install some dependencies: $INSTALLDEPS."
	    	return 1
        	fi
        fi
    fi
}


#####################################################################
####### Funciones para el manejo de Subversion
#####################################################################

function svnExportCode()
{
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local url=$1

	echoAndLog "${FUNCNAME}(): downloading subversion code..."

	svn checkout "${url}" opengnsys
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error getting code from ${url}, verify your user and password"
		return 1
	fi
	echoAndLog "${FUNCNAME}(): subversion code downloaded"
	return 0
}


############################################################
###  Detectar red
############################################################

function getNetworkSettings()
{
        local MAINDEV

 	echoAndLog "getNetworkSettings(): Detecting default network parameters."
	MAINDEV=$(ip -o link show up | awk '!/loopback/ {d=d$2} END {sub(/:.*/,"",d); print d}')
	if [ -z "$MAINDEV" ]; then
 		errorAndLog "${FUNCNAME}(): Network device not detected."
		return 1
	fi

	# Variables de ejecución de Apache
	# - APACHE_RUN_USER
	# - APACHE_RUN_GROUP
	if [ -f /etc/apache2/envvars ]; then
		source /etc/apache2/envvars
	fi
	APACHE_RUN_USER=${APACHE_RUN_USER:-"www-data"}
	APACHE_RUN_GROUP=${APACHE_RUN_GROUP:-"www-data"}
}


#####################################################################
####### Funciones específicas de la instalación de Opengnsys
#####################################################################

# Copiar ficheros del OpenGnSys Web Console.
function updateWebFiles()
{
	echoAndLog "${FUNCNAME}(): Updating web files..."
    backupFile $INSTALL_TARGET/www/controlacceso.php
	rsync --exclude .svn -irplt $WORKDIR/opengnsys/admin/WebConsole $INSTALL_TARGET/www
	if [ $? != 0 ]; then
		errorAndLog "${FUNCNAME}(): Error updating web files."
		exit 1
	fi
    restoreFile $INSTALL_TARGET/www/controlacceso.php
	# Cambiar permisos para ficheros especiales.
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP \
			$INSTALL_TARGET/www/includes \
			$INSTALL_TARGET/www/comandos/gestores/filescripts \
			$INSTALL_TARGET/www/images/iconos
	echoAndLog "${FUNCNAME}(): Web files updated successfully."
}


# Crear documentación Doxygen para la consola web.
function makeDoxygenFiles()
{
	echoAndLog "${FUNCNAME}(): Making Doxygen web files..."
	$WORKDIR/opengnsys/installer/ogGenerateDoc.sh \
			$WORKDIR/opengnsys/client/engine $INSTALL_TARGET/www
	if [ ! -d "$INSTALL_TARGET/www/html" ]; then
		errorAndLog "${FUNCNAME}(): unable to create Doxygen web files."
		return 1
	fi
 	mv "$INSTALL_TARGET/www/html" "$INSTALL_TARGET/www/api"
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/www/api
	echoAndLog "${FUNCNAME}(): Doxygen web files created successfully."
}


# Crea la estructura base de la instalación de opengnsys
function createDirs()
{
	echoAndLog "${FUNCNAME}(): creating directory paths in ${INSTALL_TARGET}"

	mkdir -p ${INSTALL_TARGET}
	mkdir -p ${INSTALL_TARGET}/admin/{autoexec,comandos,menus,usuarios}
	mkdir -p ${INSTALL_TARGET}/bin
	mkdir -p ${INSTALL_TARGET}/client
	mkdir -p ${INSTALL_TARGET}/doc
	mkdir -p ${INSTALL_TARGET}/etc
	mkdir -p ${INSTALL_TARGET}/lib
	mkdir -p ${INSTALL_TARGET}/log/clients
	mkdir -p ${INSTALL_TARGET}/sbin
	mkdir -p ${INSTALL_TARGET}/www
	mkdir -p ${INSTALL_TARGET}/images
	ln -fs /var/lib/tftpboot ${INSTALL_TARGET}
	ln -fs ${INSTALL_TARGET}/log /var/log/opengnsys

	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while creating dirs. Do you have write permissions?"
		return 1
	fi

	echoAndLog "${FUNCNAME}(): directory paths created"
	return 0
}

# Copia ficheros de configuración y ejecutables genéricos del servidor.
function updateServerFiles () {

	local SOURCES=( client/boot/initrd-generator \
                        client/boot/upgrade-clients-udeb.sh \
                        client/boot/udeblist.conf  \
                        client/boot/udeblist-jaunty.conf  \
                        client/boot/udeblist-karmic.conf \
                        doc )
	local TARGETS=( bin/initrd-generator \
                        bin/upgrade-clients-udeb.sh \
                        etc/udeblist.conf \
                        etc/udeblist-jaunty.conf  \
                        etc/udeblist-karmic.conf \
                        doc )

	if [ ${#SOURCES[@]} != ${#TARGETS[@]} ]; then
		errorAndLog "${FUNCNAME}(): inconsistent number of array items"
		exit 1
	fi

	echoAndLog "${FUNCNAME}(): updating files in server directories"
	pushd $WORKDIR/opengnsys >/dev/null
	local i
	for (( i = 0; i < ${#SOURCES[@]}; i++ )); do
		rsync --exclude .svn -irplt "${SOURCES[$i]}" "${INSTALL_TARGET}/${TARGETS[$i]}"
	done
	popd >/dev/null
    echoAndLog "${FUNCNAME}(): server files updated successfully."
}

####################################################################
### Funciones de compilación de código fuente de servicios
####################################################################

# Recompilar y actualiza el binario del clinete
function recompileClient ()
{
	# Compilar OpenGnSys Client
	echoAndLog "${FUNCNAME}(): recompiling OpenGnSys Client"
	pushd $WORKDIR/opengnsys/admin/Services/ogAdmClient
	make && mv ogAdmClient ../../../client/nfsexport/bin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnSys Client"
		hayErrores=1
	fi
	popd

	return $hayErrores
}


####################################################################
### Funciones instalacion cliente opengnsys
####################################################################

function updateClient()
{
	local OSDISTRIB OSCODENAME

	local hayErrores=0

	echoAndLog "${FUNCNAME}(): Copying OpenGnSys Client files."
        rsync --exclude .svn -irplt $WORKDIR/opengnsys/client/nfsexport/* $INSTALL_TARGET/client
	echoAndLog "${FUNCNAME}(): Copying OpenGnSys Cloning Engine files."
        mkdir -p $INSTALL_TARGET/client/lib/engine/bin
        rsync -iplt $WORKDIR/opengnsys/client/engine/*.lib $INSTALL_TARGET/client/lib/engine/bin
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while copying engine files"
		hayErrores=1
	fi

	# Cargar Kernel, Initrd y paquetes udeb para la distribución del servidor (o por defecto).
	OSDISTRIB=$(lsb_release -i | awk -F: '{sub(/\t/,""); print $2}') 2>/dev/null
	OSCODENAME=$(lsb_release -c | awk -F: '{sub(/\t/,""); print $2}') 2>/dev/null
	if [ "$OSDISTRIB" = "Ubuntu" -a -n "$OSCODENAME" ]; then
		echoAndLog "${FUNCNAME}(): Loading Kernel and Initrd files for $OSDISTRIB $OSCODENAME."
        	$INSTALL_TARGET/bin/initrd-generator -t $INSTALL_TARGET/tftpboot -v "$OSCODENAME"
		if [ $? -ne 0 ]; then
			errorAndLog "${FUNCNAME}(): error while generating initrd OpenGnSys Admin Client"
			hayErrores=1
		fi
		echoAndLog "${FUNCNAME}(): Loading udeb files for $OSDISTRIB $OSCODENAME."
        	$INSTALL_TARGET/bin/upgrade-clients-udeb.sh "$OSCODENAME"
		if [ $? -ne 0 ]; then
			errorAndLog "${FUNCNAME}(): error while upgrading udeb files OpenGnSys Admin Client"
			hayErrores=1
		fi
	else
		echoAndLog "${FUNCNAME}(): Loading default Kernel and Initrd files."
        	$INSTALL_TARGET/bin/initrd-generator -t $INSTALL_TARGET/tftpboot/
		if [ $? -ne 0 ]; then
			errorAndLog "${FUNCNAME}(): error while generating initrd OpenGnSys Admin Client"
			hayErrores=1
		fi
		echoAndLog "${FUNCNAME}(): Loading default udeb files."
        	$INSTALL_TARGET/bin/upgrade-clients-udeb.sh
		if [ $? -ne 0 ]; then
			errorAndLog "${FUNCNAME}(): error while upgrading udeb files OpenGnSys Admin Client"
			hayErrores=1
		fi
	fi

	if [ $hayErrores -eq 0 ]; then
		echoAndLog "${FUNCNAME}(): Client generation success."
	else
		errorAndLog "${FUNCNAME}(): Client generation with errors"
	fi

	return $hayErrores
}



#####################################################################
####### Proceso de actualización de OpenGnSys
#####################################################################


echoAndLog "OpenGnSys update begins at $(date)"

# Instalar dependencia.
installDependencies $DEPS
if [ $? -ne 0 ]; then
	errorAndLog "Error: you may install all needed dependencies."
	exit 1
fi

pushd $WORKDIR

# Detectar parámetros de red por defecto
getNetworkSettings
if [ $? -ne 0 ]; then
	errorAndLog "Error reading default network settings."
	exit 1
fi

# Arbol de directorios de OpenGnSys.
createDirs ${INSTALL_TARGET}
if [ $? -ne 0 ]; then
	errorAndLog "Error while creating directory paths!"
	exit 1
fi

# Si es necesario, descarga el repositorio de código en directorio temporal
if [ $USESVN -eq 1 ]; then
	svnExportCode $SVN_URL
	if [ $? -ne 0 ]; then
		errorAndLog "Error while getting code from svn"
		exit 1
	fi
else
	ln -fs "$(dirname $PROGRAMDIR)" opengnsys
fi

# Copiando ficheros complementarios del servidor
updateServerFiles
if [ $? -ne 0 ]; then
    errorAndLog "Error updating OpenGnSys Server files"
	exit 1
fi

# Copiando paqinas web
updateWebFiles
if [ $? -ne 0 ]; then
    errorAndLog "Error updating OpenGnSys Web Admin files"
	exit 1
fi
# Generar páginas Doxygen para instalar en el web
makeDoxygenFiles

# Creando la estructura del cliente
recompileClient
updateClient
if [ $? -ne 0 ]; then
	errorAndLog "Error updating clients"
	exit 1
fi

# Eliminamos el fichero de estado del tracker porque es incompatible entre los distintos paquetes
if [ -r /tmp/dstate ]
then
    rm /tmp/dstate
fi

#rm -rf $WORKDIR
echoAndLog "OpenGnSys update finished at $(date)"

popd

