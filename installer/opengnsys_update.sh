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
fi

# Comprobar si se ha descargado el paquete comprimido (USESVN=0) o sólo el instalador (USESVN=1).
PROGRAMDIR=$(readlink -e $(dirname "$0"))
PROGRAMNAME=$(basename "$0")
OPENGNSYS_SERVER="www.opengnsys.es"
if [ -d "$PROGRAMDIR/../installer" ]; then
	USESVN=0
else
	USESVN=1
fi
SVN_URL="http://$OPENGNSYS_SERVER/svn/branches/version1.1/"

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
# - APACHECFGDIR, APACHESERV, DHCPSERV, INETDCFGDIR - configuración y servicios
function autoConfigure()
{
local i

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
case "$OSDISTRIB" in
        ubuntu|debian|linuxmint)
		DEPENDENCIES=( php5-ldap xinetd rsync btrfs-tools procps arp-scan realpath php5-curl gettext )
		UPDATEPKGLIST="apt-get update"
		INSTALLPKGS="apt-get -y install --force-yes"
		CHECKPKG="dpkg -s \$package 2>/dev/null | grep -q \"Status: install ok\""
		if which service &>/dev/null; then
			STARTSERVICE="eval service \$service restart"
			STOPSERVICE="eval service \$service stop"
		else
			STARTSERVICE="eval /etc/init.d/\$service restart"
			STOPSERVICE="eval /etc/init.d/\$service stop"
		fi
		ENABLESERVICE="eval update-rc.d \$service defaults"
		APACHEUSER="www-data"
		APACHEGROUP="www-data"
		INETDCFGDIR=/etc/xinetd.d
		;;
        fedora|centos)
		DEPENDENCIES=( php-ldap xinetd rsync btrfs-progs procps-ng arp-scan gettext )
		# En CentOS 7 instalar arp-scan de CentOS 6.
		[ "$OSDISTRIB$OSVERSION" == "centos7" ] && DEPENDENCIES=( ${DEPENDENCIES[*]/arp-scan/http://dag.wieers.com/redhat/el6/en/$(arch)/dag/RPMS/arp-scan-1.9-1.el6.rf.$(arch).rpm} )
		INSTALLPKGS="yum install -y"
		CHECKPKG="rpm -q --quiet \$package"
		if which systemctl &>/dev/null; then
			STARTSERVICE="eval systemctl start \$service.service"
			STOPSERVICE="eval systemctl stop \$service.service"
			ENABLESERVICE="eval systemctl enable \$service.service"
		else
			STARTSERVICE="eval service \$service start"
			STOPSERVICE="eval service \$service stop"
			ENABLESERVICE="eval chkconfig \$service on"
		fi
		APACHEUSER="apache"
		APACHEGROUP="apache"
		INETDCFGDIR=/etc/xinetd.d
		;;
	*)	# Otras distribuciones.
		;;
esac
for i in apache2 httpd; do
	[ -f /etc/$i ] && APACHECFGDIR="/etc/$i"
	[ -f /etc/init.d/$i ] && APACHESERV="/etc/init.d/$i"
done
for i in dhcpd dhcpd3-server isc-dhcp-server; do
	[ -f /etc/init.d/$i ] && DHCPSERV="/etc/init.d/$i"
done
}


