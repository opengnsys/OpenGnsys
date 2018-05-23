#!/bin/bash
########################################################################
####### This script downloads svn repo and generates a debian package
####### Autor: Fredy <aluque@soleta.eu>      2018 Q1
####### 
########################################################################

# Needs root priviledges
if [ "$(whoami)" != 'root' ]; then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi



########################################################################
#### Variables
########################################################################
VERSION="1.1"   # dinamically updated later
SVNURL=https://opengnsys.es/svn/branches/version$VERSION
SVNURL_TICKET=https://opengnsys.es/svn/branches/version1.1-tickets/DebianPackageGenerator-ticket837
PKG_GEN_PATH=/tmp/debian-pkg		# general working dir
ROOTDIR=$PKG_GEN_PATH/$PKG_NAME		# specific working dir for each package type	
INSTALL_TARGET=$ROOTDIR/opt/opengnsys		# 
WORKDIR=/tmp/opengnsys_installer
# Registro de incidencias.
OGLOGFILE=$TMPDIR/log/${PROGRAMNAME%.sh}.log 
LOG_FILE=/tmp/$(basename $OGLOGFILE) 

#### Git
BRANCH="master"
CODE_URL="https://codeload.github.com/opengnsys/OpenGnsys/zip/$BRANCH"
API_URL="https://api.github.com/repos/opengnsys/OpenGnsys/branches/$BRANCH"
RAW_URL="https://raw.githubusercontent.com/opengnsys/OpenGnsys/$BRANCH"

DEV_BRANCH="debian-pkg"


# Usuario del cliente para acceso remoto.
OPENGNSYS_CLIENT_USER="opengnsys"
APACHE_RUN_USER=www-data
APACHE_RUN_GROUP=www-data

########################################################################
#### Functions
########################################################################

function help()
{
read -r -d '' HELP <<- EOM
########################################################################
#  This script creates debian ".deb" packages for the great            #
#           Opengnsys Deployment Software                              #
#  - Select which type of package you would like to generate           #
#  - You will find your ".deb" file inside /tmp/debian-pkg/ folder     #
#  - Send the ".deb" file to your destination machine and install it:  #
#  - apt install ./opengnsys-*.deb   (use apt instead apt-get or dpkg) #
#  The script has been tested on Ubuntu Xenial 16.04 LTS               #
########################################################################
EOM
echo "$HELP"
}

function getDateTime()
{
	date "+%Y%m%d-%H%M%S"
}

# Escribe a fichero y muestra por pantalla
function echoAndLog()
{
	echo $1
	DATETIME=`getDateTime`
	echo "$DATETIME;$SSH_CLIENT;$1" >> $LOG_FILE
}

function errorAndLog()
{
	echo "ERROR: $1"
	DATETIME=`getDateTime`
	echo "$DATETIME;$SSH_CLIENT;ERROR: $1" >> $LOG_FILE
}

# This function test if a file exist
function fileExist()
{
local FILE="$1"
if [ ! -e $FILE ]; then
   echo "$FILE is needed but does not exist." >&2
   exit 1
fi
}

# This function test if a file exist
function dependExist()
{
dpkg -l $1 | grep ii > /dev/null
if [ $? -ne 0 ]; then
   echo "Package $1 is needed but is not installed." >&2
   exit 1
fi
}

function createUser()
{
		# Crear usuario ficticio.
	if id -u $OPENGNSYS_CLIENT_USER &>/dev/null; then 
		echo "${FUNCNAME}(): user \"$OPENGNSYS_CLIENT_USER\" is already created"
	else
		echo "${FUNCNAME}(): creating OpenGnsys user"
		useradd $OPENGNSYS_CLIENT_USER 2>/dev/null
		if [ $? -ne 0 ]; then
			echo "${FUNCNAME}(): error creating OpenGnsys user" >&2
			return 1
		fi
	fi
}

