#!/bin/bash
#/**
#@file    opengnsys_update.sh
#@brief   Script actualización de OpenGnsys
#@version 0.9 - basado en opengnsys_installer.sh
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2010/01/27
#@version 1.0 - adaptación a OpenGnSys 1.0
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2011/03/02
#@version 1.0.1 - control de auto actualización del script
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2011/05/17
#@version 1.0.2a - obtiene valor de dirección IP por defecto
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2012/01/18
#@version 1.0.3 - Compatibilidad con Debian y auto configuración de acceso a BD.
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2012/03/12
#@version 1.0.4 - Detector de distribución y compatibilidad con CentOS.
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2012/05/04
#@version 1.0.5 - Actualizar BD en la misma versión, compatibilidad con Fedora (systemd) y configuración de Rsync.
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2014/04/03
#@version 1.0.6 - Redefinir URLs de ficheros de configuración usando HTTPS.
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2015/03/12
#@version 1.1.0 - Instalación de API REST y configuración de zona horaria.
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2015/11/09
#*/


####  AVISO: NO EDITAR variables de configuración.
####  WARNING: DO NOT EDIT configuration variables.
INSTALL_TARGET=/opt/opengnsys		# Directorio de instalación
PATH=$PATH:$INSTALL_TARGET/bin
OPENGNSYS_CLIENTUSER="opengnsys"	# Usuario Samba


# Sólo ejecutable por usuario root
if [ "$(whoami)" != 'root' ]; then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi
# Error si OpenGnsys no está instalado (no existe el directorio del proyecto)
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
OPENGNSYS_DATABASE=${OPENGNSYS_DATABASE:-"$CATALOG"}		# Base de datos
OPENGNSYS_DBUSER=${OPENGNSYS_DBUSER:-"$USUARIO"}		# Usuario de acceso
OPENGNSYS_DBPASSWORD=${OPENGNSYS_DBPASSWORD:-"$PASSWORD"}	# Clave del usuario
if [ -z "$OPENGNSYS_DATABASE" -o -z "$OPENGNSYS_DBUSER" -o -z "$OPENGNSYS_DBPASSWORD" ]; then
	echo "ERROR: set OPENGNSYS_DATABASE, OPENGNSYS_DBUSER and OPENGNSYS_DBPASSWORD"
	echo "       variables, and run this script again."
	exit 1
fi

# Comprobar si se ha descargado el paquete comprimido (REMOTE=0) o sólo el instalador (REMOTE=1).
PROGRAMDIR=$(readlink -e $(dirname "$0"))
PROGRAMNAME=$(basename "$0")
OPENGNSYS_SERVER="opengnsys.es"
if [ -d "$PROGRAMDIR/../installer" ]; then
	REMOTE=0
else
	REMOTE=1
fi
BRANCH="master"
CODE_URL="https://codeload.github.com/opengnsys/OpenGnsys/zip/$BRANCH"
API_URL="https://api.github.com/repos/opengnsys/OpenGnsys/branches/$BRANCH"
RAW_URL="https://raw.githubusercontent.com/opengnsys/OpenGnsys/$BRANCH"

WORKDIR=/tmp/opengnsys_update
mkdir -p $WORKDIR

# Registro de incidencias.
OGLOGFILE=$INSTALL_TARGET/log/${PROGRAMNAME%.sh}.log 
LOG_FILE=/tmp/$(basename $OGLOGFILE) 



#####################################################################
####### Algunas funciones útiles de propósito general:
#####################################################################

# Generar variables de configuración del actualizador
# Variables globales:
# - OSDISTRIB - distribución Linux
# - DEPENDENCIES - array de dependencias que deben estar instaladas
# - UPDATEPKGLIST, INSTALLPKGS, CHECKPKG - comandos para gestión de paquetes
# - APACHECFGDIR, APACHESERV, PHPFPMSERV, DHCPSERV, INETDCFGDIR - configuración y servicios