# Comprobar auto-actualización.
function checkAutoUpdate()
{
	local update=0

	# Actaulizar el script si ha cambiado o no existe el original.
	if [ $USESVN -eq 1 ]; then
		svn export $SVN_URL/installer/$PROGRAMNAME
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
	local APIKEY=$(php -r 'echo md5(uniqid(rand(), true));')
	REPOKEY=$(php -r 'echo md5(uniqid(rand(), true));')

        if [ ! -r $sqlfile ]; then
                errorAndLog "${FUNCNAME}(): Unable to read $sqlfile!!"
                return 1
        fi

        echoAndLog "${FUNCNAME}(): importing SQL file to ${database}..."
        chmod 600 $tmpfile
        sed -e "s/SERVERIP/$SERVERIP/g" -e "s/DBUSER/$OPENGNSYS_DB_USER/g" \
            -e "s/DBPASSWORD/$OPENGNSYS_DB_PASSWD/g" \
            -e "s/APIKEY/$APIKEY/g" -e "s/REPOKEY/$REPOKEY/g" $sqlfile > $tmpfile
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


#####################################################################
####### Funciones de instalación de paquetes
#####################################################################

# Instalar las deependencias necesarias para el actualizador.
function installDependencies()
{
	local package

	if [ $# = 0 ]; then
		echoAndLog "${FUNCNAME}(): no deps needed."
	else
		while [ $# -gt 0 ]; do
			package="$1"
			eval $CHECKPKG || INSTALLDEPS="$INSTALLDEPS $1"
			shift
		done
		if [ -n "$INSTALLDEPS" ]; then
			$UPDATEPKGLIST
			$INSTALLPKGS $INSTALLDEPS
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

# Obtener los parámetros de red del servidor.
function getNetworkSettings()
{
	# Variables globales definidas:
	# - SERVERIP:   IP local de la interfaz por defecto.

	local DEVICES
	local dev

	echoAndLog "${FUNCNAME}(): Detecting network parameters."
	SERVERIP="$ServidorAdm"
	DEVICES="$(ip -o link show up | awk '!/loopback/ {sub(/:.*/,"",$2); print $2}')"
	for dev in $DEVICES; do
		[ -z "$SERVERIP" ] && SERVERIP=$(ip -o addr show dev $dev | awk '$3~/inet$/ {sub (/\/.*/, ""); print ($4)}')
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
	echoAndLog "${FUNCNAME}(): Updating OpenGnsys Client files."
	rsync --exclude .svn -irplt $WORKDIR/opengnsys/client/shared/* $INSTALL_TARGET/client
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while updating client structure"
		exit 1
	fi
	find $INSTALL_TARGET/client -name .svn -type d -exec rm -fr {} \; 2>/dev/null

	# Actualizar librerías del motor de clonación.
	echoAndLog "${FUNCNAME}(): Updating OpenGnsys Cloning Engine files."
	rsync --exclude .svn -irplt $WORKDIR/opengnsys/client/engine/*.lib* $INSTALL_TARGET/client/lib/engine/bin
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

	echoAndLog "${FUNCNAME}(): client files update success."
}

# Configurar HTTPS y exportar usuario y grupo del servicio Apache.
function apacheConfiguration ()
{
	# Activar HTTPS (solo actualizando desde versiones anteriores a 1.0.2) y
	#    activar módulo Rewrite (solo actualizaciones desde 1.0.x a 1.1.x).
	if [ -e $APACHECFGDIR/sites-available/opengnsys.conf ]; then
		echoAndLog "${FUNCNAME}(): Configuring Apache modules."
		mv $APACHECFGDIR/sites-available/opengnsys.conf $APACHECFGDIR/sites-available/opengnsys
		a2ensite default-ssl
		a2enmod ssl
		a2enmod rewrite
		a2dissite opengnsys.conf
		a2ensite opengnsys
	elif [ -e $APACHECFGDIR/conf.modules.d ]; then
		echoAndLog "${FUNCNAME}(): Configuring Apache modules."
		sed -i '/rewrite/s/^#//' $APACHECFGDIR/*.conf
	fi

	# Actualizar configuración para acceso a API REST
	#    (solo actualizaciones de 1.0.x a 1.1.x).
	for config in $APACHECFGDIR/{,sites-available/}opengnsys.conf ]; do
		if [ -e $config ] && ! grep -q "/rest" $config; then 
			cat << EOT >> $config
<Directory $INSTALL_TARGET/www/rest>
	RewriteEngine On
	RewriteBase /opengnsys/rest/
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteRule ^ index.php [QSA,L]
</Directory>
EOT
		fi
	done

	# Reiniciar Apache.
	$APACHESERV restart

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
		echoAndLog "${FUNCNAME}(): Configuring Rsync service."
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
	rsync --exclude .svn -irplt $WORKDIR/opengnsys/admin/WebConsole $INSTALL_TARGET
	ERRCODE=$?
	mv $INSTALL_TARGET/WebConsole $INSTALL_TARGET/www
	unzip -o $WORKDIR/opengnsys/admin/xajax_0.5_standard.zip -d $INSTALL_TARGET/www/xajax
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

	# Cambiar permisos para ficheros especiales.
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/www/images/{fotos,iconos}
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/www/tmp/
	# Fichero de logs del agente OGAgent.
	touch $INSTALL_TARGET/log/ogagent.log
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/log/ogagent.log

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
	local dir

	mkdir -p ${INSTALL_TARGET}/{bin,doc,etc,lib,sbin,www}
	mkdir -p ${INSTALL_TARGET}/{client,images}
	mkdir -p ${INSTALL_TARGET}/log/clients
	ln -fs ${INSTALL_TARGET}/log /var/log/opengnsys
	# Detectar directorio de instalación de TFTP.
	if [ ! -L ${INSTALL_TARGET}/tftpboot ]; then
		for dir in /var/lib/tftpboot /srv/tftp; do
			[ -d $dir ] && ln -fs $dir ${INSTALL_TARGET}/tftpboot
		done
	fi
	mkdir -p ${INSTALL_TARGET}/tftpboot/menu.lst
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while creating dirs. Do you have write permissions?"
		return 1
	fi

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

	# Establecer los permisos básicos.
	echoAndLog "${FUNCNAME}(): setting directory permissions"
	chmod -R 775 $INSTALL_TARGET/{log/clients,images,tftpboot/menu.lst}
	mkdir -p $INSTALL_TARGET/tftpboot/menu.lst/examples
	! [ -f $INSTALL_TARGET/tftpboot/menu.lst/templates/00unknown ] && mv $INSTALL_TARGET/tftpboot/menu.lst/templates/* $INSTALL_TARGET/tftpboot/menu.lst/examples
	chown -R :$OPENGNSYS_CLIENTUSER $INSTALL_TARGET/{log/clients,images,tftpboot/menu.lst}
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while setting permissions"
		return 1
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
	local OLDVERSION=$(awk '{print $2}' $INSTALL_TARGET/doc/VERSION.txt)
	local NEWVERSION=$(awk '{print $2}' $WORKDIR/opengnsys/doc/VERSION.txt)
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
			rsync --exclude .svn -irplt "${SOURCES[i]}" $(dirname $(readlink -e "$INSTALL_TARGET/${TARGETS[i]}"))
		else
			rsync -irplt "${SOURCES[i]}" $(readlink -m "$INSTALL_TARGET/${TARGETS[i]}")
		fi
	done
	popd >/dev/null
	NEWFILES=""		# Ficheros de configuración que han cambiado de formato.
	if grep -q 'pxelinux.0' /etc/dhcp*/dhcpd*.conf; then
		echoAndLog "${FUNCNAME}(): updating DHCP files"
		perl -pi -e 's/pxelinux.0/grldr/' /etc/dhcp*/dhcpd*.conf
		$DHCPSERV restart
		NEWFILES="/etc/dhcp*/dhcpd*.conf"
	fi
	if ! diff -q $WORKDIR/opengnsys/admin/Sources/Services/opengnsys.init /etc/init.d/opengnsys 2>/dev/null; then
		echoAndLog "${FUNCNAME}(): updating new init file"
		backupFile /etc/init.d/opengnsys
		cp -a $WORKDIR/opengnsys/admin/Sources/Services/opengnsys.init /etc/init.d/opengnsys
		NEWFILES="$NEWFILES /etc/init.d/opengnsys"
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
	echoAndLog "${FUNCNAME}(): server files updated successfully."
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
	# Compilar OpenGnsys Repository Manager
	echoAndLog "${FUNCNAME}(): Recompiling OpenGnsys Repository Manager"
	pushd $WORKDIR/opengnsys/admin/Sources/Services/ogAdmRepo
	make && moveNewService ogAdmRepo $INSTALL_TARGET/sbin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnsys Repository Manager"
		hayErrores=1
	fi
	popd
	# Actualizar o insertar clave de acceso REST en el fichero de configuración del repositorio.
	grep -q '^ApiToken=' $INSTALL_TARGET/etc/ogAdmRepo.cfg && \
		sed -i "s/^ApiToken=.*$/ApiToken=$REPOKEY/" $INSTALL_TARGET/etc/ogAdmRepo.cfg || \
		sed -i "$ a\ApiToken=$REPOKEY/" $INSTALL_TARGET/etc/ogAdmRepo.cfg
	# Compilar OpenGnsys Agent
	echoAndLog "${FUNCNAME}(): Recompiling OpenGnsys Agent"
	pushd $WORKDIR/opengnsys/admin/Sources/Services/ogAdmAgent
	make && moveNewService ogAdmAgent $INSTALL_TARGET/sbin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnsys Agent"
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
	local DOWNLOADURL="http://$OPENGNSYS_SERVER/downloads"
	#local FILENAME=ogLive-wily-4.2.0-35-generic-r4919.iso 		# 1.1.0-rc3
	local FILENAME=ogLive-xenial-4.4.0-34-generic-r4999.iso		# 1.1.0-rc4
	local SOURCEFILE=$DOWNLOADURL/$FILENAME
	local TARGETFILE=$INSTALL_TARGET/lib/$FILENAME
	local SOURCELENGTH
	local TARGETLENGTH
	local OGINITRD=$INSTALL_TARGET/tftpboot/ogclient/oginitrd.img
	local OGVMLINUZ=$INSTALL_TARGET/tftpboot/ogclient/ogvmlinuz
	local SAMBAPASS
	local KERNELVERSION

	# Comprobar si debe actualizarse el cliente.
	SOURCELENGTH=$(LANG=C wget --spider $SOURCEFILE 2>&1 | awk '/Length:/ {print $2}')
	TARGETLENGTH=$(ls -l $TARGETFILE 2>/dev/null | awk '{print $5}')
	[ -z $TARGETLENGTH ] && TARGETLENGTH=0
	if [ "$SOURCELENGTH" != "$TARGETLENGTH" ]; then
		echoAndLog "${FUNCNAME}(): Loading Client"
		wget $DOWNLOADURL/$FILENAME -O $TARGETFILE
		if [ ! -s $TARGETFILE ]; then
			errorAndLog "${FUNCNAME}(): Error loading OpenGnsys Client"
			return 1
		fi
		# Actaulizar la imagen ISO del ogclient.
		echoAndLog "${FUNCNAME}(): Updatting ogclient files"
		$INSTALL_TARGET/bin/installoglive $TARGETFILE
		
		# Obtiene versión del Kernel del cliente (con 2 decimales).
		KERNELVERSION=$(file -bkr $OGVMLINUZ 2>/dev/null | \
				awk '/Linux/ { for (i=1; i<=NF; i++)
						   if ($i~/version/) {
						      v=$(i+1);
						      printf ("%d",v);
						      sub (/[0-9]*\./,"",v);
						      printf (".%02d",v)
					     } }')
		# Actaulizar la base de datos adaptada al Kernel del cliente.
		OPENGNSYS_DBUPDATEFILE="$WORKDIR/opengnsys/admin/Database/$OPENGNSYS_DATABASE-$INSTVERSION-postinst.sql"
		if [ -f $OPENGNSYS_DBUPDATEFILE ]; then
			perl -pi -e "s/KERNELVERSION/$KERNELVERSION/g" $OPENGNSYS_DBUPDATEFILE
			importSqlFile $OPENGNSYS_DBUSER $OPENGNSYS_DBPASSWORD $OPENGNSYS_DATABASE $OPENGNSYS_DBUPDATEFILE
		fi

		echoAndLog "${FUNCNAME}(): Client update successfully"
	else
		# Si no existe, crear el fichero de claves de Rsync.
		if [ ! -f /etc/rsyncd.secrets ]; then
			echoAndLog "${FUNCNAME}(): Restoring client access key"
			SAMBAPASS=$(gzip -dc $OGINITRD | \
				    cpio -i --to-stdout scripts/ogfunctions 2>&1 | \
				    grep "^[ 	].*OPTIONS=" | \
				    sed 's/\(.*\)pass=\(\w*\)\(.*\)/\2/')
			echo -ne "$SAMBAPASS\n$SAMBAPASS\n" | \
					$INSTALL_TARGET/bin/setsmbpass
		else
			echoAndLog "${FUNCNAME}(): Client is already updated"
		fi
		# Versión del ogLive instalado.
		echo "${FILENAME%.*}" > $INSTALL_TARGET/doc/veroglive.txt 
	fi
}

# Comprobar permisos y ficheros.
function checkFiles()
{
	# Comprobar permisos adecuados.
	if [ -x	$INSTALL_TARGET/bin/checkperms ]; then
		echoAndLog "${FUNCNAME}(): Checking permissions." 
		OPENGNSYS_DIR="$INSTALL_TARGET" OPENGNSYS_USER="$OPENGNSYS_CLIENTUSER" APACHE_USER="$APACHE_RUN_USER" APACHE_GROUP="$APACHE_RUN_GROUP" $INSTALL_TARGET/bin/checkperms
	fi

	# Eliminamos el fichero de estado del tracker porque es incompatible entre los distintos paquetes
	if [ -f /tmp/dstate ]; then
		echoAndLog "${FUNCNAME}(): Delete unused files." 
		rm -f /tmp/dstate
	fi
}

# Resumen de actualización.
function updateSummary()
{
	# Actualizar fichero de versión y revisión.
	local VERSIONFILE="$INSTALL_TARGET/doc/VERSION.txt"
	local REVISION=$(LANG=C svn info $SVN_URL|awk '/Rev:/ {print "r"$4}')

	[ -f $VERSIONFILE ] || echo "OpenGnsys" >$VERSIONFILE
	perl -pi -e "s/($| r[0-9]*)/ $REVISION/" $VERSIONFILE

	echo
	echoAndLog "OpenGnsys Update Summary"
	echo       "========================"
	echoAndLog "Project version:                  $(cat $VERSIONFILE)"
	echoAndLog "Update log file:                  $LOG_FILE"
	if [ -n "$NEWFILES" ]; then
		echoAndLog "Check new config files:           $(echo $NEWFILES)"
	fi
	if [ -n "$NEWSERVICES" ]; then
		echoAndLog "New compiled services:            $(echo $NEWSERVICES)"
		# Indicar si se debe reiniciar servicios manualmente o usando el Cron.
		[ -f /etc/default/opengnsys ] && source /etc/default/opengnsys
		if [ "$RUN_CRONJOB" == "no" ]; then
			echoAndLog "        WARNING: you must restart OpenGnsys services manually."
		else
			echoAndLog "        New OpenGnsys services will be restarted by the cronjob."
		fi
	fi
	echoAndLog "Warning: You must to clear web browser cache before loading OpenGnSys page."
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
	errorAndLog " - Network is unreachable, review devices parameters."
	errorAndLog " - You are inside a private network, configure the proxy service."
	errorAndLog " - Server is temporally down, try agian later."
	exit 1
fi
getNetworkSettings

# Comprobar auto-actualización del programa.
if [ "$PROGRAMDIR" != "$INSTALL_TARGET/bin" ]; then
	checkAutoUpdate
	if [ $? -ne 0 ]; then
		echoAndLog "OpenGnsys updater has been overwritten."
		echoAndLog "Please, re-execute this script."
		exit
	fi
fi

# Detectar datos de auto-configuración del instalador.
autoConfigure

# Instalar dependencias.
installDependencies ${DEPENDENCIES[*]}
if [ $? -ne 0 ]; then
	errorAndLog "Error: you may install all needed dependencies."
	exit 1
fi

# Arbol de directorios de OpenGnsys.
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

# Actualizar ficheros del cliente
updateClientFiles
updateInterfaceAdm

# Actualizar páqinas web
apacheConfiguration
updateWebFiles
if [ $? -ne 0 ]; then
	errorAndLog "Error updating OpenGnsys Web Admin files"
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

# Comprobar permisos y ficheros.
checkFiles

# Mostrar resumen de actualización.
updateSummary

#rm -rf $WORKDIR
echoAndLog "OpenGnsys update finished at $(date)"

popd

