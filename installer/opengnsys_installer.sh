#!/bin/bash

#####################################################################
####### Script instalador OpenGnSys
####### autor: Luis Guillén <lguillen@unizar.es>
#####################################################################



####  AVISO: Editar configuración de acceso por defecto.
MYSQL_ROOT_PASSWORD="passwordroot"	# Clave root de MySQL
OPENGNSYS_DB_USER="usuog"		# Usuario de acceso a la base de datos
OPENGNSYS_DB_PASSWD="passusuog"		# Clave de acceso a la base de datos
OPENGNSYS_CLIENT_PASSWD="og"		# Clave de acceso del cliente


####  AVISO: NO EDITAR. 
OPENGNSYS_DATABASE="ogAdmBD"		# Nombre de la base datos
OPENGNSYS_CLIENT_USER="opengnsys"	# Usuario del cliente para acceso remoto



# Sólo ejecutable por usuario root
if [ "$(whoami)" != 'root' ]; then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi

# Comprobar si se ha descargado el paquete comprimido (USESVN=0) o sólo el instalador (USESVN=1).
PROGRAMDIR=$(readlink -e $(dirname "$0"))
OPENGNSYS_SERVER="www.opengnsys.es"
if [ -d "$PROGRAMDIR/../installer" ]; then
	USESVN=0
else
	USESVN=1
fi
SVN_URL="http://$OPENGNSYS_SERVER/svn/branches/version1.0/"

WORKDIR=/tmp/opengnsys_installer
mkdir -p $WORKDIR

INSTALL_TARGET=/opt/opengnsys
LOG_FILE=/tmp/opengnsys_installation.log

# Base de datos
OPENGNSYS_DB_CREATION_FILE=opengnsys/admin/Database/ogAdmBD.sql


#####################################################################
####### Funciones de configuración
#####################################################################

# Generar variables de configuración del instalador
# Variables globales:
# - OSDISTRIB, OSCODENAME - datos de la distribución Linux
# - DEPENDENCIES - array de dependencias que deben estar instaladas
# - UPDATEPKGLIST, INSTALLPKGS, CHECKPKGS - comandos para gestión de paquetes
# - APACHEINIT, APACHECFGDIR, APACHEUSER, APACHEGROUP - arranque y configuración de Apache
# - ENABLEMOD, ENABLESITE - habilitar módulo Apache y sitio web
# - DHCPINIT, DHCPCFGDIR - arranque y configuración de DHCP
# - SAMBAINIT, SAMBACFGDIR - arranque y configuración de Samba
# - TFTPCFGDIR - configuración de TFTP
function autoConfigure()
{
# Detectar sistema operativo del servidor (debe soportar LSB).
OSDISTRIB=$(lsb_release -is 2>/dev/null)
OSCODENAME=$(lsb_release -cs 2>/dev/null)

# Configuración según la distribución de Linux.
case "$OSDISTRIB" in
	Ubuntu)	DEPENDENCIES=( subversion apache2 php5 libapache2-mod-php5 mysql-server php5-mysql isc-dhcp-server bittorrent tftp-hpa tftpd-hpa syslinux openbsd-inetd update-inetd build-essential g++-multilib libmysqlclient15-dev wget doxygen graphviz bittornado ctorrent samba unzip netpipes debootstrap schroot squashfs-tools )
		UPDATEPKGLIST="apt-get update"
		INSTALLPKG="apt-get -y install --force-yes"
		CHECKPKG="dpkg -s \$package 2>/dev/null | grep Status | grep -qw install"
		APACHEINIT=/etc/init.d/apache2
		APACHECFGDIR=/etc/apache2
		APACHEUSER="www-data"
		APACHEGROUP="www-data"
		ENABLEMOD="a2enmod"
		ENABLESITE="a2ensite"
		DHCPINIT=/etc/init.d/isc-dhcp-server
		DHCPCFGDIR=/etc/dhcp
		SAMBAINIT=/etc/init.d/smbd
		SAMBACFGDIR=/etc/samba
		TFTPCFGDIR=/var/lib/tftpboot
		;;
	Fedora)	DEPENDENCIES=( subversion httpd mod_ssl php mysql-server mysql-devel mysql-devel.i686 php-mysql dhcp bittorrent tftp-server syslinux binutils gcc gcc-c++ glibc-devel.i686 make wget doxygen graphviz python-tornado ctorrent samba unzip NetPIPE debootstrap schroot squashfs-tools )		# TODO comprobar paquetes
		UPDATEPKGLIST=""
		INSTALLPKG="yum install -y"
		CHECKPKG="rpm -q \$package"
		APACHEINIT=/etc/init.d/httpd
		APACHECFGDIR=/etc/httpd/conf.d
		APACHEUSER="apache"
		APACHEGROUP="apache"
		DHCPINIT=/etc/init.d/dhcpd
		DHCPCFGDIR=/etc/dhcp
		SAMBAINIT=/etc/init.d/smb
		SAMBACFGDIR=/etc/samba
		TFTPCFGDIR=/var/lib/tftpboot
		;;
	"") 	echo "ERROR: Unknown Linux distribution, please install \"lsb_release\" command."
		exit 1 ;;
	*) 	echo "ERROR: Distribution not supported by OpenGnSys."
		exit 1 ;;
esac
}

# Cargar lista de paquetes del sistema y actualizar algunas variables de configuración
# dependiendo de la versión instalada.
function updatePackageList()
{
local DHCPVERSION

# Si es necesario, actualizar la lista de paquetes disponibles.
[ -n "$UPDATEPKGLIST" ] && eval $UPDATEPKGLIST

# Configuración personallizada de algunos paquetes.
case "$OSDISTRIB" in
	Ubuntu) # Postconfiguación personalizada para Ubuntu.
		# Configuración para DHCP v3.
		DHCPVERSION=$(apt-cache show dhcp.?-server$ | \
			      awk '/Version/ {print substr($2,1,1);}' | \
			      sort -n | tail -1)
		if [ $DHCPVERSION = 3 ]; then
			DEPENDENCIES=${DEPENDENCIES[@]/isc-dhcp-server/dhcp3-server}
			DHCPINIT=/etc/init.d/dhcp3-server
			DHCPCFGDIR=/etc/dhcp3
		fi
		;;
