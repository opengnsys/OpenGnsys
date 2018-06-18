#!/bin/bash
####  AVISO: NO EDITAR variables de configuracion.
####  WARNING: DO NOT EDIT configuration variables.
INSTALL_TARGET=/opt/opengnsys           # Directorio de instalacion
PATH=$PATH:$INSTALL_TARGET/bin
OPENGNSYS_CLIENTUSER="opengnsys"        # Usuario Samba


# Solo ejecutable por usuario root
if [ "$(whoami)" != 'root' ]; then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi
# Error si OpenGnsys no esta instalado (no existe el directorio del proyecto)
if [ ! -d $INSTALL_TARGET ]; then
        echo "ERROR: OpenGnsys is not installed, cannot update!!"
        exit 1
fi
# Cargar configuración de acceso a la base de datos.
if [ -r $INSTALL_TARGET/etc/ogAdmServer.cfg ]; then
        source $INSTALL_TARGET/etc/ogAdmServer.cfg
elif [ -r $INSTALL_TARGET/etc/ogAdmAgent.cfg ]; then
        source $INSTALL_TARGET/etc/ogAdmAgent.cfg
fi
OPENGNSYS_DATABASE=${OPENGNSYS_DATABASE:-"$CATALOG"}            # Base de datos
OPENGNSYS_DBUSER=${OPENGNSYS_DBUSER:-"$USUARIO"}                # Usuario de acceso
OPENGNSYS_DBPASSWORD=${OPENGNSYS_DBPASSWORD:-"$PASSWORD"}       # Clave del usuario
if [ -z "$OPENGNSYS_DATABASE" -o -z "$OPENGNSYS_DBUSER" -o -z "$OPENGNSYS_DBPASSWORD" ]; then
        echo "ERROR: set OPENGNSYS_DATABASE, OPENGNSYS_DBUSER and OPENGNSYS_DBPASSWORD"
        echo "       variables, and run this script again."
        exit 1
fi

# Comprobar si se ha descargado el paquete comprimido (USESVN=0) o sólo el instalador (USESVN=1).
PROGRAMDIR=$(readlink -e $(dirname "$0"))
PROGRAMNAME=$(basename "$0")
OPENGNSYS_SERVER="opengnsys.es"
if [ -d "$PROGRAMDIR/../installer" ]; then
        USESVN=0
else
        USESVN=1
fi
SVN_URL="https://$OPENGNSYS_SERVER/svn/branches/version1.1/"

# El directorio de trabajo es directamente donde se descargue el tar.gz con la instalació
WORKDIR=..
mkdir -p $WORKDIR

# Registro de incidencias.
OGLOGFILE=$INSTALL_TARGET/log/${PROGRAMNAME%.sh}.log
LOG_FILE=/tmp/$(basename $OGLOGFILE)


############################################################
###  Detectar red
############################################################

# Comprobar si existe conexión.
function checkNetworkConnection()
{
        OPENGNSYS_SERVER=${OPENGNSYS_SERVER:-"opengnsys.es"}
        wget --spider -q $OPENGNSYS_SERVER
}


# Obtener los parametros de red del servidor.
function getNetworkSettings()
{
        # Variables globales definidas:
        # - SERVERIP:   IP local de la interfaz por defecto.

        local DEVICES
        local dev

        echoAndLog "${FUNCNAME}(): Detecting network parameters"
        SERVERIP="$ServidorAdm"
        DEVICES=$(ip -o link show up | awk '!/loopback/ {sub(/:.*/,"",$2); print $2}')
        for dev in $DEVICES; do
                [ -z "$SERVERIP" ] && SERVERIP=$(ip -o addr show dev $dev | awk '$3~/inet$/ {sub (/\/.*/, ""); print ($4)}')
        done
}



#function downloadFiles() {
	#git clone gituser@opengnsys.es:/git/opengnsys -b webconsole3 $WORKDIR
#}


function installDependencies() {
	apt-add-repository ppa:ondrej/php
	apt-get update
	apt-get upgrade -y
	apt-get install php7.0 php7.0-mysql php7.0-xml libcurl3 php7.0-curl
	apt-get install git
	apt-get install nodejs
	apt-get install npm
	npm config set ca=""
	npm install -g bower
	ln -s /usr/bin/nodejs /usr/bin/node
}

