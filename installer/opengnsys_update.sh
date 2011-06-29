#!/bin/bash
#/**
#@file    opengnsys_update.sh
#@brief   Script actualización de OpenGnSys
#@warning No se actualiza BD, ni ficheros de configuración.
#@version 0.9 - basado en opengnsys_installer.sh
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2010/01/27
#@version 1.0 - adaptación a OpenGnSys 1.0
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2011/03/02
#@version 1.0.1 - control de auto actualización del script
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2011/05/17
#*/


####  AVISO: Editar configuración de acceso por defecto a la Base de Datos.
OPENGNSYS_DATABASE="ogAdmBD"		# Nombre de la base datos
OPENGNSYS_DBUSER="usuog"		# Usuario de acceso
OPENGNSYS_DBPASSWORD="passusuog"	# Clave del usuario

####  AVISO: NO Editar variables de acceso desde el cliente
OPENGNSYS_CLIENTUSER="opengnsys"	# Usuario Samba


# Sólo ejecutable por usuario root
if [ "$(whoami)" != 'root' ]
then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi
# Error si OpenGnSys no está instalado (no existe el directorio del proyecto)
INSTALL_TARGET=/opt/opengnsys
if [ ! -d $INSTALL_TARGET ]; then
        echo "ERROR: OpenGnSys is not installed, cannot update!!"
        exit 1
fi

# Comprobar si se ha descargado el paquete comprimido (USESVN=0) o sólo el instalador (USESVN=1).
PROGRAMDIR=$(readlink -e $(dirname "$0"))
PROGRAMNAME=$(basename "$0")
DEPS="build-essential g++-multilib rsync ctorrent samba unzip netpipes debootstrap schroot squashfs-tools"
OPENGNSYS_SERVER="www.opengnsys.es"
if [ -d "$PROGRAMDIR/../installer" ]; then
    USESVN=0
else
    USESVN=1
    DEPS="$DEPS subversion"
fi
SVN_URL="http://$OPENGNSYS_SERVER/svn/branches/version1.0/"

WORKDIR=/tmp/opengnsys_update
mkdir -p $WORKDIR

LOG_FILE=/tmp/opengnsys_update.log



#####################################################################
####### Algunas funciones útiles de propósito general:
#####################################################################

# Comprobar auto-actualización.
function checkAutoUpdate()
{
	local update=0

	# Actaulizar el script si ha cambiado o no existe el original.
	if [ $USESVN -eq 1 ]; then
		svn export $SVN_URL/installer/$PROGRAMNAME
		if ! diff --brief $PROGRAMNAME $INSTALL_TARGET/lib/$PROGRAMNAME &>/dev/null || ! test -f $INSTALL_TARGET/lib/$PROGRAMNAME; then
			mv $PROGRAMNAME $INSTALL_TARGET/lib
			update=1
		else
			rm -f $PROGRAMNAME
		fi
	else
		if ! diff --brief $PROGRAMDIR/$PROGRAMNAME $INSTALL_TARGET/lib/$PROGRAMNAME &>/dev/null || ! test -f $INSTALL_TARGET/lib/$PROGRAMNAME; then
			cp -a $PROGRAMDIR/$PROGRAMNAME $INSTALL_TARGET/lib
			update=1
		fi
	fi

	return $update
}


function getDateTime()
{
	date "+%Y%m%d-%H%M%S"
}

# Escribe a fichero y muestra por pantalla
function echoAndLog()
{
	echo $1
	DATETIME==`getDateTime`
	echo "$DATETIME=;$SSH_CLIENT;$1" >> $LOG_FILE
}

function errorAndLog()
{
	echo "ERROR: $1"
	DATETIME=`getDateTime`
	echo "$DATETIME=;$SSH_CLIENT;ERROR: $1" >> $LOG_FILE
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

	echoAndLog "${FUNCNAME}(): restoring file $fichero"
	if [ -f "${fichero}-LAST" ]; then
		cp -p "$fichero-LAST" "$fichero"
	fi
}


#####################################################################
####### Funciones de acceso a base de datos
#####################################################################