# Crea la estructura base de la instalación de opengnsys
function createDirs()
{
	if [ $# -ne 1 ]; then
		echo "${FUNCNAME}(): invalid number of parameters" >&2
		exit 1
	fi

	local path_opengnsys_base="$1"

	# Crear estructura de directorios.
	echo "${FUNCNAME}(): creating directory paths in $path_opengnsys_base"
	mkdir -p $path_opengnsys_base
	mkdir -p $path_opengnsys_base/bin
	mkdir -p $path_opengnsys_base/client/shared/bin
	mkdir -p $path_opengnsys_base/doc
	mkdir -p $path_opengnsys_base/etc
	mkdir -p $path_opengnsys_base/images/groups
	mkdir -p $path_opengnsys_base/lib
	mkdir -p $path_opengnsys_base/log/clients
	mkdir -p $path_opengnsys_base/sbin
#	mkdir -p $path_opengnsys_base/tftpboot/{menu.lst,grub}
	mkdir -p $path_opengnsys_base/www
	if [ $? -ne 0 ]; then
		echo "${FUNCNAME}(): error while creating dirs. Do you have write permissions?" >&2
		return 1
	fi

	# Crear usuario ficticio.
	createUser

	# Establecer los permisos básicos.
	echo "${FUNCNAME}(): setting directory permissions"
	chmod -R 775 $path_opengnsys_base/{log/clients,images}
	chown -R :$OPENGNSYS_CLIENT_USER $path_opengnsys_base/{log/clients,images}
	if [ $? -ne 0 ]; then
		echo "${FUNCNAME}(): error while setting permissions" >&2
		return 1
	fi

	# Mover el fichero de registro de instalación al directorio de logs.
#	echoAndLog "${FUNCNAME}(): moving installation log file"
#	mv $LOG_FILE $OGLOGFILE && LOG_FILE=$OGLOGFILE
#	chmod 600 $LOG_FILE

	echo "${FUNCNAME}(): directory paths created"
	return 0
}

# Copia ficheros de configuración y ejecutables genéricos del servidor.
function copyServerFiles ()
{
	if [ $# -ne 1 ]; then
		echo "${FUNCNAME}(): invalid number of parameters" >&2
		exit 1
	fi

	local path_opengnsys_base="$1"

	# Lista de ficheros y directorios origen y de directorios destino.
	local SOURCES=( server/tftpboot \
			server/bin \
			repoman/bin \
			server/lib \
			admin/Sources/Services/ogAdmServerAux
			admin/Sources/Services/ogAdmRepoAux
			installer/opengnsys_uninstall.sh \
			installer/opengnsys_update.sh \
			installer/opengnsys_export.sh \
			installer/opengnsys_import.sh \
			doc )
	local TARGETS=( tftpboot \
			bin \
			bin \
			lib \
			sbin \
			sbin \
			lib \
			lib \
			lib \
			lib \
			doc )

	if [ ${#SOURCES[@]} != ${#TARGETS[@]} ]; then
		echo "${FUNCNAME}(): inconsistent number of array items" >&2
		exit 1
	fi

	# Copiar ficheros.
	echo "${FUNCNAME}(): copying files to server directories"

	pushd $WORKDIR/opengnsys
	local i
	for (( i = 0; i < ${#SOURCES[@]}; i++ )); do
		if [ -f "${SOURCES[$i]}" ]; then
			echo "Copying ${SOURCES[$i]} to $path_opengnsys_base/${TARGETS[$i]}"
			cp -a "${SOURCES[$i]}" "${path_opengnsys_base}/${TARGETS[$i]}"
		elif [ -d "${SOURCES[$i]}" ]; then
			echo "Copying content of ${SOURCES[$i]} to $path_opengnsys_base/${TARGETS[$i]}"
			cp -a "${SOURCES[$i]}"/* "${path_opengnsys_base}/${TARGETS[$i]}"
        else
			echo "Unable to copy ${SOURCES[$i]} to $path_opengnsys_base/${TARGETS[$i]}"
		fi
	done

	popd
}

# Copiar carpeta de Interface
function copyInterfaceAdm ()
{
	local hayErrores=0
	
	# Crear carpeta y copiar Interface
	echo "${FUNCNAME}(): Copying Administration Interface Folder"
	cp -ar $WORKDIR/opengnsys/admin/Interface $INSTALL_TARGET/client/interfaceAdm
	if [ $? -ne 0 ]; then
		echo "${FUNCNAME}(): error while copying Administration Interface Folder" >&2
		hayErrores=1
	fi
	chown $OPENGNSYS_CLIENT_USER:$OPENGNSYS_CLIENT_USER $INSTALL_TARGET/client/interfaceAdm/CambiarAcceso
	chmod 700 $INSTALL_TARGET/client/interfaceAdm/CambiarAcceso

	return $hayErrores
}

function copyClientFiles()
{
	local errstatus=0

	echo "${FUNCNAME}(): Copying OpenGnsys Client files."
	cp -a $WORKDIR/opengnsys/client/shared/* $INSTALL_TARGET/client
	if [ $? -ne 0 ]; then
		echo "${FUNCNAME}(): error while copying client estructure" >&2
		errstatus=1
	fi
	find $INSTALL_TARGET/client -name .svn -type d -exec rm -fr {} \; 2>/dev/null
	
	echo "${FUNCNAME}(): Copying OpenGnsys Cloning Engine files."
	mkdir -p $INSTALL_TARGET/client/lib/engine/bin
	cp -a $WORKDIR/opengnsys/client/engine/*.lib* $INSTALL_TARGET/client/lib/engine/bin
	if [ $? -ne 0 ]; then
		echo "${FUNCNAME}(): error while copying engine files" >&2
		errstatus=1
	fi
	
	if [ $errstatus -eq 0 ]; then
		echo "${FUNCNAME}(): client copy files success."
	else
		echo "${FUNCNAME}(): client copy files with errors" >&2
	fi

	return $errstatus
}

# Copiar ficheros del OpenGnsys Web Console.
function installWebFiles()
{
	local COMPATDIR f
	local SLIMFILE="slim-2.6.1.zip"
	local SWAGGERFILE="swagger-ui-2.2.5.zip"

	echo "${FUNCNAME}(): Installing web files..."
	# Copiar ficheros.
	cp -a $WORKDIR/opengnsys/admin/WebConsole/* $INSTALL_TARGET/www   #*/ comentario para Doxygen.
	if [ $? != 0 ]; then
		echo "${FUNCNAME}(): Error copying web files." >&2
		exit 1
	fi
        find $INSTALL_TARGET/www -name .svn -type d -exec rm -fr {} \; 2>/dev/null

	# Descomprimir librerías: Slim y Swagger-UI.
	unzip -o $WORKDIR/opengnsys/admin/$SLIMFILE -d $INSTALL_TARGET/www/rest
	unzip -o $WORKDIR/opengnsys/admin/$SWAGGERFILE -d $INSTALL_TARGET/www/rest

	# Compatibilidad con dispositivos móviles.
	COMPATDIR="$INSTALL_TARGET/www/principal"
	for f in acciones administracion aula aulas hardwares imagenes menus repositorios softwares; do
		sed 's/clickcontextualnodo/clicksupnodo/g' $COMPATDIR/$f.php > $COMPATDIR/$f.device.php
	done
	cp -a $COMPATDIR/imagenes.device.php $COMPATDIR/imagenes.device4.php
	# Cambiar permisos para ficheros especiales.
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/www/images/{fotos,iconos}
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/www/tmp/
	# Ficheros de log de la API REST.
	touch $INSTALL_TARGET/log/{ogagent,remotepc,rest}.log
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/log/{ogagent,remotepc,rest}.log

	echo "${FUNCNAME}(): Web files installed successfully."
}

function installDownloadableFiles()
{
	INSTVERSION=1.1.0	###  Temporal.
	local FILENAME=ogagentpkgs-$INSTVERSION.tar.gz
	local TARGETFILE=$WORKDIR/$FILENAME
	local DOWNLOADURL="https://opengnsys.es/trac/downloads"
 
	# Descargar archivo comprimido, si es necesario.
	if [ -s $PROGRAMDIR/$FILENAME ]; then
		echo "${FUNCNAME}(): Moving $PROGRAMDIR/$FILENAME file to $(dirname $TARGETFILE)"
		mv $PROGRAMDIR/$FILENAME $TARGETFILE
	else
		echo "${FUNCNAME}(): Downloading $FILENAME"
		curl $DOWNLOADURL/$FILENAME -o $TARGETFILE
	fi
	if [ ! -s $TARGETFILE ]; then
		echo "${FUNCNAME}(): Cannot download $FILENAME" >&2
		return 1
	fi
	
	# Descomprimir fichero en zona de descargas.
	tar xvzf $TARGETFILE -C $INSTALL_TARGET/www/descargas
	if [ $? != 0 ]; then
		echo "${FUNCNAME}(): Error uncompressing archive." >&2
		exit 1
	fi
}

# Crear documentación Doxygen para la consola web.
function makeDoxygenFiles()
{
	echo "${FUNCNAME}(): Making Doxygen web files..."
	$WORKDIR/opengnsys/installer/ogGenerateDoc.sh \
			$WORKDIR/opengnsys/client/engine $INSTALL_TARGET/www
	if [ ! -d "$INSTALL_TARGET/www/html" ]; then
		echo "${FUNCNAME}(): unable to create Doxygen web files." >&2
		return 1
	fi
	mv "$INSTALL_TARGET/www/html" "$INSTALL_TARGET/www/api"
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/www/api
	echo "${FUNCNAME}(): Doxygen web files created successfully."
}

# Deletes non usefull files form .deb
function cleanFiles()
{
	local TO_CLEAN=( server/bin \
			repoman/bin \
			server/lib \
			admin/Sources/Services/ogAdmServerAux
			admin/Sources/Services/ogAdmRepoAux
			installer \
			doc \
			ogagentpkgs-1.1.0.tar.gz \
			admin/WebConsole \
			client/shared
			client/engine
			pkg-generator
			.git )
			
	pushd $ROOTDIR/tmp/opengnsys_installer
	local i
	for (( i = 0; i < ${#TO_CLEAN[@]}; i++ )); do
		echo "Deleting ${TO_CLEAN[$i]}"
		rm -Rf "${TO_CLEAN[$i]}"
	done
	popd
}

function downloadCode()
{
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local url="$1"

	echoAndLog "${FUNCNAME}(): downloading code..."

	curl "${url}" -o opengnsys.zip && unzip opengnsys.zip && mv "OpenGnsys-$BRANCH" opengnsys
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error getting code from ${url}, verify your user and password"
		return 1
	fi
	rm -f opengnsys.zip
	echoAndLog "${FUNCNAME}(): code was downloaded"
	return 0
}

function createFullPackage()
{
PKG_NAME="opengnsys-full"
ROOTDIR=$PKG_GEN_PATH/$PKG_NAME
# Delete previously package structure
if [ -d $ROOTDIR ]; then rm -Rf $ROOTDIR; fi
INSTALL_TARGET=$ROOTDIR/opt/opengnsys
mkdir -p $WORKDIR $ROOTDIR $INSTALL_TARGET
#svn export --force $SVNURL $WORKDIR
#svn export --force $SVNURL_TICKET $WORKDIR
#downloadCode $CODE_URL
# for testing and development:
git clone gituser@opengnsys.es:/git/opengnsys -b $DEV_BRANCH $WORKDIR
mkdir -p $ROOTDIR/DEBIAN $ROOTDIR/tmp
echoAndLog "Copying $WORKDIR/pkg-generator/* to $ROOTDIR"
cp -a $WORKDIR/installer/pkg-generator/* $ROOTDIR
ln -s -f $WORKDIR/ $WORKDIR/opengnsys

# Create needed files inside $ROOTDIR/DEBIAN
createDirs $INSTALL_TARGET
copyServerFiles $INSTALL_TARGET
copyInterfaceAdm
copyClientFiles
installWebFiles
installDownloadableFiles
makeDoxygenFiles

# Ejemplo de modificacion del postinst al vuelo
# sed -i 's/wget --spider -q/wget --spider -q --no-check-certificate/g' $ROOTDIR/DEBIAN/postinst
# deactivate svn function
#sed -i '/function svnExportCode/{N;s/$/\nreturn 0/}' $ROOTDIR/DEBIAN/postinst

# copy svn repo structure inside .deb package
echo "Copying from $WORKDIR to $ROOTDIR/tmp"
cp -a $WORKDIR $ROOTDIR/tmp

# Clean temp files
cleanFiles
echo "Finished cleaning duplicated files"

#
local VERSIONFILE="$INSTALL_TARGET/doc/VERSION.txt"
[ -f $VERSIONFILE ] || echo "OpenGnsys Server" >$VERSIONFILE
local VERSION=$(cat $VERSIONFILE | awk '{print $2}')
local REVISION=$(cat $VERSIONFILE | awk '{print $3}')
local TIMESTAMP=`getDateTime`
local ARCH=$(grep Architecture $ROOTDIR/DEBIAN/control | awk '{print $2}')
echo -e "\n"
echo "Version is: $VERSION    Revision: $REVISION"   Timestamp: $TIMESTAMP
echo -e "\n"
sed -ri "s/($| r[0-9]*)/ $REVISION/" $VERSIONFILE
sed -ri "s/^Version\: VERSION/Version\: $VERSION-$TIMESTAMP/" $ROOTDIR/DEBIAN/control

# Finally Generate package
cd $PKG_GEN_PATH
dpkg --build $PKG_NAME .
if [ $? = 0 ]; then
	echo -e "\n"
	echo -e " Package Generated: ${PKG_GEN_PATH}/${PKG_NAME}_${VERSION}-${TIMESTAMP}_${ARCH}.deb \n"
	echo -e "\n"
fi
}

########################################################################
# Start the Menu
########################################################################
function mainMenu()
{
echo "Main Menu"

# Define the choices to present to the user.
choices=( 'help' "Create full package" 'exit')

while [ "$menu" != 1 ]; do
# Present the choices.
# The user chooses by entering the *number* before the desired choice.
	select choice in "${choices[@]}"; do

		# Examine the choice.
		case $choice in
		help)
		  echo "Generate Package Help"
		  help

		  ;;
		"Create full package")
			echo "Creating new full package..."
			createFullPackage
			exit 0
		  ;;
		#"Client package (testing)")
			#echo "Creating Client package..."
			#createClientPackage
			#exit 0
		  #;;		  
		exit)
		  echo "Exiting. "
		  exit 0
		  ;;
		*)
		  echo "Wrong choice!" >&2
		  exit 1
		esac
		break

	done
done

echo "End of the script"
exit
}

# From here the script starts working

# Needed dependencies and files for this script:
# curl doxygen subversion graphviz unzip
for d in curl doxygen subversion graphviz unzip; do
	dependExist $d
done

for f in curl doxygen svn gvgen unzip; do
    fileExist /usr/bin/$f
done
mainMenu