function configureApache() {
	# Usar el fichero de configuracióe apache y moverlo al directorio sites-available de apache
	configFile="opengnsys3.conf"
	template=$WORKDIR/server/etc/apache-console3.conf.tmpl
	sed -e "s,CONSOLEDIR,$INSTALL_TARGET/www3,g" $template > /etc/apache2/sites-available/$configFile

	a2ensite $configFile
	a2dismod php5
	a2enmod php7.0
	service apache2 reload
}

function configureClient() {
	local clientFilesDir ogClientFilesDir
	clientFilesDir=$PWD/client/shared
	ogClientFilesDir=$INSTALL_TARGET/client/
	# asignar permisos a los ficheros antes de copiar
	chmod +x $clientFilesDir/etc/init/default.sh
	chmod +x $clientFilesDir/etc/preinit/loadenviron.sh
	chmod +x $clientFilesDir/lib/httpd/httpd-history-log.sh
	chmod +sx $clientFilesDir/lib/httpd/api/exec_root
        chmod +sx $clientFilesDir/lib/httpd/api/LogCommand.sh
        chmod +sx $clientFilesDir/lib/httpd/api/ogAgent.sh
	chmod +x $clientFilesDir/scripts/generateMenuDefault
	chmod +x $clientFilesDir/scripts/poweroff
	chmod +x $clientFilesDir/scripts/reboot

	# Copiar haciendo backup de los originales
	cp -ab $clientFilesDir/* $ogClientFilesDir
}

function configureBackend() {
	mkdir -p $INSTALL_TARGET/www3
	cp -r $WORKDIR/admin/WebConsole3/backend $INSTALL_TARGET/www3/backend
	pushd $INSTALL_TARGET/www3/backend
	php composer.phar update
	chmod 777 -R cache
	chmod 777 -R logs
	php app/console doctrine:database:create --if-not-exists
	php app/console doctrine:schema:update --force
	php app/console doctrine:fixtures:load
	php app/console fos:user:create test test@opengnsys.es test
	popd
	# Añr al fichero de configuracion del cliente "ogAdmClient.cfg" la Url de la nueva API Rest
	echo "UrlApi=https://$SERVERIP/opengnsys3/rest/web/app_dev.php/api/" >> $INSTALL_TARGET/client/etc/ogAdmClient.cfg
	# TODO - añr la url del endpoint para la gestion de menus cuando este hecho
	#sed -e "s,\(UrlMenu=.*\),UrlMenu=nuevaurl,g" $INSTALL_TARGET/client/etc/ogAdmClient.cfg >> $WORKDIR/ogAdmClient.cfg
	#
	cp $WORKDIR/ogAdmClient.cfg $INSTALL_TARGET/client/etc/ogAdmClient.cfg
}

function configureFrontend() {
	local frontendDir constantsFile
	frontendDir=$INSTALL_TARGET/www3/frontend
	mkdir -p $frontendDir
	# copiar todos los ficheros del frontend
	cp -r $WORKDIR/admin/WebConsole3/frontend/ $frontendDir
	# TODO - Modificar el fichero de constantes con la ruta de la api
	constantsFile=console/assets/js/config.constants.js
	sed -e "s,\(var OGSERVER = \)\(\"[0-9\.]*\"\),var OGSERVER = \"$SERVERIP\",g" $WORKDIR/admin/WebConsole3/frontend/$constantsFile > $frontendDir/$constantsFile
	pushd $frontendDir
	bower --allow-root -V install
	popd
}


# Logs

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

# Escribe a fichero y muestra mensaje de aviso
function warningAndLog()
{
        local DATETIME=`getDateTime`
        echo "Warning: $1"
        echo "$DATETIME;$SSH_CLIENT;Warning: $1" >> $LOG_FILE
}

#####################################################################
####### Proceso de actualización de OpenGnsys
#####################################################################


echoAndLog "OpenGnsys WebConsole 3 installation begins at $(date)"

pushd $WORKDIR

# Comprobar si hay conexión y detectar parámetros de red por defecto.
checkNetworkConnection
if [ $? -ne 0 ]; then
        errorAndLog "Error connecting to server. Causes:"
        errorAndLog " - Network is unreachable, check device parameters"
        errorAndLog " - You are inside a private network, configure the proxy service"
        errorAndLog " - Server is temporally down, try again later"
        exit 1
fi
getNetworkSettings
echoAndLog "Install dependencies"
#installDependencies
#echoAndLog "Download source files"
#downloadFiles
echoAndLog "Configuring apache"
#configureApache
echoAndLog "Configuring opengnsys client"
configureClient
echoAndLog "Configuring backend"
#configureBackend
echoAndLog "Configuring frontend"
#configureFrontend


popd