# Actualizar la base datos
function importSqlFile()
{
        if [ $# -ne 4 ]; then
                errorAndLog "${FNCNAME}(): invalid number of parameters"
                exit 1
        fi

        local dbuser="$1"
        local dbpassword="$2"
        local database="$3"
        local sqlfile="$4"
        local tmpfile=$(mktemp)
        local status

        if [ ! -r $sqlfile ]; then
                errorAndLog "${FUNCNAME}(): Unable to read $sqlfile!!"
                return 1
        fi

        echoAndLog "${FUNCNAME}(): importing SQL file to ${database}..."
        chmod 600 $tmpfile
        sed -e "s/SERVERIP/$SERVERIP/g" -e "s/DBUSER/$OPENGNSYS_DB_USER/g" \
            -e "s/DBPASSWORD/$OPENGNSYS_DB_PASSWD/g" $sqlfile > $tmpfile
        mysql -u$dbuser -p"$dbpassword" --default-character-set=utf8 "$database" < $tmpfile
	status=$?
	rm -f $tmpfile
	if [ $status -ne 0 ]; then
                errorAndLog "${FUNCNAME}(): error importing $sqlfile in database $database"
                return 1
        fi
        echoAndLog "${FUNCNAME}(): file imported to database $database"
        return 0
}


#####################################################################
####### Funciones de instalación de paquetes
#####################################################################

# Instalar las deependencias necesarias para el actualizador.
function installDependencies()
{
	if [ $# = 0 ]; then
		echoAndLog "${FUNCNAME}(): no deps needed."
	else
		while [ $# -gt 0 ]; do
			dpkg -s $1 2>/dev/null | grep -q "Status: install ok"
			if [ $? -ne 0 ]; then
				INSTALLDEPS="$INSTALLDEPS $1"
			fi
			shift
		done
		if [ -n "$INSTALLDEPS" ]; then
			apt-get update && apt-get -y install --force-yes $INSTALLDEPS
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

	local url="$1"

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

# Comprobar si existe conexión.
function checkNetworkConnection()
{
	OPENGNSYS_SERVER=${OPENGNSYS_SERVER:-"www.opengnsys.es"}
	wget --spider -q $OPENGNSYS_SERVER
}


#####################################################################
####### Funciones específicas de la instalación de Opengnsys
#####################################################################

# Copiar ficheros de arranque de los servicios del sistema de OpenGnSys
function updateServicesStart()
{
	echoAndLog "${FUNCNAME}(): Updating OpenGnSys init file ..."
	cp -p $WORKDIR/opengnsys/admin/Sources/Services/opengnsys.init /etc/init.d/opengnsys
	if [ $? != 0 ]; then
		errorAndLog "${FUNCNAME}(): Error updating /etc/init.d/opengnsys"
		exit 1
	fi
	echoAndLog "${FUNCNAME}(): init file updated successfully."
}

# Actualizar cliente OpenGnSys
function updateClientFiles()
{
	echoAndLog "${FUNCNAME}(): Updating OpenGnSys Client files."
	rsync --exclude .svn -irplt $WORKDIR/opengnsys/client/shared/* $INSTALL_TARGET/client
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while updating client structure"
		exit 1
	fi
	find $INSTALL_TARGET/client -name .svn -type d -exec rm -fr {} \; 2>/dev/null
	
	echoAndLog "${FUNCNAME}(): Updating OpenGnSys Cloning Engine files."
	rsync --exclude .svn -irplt $WORKDIR/opengnsys/client/engine/*.lib* $INSTALL_TARGET/client/lib/engine/bin
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while updating engine files"
		exit 1
	fi
	
	echoAndLog "${FUNCNAME}(): client files update success."
}

# Exportar nombre de usuario y grupo del servicio Apache.
function getApacheUser()
{
	# Variables de ejecución de Apache
	# - APACHE_RUN_USER
	# - APACHE_RUN_GROUP
	if [ -f /etc/apache2/envvars ]; then
		source /etc/apache2/envvars
	fi
	APACHE_RUN_USER=${APACHE_RUN_USER:-"www-data"}
	APACHE_RUN_GROUP=${APACHE_RUN_GROUP:-"www-data"}
}

# Copiar ficheros del OpenGnSys Web Console.
function updateWebFiles()
{
        local ERRCODE
	echoAndLog "${FUNCNAME}(): Updating web files..."
        backupFile $INSTALL_TARGET/www/controlacceso.php
        mv $INSTALL_TARGET/www $INSTALL_TARGET/WebConsole
	rsync --exclude .svn -irplt $WORKDIR/opengnsys/admin/WebConsole $INSTALL_TARGET
        ERRCODE=$?
        mv $INSTALL_TARGET/WebConsole $INSTALL_TARGET/www
	unzip -o $WORKDIR/opengnsys/admin/xajax_0.5_standard.zip -d $INSTALL_TARGET/www/xajax
	if [ $ERRCODE != 0 ]; then
		errorAndLog "${FUNCNAME}(): Error updating web files."
		exit 1
	fi
        restoreFile $INSTALL_TARGET/www/controlacceso.php
	# Cambiar permisos para ficheros especiales.
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/www/includes $INSTALL_TARGET/www/images/iconos
	echoAndLog "${FUNCNAME}(): Web files updated successfully."
}

# Copiar carpeta de Interface 
function updateInterfaceAdm()
{ 
	local errcode=0 
         
	# Crear carpeta y copiar Interface 
	echoAndLog "${FUNCNAME}(): Copying Administration Interface Folder" 
	mv $INSTALL_TARGET/client/interfaceAdm $INSTALL_TARGET/client/Interface
	rsync --exclude .svn -irplt $WORKDIR/opengnsys/admin/Interface $INSTALL_TARGET/client
	errcoce=$?
	mv $INSTALL_TARGET/client/Interface $INSTALL_TARGET/client/interfaceAdm
	if [ $errcode -ne 0 ]; then 
		echoAndLog "${FUNCNAME}(): error while updating admin interface" 
		exit 1
	fi 
	chmod -R +x $INSTALL_TARGET/client/interfaceAdm 
	chown $OPENGNSYS_CLIENTUSER:$OPENGNSYS_CLIENTUSER $INSTALL_TARGET/client/interfaceAdm/CambiarAcceso
	chmod 700 $INSTALL_TARGET/client/interfaceAdm/CambiarAcceso
	echoAndLog "${FUNCNAME}(): Admin interface updated successfully."
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
	rm -fr "$INSTALL_TARGET/www/api"
	mv "$INSTALL_TARGET/www/html" "$INSTALL_TARGET/www/api"
	rm -fr $INSTALL_TARGET/www/{man,perlmod,rtf}
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/www/api
	echoAndLog "${FUNCNAME}(): Doxygen web files created successfully."
}


# Crea la estructura base de la instalación de opengnsys
function createDirs()
{
	# Crear estructura de directorios.
	echoAndLog "${FUNCNAME}(): creating directory paths in ${INSTALL_TARGET}"
	mkdir -p ${INSTALL_TARGET}/{bin,doc,etc,lib,sbin,www}
	mkdir -p ${INSTALL_TARGET}/{client,images}
	mkdir -p ${INSTALL_TARGET}/log/clients
	ln -fs ${INSTALL_TARGET}/log /var/log/opengnsys
	ln -fs /var/lib/tftpboot ${INSTALL_TARGET}
	mkdir -p ${INSTALL_TARGET}/tftpboot/{pxelinux.cfg,menu.lst}
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while creating dirs. Do you have write permissions?"
		return 1
	fi

	# Crear usuario ficticio.
	if id -u $OPENGNSYS_CLIENTUSER &>/dev/null; then
		echoAndLog "${FUNCNAME}(): user \"$OPENGNSYS_CLIENTUSER\" is already created"
	else
		echoAndLog "${FUNCNAME}(): creating OpenGnSys user"
		useradd $OPENGNSYS_CLIENTUSER 2>/dev/null
		if [ $? -ne 0 ]; then
			errorAndLog "${FUNCNAME}(): error creating OpenGnSys user"
			return 1
		fi
	fi

	# Establecer los permisos básicos.
	echoAndLog "${FUNCNAME}(): setting directory permissions"
	chmod -R 775 $INSTALL_TARGET/{log/clients,images,tftpboot/pxelinux.cfg,tftpboot/menu.lst}
	chown -R :$OPENGNSYS_CLIENTUSER $INSTALL_TARGET/{log/clients,images,tftpboot/pxelinux.cfg,tftpboot/menu.lst}
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while setting permissions"
		return 1
	fi

	echoAndLog "${FUNCNAME}(): directory paths created"
	return 0
}

# Copia ficheros de configuración y ejecutables genéricos del servidor.
function updateServerFiles()
{
	# No copiar ficheros del antiguo cliente Initrd
	local SOURCES=(	repoman/bin \
			server/bin \
			server/tftpboot \
			installer/opengnsys_uninstall.sh \
			doc )
	local TARGETS=(	bin \
			bin \
			tftpboot \
			lib/opengnsys_uninstall.sh \
			doc )

	if [ ${#SOURCES[@]} != ${#TARGETS[@]} ]; then
		errorAndLog "${FUNCNAME}(): inconsistent number of array items"
		exit 1
	fi

	echoAndLog "${FUNCNAME}(): updating files in server directories"
	pushd $WORKDIR/opengnsys >/dev/null
	local i
	for (( i = 0; i < ${#SOURCES[@]}; i++ )); do
		if [ -d "$INSTALL_TARGET/${TARGETS[i]}" ]; then
			rsync --exclude .svn -irplt "${SOURCES[i]}" $(dirname $(readlink -e "$INSTALL_TARGET/${TARGETS[i]}"))
		else
			rsync --exclude .svn -irplt "${SOURCES[i]}" $(readlink -e "$INSTALL_TARGET/${TARGETS[i]}")
		fi
	done
	popd >/dev/null
	echoAndLog "${FUNCNAME}(): updating cron files"
	echo "* * * * *   root   [ -x $INSTALL_TARGET/bin/torrent-creator ] && $INSTALL_TARGET/bin/torrent-creator" > /etc/cron.d/torrentcreator
	echoAndLog "${FUNCNAME}(): server files updated successfully."
}

####################################################################
### Funciones de compilación de código fuente de servicios
####################################################################

# Recompilar y actualiza los serivicios y clientes.
function compileServices()
{
	local hayErrores=0

	# Compilar OpenGnSys Server
	echoAndLog "${FUNCNAME}(): Recompiling OpenGnSys Admin Server"
	pushd $WORKDIR/opengnsys/admin/Sources/Services/ogAdmServer
	make && mv ogAdmServer $INSTALL_TARGET/sbin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnSys Admin Server"
		hayErrores=1
	fi
	popd
	# Compilar OpenGnSys Repository Manager
	echoAndLog "${FUNCNAME}(): Recompiling OpenGnSys Repository Manager"
	pushd $WORKDIR/opengnsys/admin/Sources/Services/ogAdmRepo
	make && mv ogAdmRepo $INSTALL_TARGET/sbin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnSys Repository Manager"
		hayErrores=1
	fi
	popd
	# Compilar OpenGnSys Agent
	echoAndLog "${FUNCNAME}(): Recompiling OpenGnSys Agent"
	pushd $WORKDIR/opengnsys/admin/Sources/Services/ogAdmAgent
	make && mv ogAdmAgent $INSTALL_TARGET/sbin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnSys Agent"
		hayErrores=1
	fi
	popd

	# Compilar OpenGnSys Client
	echoAndLog "${FUNCNAME}(): Recompiling OpenGnSys Client"
	pushd $WORKDIR/opengnsys/admin/Sources/Clients/ogAdmClient
	make && mv ogAdmClient $INSTALL_TARGET/client/bin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnSys Client"
		hayErrores=1
	fi
	popd

	return $hayErrores
}


####################################################################
### Funciones instalacion cliente OpenGnSys
####################################################################

# Actualizar nuevo cliente para OpenGnSys 1.0
function updateClient()
{
	local DOWNLOADURL="http://www.opengnsys.es/downloads"
	local FILENAME=ogclient-1.0.2-natty-32bit-beta01-rev2111.iso
	local SOURCEFILE=$DOWNLOADURL/$FILENAME
	local TARGETFILE=$INSTALL_TARGET/lib/$FILENAME
	local SOURCELENGTH
	local TARGETLENGTH
	local TMPDIR=/tmp/${FILENAME%.iso}

	# Comprobar si debe actualizarse el cliente.
	SOURCELENGTH=$(LANG=C wget --spider $SOURCEFILE | awk '/Length:/ {print $2}')
	TARGETLENGTH=$(ls -l $TARGETFILE 2>/dev/null | awk '{print $5}')
	if [ "$SOURCELENGTH" != "$TARGETLENGTH" ]; then
		echoAndLog "${FUNCNAME}(): Loading Client"
		wget $DOWNLOADURL/$FILENAME -O $TARGETFILE
		if [ ! -s $TARGETFILE ]; then
			errorAndLog "${FUNCNAME}(): Error loading OpenGnSys Client"
			return 1
		fi
	else
		echoAndLog "${FUNCNAME}(): Client is already loaded"
	fi
	# Montar la imagen ISO del ogclient, actualizar ficheros y desmontar.
	echoAndLog "${FUNCNAME}(): Updatting ogclient files"
	mkdir -p $TMPDIR
	mount -o loop,ro $TARGETFILE $TMPDIR
	rsync -irlt $TMPDIR/ogclient $INSTALL_TARGET/tftpboot
	umount $TMPDIR
	rmdir $TMPDIR

	# Establecer los permisos.
	find -L $INSTALL_TARGET/tftpboot -type d -exec chmod 755 {} \;
	find -L $INSTALL_TARGET/tftpboot -type f -exec chmod 644 {} \;
	chown -R :$OPENGNSYS_CLIENTUSER $INSTALL_TARGET/tftpboot/ogclient
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/tftpboot/{menu.lst,pxelinux.cfg}
	echoAndLog "${FUNCNAME}(): Client update successfully"
}

# Resumen de actualización.
function updateSummary()
{
	# Actualizar fichero de versión y revisión.
	local VERSIONFILE="$INSTALL_TARGET/doc/VERSION.txt"
	local REVISION=$(LANG=C svn info $SVN_URL|awk '/Revision:/ {print "r"$2}')

	[ -f $VERSIONFILE ] || echo "OpenGnSys" >$VERSIONFILE
	perl -pi -e "s/($| r[0-9]*)/ $REVISION/" $VERSIONFILE

	echo
	echoAndLog "OpenGnSys Update Summary"
        echo       "========================"
        echoAndLog "Project version:                  $(cat $VERSIONFILE)"
	echo
}



#####################################################################
####### Proceso de actualización de OpenGnSys
#####################################################################


echoAndLog "OpenGnSys update begins at $(date)"

pushd $WORKDIR

# Comprobar si hay conexión y detectar parámetros de red por defecto.
checkNetworkConnection
if [ $? -ne 0 ]; then
	errorAndLog "Error connecting to server. Causes:"
	errorAndLog " - Network is unreachable, review devices parameters."
	errorAndLog " - You are inside a private network, configure the proxy service."
	errorAndLog " - Server is temporally down, try agian later."
	exit 1
fi

# Comprobar auto-actualización del programa.
if [ "$PROGRAMDIR" != "$INSTALL_TARGET/bin" ]; then
	checkAutoUpdate
	if [ $? -ne 0 ]; then
		echoAndLog "OpenGnSys updater has been overwritten."
		echoAndLog "Please, re-execute this script."
		exit
	fi
fi

# Instalar dependencias.
installDependencies $DEPS
if [ $? -ne 0 ]; then
	errorAndLog "Error: you may install all needed dependencies."
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

# Si existe fichero de actualización de la base de datos; aplicar cambios.
INSTVERSION=$(awk '{print $2}' $INSTALL_TARGET/doc/VERSION.txt)
REPOVERSION=$(awk '{print $2}' $WORKDIR/opengnsys/doc/VERSION.txt)
OPENGNSYS_DBUPDATEFILE="$WORKDIR/opengnsys/admin/Database/$OPENGNSYS_DATABASE-$INSTVERSION-$REPOVERSION.sql"
if [ -f $OPENGNSYS_DBUPDATEFILE ]; then
	echoAndLog "Updating tables from version $INSTVERSION to $REPOVERSION"
	importSqlFile $OPENGNSYS_DBUSER $OPENGNSYS_DBPASSWORD $OPENGNSYS_DATABASE $OPENGNSYS_DBUPDATEFILE
else
	echoAndLog "Database unchanged."
fi

# Actualizar ficheros complementarios del servidor
updateServerFiles
if [ $? -ne 0 ]; then
	errorAndLog "Error updating OpenGnSys Server files"
	exit 1
fi

# Actualizar ficheros del cliente
updateClientFiles
updateInterfaceAdm

# Actualizar páqinas web
getApacheUser
updateWebFiles
if [ $? -ne 0 ]; then
	errorAndLog "Error updating OpenGnSys Web Admin files"
	exit 1
fi
# Generar páginas Doxygen para instalar en el web
makeDoxygenFiles

# Recompilar y actualizar los servicios del sistema
compileServices

# Actaulizar ficheros auxiliares del cliente
updateClient
if [ $? -ne 0 ]; then
	errorAndLog "Error updating clients"
	exit 1
fi

# Actualizamos el fichero que arranca los servicios de OpenGnSys
updateServicesStart

# Eliminamos el fichero de estado del tracker porque es incompatible entre los distintos paquetes
if [ -f /tmp/dstate ]; then
	rm -f /tmp/dstate
fi

# Mostrar resumen de actualización.
updateSummary

#rm -rf $WORKDIR
echoAndLog "OpenGnSys update finished at $(date)"

popd

