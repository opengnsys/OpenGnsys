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

# Comprobar si se ha descargado el paquete comprimido (USESVN=0) o sólo el instalador (USESVN=1).
PROGRAMDIR=$(readlink -e $(dirname "$0"))
DEPS="build-essential g++-multilib rsync ctorrent samba unzip netpipes debootstrap schroot squashfs-tools"
OPENGNSYS_SERVER="www.opengnsys.es"
if [ -d "$PROGRAMDIR/../installer" ]; then
    USESVN=0
else
    USESVN=1
    SVN_URL="http://$OPENGNSYS_SERVER/svn/trunk/"
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
        date "+%Y%m%d-%H%M%S"
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
function installDependencies ()
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

# Comprobar si existe conexión.
function checkNetworkConnection()
{
	OPENGNSYS_SERVER=${OPENGNSYS_SERVER:-"www.opengnsys.es"}
	wget --spider -q $OPENGNSYS_SERVER
}

# Obtener los parámetros de red de la interfaz por defecto.
function getNetworkSettings()
{
        local MAINDEV

 	echoAndLog "$FUNCNAME(): Detecting default network parameters."
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

# Copiar ficheros de arranque de los servicios del sistema de OpenGnSys

function updateServicesStart(){
	echoAndLog "${FUNCNAME}(): Updating /etc/init.d/opengnsys ..."
	cp -p $WORKDIR/opengnsys/admin/Sources/Services/opengnsys.init /etc/init.d/opengnsys
	if [ $? != 0 ]; then
		errorAndLog "${FUNCNAME}(): Error updating /etc/init.d/opengnsys"
		exit 1
	fi
	echoAndLog "${FUNCNAME}(): /etc/init.d/opengnsys updated successfully."
}

# Actualizar cliente OpenGnSys
function updateClientFiles()
{
	local hayErrores=0

	echoAndLog "${FUNCNAME}(): Updating OpenGnSys Client files."
	rsync --exclude .svn -irplt $WORKDIR/opengnsys/client/shared/* $INSTALL_TARGET/client
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while updating client structure"
		hayErrores=1
	fi
	find $INSTALL_TARGET/client -name .svn -type d -exec rm -fr {} \; 2>/dev/null
	
	echoAndLog "${FUNCNAME}(): Updating OpenGnSys Cloning Engine files."
	rsync --exclude .svn -irplt $WORKDIR/opengnsys/client/engine/*.lib $INSTALL_TARGET/client/lib/engine/bin
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while updating engine files"
		hayErrores=1
	fi
	
	if [ $hayErrores -eq 0 ]; then
		echoAndLog "${FUNCNAME}(): client  files update success."
	else
		errorAndLog "${FUNCNAME}(): client files update with errors"
	fi

	return $hayErrores
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
function updateInterfaceAdm () 
{ 
	local hayErrores=0 
         
	# Crear carpeta y copiar Interface 
	echoAndLog "${FUNCNAME}(): Copying Administration Interface Folder" 
	mv $INSTALL_TARGET/client/interfaceAdm $INSTALL_TARGET/client/Interface
	rsync --exclude .svn -irplt $WORKDIR/opengnsys/admin/Interface $INSTALL_TARGET/client
	ERRCODE=$?
	mv $INSTALL_TARGET/client/Interface $INSTALL_TARGET/client/interfaceAdm
	if [ $? -ne 0 ]; then 
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
	mkdir -p ${INSTALL_TARGET}
	mkdir -p ${INSTALL_TARGET}/bin
	mkdir -p ${INSTALL_TARGET}/client
	mkdir -p ${INSTALL_TARGET}/doc
	mkdir -p ${INSTALL_TARGET}/etc
	mkdir -p ${INSTALL_TARGET}/lib
	mkdir -p ${INSTALL_TARGET}/log/clients
	ln -fs ${INSTALL_TARGET}/log /var/log/opengnsys
	mkdir -p ${INSTALL_TARGET}/sbin
	mkdir -p ${INSTALL_TARGET}/www
	mkdir -p ${INSTALL_TARGET}/images
	ln -fs /var/lib/tftpboot ${INSTALL_TARGET}
	mkdir -p ${INSTALL_TARGET}/tftpboot/pxelinux.cfg
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while creating dirs. Do you have write permissions?"
		return 1
	fi

	# Crear usuario ficticio.
	if id -u $OPENGNSYS_CLIENTUSER &>/dev/null; then
		echoAndLog "${FUNCNAME}(): user \"$OPENGNSYS_CLIENTUSER\"is already created"
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
	chmod -R 775 $INSTALL_TARGET/{log/clients,images,tftpboot/pxelinux.cfg}
	chown -R :$OPENGNSYS_CLIENTUSER $INSTALL_TARGET/{log/clients,images,tftpboot/pxelinux.cfg}
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while setting permissions"
		return 1
	fi

	echoAndLog "${FUNCNAME}(): directory paths created"
	return 0
}

# Copia ficheros de configuración y ejecutables genéricos del servidor.
function updateServerFiles () {

	# No copiar ficheros del antiguo cliente Initrd
	local SOURCES=( repoman/bin \
			server/bin \
                        doc )
	local TARGETS=( bin \
                        bin \
                        doc )

	if [ ${#SOURCES[@]} != ${#TARGETS[@]} ]; then
		errorAndLog "${FUNCNAME}(): inconsistent number of array items"
		exit 1
	fi

	echoAndLog "${FUNCNAME}(): updating files in server directories"
	pushd $WORKDIR/opengnsys >/dev/null
	local i
	for (( i = 0; i < ${#SOURCES[@]}; i++ )); do
		rsync --exclude .svn -irplt "${SOURCES[$i]}" $(dirname "${INSTALL_TARGET}/${TARGETS[$i]}")
	done
	popd >/dev/null
	echoAndLog "${FUNCNAME}(): updating cron files"
	echo "* * * * *   root   [ -x $INSTALL_TARGET/bin/torrent-creator ] && $INSTALL_TARGET/bin/torrent-creator" > /etc/cron.d/torrentcreator
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
### Funciones instalacion cliente opengnsys
####################################################################

# Actualizar antiguo cliente Initrd.
function updateOldClient()
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
	OSDISTRIB=$(lsb_release -is) 2>/dev/null
	OSCODENAME=$(lsb_release -cs) 2>/dev/null
	if [ "$OSDISTRIB" = "Ubuntu" -a -n "$OSCODENAME" ]; then
		echoAndLog "${FUNCNAME}(): Loading Kernel and Initrd files for $OSDISTRIB $OSCODENAME."
        	$INSTALL_TARGET/bin/initrd-generator -t $INSTALL_TARGET/tftpboot -v $OSCODENAME 2>&1 | tee -a $LOG_FILE
		if [ $? -ne 0 ]; then
			errorAndLog "${FUNCNAME}(): error while generating initrd OpenGnSys Admin Client"
			hayErrores=1
		fi
		echoAndLog "${FUNCNAME}(): Loading udeb files for $OSDISTRIB $OSCODENAME."
        	$INSTALL_TARGET/bin/upgrade-clients-udeb.sh $OSCODENAME 2>&1 | tee -a $LOG_FILE
		if [ $? -ne 0 ]; then
			errorAndLog "${FUNCNAME}(): error while upgrading udeb files OpenGnSys Admin Client"
			hayErrores=1
		fi
	else
		echoAndLog "${FUNCNAME}(): Loading default Kernel and Initrd files."
        	$INSTALL_TARGET/bin/initrd-generator -t $INSTALL_TARGET/tftpboot 2>&1 | tee -a $LOG_FILE
		if [ $? -ne 0 ]; then
			errorAndLog "${FUNCNAME}(): error while generating initrd OpenGnSys Admin Client"
			hayErrores=1
		fi
		echoAndLog "${FUNCNAME}(): Loading default udeb files."
        	$INSTALL_TARGET/bin/upgrade-clients-udeb.sh 2>&1 | tee -a $LOG_FILE
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

# Actualizar nuevo cliente para OpenGnSys 1.0
function updateClient()
{
	local DOWNLOADURL=http://www.opengnsys.es/downloads
	local FILENAME=ogclient-1.0.1-lucid-32bit.tar.gz
	local TMPFILE=/tmp/$FILENAME

	echoAndLog "${FUNCNAME}(): Loading Client"
	# Descargar y descomprimir cliente ogclient
	wget $DOWNLOADURL/$FILENAME -O $TMPFILE
	if [ ! -s $TMPFILE ]; then
		errorAndLog "${FUNCNAME}(): Error loading OpenGnSys Client"
		return 1
	fi
	echoAndLog "${FUNCNAME}(): Extracting Client files"
	tar xzvf $TMPFILE -C $INSTALL_TARGET/tftpboot
	rm -f $TMPFILE
	# Usar la versión más reciente del Kernel y del Initrd para el cliente.
	ln -f $(ls $INSTALL_TARGET/tftpboot/ogclient/vmlinuz-*|tail -1) $INSTALL_TARGET/tftpboot/ogclient/ogvmlinuz
	ln -f $(ls $INSTALL_TARGET/tftpboot/ogclient/initrd.img-*|tail -1) $INSTALL_TARGET/tftpboot/ogclient/oginitrd.img
	# Establecer los permisos.
	chmod -R 755 $INSTALL_TARGET/tftpboot/ogclient
	chown -R :$OPENGNSYS_CLIENTUSER $INSTALL_TARGET/tftpboot/ogclient
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

# Instalar dependencia.
installDependencies $DEPS
if [ $? -ne 0 ]; then
	errorAndLog "Error: you may install all needed dependencies."
	exit 1
fi

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
updateWebFiles
if [ $? -ne 0 ]; then
	errorAndLog "Error updating OpenGnSys Web Admin files"
	exit 1
fi
# Generar páginas Doxygen para instalar en el web
makeDoxygenFiles

# Creando la estructura del cliente
recompileClient
# NO se actualiza el antiguo cliente Initrd
#updateOldClient
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