esac
}


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
	echo "$1"
	local DATETIME=`getDateTime`
	echo "$DATETIME;$SSH_CLIENT;$1" >> $LOG_FILE
}

function errorAndLog()
{
	echo "ERROR: $1"
	local DATETIME=`getDateTime`
	echo "$DATETIME;$SSH_CLIENT;ERROR: $1" >> $LOG_FILE
}

# comprueba si el elemento pasado en $2 esta en el array $1
function isInArray()
{
	if [ $# -ne 2 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	echoAndLog "${FUNCNAME}(): checking if $2 is in $1"
	local deps
	eval "deps=( \"\${$1[@]}\" )"
	elemento=$2

	local is_in_array=1
	# copia local del array del parametro 1
	for (( i = 0 ; i < ${#deps[@]} ; i++ ))
	do
		if [ "${deps[$i]}" = "${elemento}" ]; then
			echoAndLog "isInArray(): $elemento found in array"
			is_in_array=0
		fi
	done

	if [ $is_in_array -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): $elemento NOT found in array"
	fi

	return $is_in_array

}

#####################################################################
####### Funciones de manejo de paquetes Debian
#####################################################################

function checkPackage()
{
	package=$1
	if [ -z $package ]; then
		errorAndLog "${FUNCNAME}(): parameter required"
		exit 1
	fi
	echoAndLog "${FUNCNAME}(): checking if package $package exists"
	eval $CHECKPKG
	if [ $? -eq 0 ]; then
		echoAndLog "${FUNCNAME}(): package $package exists"
		return 0
	else
		echoAndLog "${FUNCNAME}(): package $package doesn't exists"
		return 1
	fi
}

# recibe array con dependencias
# por referencia deja un array con las dependencias no resueltas
# devuelve 1 si hay alguna dependencia no resuelta
function checkDependencies()
{
	if [ $# -ne 2 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	echoAndLog "${FUNCNAME}(): checking dependences"
	uncompletedeps=0

	# copia local del array del parametro 1
	local deps
	eval "deps=( \"\${$1[@]}\" )"

	declare -a local_notinstalled

	for (( i = 0 ; i < ${#deps[@]} ; i++ ))
	do
		checkPackage ${deps[$i]}
		if [ $? -ne 0 ]; then
			local_notinstalled[$uncompletedeps]=$package
			let uncompletedeps=uncompletedeps+1
		fi
	done

	# relleno el array especificado en $2 por referencia
	for (( i = 0 ; i < ${#local_notinstalled[@]} ; i++ ))
	do
		eval "${2}[$i]=${local_notinstalled[$i]}"
	done

	# retorna el numero de paquetes no resueltos
	echoAndLog "${FUNCNAME}(): dependencies uncompleted: $uncompletedeps"
	return $uncompletedeps
}

# Recibe un array con las dependencias y lo instala
function installDependencies()
{
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi
	echoAndLog "${FUNCNAME}(): installing uncompleted dependencies"

	# copia local del array del parametro 1
	local deps
	eval "deps=( \"\${$1[@]}\" )"

	local string_deps=""
	for (( i = 0 ; i < ${#deps[@]} ; i++ ))
	do
		string_deps="$string_deps ${deps[$i]}"
	done

	if [ -z "${string_deps}" ]; then
		errorAndLog "${FUNCNAME}(): array of dependeces is empty"
		exit 1
	fi

	OLD_DEBIAN_FRONTEND=$DEBIAN_FRONTEND
	export DEBIAN_FRONTEND=noninteractive

	echoAndLog "${FUNCNAME}(): now $string_deps will be installed"
	eval $INSTALLPKG $string_deps
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error installing dependencies"
		return 1
	fi

	DEBIAN_FRONTEND=$OLD_DEBIAN_FRONTEND
	echoAndLog "${FUNCNAME}(): dependencies installed"
}

# Hace un backup del fichero pasado por parámetro
# deja un -last y uno para el día
function backupFile()
{
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local file="$1"
	local dateymd=`date +%Y%m%d`

	if [ ! -f "$file" ]; then
		errorAndLog "${FUNCNAME}(): file $file doesn't exists"
		return 1
	fi

	echoAndLog "${FUNCNAME}(): making $file backup"

	# realiza una copia de la última configuración como last
	cp -a "$file" "${file}-LAST"

	# si para el día no hay backup lo hace, sino no
	if [ ! -f "${file}-${dateymd}" ]; then
		cp -a "$file" "${file}-${dateymd}"
	fi

	echoAndLog "${FUNCNAME}(): $file backup success"
}

#####################################################################
####### Funciones para el manejo de bases de datos
#####################################################################

# This function set password to root
function mysqlSetRootPassword()
{
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local root_mysql="$1"
	echoAndLog "${FUNCNAME}(): setting root password in MySQL server"
	mysqladmin -u root password "$root_mysql"
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while setting root password in MySQL server"
		return 1
	fi
	echoAndLog "${FUNCNAME}(): root password saved!"
	return 0
}

# Si el servicio mysql esta ya instalado cambia la variable de la clave del root por la ya existente
function mysqlGetRootPassword()
{
	local pass_mysql
	local pass_mysql2
        # Comprobar si MySQL está instalado con la clave de root por defecto.
        if mysql -u root -p"$MYSQL_ROOT_PASSWORD" <<<"quit" 2>/dev/null; then
		echoAndLog "${FUNCNAME}(): Using default mysql root password."
        else
	        stty -echo
	        echo "There is a MySQL service already installed."
	        read -p "Enter MySQL root password: " pass_mysql
	        echo ""
	        read -p "Confrim password:" pass_mysql2
	        echo ""
	        stty echo
	        if [ "$pass_mysql" == "$pass_mysql2" ] ;then
		        MYSQL_ROOT_PASSWORD="$pass_mysql"
		        return 0
	        else
			echo "The keys don't match. Do not configure the server's key,"
	        	echo "transactions in the database will give error."
	        	return 1
	        fi
	fi
}

# comprueba si puede conectar con mysql con el usuario root
function mysqlTestConnection()
{
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	echoAndLog "${FUNCNAME}(): checking connection to mysql..."
	echo "" | mysql -uroot -p"${root_password}"
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): connection to mysql failed, check root password and if daemon is running!"
		return 1
	else
		echoAndLog "${FUNCNAME}(): connection success"
		return 0
	fi
}

# comprueba si la base de datos existe
function mysqlDbExists()
{
	if [ $# -ne 2 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local database=$2
	echoAndLog "${FUNCNAME}(): checking if $database exists..."
	echo "show databases" | mysql -uroot -p"${root_password}" | grep "^${database}$"
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}():database $database doesn't exists"
		return 1
	else
		echoAndLog "${FUNCNAME}():database $database exists"
		return 0
	fi
}

function mysqlCheckDbIsEmpty()
{
	if [ $# -ne 2 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local database=$2
	echoAndLog "${FUNCNAME}(): checking if $database is empty..."
	num_tablas=`echo "show tables" | mysql -uroot -p"${root_password}" "${database}" | wc -l`
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error executing query, check database and root password"
		exit 1
	fi

	if [ $num_tablas -eq 0 ]; then
		echoAndLog "${FUNCNAME}():database $database is empty"
		return 0
	else
		echoAndLog "${FUNCNAME}():database $database has tables"
		return 1
	fi

}


function mysqlImportSqlFileToDb()
{
	if [ $# -ne 3 ]; then
		errorAndLog "${FNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local root_password="$1"
	local database="$2"
	local sqlfile="$3"
	local tmpfile=$(mktemp)
	local i=0
	local dev=""
	local status

	if [ ! -f $sqlfile ]; then
		errorAndLog "${FUNCNAME}(): Unable to locate $sqlfile!!"
		return 1
	fi

	echoAndLog "${FUNCNAME}(): importing SQL file to ${database}..."
	chmod 600 $tmpfile
	for dev in ${DEVICE[*]}; do
		if [ "${DEVICE[i]} == $DEFAULTDEV" ]; then
			sed -e "s/SERVERIP/${SERVERIP[i]}/g" \
			    -e "s/DBUSER/$OPENGNSYS_DB_USER/g" \
			    -e "s/DBPASSWORD/$OPENGNSYS_DB_PASSWD/g" \
				$sqlfile > $tmpfile
		fi
		let i++
	done
	mysql -uroot -p"${root_password}" --default-character-set=utf8 "${database}" < $tmpfile
	status=$?
	rm -f $tmpfile
	if [ $status -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while importing $sqlfile in database $database"
		return 1
	fi
	echoAndLog "${FUNCNAME}(): file imported to database $database"
	return 0
}

# Crea la base de datos
function mysqlCreateDb()
{
	if [ $# -ne 2 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local database=$2

	echoAndLog "${FUNCNAME}(): creating database..."
	mysqladmin -u root --password="${root_password}" create $database
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while creating database $database"
		return 1
	fi
	echoAndLog "${FUNCNAME}(): database $database created"
	return 0
}


function mysqlCheckUserExists()
{
	if [ $# -ne 2 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local userdb=$2

	echoAndLog "${FUNCNAME}(): checking if $userdb exists..."
	echo "select user from user where user='${userdb}'\\G" |mysql -uroot -p"${root_password}" mysql | grep user
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): user doesn't exists"
		return 1
	else
		echoAndLog "${FUNCNAME}(): user already exists"
		return 0
	fi

}

# Crea un usuario administrativo para la base de datos
function mysqlCreateAdminUserToDb()
{
	if [ $# -ne 4 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local root_password=$1
	local database=$2
	local userdb=$3
	local passdb=$4

	echoAndLog "${FUNCNAME}(): creating admin user ${userdb} to database ${database}"

	cat > $WORKDIR/create_${database}.sql <<EOF
GRANT USAGE ON *.* TO '${userdb}'@'localhost' IDENTIFIED BY '${passdb}' ;
GRANT ALL PRIVILEGES ON ${database}.* TO '${userdb}'@'localhost' WITH GRANT OPTION ;
FLUSH PRIVILEGES ;
EOF
	mysql -u root --password=${root_password} < $WORKDIR/create_${database}.sql
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while creating user in mysql"
		rm -f $WORKDIR/create_${database}.sql
		return 1
	else
		echoAndLog "${FUNCNAME}(): user created ok"
		rm -f $WORKDIR/create_${database}.sql
		return 0
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

	svn export --force "$url" opengnsys
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error getting OpenGnSys code from $url"
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
	# Arrays globales definidas:
	# - DEVICE:     nombres de dispositivos de red activos.
	# - SERVERIP:   IPs locales del servidor.
	# - NETIP:      IPs de redes.
	# - NETMASK:    máscaras de red.
	# - NETBROAD:   IPs de difusión de redes.
	# - ROUTERIP:   IPs de routers.
	# Otras variables globales:
	# - DEFAULTDEV: dispositivo de red por defecto.
	# - DNSIP:      IP del servidor DNS principal.

	local i=0
	local dev=""

        echoAndLog "${FUNCNAME}(): Detecting network parameters."
	DEVICE=( $(ip -o link show up | awk '!/loopback/ {sub(/:.*/,"",$2); print $2}') )
	if [ -z "$DEVICE" ]; then
		errorAndLog "${FUNCNAME}(): Network devices not detected."
		exit 1
	fi
	for dev in ${DEVICE[*]}; do
		SERVERIP[i]=$(ip -o addr show dev $dev | awk '$3~/inet$/ {sub (/\/.*/, ""); print ($4)}')
		if [ -n "${SERVERIP[i]}" ]; then
			NETMASK[i]=$(LANG=C ifconfig $dev | awk '/Mask/ {sub(/.*:/,"",$4); print $4}')
			NETBROAD[i]=$(ip -o addr show dev $dev | awk '$3~/inet$/ {print ($6)}')
			NETIP[i]=$(netstat -nr | awk -v d="$dev" '$1!~/0\.0\.0\.0/&&$8==d {if (n=="") n=$1} END {print n}')
			ROUTERIP[i]=$(netstat -nr | awk -v d="$dev" '$1~/0\.0\.0\.0/&&$8==d {print $2}')
			DEFAULTDEV=${DEFAULTDEV:-"$dev"}
			let i++
		fi
	done
	DNSIP=$(awk '/nameserver/ {print $2}' /etc/resolv.conf | head -n1)
	if [ -z "${NETIP}[*]" -o -z "${NETMASK[*]}" ]; then
		errorAndLog "${FUNCNAME}(): Network not detected."
		exit 1
	fi

	# Variables de ejecución de Apache
	# - APACHE_RUN_USER
	# - APACHE_RUN_GROUP
	if [ -f $APACHECFGDIR/envvars ]; then
		source $APACHECFGDIR/envvars
	fi
	APACHE_RUN_USER=${APACHE_RUN_USER:-"$APACHEUSER"}
	APACHE_RUN_GROUP=${APACHE_RUN_GROUP:-"$APACHEGROUP"}
}


############################################################
### Esqueleto para el Servicio pxe y contenedor tftpboot ###
############################################################

function tftpConfigure()
{
        echoAndLog "${FUNCNAME}(): Configuring TFTP service."
        # reiniciamos demonio internet ????? porque ????
        /etc/init.d/openbsd-inetd start

        # preparacion contenedor tftpboot
        cp -ar /usr/lib/syslinux/ $TFTPCFGDIR/syslinux
        cp -a /usr/lib/syslinux/pxelinux.0 $TFTPCFGDIR
        # prepamos el directorio de la configuracion de pxe
        mkdir -p $TFTPCFGDIR/pxelinux.cfg
        cat > $TFTPCFGDIR/pxelinux.cfg/default <<EOF
DEFAULT syslinux/vesamenu.c32 
MENU TITLE Aplicacion GNSYS 
 
LABEL 1 
MENU LABEL 1 
KERNEL syslinux/chain.c32 
APPEND hd0 
 
PROMPT 0 
TIMEOUT 10 
EOF
        # comprobamos el servicio tftp
        sleep 1
        testPxe
}

function testPxe ()
{
        echoAndLog "${FUNCNAME}(): Checking TFTP service... please wait."
        cd /tmp
        tftp -v localhost -c get pxelinux.0 /tmp/pxelinux.0 && echoAndLog "TFTP service is OK." || errorAndLog "TFTP service is down."
        cd /
}


########################################################################
## Configuracion servicio Samba
########################################################################
function smbConfigure()
{
	echoAndLog "${FUNCNAME}(): Configuring Samba service."

	backupFile $SAMBACFGDIR/smb.conf
	
	# Copiar plantailla de recursos para OpenGnSys
        sed -e "s/OPENGNSYSDIR/${INSTALL_TARGET//\//\\/}/g" \
		$WORKDIR/opengnsys/server/etc/smb-og.conf.tmpl > $SAMBACFGDIR/smb-og.conf
	# Configurar y recargar Samba"
	perl -pi -e "s/WORKGROUP/OPENGNSYS/; s/server string \=.*/server string \= OpenGnSys Samba Server/; s/^\; *include \=.*$/   include \= \/etc\/samba\/smb-og.conf/" $SAMBACFGDIR/smb.conf
	$SAMBAINIT restart
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while configure Samba"
		return 1
	fi
	# Crear clave para usuario de acceso a los recursos.
	echo -ne "$OPENGNSYS_CLIENT_PASSWD\n$OPENGNSYS_CLIENT_PASSWD\n" | smbpasswd -a -s $OPENGNSYS_CLIENT_USER

	echoAndLog "${FUNCNAME}(): Added Samba configuration."
	return 0
}


########################################################################
## Configuracion servicio DHCP
########################################################################

function dhcpConfigure()
{
	echoAndLog "${FUNCNAME}(): Sample DHCP configuration."

	local errcode=0
	local i=0
	local dev=""

	backupFile $DHCPCFGDIR/dhcpd.conf
	for dev in ${DEVICE[*]}; do
		if [ -n "${SERVERIP[i]}" ]; then
			backupFile $DHCPCFGDIR/dhcpd-$dev.conf
			sed -e "s/SERVERIP/${SERVERIP[$i]}/g" \
			    -e "s/NETIP/${NETIP[$i]}/g" \
			    -e "s/NETMASK/${NETMASK[$i]}/g" \
			    -e "s/NETBROAD/${NETBROAD[$i]}/g" \
			    -e "s/ROUTERIP/${ROUTERIP[$i]}/g" \
			    -e "s/DNSIP/$DNSIP/g" \
			    $WORKDIR/opengnsys/server/etc/dhcpd.conf.tmpl > $DHCPCFGDIR/dhcpd-$dev.conf || errcode=1
		fi
		let i++
	done
	if [ $errcode -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while configuring DHCP server"
		return 1
	fi
	ln -f $DHCPCFGDIR/dhcpd-$DEFAULTDEV.conf $DHCPCFGDIR/dhcpd.conf
	$DHCPINIT restart
	echoAndLog "${FUNCNAME}(): Sample DHCP configured in \"$DHCPCFGDIR\"."
	return 0
}


#####################################################################
####### Funciones específicas de la instalación de Opengnsys
#####################################################################

# Copiar ficheros del OpenGnSys Web Console.
function installWebFiles()
{
	echoAndLog "${FUNCNAME}(): Installing web files..."
	cp -ar $WORKDIR/opengnsys/admin/WebConsole/* $INSTALL_TARGET/www   #*/ comentario para doxigen
	if [ $? != 0 ]; then
		errorAndLog "${FUNCNAME}(): Error copying web files."
		exit 1
	fi
        find $INSTALL_TARGET/www -name .svn -type d -exec rm -fr {} \; 2>/dev/null
	# Descomprimir XAJAX.
	unzip $WORKDIR/opengnsys/admin/xajax_0.5_standard.zip -d $INSTALL_TARGET/www/xajax
	# Cambiar permisos para ficheros especiales.
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/www/images/iconos
	echoAndLog "${FUNCNAME}(): Web files installed successfully."
}

# Configuración específica de Apache.
function openGnsysInstallWebConsoleApacheConf()
{
	if [ $# -ne 2 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local path_opengnsys_base=$1
	local path_apache2_confd=$2
	local path_web_console=${path_opengnsys_base}/www

	if [ ! -d $path_apache2_confd ]; then
		errorAndLog "${FUNCNAME}(): path to apache2 conf.d can not found, verify your server installation"
		return 1
	fi

        mkdir -p $path_apache2_confd/{sites-available,sites-enabled}

	echoAndLog "${FUNCNAME}(): creating apache2 config file.."

	# Activar HTTPS.
	$ENABLESITE default-ssl
	$ENABLEMOD ssl
	make-ssl-cert generate-default-snakeoil --force-overwrite

	# Genera configuración de consola web.
	cat > $path_opengnsys_base/etc/apache.conf <<EOF
# OpenGnSys Web Console configuration for Apache

Alias /opengnsys ${path_web_console}

<Directory ${path_web_console}>
	Options -Indexes FollowSymLinks
	DirectoryIndex acceso.php
</Directory>
EOF

	ln -fs $path_opengnsys_base/etc/apache.conf $path_apache2_confd/sites-available/opengnsys
	$ENABLESITE opengnsys
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): config file can't be linked to apache conf, verify your server installation"
		return 1
	else
		echoAndLog "${FUNCNAME}(): config file created and linked, restarting apache daemon"
		$APACHEINIT restart
		return 0
	fi
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
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local path_opengnsys_base="$1"

	# Crear estructura de directorios.
	echoAndLog "${FUNCNAME}(): creating directory paths in $path_opengnsys_base"
	mkdir -p $path_opengnsys_base
	mkdir -p $path_opengnsys_base/bin
	mkdir -p $path_opengnsys_base/client
	mkdir -p $path_opengnsys_base/doc
	mkdir -p $path_opengnsys_base/etc
	mkdir -p $path_opengnsys_base/lib
	mkdir -p $path_opengnsys_base/log/clients
	ln -fs $path_opengnsys_base/log /var/log/opengnsys
	mkdir -p $path_opengnsys_base/sbin
	mkdir -p $path_opengnsys_base/www
	mkdir -p $path_opengnsys_base/images
	ln -fs /var/lib/tftpboot $path_opengnsys_base
	mkdir -p $path_opengnsys_base/tftpboot/pxelinux.cfg
	mkdir -p $path_opengnsys_base/tftpboot/menu.lst
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while creating dirs. Do you have write permissions?"
		return 1
	fi

	# Crear usuario ficticio.
	if id -u $OPENGNSYS_CLIENT_USER &>/dev/null; then 
		echoAndLog "${FUNCNAME}(): user \"$OPENGNSYS_CLIENT_USER\" is already created"
	else
		echoAndLog "${FUNCNAME}(): creating OpenGnSys user"
		useradd $OPENGNSYS_CLIENT_USER 2>/dev/null
		if [ $? -ne 0 ]; then
			errorAndLog "${FUNCNAME}(): error creating OpenGnSys user"
			return 1
		fi
	fi

	# Establecer los permisos básicos.
	echoAndLog "${FUNCNAME}(): setting directory permissions"
	chmod -R 775 $path_opengnsys_base/{log/clients,images}
	chown -R :$OPENGNSYS_CLIENT_USER $path_opengnsys_base/{log/clients,images}
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while setting permissions"
		return 1
	fi

	echoAndLog "${FUNCNAME}(): directory paths created"
	return 0
}

# Copia ficheros de configuración y ejecutables genéricos del servidor.
function openGnsysCopyServerFiles ()
{
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local path_opengnsys_base="$1"

	local SOURCES=( server/tftpboot \
			server/bin \
			repoman/bin \
			installer/opengnsys_uninstall.sh \
			installer/opengnsys_update.sh \
			doc )
	local TARGETS=( tftpboot \
			bin \
			bin \
			lib \
			lib \
			doc )

	if [ ${#SOURCES[@]} != ${#TARGETS[@]} ]; then
		errorAndLog "${FUNCNAME}(): inconsistent number of array items"
		exit 1
	fi

	echoAndLog "${FUNCNAME}(): copying files to server directories"

	pushd $WORKDIR/opengnsys
	local i
	for (( i = 0; i < ${#SOURCES[@]}; i++ )); do
		if [ -f "${SOURCES[$i]}" ]; then
			echoAndLog "Copying ${SOURCES[$i]} to $path_opengnsys_base/${TARGETS[$i]}"
			cp -a "${SOURCES[$i]}" "${path_opengnsys_base}/${TARGETS[$i]}"
		elif [ -d "${SOURCES[$i]}" ]; then
			echoAndLog "Copying content of ${SOURCES[$i]} to $path_opengnsys_base/${TARGETS[$i]}"
			cp -a "${SOURCES[$i]}"/* "${path_opengnsys_base}/${TARGETS[$i]}"
        else
			echoAndLog "Warning: Unable to copy ${SOURCES[$i]} to $path_opengnsys_base/${TARGETS[$i]}"
		fi
	done
	popd
}

####################################################################
### Funciones de compilación de código fuente de servicios
####################################################################

# Compilar los servicios de OpenGnSys
function servicesCompilation ()
{
	local hayErrores=0

	# Compilar OpenGnSys Server
	echoAndLog "${FUNCNAME}(): Compiling OpenGnSys Admin Server"
	pushd $WORKDIR/opengnsys/admin/Sources/Services/ogAdmServer
	make && mv ogAdmServer $INSTALL_TARGET/sbin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnSys Admin Server"
		hayErrores=1
	fi
	popd
	# Compilar OpenGnSys Repository Manager
	echoAndLog "${FUNCNAME}(): Compiling OpenGnSys Repository Manager"
	pushd $WORKDIR/opengnsys/admin/Sources/Services/ogAdmRepo
	make && mv ogAdmRepo $INSTALL_TARGET/sbin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnSys Repository Manager"
		hayErrores=1
	fi
	popd
	# Compilar OpenGnSys Agent
	echoAndLog "${FUNCNAME}(): Compiling OpenGnSys Agent"
	pushd $WORKDIR/opengnsys/admin/Sources/Services/ogAdmAgent
	make && mv ogAdmAgent $INSTALL_TARGET/sbin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnSys Agent"
		hayErrores=1
	fi
	popd	
	# Compilar OpenGnSys Client
	echoAndLog "${FUNCNAME}(): Compiling OpenGnSys Admin Client"
	pushd $WORKDIR/opengnsys/admin/Sources/Clients/ogAdmClient
	make && mv ogAdmClient ../../../../client/shared/bin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnSys Admin Client"
		hayErrores=1
	fi
	popd

	return $hayErrores
}

####################################################################
### Funciones de copia de la Interface de administración
####################################################################

# Copiar carpeta de Interface
function copyInterfaceAdm ()
{
	local hayErrores=0
	
	# Crear carpeta y copiar Interface
	echoAndLog "${FUNCNAME}(): Copying Administration Interface Folder"
	cp -ar $WORKDIR/opengnsys/admin/Interface $INSTALL_TARGET/client/interfaceAdm
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while copying Administration Interface Folder"
		hayErrores=1
	fi
	chown $OPENGNSYS_CLIENT_USER:$OPENGNSYS_CLIENT_USER $INSTALL_TARGET/client/interfaceAdm/CambiarAcceso
	chmod 700 $INSTALL_TARGET/client/interfaceAdm/CambiarAcceso

	return $hayErrores
}

####################################################################
### Funciones instalacion cliente opengnsys
####################################################################

function openGnsysCopyClientFiles()
{
	local errstatus=0

	echoAndLog "${FUNCNAME}(): Copying OpenGnSys Client files."
	cp -ar $WORKDIR/opengnsys/client/shared/* $INSTALL_TARGET/client
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while copying client estructure"
		errstatus=1
	fi
	find $INSTALL_TARGET/client -name .svn -type d -exec rm -fr {} \; 2>/dev/null
	
	echoAndLog "${FUNCNAME}(): Copying OpenGnSys Cloning Engine files."
	mkdir -p $INSTALL_TARGET/client/lib/engine/bin
	cp -ar $WORKDIR/opengnsys/client/engine/*.lib* $INSTALL_TARGET/client/lib/engine/bin
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while copying engine files"
		errstatus=1
	fi
	
	if [ $errstatus -eq 0 ]; then
		echoAndLog "${FUNCNAME}(): client copy files success."
	else
		errorAndLog "${FUNCNAME}(): client copy files with errors"
	fi

	return $errstatus
}


# Crear cliente OpenGnSys 1.0.2
function clientCreate()
{
	local DOWNLOADURL="http://www.opengnsys.es/downloads"
	local FILENAME=ogLive-natty-2.6.38-8-generic-pae-r2303.iso
	local TARGETFILE=$INSTALL_TARGET/lib/$FILENAME
	local TMPDIR=/tmp/${FILENAME%.iso}
 
	echoAndLog "${FUNCNAME}(): Loading Client"
	# Descargar, montar imagen, copiar cliente ogclient y desmontar.
	wget $DOWNLOADURL/$FILENAME -O $TARGETFILE
	if [ ! -s $TARGETFILE ]; then
		errorAndLog "${FUNCNAME}(): Error loading OpenGnSys Client"
		return 1
	fi
	echoAndLog "${FUNCNAME}(): Copying Client files"
	mkdir -p $TMPDIR
	mount -o loop,ro $TARGETFILE $TMPDIR
	cp -avr $TMPDIR/ogclient $INSTALL_TARGET/tftpboot
	umount $TMPDIR
	rmdir $TMPDIR
	# Asignar la clave cliente para acceso a Samba.
	echoAndLog "${FUNCNAME}(): Set client access key"
	echo -ne "$OPENGNSYS_CLIENT_PASSWD\n$OPENGNSYS_CLIENT_PASSWD\n" | \
			$INSTALL_TARGET/bin/setsmbpass

	# Establecer los permisos.
	find -L $INSTALL_TARGET/tftpboot -type d -exec chmod 755 {} \;
	find -L $INSTALL_TARGET/tftpboot -type f -exec chmod 644 {} \;
	chown -R :$OPENGNSYS_CLIENT_USER $INSTALL_TARGET/tftpboot/ogclient
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/tftpboot/{menu.lst,pxelinux.cfg}

	# Ofrecer md5 del kernel y vmlinuz para ogupdateinitrd en cache
	cp -arv $INSTALL_TARGET/tftpboot/ogclient/ogvmlinuz* $INSTALL_TARGET/tftpboot
	cp -arv $INSTALL_TARGET/tftpboot/ogclient/oginitrd.img* $INSTALL_TARGET/tftpboot

	echoAndLog "${FUNCNAME}(): Client generation success"
}


# Configuración básica de servicios de OpenGnSys
function openGnsysConfigure()
{
	local i=0
	local dev=""

	echoAndLog "${FUNCNAME}(): Copying init files."
	cp -p $WORKDIR/opengnsys/admin/Sources/Services/opengnsys.init /etc/init.d/opengnsys
	cp -p $WORKDIR/opengnsys/admin/Sources/Services/opengnsys.default /etc/default/opengnsys
	cp -p $WORKDIR/opengnsys/admin/Sources/Services/ogAdmRepoAux /opt/opengnsys/sbin/
	update-rc.d opengnsys defaults
	echoAndLog "${FUNCNAME}(): Creating cron files."
	echo "* * * * *   root   [ -x $INSTALL_TARGET/bin/opengnsys.cron ] && $INSTALL_TARGET/bin/opengnsys.cron" > /etc/cron.d/opengnsys
	echo "* * * * *   root   [ -x $INSTALL_TARGET/bin/torrent-creator ] && $INSTALL_TARGET/bin/torrent-creator" > /etc/cron.d/torrentcreator
	echo "5 * * * *   root   [ -x $INSTALL_TARGET/bin/torrent-tracker ] && $INSTALL_TARGET/bin/torrent-tracker" > /etc/cron.d/torrenttracker

	echoAndLog "${FUNCNAME}(): Creating logrotate configuration file."
	sed -e "s/OPENGNSYSDIR/${INSTALL_TARGET//\//\\/}/g" \
		$WORKDIR/opengnsys/server/etc/logrotate.tmpl > /etc/logrotate.d/opengnsys

	echoAndLog "${FUNCNAME}(): Creating OpenGnSys config files."
	for dev in ${DEVICE[*]}; do
		if [ -n "${SERVERIP[i]}" ]; then
			sed -e "s/SERVERIP/${SERVERIP[i]}/g" \
			    -e "s/DBUSER/$OPENGNSYS_DB_USER/g" \
			    -e "s/DBPASSWORD/$OPENGNSYS_DB_PASSWD/g" \
			    -e "s/DATABASE/$OPENGNSYS_DATABASE/g" \
				$WORKDIR/opengnsys/admin/Sources/Services/ogAdmServer/ogAdmServer.cfg > $INSTALL_TARGET/etc/ogAdmServer-$dev.cfg
			sed -e "s/SERVERIP/${SERVERIP[i]}/g" \
				$WORKDIR/opengnsys/admin/Sources/Services/ogAdmRepo/ogAdmRepo.cfg > $INSTALL_TARGET/etc/ogAdmRepo-$dev.cfg
			sed -e "s/SERVERIP/${SERVERIP[i]}/g" \
			    -e "s/DBUSER/$OPENGNSYS_DB_USER/g" \
			    -e "s/DBPASSWORD/$OPENGNSYS_DB_PASSWD/g" \
			    -e "s/DATABASE/$OPENGNSYS_DATABASE/g" \
				$WORKDIR/opengnsys/admin/Sources/Services/ogAdmAgent/ogAdmAgent.cfg > $INSTALL_TARGET/etc/ogAdmAgent-$dev.cfg
			OPENGNSYS_CONSOLEURL="http://${SERVERIP[i]}/opengnsys"
			sed -e "s/SERVERIP/${SERVERIP[i]}/g" \
			    -e "s/DBUSER/$OPENGNSYS_DB_USER/g" \
			    -e "s/DBPASSWORD/$OPENGNSYS_DB_PASSWD/g" \
			    -e "s/DATABASE/$OPENGNSYS_DATABASE/g" \
			    -e "s/OPENGNSYSURL/${OPENGNSYS_CONSOLEURL//\//\\/}/g" \
				$INSTALL_TARGET/www/controlacceso.php > $INSTALL_TARGET/www/controlacceso-$dev.php
			sed -e "s/SERVERIP/${SERVERIP[i]}/g" \
			    -e "s/OPENGNSYSURL/${OPENGNSYS_CONSOLEURL//\//\\/}/g" \
				$WORKDIR/opengnsys/admin/Sources/Clients/ogAdmClient/ogAdmClient.cfg > $INSTALL_TARGET/client/etc/ogAdmClient-$dev.cfg
		fi
		let i++
	done
	ln -f $INSTALL_TARGET/etc/ogAdmServer-$DEFAULTDEV.cfg $INSTALL_TARGET/etc/ogAdmServer.cfg
	ln -f $INSTALL_TARGET/etc/ogAdmRepo-$DEFAULTDEV.cfg $INSTALL_TARGET/etc/ogAdmRepo.cfg
	ln -f $INSTALL_TARGET/etc/ogAdmAgent-$DEFAULTDEV.cfg $INSTALL_TARGET/etc/ogAdmAgent.cfg
	ln -f $INSTALL_TARGET/client/etc/ogAdmClient-$DEFAULTDEV.cfg $INSTALL_TARGET/client/etc/ogAdmClient.cfg
	ln -f $INSTALL_TARGET/www/controlacceso-$DEFAULTDEV.php $INSTALL_TARGET/www/controlacceso.php
	chown root:root $INSTALL_TARGET/etc/{ogAdmServer,ogAdmAgent}*.cfg
	chmod 600 $INSTALL_TARGET/etc/{ogAdmServer,ogAdmAgent}*.cfg
	chown $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/www/controlacceso*.php
	chmod 600 $INSTALL_TARGET/www/controlacceso*.php
	echoAndLog "${FUNCNAME}(): Starting OpenGnSys services."
	/etc/init.d/opengnsys start
}


#####################################################################
#######  Función de resumen informativo de la instalación
#####################################################################

function installationSummary()
{
	# Crear fichero de versión y revisión, si no existe.
	local VERSIONFILE="$INSTALL_TARGET/doc/VERSION.txt"
	local REVISION=$(LANG=C svn info $SVN_URL|awk '/Rev:/ {print "r"$4}')
	[ -f $VERSIONFILE ] || echo "OpenGnSys" >$VERSIONFILE
	perl -pi -e "s/($| r[0-9]*)/ $REVISION/" $VERSIONFILE

	# Mostrar información.
	echo
	echoAndLog "OpenGnSys Installation Summary"
	echo       "=============================="
	echoAndLog "Project version:                  $(cat $VERSIONFILE 2>/dev/null)"
	echoAndLog "Installation directory:           $INSTALL_TARGET"
	echoAndLog "Repository directory:             $INSTALL_TARGET/images"
	echoAndLog "DHCP configuration directory:     $DHCPCFGDIR"
	echoAndLog "TFTP configuration directory:     /var/lib/tftpboot"
	echoAndLog "Samba configuration directory:    /etc/samba"
	echoAndLog "Web Console URL:                  $OPENGNSYS_CONSOLEURL"
	echoAndLog "Web Console admin user:           $OPENGNSYS_DB_USER"
	echoAndLog "Web Console admin password:       $OPENGNSYS_DB_PASSWD"
	echo
	echoAndLog "Post-Installation Instructions:"
	echo       "==============================="
	echoAndLog "Review or edit all configuration files."
	echoAndLog "Insert DHCP configuration data and restart service."
	echoAndLog "Log-in as Web Console admin user."
	echoAndLog " - Review default Organization data and assign default user."
	echoAndLog "Log-in as Web Console organization user."
	echoAndLog " - Insert OpenGnSys data (rooms, computers, menus, etc)."
echo
}



#####################################################################
####### Proceso de instalación de OpenGnSys
#####################################################################

echoAndLog "OpenGnSys installation begins at $(date)"
pushd $WORKDIR

# Detectar datos de auto-configuración del instalador.
autoConfigure

# Detectar parámetros de red y comprobar si hay conexión.
getNetworkSettings
if [ $? -ne 0 ]; then
	errorAndLog "Error reading default network settings."
	exit 1
fi
checkNetworkConnection
if [ $? -ne 0 ]; then
	errorAndLog "Error connecting to server. Causes:"
	errorAndLog " - Network is unreachable, review devices parameters."
	errorAndLog " - You are inside a private network, configure the proxy service."
	errorAndLog " - Server is temporally down, try agian later."
	exit 1
fi

# Detener servicios de OpenGnSys, si están activos previamente.
[ -f /etc/init.d/opengnsys ] && /etc/init.d/opengnsys stop

# Actualizar repositorios
updatePackageList

# Instalación de dependencias (paquetes de sistema operativo).
declare -a notinstalled
checkDependencies DEPENDENCIES notinstalled
if [ $? -ne 0 ]; then
	installDependencies notinstalled
	if [ $? -ne 0 ]; then
		echoAndLog "Error while installing some dependeces, please verify your server installation before continue"
		exit 1
	fi
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

# Compilar código fuente de los servicios de OpenGnSys.
servicesCompilation
if [ $? -ne 0 ]; then
	errorAndLog "Error while compiling OpenGnsys services"
	exit 1
fi

# Copiar carpeta Interface entre administración y motor de clonación.
copyInterfaceAdm
if [ $? -ne 0 ]; then
	errorAndLog "Error while copying Administration Interface"
	exit 1
fi

# Configurando tftp
tftpConfigure

# Configuración Samba
smbConfigure
if [ $? -ne 0 ]; then
	errorAndLog "Error while configuring Samba server!"
	exit 1
fi

# Configuración ejemplo DHCP
dhcpConfigure
if [ $? -ne 0 ]; then
	errorAndLog "Error while copying your dhcp server files!"
	exit 1
fi

# Copiar ficheros de servicios OpenGnSys Server.
openGnsysCopyServerFiles ${INSTALL_TARGET}
if [ $? -ne 0 ]; then
	errorAndLog "Error while copying the server files!"
	exit 1
fi

# Instalar Base de datos de OpenGnSys Admin.
isInArray notinstalled "mysql-server"
if [ $? -eq 0 ]; then
	mysqlSetRootPassword ${MYSQL_ROOT_PASSWORD}
else
	mysqlGetRootPassword
fi

mysqlTestConnection ${MYSQL_ROOT_PASSWORD}
if [ $? -ne 0 ]; then
	errorAndLog "Error while connection to mysql"
	exit 1
fi
mysqlDbExists ${MYSQL_ROOT_PASSWORD} ${OPENGNSYS_DATABASE}
if [ $? -ne 0 ]; then
	echoAndLog "Creating Web Console database"
	mysqlCreateDb ${MYSQL_ROOT_PASSWORD} ${OPENGNSYS_DATABASE}
	if [ $? -ne 0 ]; then
		errorAndLog "Error while creating Web Console database"
		exit 1
	fi
else
	echoAndLog "Web Console database exists, ommiting creation"
fi

mysqlCheckUserExists ${MYSQL_ROOT_PASSWORD} ${OPENGNSYS_DB_USER}
if [ $? -ne 0 ]; then
	echoAndLog "Creating user in database"
	mysqlCreateAdminUserToDb ${MYSQL_ROOT_PASSWORD} ${OPENGNSYS_DATABASE} ${OPENGNSYS_DB_USER} "${OPENGNSYS_DB_PASSWD}"
	if [ $? -ne 0 ]; then
		errorAndLog "Error while creating database user"
		exit 1
	fi

fi

mysqlCheckDbIsEmpty ${MYSQL_ROOT_PASSWORD} ${OPENGNSYS_DATABASE}
if [ $? -eq 0 ]; then
	echoAndLog "Creating tables..."
	if [ -f $WORKDIR/$OPENGNSYS_DB_CREATION_FILE ]; then
		mysqlImportSqlFileToDb ${MYSQL_ROOT_PASSWORD} ${OPENGNSYS_DATABASE} $WORKDIR/$OPENGNSYS_DB_CREATION_FILE
	else
		errorAndLog "Unable to locate $WORKDIR/$OPENGNSYS_DB_CREATION_FILE!!"
		exit 1
	fi
else
	# Si existe fichero ogBDAdmin-VersLocal-VersRepo.sql; aplicar cambios.
	INSTVERSION=$(awk '{print $2}' $INSTALL_TARGET/doc/VERSION.txt)
	REPOVERSION=$(awk '{print $2}' $WORKDIR/opengnsys/doc/VERSION.txt)
	OPENGNSYS_DB_UPDATE_FILE="opengnsys/admin/Database/$OPENGNSYS_DATABASE-$INSTVERSION-$REPOVERSION.sql"
 	if [ -f $WORKDIR/$OPENGNSYS_DB_UPDATE_FILE ]; then
 		echoAndLog "Updating tables from version $INSTVERSION to $REPOVERSION"
 		mysqlImportSqlFileToDb ${MYSQL_ROOT_PASSWORD} ${OPENGNSYS_DATABASE} $WORKDIR/$OPENGNSYS_DB_UPDATE_FILE
 	else
 		echoAndLog "Database unchanged."
 	fi
fi

# copiando paqinas web
installWebFiles
# Generar páqinas web de documentación de la API
makeDoxygenFiles

# creando configuracion de apache2
openGnsysInstallWebConsoleApacheConf $INSTALL_TARGET /etc/apache2
if [ $? -ne 0 ]; then
	errorAndLog "Error configuring Apache for OpenGnSYS Admin"
	exit 1
fi

popd


# Crear la estructura de los accesos al servidor desde el cliente (shared)
openGnsysCopyClientFiles
if [ $? -ne 0 ]; then
	errorAndLog "Error creating client structure"
fi

# Crear la estructura del cliente de OpenGnSys
clientCreate
if [ $? -ne 0 ]; then
	errorAndLog "Error creating client"
	exit 1
fi

# Configuración de servicios de OpenGnSys
openGnsysConfigure

# Mostrar sumario de la instalación e instrucciones de post-instalación.
installationSummary

#rm -rf $WORKDIR
echoAndLog "OpenGnSys installation finished at $(date)"