function autoConfigure()
{
	local service

	# Detectar sistema operativo del servidor (compatible con fichero os-release y con LSB).
	if [ -f /etc/os-release ]; then
		source /etc/os-release
		OSDISTRIB="$ID"
		OSVERSION="$VERSION_ID"
	else
		OSDISTRIB=$(lsb_release -is 2>/dev/null)
		OSVERSION=$(lsb_release -rs 2>/dev/null)
	fi
	# Convertir distribución a minúsculas y obtener solo el 1er número de versión.
	OSDISTRIB="${OSDISTRIB,,}"
	OSVERSION="${OSVERSION%%.*}"

	# Configuración según la distribución de Linux.
	if [ -f /etc/debian_version ]; then
		# Distribución basada en paquetes Deb.
		DEPENDENCIES=( curl rsync btrfs-tools procps arp-scan realpath php-curl gettext moreutils jq wakeonlan udpcast libev-dev libjansson-dev libssl-dev shim-signed grub-efi-amd64-signed php-fpm gawk )
		# Paquete correcto para realpath.
		[ -z "$(apt-cache pkgnames realpath)" ] && DEPENDENCIES=( ${DEPENDENCIES[@]//realpath/coreutils} )
		UPDATEPKGLIST="add-apt-repository -y ppa:ondrej/php; apt-get update"
		INSTALLPKGS="apt-get -y install"
		DELETEPKGS="apt-get -y purge"
		CHECKPKG="dpkg -s \$package 2>/dev/null | grep -q \"Status: install ok\""
		if which service &>/dev/null; then
			STARTSERVICE="eval service \$service restart"
			STOPSERVICE="eval service \$service stop"
			SERVICESTATUS="eval service \$service status"
		else
			STARTSERVICE="eval /etc/init.d/\$service restart"
			STOPSERVICE="eval /etc/init.d/\$service stop"
			SERVICESTATUS="eval /etc/init.d/\$service status"
		fi
		ENABLESERVICE="eval update-rc.d \$service defaults"
		APACHEENABLEMODS="ssl rewrite proxy_fcgi fastcgi actions alias"
		APACHEDISABLEMODS="php"
		APACHEUSER="www-data"
		APACHEGROUP="www-data"
		PHPFPMSERV="php-fpm"
		INETDCFGDIR=/etc/xinetd.d
	elif [ -f /etc/redhat-release ]; then
		# Distribución basada en paquetes rpm.
		DEPENDENCIES=( curl rsync btrfs-progs procps-ng arp-scan gettext moreutils jq net-tools udpcast libev-devel shim-x64 grub2-efi-x64 grub2-efi-x64-modules gawk )
		# Repositorios para PHP 7 en CentOS.
		[ "$OSDISTRIB" == "centos" ] && UPDATEPKGLIST="yum update -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-$OSVERSION.noarch.rpm http://rpms.remirepo.net/enterprise/remi-release-$OSVERSION.rpm"
		INSTALLPKGS="yum install -y"
		DELETEPKGS="yum remove -y"
		CHECKPKG="rpm -q --quiet \$package"
		if which systemctl &>/dev/null; then
			STARTSERVICE="eval systemctl restart \$service.service"
			STOPSERVICE="eval systemctl stop \$service.service"
			ENABLESERVICE="eval systemctl enable \$service.service"
			SERVICESTATUS="eval systemctl status \$service.service"
		else
			STARTSERVICE="eval service \$service restart"
			STOPSERVICE="eval service \$service stop"
			ENABLESERVICE="eval chkconfig \$service on"
			SERVICESTATUS="eval service \$service status"
		fi
		APACHEUSER="apache"
		APACHEGROUP="apache"
		PHPFPMSERV="php-fpm"
		INETDCFGDIR=/etc/xinetd.d
	else
		# Otras distribuciones.
		:
	fi
	for service in apache2 httpd; do
		[ -d "/etc/$service" ] && APACHECFGDIR="/etc/$service"
		if $SERVICESTATUS &>/dev/null; then APACHESERV="$service"; fi
	done
	for service in dhcpd dhcpd3-server isc-dhcp-server; do
		if $SERVICESTATUS &>/dev/null; then DHCPSERV="$service"; fi
	done
}


# Comprobar auto-actualización.
function checkAutoUpdate()
{
	local update=0

	# Actaulizar el script si ha cambiado o no existe el original.
	if [ $REMOTE -eq 1 ]; then
		curl -s $RAW_URL/installer/$PROGRAMNAME -o $PROGRAMNAME
		chmod +x $PROGRAMNAME
		if ! diff -q $PROGRAMNAME $INSTALL_TARGET/lib/$PROGRAMNAME 2>/dev/null || ! test -f $INSTALL_TARGET/lib/$PROGRAMNAME; then
			mv $PROGRAMNAME $INSTALL_TARGET/lib
			update=1
		else
			rm -f $PROGRAMNAME
		fi
	else
		if ! diff -q $PROGRAMDIR/$PROGRAMNAME $INSTALL_TARGET/lib/$PROGRAMNAME 2>/dev/null || ! test -f $INSTALL_TARGET/lib/$PROGRAMNAME; then
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
	echo "$1"
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
		warningAndLog "${FUNCNAME}(): file $fichero doesn't exists"
		return 1
	fi

	echoAndLog "${FUNCNAME}(): Making $fichero back-up"

	# realiza una copia de la última configuración como last
	cp -a $fichero "${fichero}-LAST"

	# si para el día no hay backup lo hace, sino no
	if [ ! -f "${fichero}-${fecha}" ]; then
		cp -a $fichero "${fichero}-${fecha}"
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
		cp -a "$fichero-LAST" "$fichero"
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
        local mycnf=/tmp/.my.cnf.$$
        local status

        if [ ! -r $sqlfile ]; then
                errorAndLog "${FUNCNAME}(): Unable to read $sqlfile!!"
                return 1
        fi

        echoAndLog "${FUNCNAME}(): importing SQL file to ${database}..."
        chmod 600 $tmpfile
        sed -e "s/SERVERIP/$SERVERIP/g" -e "s/DBUSER/$OPENGNSYS_DB_USER/g" \
            -e "s/DBPASSWORD/$OPENGNSYS_DB_PASSWD/g" $sqlfile > $tmpfile
	# Componer fichero con credenciales de conexión.  
	touch $mycnf
	chmod 600 $mycnf
	cat << EOT > $mycnf
[client]
user=$dbuser
password=$dbpassword
EOT
	# Ejecutar actualización y borrar fichero de credenciales.
	mysql --defaults-extra-file=$mycnf --default-character-set=utf8 -D "$database" < $tmpfile
	status=$?
	rm -f $mycnf $tmpfile
	if [ $status -ne 0 ]; then
                errorAndLog "${FUNCNAME}(): error importing $sqlfile in database $database"
                return 1
        fi
        echoAndLog "${FUNCNAME}(): file imported to database $database"
        return 0
}

# Comprobar configuración de MySQL y recomendar cambios necesarios.
function checkMysqlConfig()
{
	if [ $# -ne 2 ]; then
		errorAndLog "${FNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local dbuser="$1"
	local dbpassword="$2"
	local mycnf=/tmp/.my.cnf.$$

	echoAndLog "${FUNCNAME}(): checking MySQL configuration"
	touch $mycnf
	cat << EOT > $mycnf
[client]
user=$dbuser
password=$dbpassword
EOT
	# Check if scheduler is active.
	if [ "$(mysql --defaults-extra-file=$mycnf -Nse 'SELECT @@GLOBAL.event_scheduler;')" = "OFF" ]; then
		MYSQLCONFIG="SET GLOBAL event_scheduler = ON; "
	fi
	rm -f $mycnf

        echoAndLog "${FUNCNAME}(): MySQL configuration has checked"
        return 0
}

#####################################################################
####### Funciones de instalación de paquetes
#####################################################################

# Instalar las deependencias necesarias para el actualizador.
function installDependencies()
{
	local package

	# Comprobar si hay que actualizar PHP 5 a PHP 7.
	eval $UPDATEPKGLIST
	if [ -f /etc/debian_version ]; then
		# Basado en paquetes Deb.
		PHP7VERSION=$(apt-cache pkgnames php7 2>/dev/null | sort | head -1)
		PHPFPMSERV="${PHP7VERSION}-fpm"
		PHP5PKGS=( $(dpkg -l | awk '$2~/^php5/ {print $2}') )
		if [ -n "$PHP5PKGS" ]; then
			$DELETEPKGS ${PHP5PKGS[@]}
			PHP5PKGS[0]="$PHP7VERSION"
			INSTALLDEPS=${PHP5PKGS[@]//php5*-/${PHP7VERSION}-}
		fi
	fi
	if [ "$OSDISTRIB" == "centos" ]; then
		PHP7VERSION=$(yum list -q php7\* 2>/dev/null | awk -F. '/^php/ {print $1; exit;}')
		PHPFPMSERV="${PHP7VERSION}-${PHPFPMSERV}"
		PHP5PKGS=( $(yum list installed | awk '$1~/^php/ && $2~/^5\./ {sub(/\..*$/, "", $1); print $1}') )
		if [ -n "$PHP5PKGS" ]; then
			$DELETEPKGS ${PHP5PKGS[@]}
			PHP5PKGS[0]="$PHP7VERSION-php"
			INSTALLDEPS=${PHP5PKGS[@]//php-/${PHP7VERSION}-php}
		fi
	fi

	if [ $# = 0 ]; then
		echoAndLog "${FUNCNAME}(): no dependencies are needed"
	else
		while [ $# -gt 0 ]; do
			package="${1/php/$PHP7VERSION}"
			eval $CHECKPKG || INSTALLDEPS="$INSTALLDEPS $package"
			shift
		done
		if [ -n "$INSTALLDEPS" ]; then
			$INSTALLPKGS $INSTALLDEPS
			if [ $? -ne 0 ]; then
				errorAndLog "${FUNCNAME}(): cannot install some dependencies: $INSTALLDEPS"
				return 1
			fi
		fi
	fi
}


#####################################################################
####### Funciones para descargar código
#####################################################################

function downloadCode()
{
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local url="$1"

	echoAndLog "${FUNCNAME}(): downloading code..."

	curl "$url" -o opengnsys.zip && \
		unzip -qo opengnsys.zip && \
		rm -fr opengnsys && \
		mv "OpenGnsys-$BRANCH" opengnsys
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error getting code from ${url}, verify your user and password"
		return 1
	fi
	rm -f opengnsys.zip
	echoAndLog "${FUNCNAME}(): code was downloaded"
	return 0
}


############################################################
###  Detectar red
############################################################

# Comprobar si existe conexión.
function checkNetworkConnection()
{
	OPENGNSYS_SERVER=${OPENGNSYS_SERVER:-"opengnsys.es"}
	if which curl &>/dev/null; then
		curl --connect-timeout 10 -s "https://$OPENGNSYS_SERVER" -o /dev/null && \
			curl --connect-timeout 10 -s "http://$OPENGNSYS_SERVER" -o /dev/null
	elif which wget &>/dev/null; then
		wget --spider -q "https://$OPENGNSYS_SERVER" && \
			wget --spider -q "http://$OPENGNSYS_SERVER"
	else
		echoAndLog "${FUNCNAME}(): Cannot execute \"wget\" nor \"curl\"."
		return 1
	fi
}

# Comprobar si la versión es anterior a la actual.
function checkVersion()
{
	local PRE

	# Obtener versión actual y versión a actualizar.
	[ -f $INSTALL_TARGET/doc/VERSION.txt ] && OLDVERSION=$(awk '{print $2}' $INSTALL_TARGET/doc/VERSION.txt 2>/dev/null)
	[ -f $INSTALL_TARGET/doc/VERSION.json ] && OLDVERSION=$(jq -r '.version' $INSTALL_TARGET/doc/VERSION.json 2>/dev/null)
	if [ $REMOTE -eq 1 ]; then
		NEWVERSION=$(curl -s $RAW_URL/doc/VERSION.json 2>/dev/null | jq -r '.version')
	else
		NEWVERSION=$(jq -r '.version' $PROGRAMDIR/../doc/VERSION.json 2>/dev/null)
	fi
	[[ "$NEWVERSION" =~ pre ]] && PRE=1

	# Comparar versiones.
	[[ "$NEWVERSION" < "${OLDVERSION/pre/}" ]] && return 1
	[ "${NEWVERSION/pre/}" == "$OLDVERSION" -a "$PRE" == "1" ] && return 1

	return 0
}

# Obtener los parámetros de red del servidor.
function getNetworkSettings()
{
	# Variables globales definidas:
	# - SERVERIP:   IP local de la interfaz por defecto.

	local DEVICES
	local dev

	echoAndLog "${FUNCNAME}(): Detecting network parameters"
	SERVERIP="$ServidorAdm"
	DEVICES="$(ip -o link show up | awk '!/loopback/ {sub(/:.*/,"",$2); print $2}')"
	for dev in $DEVICES; do
		[ -z "$SERVERIP" ] && SERVERIP=$(ip -o addr show dev $dev | awk '$3~/inet$/ {sub (/\/.*/, ""); print ($4); exit;}')
	done
}


#####################################################################
####### Funciones específicas de la instalación de Opengnsys
#####################################################################

# Actualizar cliente OpenGnsys.
function updateClientFiles()
{
	local ENGINECFG=$INSTALL_TARGET/client/etc/engine.cfg

	# Actualizar ficheros del cliente.
	backupFile $ENGINECFG
	echoAndLog "${FUNCNAME}(): Updating OpenGnsys Client files"
	rsync -irplt $WORKDIR/opengnsys/client/shared/* $INSTALL_TARGET/client
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while updating client structure"
		exit 1
	fi

	# Actualizar librerías del motor de clonación.
	echoAndLog "${FUNCNAME}(): Updating OpenGnsys Cloning Engine files"
	rsync -irplt $WORKDIR/opengnsys/client/engine/*.lib* $INSTALL_TARGET/client/lib/engine/bin
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while updating engine files"
		exit 1
	fi
	# Actualizar fichero de configuración del motor de clonación.
	if ! grep -q "^TZ" $ENGINECFG; then
		TZ=$(timedatectl status | awk -F"[:()]" '/Time.*zone/ {print $2}')
		cat << EOT >> $ENGINECFG
# OpenGnsys Server timezone.
TZ="${TZ// /}"
EOT
	fi
	if ! diff -q ${ENGINECFG}{,-LAST} &>/dev/null; then
		NEWFILES="$NEWFILES $ENGINECFG"
	else
		rm -f ${ENGINECFG}-LAST
	fi
	# Obtener URL para descargas adicionales.
	DOWNLOADURL=$(oglivecli config download-url 2>/dev/null)
	DOWNLOADURL=${DOWNLOADURL:-"https://$OPENGNSYS_SERVER/trac/downloads"}

	echoAndLog "${FUNCNAME}(): client files successfully updated"
}

# Crear certificado para la firma de cargadores de arranque, si es necesario.
function createCerts ()
{
	local SSLCFGDIR=$INSTALL_TARGET/client/etc/ssl
	mkdir -p $SSLCFGDIR/{certs,private}
	if [ ! -f $SSLCFGDIR/private/opengnsys.key ]; then
		echoAndLog "${FUNCNAME}(): creating certificate files"
		openssl req -new -x509 -newkey rsa:2048 -keyout $SSLCFGDIR/private/opengnsys.key -out $SSLCFGDIR/certs/opengnsys.crt -nodes -days 3650 -subj "/CN=OpenGnsys/"
		openssl x509 -in $SSLCFGDIR/certs/opengnsys.crt -out $SSLCFGDIR/certs/opengnsys.cer -outform DER
		echoAndLog "${FUNCNAME}(): certificate successfully created"
	fi
}

# Configurar HTTPS y exportar usuario y grupo del servicio Apache.
function apacheConfiguration ()
{
	local config template module socketfile

	# Avtivar PHP-FPM.
	echoAndLog "${FUNCNAME}(): configuring PHP-FPM"
	service=$PHPFPMSERV
	$ENABLESERVICE; $STARTSERVICE

	# Activar módulos de Apache.
	if [ -e $APACHECFGDIR/sites-available/opengnsys.conf ]; then
		echoAndLog "${FUNCNAME}(): Configuring Apache modules"
		a2ensite default-ssl
		for module in $APACHEENABLEMODS; do a2enmod -q "$module"; done
		for module in $APACHEDISABLEMODS; do a2dismod -q "${module//PHP7VERSION}"; done
		a2ensite opengnsys
	elif [ -e $APACHECFGDIR/conf.modules.d ]; then
		echoAndLog "${FUNCNAME}(): Configuring Apache modules"
		sed -i '/rewrite/s/^#//' $APACHECFGDIR/*.conf
	fi
	# Elegir plantilla según versión de Apache.
	if [ -n "$(apachectl -v | grep "2\.[0-2]")" ]; then
	       template=$WORKDIR/opengnsys/server/etc/apache-prev2.4.conf.tmpl > $config
	else
	       template=$WORKDIR/opengnsys/server/etc/apache.conf.tmpl
	fi
	sockfile=$(find /run/php -name "php*.sock" -type s -print 2>/dev/null | tail -1)
	# Actualizar configuración de Apache a partir de fichero de plantilla.
	for config in $APACHECFGDIR/{,sites-available/}opengnsys.conf; do
		if [ -e $config ]; then
			if [ -n "$sockfile" ]; then
				sed -e "s,CONSOLEDIR,$INSTALL_TARGET/www,g; s,proxy:fcgi:.*,proxy:unix:${sockfile%% *}|fcgi://localhost\",g" $template > $config
			else
				sed -e "s,CONSOLEDIR,$INSTALL_TARGET/www,g" $template > $config
			fi
		fi
	done

	# Reiniciar Apache.
	service=$APACHESERV; $STARTSERVICE

	# Variables de ejecución de Apache.
	# - APACHE_RUN_USER
	# - APACHE_RUN_GROUP
	if [ -f $APACHECFGDIR/envvars ]; then
		source $APACHECFGDIR/envvars
	fi
	APACHE_RUN_USER=${APACHE_RUN_USER:-"$APACHEUSER"}
	APACHE_RUN_GROUP=${APACHE_RUN_GROUP:-"$APACHEGROUP"}
}

# Configurar servicio Rsync.
function rsyncConfigure()
{
	local service 

	# Configurar acceso a Rsync.
	if [ ! -f /etc/rsyncd.conf ]; then
		echoAndLog "${FUNCNAME}(): Configuring Rsync service"
		NEWFILES="$NEWFILES /etc/rsyncd.conf"
		sed -e "s/CLIENTUSER/$OPENGNSYS_CLIENTUSER/g" \
		    $WORKDIR/opengnsys/repoman/etc/rsyncd.conf.tmpl > /etc/rsyncd.conf
		# Habilitar Rsync.
		if [ -f /etc/default/rsync ]; then
			perl -pi -e 's/RSYNC_ENABLE=.*/RSYNC_ENABLE=inetd/' /etc/default/rsync
		fi
		if [ -f $INETDCFGDIR/rsync ]; then
			perl -pi -e 's/disable.*/disable = no/' $INETDCFGDIR/rsync
		else
			cat << EOT > $INETDCFGDIR/rsync
service rsync
{
	disable = no
	socket_type = stream
	wait = no
	user = root
	server = $(which rsync)
	server_args = --daemon
	log_on_failure += USERID
	flags = IPv6
}
EOT
		fi
		# Activar e iniciar Rsync.
		service="rsync"  $ENABLESERVICE
		service="xinetd"
		$ENABLESERVICE; $STARTSERVICE
	fi
}

# Copiar ficheros del OpenGnsys Web Console.
function updateWebFiles()
{
	local ERRCODE COMPATDIR f

	echoAndLog "${FUNCNAME}(): Updating web files..."

	# Copiar los ficheros nuevos conservando el archivo de configuración de acceso.
	backupFile $INSTALL_TARGET/www/controlacceso.php
	mv $INSTALL_TARGET/www $INSTALL_TARGET/WebConsole
	rsync -irplt $WORKDIR/opengnsys/admin/WebConsole $INSTALL_TARGET
	ERRCODE=$?
	mv $INSTALL_TARGET/WebConsole $INSTALL_TARGET/www
	rm -fr $INSTALL_TARGET/www/xajax
	unzip -o $WORKDIR/opengnsys/admin/slim-2.6.1.zip -d $INSTALL_TARGET/www/rest
	unzip -o $WORKDIR/opengnsys/admin/swagger-ui-2.2.5.zip -d $INSTALL_TARGET/www/rest
	if [ $ERRCODE != 0 ]; then
		errorAndLog "${FUNCNAME}(): Error updating web files."
		exit 1
	fi
	restoreFile $INSTALL_TARGET/www/controlacceso.php

	# Cambiar acceso a protocolo HTTPS.
	if grep -q "http://" $INSTALL_TARGET/www/controlacceso.php 2>/dev/null; then
		echoAndLog "${FUNCNAME}(): updating web access file"
		perl -pi -e 's!http://!https://!g' $INSTALL_TARGET/www/controlacceso.php
		NEWFILES="$NEWFILES $INSTALL_TARGET/www/controlacceso.php"
	fi

	# Compatibilidad con dispositivos móviles.
	COMPATDIR="$INSTALL_TARGET/www/principal"
	for f in acciones administracion aula aulas hardwares imagenes menus repositorios softwares; do
		sed 's/clickcontextualnodo/clicksupnodo/g' $COMPATDIR/$f.php > $COMPATDIR/$f.device.php
	done
	cp -a $COMPATDIR/imagenes.device.php $COMPATDIR/imagenes.device4.php
	# Acceso al manual de usuario
	ln -fs ../doc/userManual $INSTALL_TARGET/www/userManual
	# Fichero de log de la API REST.
	touch $INSTALL_TARGET/log/{ogagent,rest,remotepc}.log

	echoAndLog "${FUNCNAME}(): Web files successfully updated"
}

# Copiar ficheros en la zona de descargas de OpenGnsys Web Console.
function updateDownloadableFiles()
{
	local FILENAME=ogagentpkgs-$NEWVERSION.tar.gz
	local TARGETFILE=$WORKDIR/$FILENAME

	# Descargar archivo comprimido, si es necesario.
	if [ -s $PROGRAMDIR/$FILENAME ]; then
		echoAndLog "${FUNCNAME}(): Moving $PROGRAMDIR/$FILENAME file to $(dirname $TARGETFILE)"
		mv $PROGRAMDIR/$FILENAME $TARGETFILE
	else
		echoAndLog "${FUNCNAME}(): Downloading $FILENAME"
		curl $DOWNLOADURL/$FILENAME -o $TARGETFILE
	fi
	if [ ! -s $TARGETFILE ]; then
		errorAndLog "${FUNCNAME}(): Cannot download $FILENAME"
		return 1
	fi

	# Descomprimir fichero en zona de descargas.
	tar xvzf $TARGETFILE -C $INSTALL_TARGET/www/descargas
	if [ $? != 0 ]; then
		errorAndLog "${FUNCNAME}(): Error uncompressing archive $FILENAME"
		return 1
	fi
}

# Copiar carpeta de Interface 
function updateInterfaceAdm()
{ 
	local errcode=0 

	# Crear carpeta y copiar Interface 
	echoAndLog "${FUNCNAME}(): Copying Administration Interface Folder" 
	mv $INSTALL_TARGET/client/interfaceAdm $INSTALL_TARGET/client/Interface
	rsync -irplt $WORKDIR/opengnsys/admin/Interface $INSTALL_TARGET/client
	errcoce=$?
	mv $INSTALL_TARGET/client/Interface $INSTALL_TARGET/client/interfaceAdm
	if [ $errcode -ne 0 ]; then 
		echoAndLog "${FUNCNAME}(): error while updating admin interface" 
		exit 1
	fi 
	echoAndLog "${FUNCNAME}(): Admin interface successfully updated"
} 

# Crear documentación Doxygen para la consola web.
function makeDoxygenFiles()
{
	echoAndLog "${FUNCNAME}(): Making Doxygen web files..."
	$WORKDIR/opengnsys/installer/ogGenerateDoc.sh \
			$WORKDIR/opengnsys/client/engine $INSTALL_TARGET/www
	if [ ! -d "$INSTALL_TARGET/www/html" ]; then
		errorAndLog "${FUNCNAME}(): unable to create Doxygen web files"
		return 1
	fi
	rm -fr "$INSTALL_TARGET/www/api"
	mv "$INSTALL_TARGET/www/html" "$INSTALL_TARGET/www/api"
	rm -fr $INSTALL_TARGET/www/{man,perlmod,rtf}
	echoAndLog "${FUNCNAME}(): Doxygen web files created successfully"
}


# Crea la estructura base de la instalación de opengnsys
function createDirs()
{
	# Crear estructura de directorios.
	echoAndLog "${FUNCNAME}(): creating directory paths in ${INSTALL_TARGET}"
	local dir MKNETDIR

	mkdir -p ${INSTALL_TARGET}/{bin,doc,etc,lib,sbin,www}
	mkdir -p ${INSTALL_TARGET}/{client,images/groups}
	mkdir -p ${INSTALL_TARGET}/log/clients
	ln -fs ${INSTALL_TARGET}/log /var/log/opengnsys
	# Detectar directorio de instalación de TFTP.
	if [ ! -L ${INSTALL_TARGET}/tftpboot ]; then
		for dir in /var/lib/tftpboot /srv/tftp; do
			[ -d $dir ] && ln -fs $dir ${INSTALL_TARGET}/tftpboot
		done
	fi
	mkdir -p $INSTALL_TARGET/tftpboot/{menu.lst,grub}/examples
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while creating dirs. Do you have write permissions?"
		return 1
	fi
	! [ -f $INSTALL_TARGET/tftpboot/menu.lst/templates/00unknown ] && mv $INSTALL_TARGET/tftpboot/menu.lst/templates/* $INSTALL_TARGET/tftpboot/menu.lst/examples
	! [ -f $INSTALL_TARGET/tftpboot/grub/templates/10 ] && mv $INSTALL_TARGET/tftpboot/grub/templates/* $INSTALL_TARGET/tftpboot/grub/examples

	# Preparar arranque en red con Grub.
	for f in grub-mknetdir grub2-mknetdir; do
		if which $f &>/dev/null; then MKNETDIR=$f; fi
	done
	$MKNETDIR --net-directory=$TFTPCFGDIR --subdir=grub

	# Crear usuario ficticio.
	if id -u $OPENGNSYS_CLIENTUSER &>/dev/null; then
		echoAndLog "${FUNCNAME}(): user \"$OPENGNSYS_CLIENTUSER\" is already created"
	else
		echoAndLog "${FUNCNAME}(): creating OpenGnsys user"
		useradd $OPENGNSYS_CLIENTUSER 2>/dev/null
		if [ $? -ne 0 ]; then
			errorAndLog "${FUNCNAME}(): error creating OpenGnsys user"
			return 1
		fi
	fi

	# Mover el fichero de registro al directorio de logs. 
	echoAndLog "${FUNCNAME}(): moving update log file" 
	mv $LOG_FILE $OGLOGFILE && LOG_FILE=$OGLOGFILE 
	chmod 600 $LOG_FILE

	echoAndLog "${FUNCNAME}(): directory paths created"
	return 0
}

# Actualización incremental de la BD (versión actaul a actaul+1, hasta final-1 a final).
function updateDatabase()
{
	local DBDIR="$WORKDIR/opengnsys/admin/Database"
	local file FILES=""

	echoAndLog "${FUNCNAME}(): looking for database updates"
	pushd $DBDIR >/dev/null
	# Bucle de actualización incremental desde versión actual a la final.
	for file in $OPENGNSYS_DATABASE-*-*.sql; do
		case "$file" in
			$OPENGNSYS_DATABASE-$OLDVERSION-$NEWVERSION.sql)
				# Actualización única de versión inicial y final.
				FILES="$FILES $file"
				break
				;;
			$OPENGNSYS_DATABASE-*-postinst.sql)
				# Ignorar fichero específico de post-instalación.
				;;
			$OPENGNSYS_DATABASE-$OLDVERSION-*.sql)
				# Actualización de versión n a n+1.
				FILES="$FILES $file"
				OLDVERSION="$(echo $file | cut -f3 -d-)"
				;;
			$OPENGNSYS_DATABASE-*-$NEWVERSION.sql)
				# Última actualización de versión final-1 a final.
				if [ -n "$FILES" ]; then
					FILES="$FILES $file"
					break
				fi
				;;
		esac
	done
	# Aplicar posible actualización propia para la versión final.
	file=$OPENGNSYS_DATABASE-$NEWVERSION.sql
	if [ -n "$FILES" -o "$OLDVERSION" = "$NEWVERSION" -a -r $file ]; then
		FILES="$FILES $file"
	fi

	popd >/dev/null
	if [ -n "$FILES" ]; then
		for file in $FILES; do
			importSqlFile $OPENGNSYS_DBUSER $OPENGNSYS_DBPASSWORD $OPENGNSYS_DATABASE $DBDIR/$file
		done
		echoAndLog "${FUNCNAME}(): database is update"
	else
		echoAndLog "${FUNCNAME}(): database unchanged"
	fi
}

# Copia ficheros de configuración y ejecutables genéricos del servidor.
function updateServerFiles()
{
	# No copiar ficheros del antiguo cliente Initrd
	local SOURCES=(	repoman/bin \
			server/bin \
			server/lib \
			admin/Sources/Services/ogAdmServerAux \
			admin/Sources/Services/ogAdmRepoAux \
			server/tftpboot \
			installer/opengnsys_uninstall.sh \
			installer/opengnsys_export.sh \
			installer/opengnsys_import.sh \
			doc )
	local TARGETS=(	bin \
			bin \
			lib \
			sbin/ogAdmServerAux \
			sbin/ogAdmRepoAux \
			tftpboot \
			lib/opengnsys_uninstall.sh \
			lib/opengnsys_export.sh \
			lib/opengnsys_import.sh \
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
			rsync -irplt "${SOURCES[i]}" $(dirname $(readlink -e "$INSTALL_TARGET/${TARGETS[i]}"))
		else
			rsync -irplt "${SOURCES[i]}" $(readlink -m "$INSTALL_TARGET/${TARGETS[i]}")
		fi
	done
	popd >/dev/null
	NEWFILES=""		# Ficheros de configuración que han cambiado de formato.
	if grep -q 'pxelinux.0' /etc/dhcp*/dhcpd*.conf; then
		echoAndLog "${FUNCNAME}(): updating DHCP files"
		perl -pi -e 's/pxelinux.0/grldr/' /etc/dhcp*/dhcpd*.conf
		service=$DHCPSERV; $STARTSERVICE
		NEWFILES="/etc/dhcp*/dhcpd*.conf"
	fi
	if ! diff -q $WORKDIR/opengnsys/admin/Sources/Services/opengnsys.init /etc/init.d/opengnsys 2>/dev/null; then
		echoAndLog "${FUNCNAME}(): updating new init file"
		backupFile /etc/init.d/opengnsys
		cp -a $WORKDIR/opengnsys/admin/Sources/Services/opengnsys.init /etc/init.d/opengnsys
		NEWFILES="$NEWFILES /etc/init.d/opengnsys"
	fi
	if ! diff -q $WORKDIR/opengnsys/admin/Sources/Services/opengnsys.default /etc/default/opengnsys >/dev/null; then
		echoAndLog "${FUNCNAME}(): updating new default file"
		backupFile /etc/default/opengnsys
		# Buscar si hay nuevos parámetros.
		local var valor
		while IFS="=" read -e var valor; do
			[[ $var =~ ^# ]] || \
				grep -q "^$var=" /etc/default/opengnsys || \
				echo "$var=$valor" >> /etc/default/opengnsys
		done < $WORKDIR/opengnsys/admin/Sources/Services/opengnsys.default
		NEWFILES="$NEWFILES /etc/default/opengnsys"
	fi
	if egrep -q "(UrlMsg=.*msgbrowser.php)|(UrlMenu=http://)" $INSTALL_TARGET/client/etc/ogAdmClient.cfg 2>/dev/null; then
		echoAndLog "${FUNCNAME}(): updating new client config file"
		backupFile $INSTALL_TARGET/client/etc/ogAdmClient.cfg
		perl -pi -e 's!UrlMsg=.*msgbrowser\.php!UrlMsg=http://localhost/cgi-bin/httpd-log\.sh!g; s!UrlMenu=http://!UrlMenu=https://!g' $INSTALL_TARGET/client/etc/ogAdmClient.cfg
		NEWFILES="$NEWFILES $INSTALL_TARGET/client/etc/ogAdmClient.cfg"
	fi

	echoAndLog "${FUNCNAME}(): updating cron files"
	[ ! -f /etc/cron.d/opengnsys ] && echo "* * * * *   root   [ -x $INSTALL_TARGET/bin/opengnsys.cron ] && $INSTALL_TARGET/bin/opengnsys.cron" > /etc/cron.d/opengnsys
	[ ! -f /etc/cron.d/torrentcreator ] && echo "* * * * *   root   [ -x $INSTALL_TARGET/bin/torrent-creator ] && $INSTALL_TARGET/bin/torrent-creator" > /etc/cron.d/torrentcreator
	[ ! -f /etc/cron.d/torrenttracker ] && echo "5 * * * *   root   [ -x $INSTALL_TARGET/bin/torrent-tracker ] && $INSTALL_TARGET/bin/torrent-tracker" > /etc/cron.d/torrenttracker
	[ ! -f /etc/cron.d/imagedelete ] && echo "* * * * *   root   [ -x $INSTALL_TARGET/bin/deletepreimage ] && $INSTALL_TARGET/bin/deletepreimage" > /etc/cron.d/imagedelete
	[ ! -f /etc/cron.d/ogagentqueue ] && echo "* * * * *   root   [ -x $INSTALL_TARGET/bin/ogagentqueue.cron ] && $INSTALL_TARGET/bin/ogagentqueue.cron" > /etc/cron.d/ogagentqueue
	echoAndLog "${FUNCNAME}(): server files successfully updated"

	# Se modifican los nombres de las plantilla PXE por compatibilidad con los equipos UEFI.
	if [ -f $INSTALL_TARGET/tftpboot/menu.lst/templates/01 ]; then
            BIOSPXEDIR="$INSTALL_TARGET/tftpboot/menu.lst/templates"
	    mv $BIOSPXEDIR/01 $BIOSPXEDIR/10
	    sed -i "s/\bMBR\b/1hd/" $BIOSPXEDIR/10
	fi
}

####################################################################
### Funciones de compilación de código fuente de servicios
####################################################################

# Mueve el fichero del nuevo servicio si es distinto al del directorio destino.
function moveNewService()
{
	local service 

	# Recibe 2 parámetros: fichero origen y directorio destino.
	[ $# == 2 ] || return 1
	[ -f  $1 -a -d $2 ] || return 1

	# Comparar los ficheros.
	if ! diff -q $1 $2/$(basename $1) &>/dev/null; then
		# Parar los servicios si fuese necesario.
		[ -z "$NEWSERVICES" ] && service="opengnsys" $STOPSERVICE
		# Nuevo servicio.
		NEWSERVICES="$NEWSERVICES $(basename $1)"
		# Mover el nuevo fichero de servicio
		mv $1 $2
	fi
}


# Recompilar y actualiza los serivicios y clientes.
function compileServices()
{
	local hayErrores=0

	# Compilar OpenGnsys Server
	echoAndLog "${FUNCNAME}(): Recompiling OpenGnsys Admin Server"
	pushd $WORKDIR/opengnsys/admin/Sources/Services/ogAdmServer
	make && moveNewService ogAdmServer $INSTALL_TARGET/sbin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnsys Admin Server"
		hayErrores=1
	fi
	popd
	# Parar antiguo servicio de repositorio.
	pgrep ogAdmRepo > /dev/null && service="ogAdmRepo" $STOPSERVICE
	# Compilar OpenGnsys Agent
	echoAndLog "${FUNCNAME}(): Recompiling OpenGnsys Server Agent"
	pushd $WORKDIR/opengnsys/admin/Sources/Services/ogAdmAgent
	make && moveNewService ogAdmAgent $INSTALL_TARGET/sbin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnsys Server Agent"
		hayErrores=1
	fi
	popd

	# Compilar OpenGnsys Client
	echoAndLog "${FUNCNAME}(): Recompiling OpenGnsys Client"
	pushd $WORKDIR/opengnsys/admin/Sources/Clients/ogAdmClient
	make && mv ogAdmClient $INSTALL_TARGET/client/bin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnsys Client"
		hayErrores=1
	fi
	popd

	return $hayErrores
}


####################################################################
### Funciones instalacion cliente OpenGnsys
####################################################################

# Actualizar cliente OpenGnsys
function updateClient()
{
	#local FILENAME=ogLive-precise-3.2.0-23-generic-r5159.iso       # 1.1.0-rc6 (32-bit)
	local FILENAME=ogLive-bionic-5.0.0-27-generic-amd64-r20190830.7208cc9.iso	# 1.1.1-rc5
	local SOURCEFILE=$DOWNLOADURL/$FILENAME
	local TARGETFILE=$(oglivecli config download-dir)/$FILENAME
	local SOURCELENGTH
	local TARGETLENGTH
	local OGINITRD
	local SAMBAPASS

	# Comprobar si debe convertirse el antiguo cliente al nuevo formato ogLive.
	if oglivecli check | grep -q "oglivecli convert"; then
		echoAndLog "${FUNCNAME}(): Converting OpenGnsys Client to default ogLive"
		oglivecli convert
	fi
	# Comprobar si debe actualizarse el cliente.
	SOURCELENGTH=$(curl -sI $SOURCEFILE 2>&1 | awk '/Content-Length:/ {gsub("\r", ""); print $2}')
	TARGETLENGTH=$(stat -c "%s" $TARGETFILE 2>/dev/null)
	[ -z $TARGETLENGTH ] && TARGETLENGTH=0
	if [ "$SOURCELENGTH" != "$TARGETLENGTH" ]; then
		echoAndLog "${FUNCNAME}(): Downloading $FILENAME"
		oglivecli download $FILENAME
		if [ ! -s $TARGETFILE ]; then
			errorAndLog "${FUNCNAME}(): Error downloading $FILENAME"
			return 1
		fi
		# Actaulizar la imagen ISO del ogclient.
		echoAndLog "${FUNCNAME}(): Updatting ogLive client"
		oglivecli install $FILENAME
		
		INSTALLEDOGLIVE=${FILENAME%.*}

		echoAndLog "${FUNCNAME}(): ogLive successfully updated"
	else
		# Si no existe, crear el fichero de claves de Rsync.
		if [ ! -f /etc/rsyncd.secrets ]; then
			echoAndLog "${FUNCNAME}(): Restoring ogLive access key"
			OGINITRD=$(oglivecli config install-dir)/$(jq -r ".oglive[.default].directory")/oginitrd.img
			SAMBAPASS=$(gzip -dc $OGINITRD | \
				    cpio -i --to-stdout scripts/ogfunctions 2>&1 | \
				    grep "^[ 	].*OPTIONS=" | \
				    sed 's/\(.*\)pass=\(\w*\)\(.*\)/\2/')
			echo -ne "$SAMBAPASS\n$SAMBAPASS\n" | setsmbpass
		else
			echoAndLog "${FUNCNAME}(): ogLive is already updated"
		fi
		# Versión del ogLive instalado.
		echo "${FILENAME%.*}" > $INSTALL_TARGET/doc/veroglive.txt 
	fi
}

# Comprobar permisos y ficheros.
function checkFiles()
{
	local LOGROTATEDIR=/etc/logrotate.d

	# Comprobar permisos adecuados.
	if [ -x	$INSTALL_TARGET/bin/checkperms ]; then
		echoAndLog "${FUNCNAME}(): Checking permissions"
		OPENGNSYS_DIR="$INSTALL_TARGET" OPENGNSYS_USER="$OPENGNSYS_CLIENTUSER" APACHE_USER="$APACHE_RUN_USER" APACHE_GROUP="$APACHE_RUN_GROUP" $INSTALL_TARGET/bin/checkperms
	fi
	# Eliminamos el fichero de estado del tracker porque es incompatible entre los distintos paquetes
	if [ -f /tmp/dstate ]; then
		echoAndLog "${FUNCNAME}(): Deleting unused files"
		rm -f /tmp/dstate
	fi
	# Crear nuevos ficheros de logrotate y borrar el fichero antiguo.
	if [ -d $LOGROTATEDIR ]; then
		rm -f $LOGROTATEDIR/opengnsys
		if [ ! -f $LOGROTATEDIR/opengnsysServer ]; then
			echoAndLog "${FUNCNAME}(): Creating logrotate configuration file for server"
			sed -e "s/OPENGNSYSDIR/${INSTALL_TARGET//\//\\/}/g" \
				$WORKDIR/opengnsys/server/etc/logrotate.tmpl > $LOGROTATEDIR/opengnsysServer
		fi
		if [ ! -f $LOGROTATEDIR/opengnsysRepo ]; then
			echoAndLog "${FUNCNAME}(): Creating logrotate configuration file for repository"
			sed -e "s/OPENGNSYSDIR/${INSTALL_TARGET//\//\\/}/g" \
				$WORKDIR/opengnsys/server/etc/logrotate.tmpl > $LOGROTATEDIR/opengnsysRepo
		fi
	fi
}

# Resumen de actualización.
function updateSummary()
{
	# Actualizar fichero de versión y revisión.
	local VERSIONFILE REVISION
	VERSIONFILE="$INSTALL_TARGET/doc/VERSION.json"
	# Obtener revisión.
	if [ $REMOTE -eq 1 ]; then
		# Revisión: rAñoMesDía.Gitcommit (8 caracteres de fecha y 7 primeros de commit).
		REVISION=$(curl -s "$API_URL" | jq '"r" + (.commit.commit.committer.date | split("-") | join("")[:8]) + "." + (.commit.sha[:7])')
	else
		# Parámetro "release" del fichero JSON.
		REVISION=$(jq -r '.release' $PROGRAMDIR/../doc/VERSION.json 2>/dev/null)
	fi
	[ -f $VERSIONFILE ] || echo '{ "project": "OpenGnsys" }' > $VERSIONFILE
	jq ".release=$REVISION" $VERSIONFILE | sponge $VERSIONFILE
	VERSION="$(jq -r '[.project, .version, .codename, .release] | join(" ")' $VERSIONFILE 2>/dev/null)"
	# Borrar antiguo fichero de versión.
	rm -f "${VERSIONFILE/json/txt}"

	echo
	echoAndLog "OpenGnsys Update Summary"
	echo       "========================"
	echoAndLog "Project version:                  $VERSION"
	echoAndLog "Update log file:                  $LOG_FILE"
	if [ -n "$NEWFILES" ]; then
		echoAndLog "Check new config files:           $(echo $NEWFILES)"
	fi
	if [ -n "$NEWSERVICES" ]; then
		echoAndLog "New compiled services:            $(echo $NEWSERVICES)"
		# Indicar si se debe reiniciar servicios manualmente o usando el Cron.
		[ -f /etc/default/opengnsys ] && source /etc/default/opengnsys
		if [ "$RUN_CRONJOB" == "no" ]; then
			echoAndLog "        WARNING: you must to restart OpenGnsys services manually"
		else
			echoAndLog "        New OpenGnsys services will be restarted by the cronjob"
		fi
	fi
	echoAndLog "Warnings:"
	echoAndLog " - You must to clear web browser cache before loading OpenGnsys page"
	echoAndLog " - Run \"settoken\" script to update authentication tokens"
	if [ -n "$INSTALLEDOGLIVE" ]; then
		echoAndLog " - Installed new ogLive Client: $INSTALLEDOGLIVE"
	fi
	if [ -n "$MYSQLCONFIG" ]; then
		echoAndLog " - MySQL must be reconfigured, run next code as DB root user and restart service:"
		echoAndLog "      $MYSQLCONFIG"
	fi
	echo
}



#####################################################################
####### Proceso de actualización de OpenGnsys
#####################################################################


echoAndLog "OpenGnsys update begins at $(date)"

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

# Comprobar si se intanta actualizar a una versión anterior.
checkVersion
if [ $? -ne 0 ]; then
	errorAndLog "Cannot downgrade to an older version ($OLDVERSION to $NEWVERSION)"
	errorAndLog "You must to uninstall OpenGnsys and install desired release"
	exit 1
fi

# Comprobar auto-actualización del programa.
if [ "$PROGRAMDIR" != "$INSTALL_TARGET/bin" ]; then
	checkAutoUpdate
	if [ $? -ne 0 ]; then
		echoAndLog "OpenGnsys updater has been overwritten"
		echoAndLog "Please, run this script again"
		exit
	fi
fi

# Detectar datos de auto-configuración del instalador.
autoConfigure

# Instalar dependencias.
installDependencies ${DEPENDENCIES[*]}
if [ $? -ne 0 ]; then
	errorAndLog "Error: you must to install all needed dependencies"
	exit 1
fi

# Arbol de directorios de OpenGnsys.
createDirs ${INSTALL_TARGET}
if [ $? -ne 0 ]; then
	errorAndLog "Error while creating directory paths"
	exit 1
fi

# Si es necesario, descarga el repositorio de código en directorio temporal
if [ $REMOTE -eq 1 ]; then
	downloadCode $CODE_URL
	if [ $? -ne 0 ]; then
		errorAndLog "Error while getting code from repository"
		exit 1
	fi
else
	ln -fs "$(dirname $PROGRAMDIR)" opengnsys
fi

# Comprobar configuración de MySQL.
checkMysqlConfig $OPENGNSYS_DBUSER $OPENGNSYS_DBPASSWORD

# Actualizar la BD.
updateDatabase

# Actualizar ficheros complementarios del servidor
updateServerFiles
if [ $? -ne 0 ]; then
	errorAndLog "Error updating OpenGnsys Server files"
	exit 1
fi

# Configurar Rsync.
rsyncConfigure

# Actualizar ficheros del cliente.
updateClientFiles
createCerts
updateInterfaceAdm

# Actualizar páqinas web.
apacheConfiguration
updateWebFiles
if [ $? -ne 0 ]; then
	errorAndLog "Error updating OpenGnsys Web Admin files"
	exit 1
fi
# Actaulizar ficheros descargables.
updateDownloadableFiles
# Generar páginas Doxygen para instalar en el web
makeDoxygenFiles

# Recompilar y actualizar los servicios del sistema
compileServices

# Actaulizar ficheros auxiliares del cliente
updateClient
if [ $? -ne 0 ]; then
	errorAndLog "Error updating client files"
	exit 1
fi

# Comprobar permisos y ficheros.
checkFiles

# Mostrar resumen de actualización.
updateSummary

rm -rf $WORKDIR
echoAndLog "OpenGnsys update finished at $(date)"

popd

