#!/bin/bash

#####################################################################
####### Script instalador OpenGnSys
####### autor: Luis Guillén <lguillen@unizar.es>
#####################################################################


####  AVISO: Puede editar configuración de acceso por defecto.
####  WARNING: Edit default access configuration if you wish.
DEFAULT_MYSQL_ROOT_PASSWORD="passwordroot"	# Clave por defecto root de MySQL
DEFAULT_OPENGNSYS_DB_USER="usuog"		    # Usuario por defecto de acceso a la base de datos
DEFAULT_OPENGNSYS_DB_PASSWD="passusuog"		# Clave por defecto de acceso a la base de datos
DEFAULT_OPENGNSYS_CLIENT_PASSWD="og"		# Clave por defecto de acceso del cliente	

# Sólo ejecutable por usuario root
if [ "$(whoami)" != 'root' ]; then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi

echo -e "\\nOpenGnSys Installation"
echo "=============================="

# Clave root de MySQL
while : ; do
	echo -n -e "\\nEnter root password for MySQL (${DEFAULT_MYSQL_ROOT_PASSWORD}): ";
	read MYSQL_ROOT_PASSWORD
	if [ -n "${MYSQL_ROOT_PASSWORD//[a-zA-Z0-9]/}" ]; then # Comprobamos que sea un valor alfanumerico
		echo -e "\\aERROR: Must be alphanumeric, try again..."
	else
		if [ -z $MYSQL_ROOT_PASSWORD ]; then # Si esta vacio ponemos el valor por defecto
			MYSQL_ROOT_PASSWORD=$DEFAULT_MYSQL_ROOT_PASSWORD
		fi
		break
	fi
done

# Usuario de acceso a la base de datos
while : ; do
	echo -n -e "\\nEnter username for OpenGnSys console (${DEFAULT_OPENGNSYS_DB_USER}): "
	read OPENGNSYS_DB_USER
	if [ -n "${OPENGNSYS_DB_USER//[a-zA-Z0-9]/}" ]; then # Comprobamos que sea un valor alfanumerico
		echo -e "\\aERROR: Must be alphanumeric, try again..."
	else
		if [ -z $OPENGNSYS_DB_USER ]; then # Si esta vacio ponemos el valor por defecto
			OPENGNSYS_DB_USER=$DEFAULT_OPENGNSYS_DB_USER
		fi
		break
	fi
done

# Clave de acceso a la base de datos
while : ; do
	echo -n -e "\\nEnter password for OpenGnSys console (${DEFAULT_OPENGNSYS_DB_PASSWD}): "
	read OPENGNSYS_DB_PASSWD
	if [ -n "${OPENGNSYS_DB_PASSWD//[a-zA-Z0-9]/}" ]; then # Comprobamos que sea un valor alfanumerico
		echo -e "\\aERROR: Must be alphanumeric, try again..."
	else
		if [ -z $OPENGNSYS_DB_PASSWD ]; then # Si esta vacio ponemos el valor por defecto
			OPENGNSYS_DB_PASSWD=$DEFAULT_OPENGNSYS_DB_PASSWD
		fi
		break
	fi
done

# Clave de acceso del cliente
while : ; do
	echo -n -e "\\nEnter root password for OpenGnSys client (${DEFAULT_OPENGNSYS_CLIENT_PASSWD}): "
	read OPENGNSYS_CLIENT_PASSWD
	if [ -n "${OPENGNSYS_CLIENT_PASSWD//[a-zA-Z0-9]/}" ]; then # Comprobamos que sea un valor alfanumerico
		echo -e "\\aERROR: Must be alphanumeric, try again..."
	else
		if [ -z $OPENGNSYS_CLIENT_PASSWD ]; then # Si esta vacio ponemos el valor por defecto
			OPENGNSYS_CLIENT_PASSWD=$DEFAULT_OPENGNSYS_CLIENT_PASSWD
		fi
		break
	fi
done

echo -e "\\n=============================="

# Comprobar si se ha descargado el paquete comprimido (USESVN=0) o sólo el instalador (USESVN=1).
PROGRAMDIR=$(readlink -e $(dirname "$0"))
PROGRAMNAME=$(basename "$0")
OPENGNSYS_SERVER="www.opengnsys.es"
if [ -d "$PROGRAMDIR/../installer" ]; then
	USESVN=0
else
	USESVN=1
fi
SVN_URL="http://$OPENGNSYS_SERVER/svn/branches/version1.0/"

WORKDIR=/tmp/opengnsys_installer
mkdir -p $WORKDIR

# Directorio destino de OpenGnSys.
INSTALL_TARGET=/opt/opengnsys

# Registro de incidencias.
OGLOGFILE=$INSTALL_TARGET/log/${PROGRAMNAME%.sh}.log
LOG_FILE=/tmp/$(basename $OGLOGFILE)

# Usuario del cliente para acceso remoto.
OPENGNSYS_CLIENT_USER="opengnsys"

# Nombre de la base datos y fichero SQL para su creación.
OPENGNSYS_DATABASE="ogAdmBD"
OPENGNSYS_DB_CREATION_FILE=opengnsys/admin/Database/${OPENGNSYS_DATABASE}.sql


#####################################################################
####### Funciones de configuración
#####################################################################

# Generar variables de configuración del instalador
# Variables globales:
# - OSDISTRIB - tipo de distribución GNU/Linux
# - DEPENDENCIES - array de dependencias que deben estar instaladas
# - UPDATEPKGLIST, INSTALLPKGS, CHECKPKGS - comandos para gestión de paquetes
# - INSTALLEXTRADEPS - instalar dependencias no incluidas en la distribución
# - STARTSERVICE, ENABLESERVICE - iniciar y habilitar un servicio
# - STOPSERVICE, DISABLESERVICE - parar y deshabilitar un servicio
# - APACHESERV, APACHECFGDIR, APACHESITESDIR, APACHEUSER, APACHEGROUP - servicio y configuración de Apache
# - APACHESSLMOD, APACHEENABLESSL, APACHEMAKECERT - habilitar módulo Apache y certificado SSL
# - APACHEENABLEOG, APACHEOGSITE, - habilitar sitio web de OpenGnSys
# - INETDSERV - servicio Inetd
# - FIREWALLSERV - servicio de cortabuegos IPTables/FirewallD
# - DHCPSERV, DHCPCFGDIR - servicio y configuración de DHCP
# - MYSQLSERV, TMPMYCNF - servicio MySQL y fichero temporal con credenciales de acceso
# - MARIADBSERV - servicio MariaDB (sustituto de MySQL en algunas distribuciones)
# - RSYNCSERV, RSYNCCFGDIR - servicio y configuración de Rsync
# - SAMBASERV, SAMBACFGDIR - servicio y configuración de Samba
# - TFTPSERV, TFTPCFGDIR, SYSLINUXDIR - servicio y configuración de TFTP/PXE
function autoConfigure()
{
# Detectar sistema operativo del servidor (compatible con fichero os-release y con LSB).
if [ -f /etc/os-release ]; then
	source /etc/os-release
	OSDISTRIB="$ID"
else
	OSDISTRIB=$(lsb_release -is 2>/dev/null)
fi
# Convertir a minúsculas para evitar errores.
OSDISTRIB="${OSDISTRIB,,}"

# Configuración según la distribución GNU/Linux (usar minúsculas).
case "$OSDISTRIB" in
	ubuntu|debian|linuxmint)
		DEPENDENCIES=( subversion apache2 php5 php5-ldap libapache2-mod-php5 mysql-server php5-mysql isc-dhcp-server bittorrent tftp-hpa tftpd-hpa syslinux xinetd build-essential g++-multilib libmysqlclient15-dev wget doxygen graphviz bittornado ctorrent samba rsync unzip netpipes debootstrap schroot squashfs-tools btrfs-tools procps arp-scan )
		UPDATEPKGLIST="apt-get update"
		INSTALLPKG="apt-get -y install --force-yes"
		CHECKPKG="dpkg -s \$package 2>/dev/null | grep Status | grep -qw install"
		if which service &>/dev/null; then
			STARTSERVICE="eval service \$service restart"
			STOPSERVICE="eval service \$service stop"
		else
			STARTSERVICE="eval /etc/init.d/\$service restart"
			STOPSERVICE="eval /etc/init.d/\$service stop"
		fi
		ENABLESERVICE="eval update-rc.d \$service defaults"
		DISABLESERVICE="eval update-rc.d \$service disable"
		APACHESERV=apache2
		APACHECFGDIR=/etc/apache2
		APACHESITESDIR=sites-available
		APACHEOGSITE=opengnsys
		APACHEUSER="www-data"
		APACHEGROUP="www-data"
		APACHESSLMOD="a2enmod ssl"
		APACHEENABLESSL="a2ensite default-ssl"
		APACHEENABLEOG="a2ensite $APACHEOGSITE"
		APACHEMAKECERT="make-ssl-cert generate-default-snakeoil --force-overwrite"
		DHCPSERV=isc-dhcp-server
		DHCPCFGDIR=/etc/dhcp
		INETDSERV=xinetd
		INETDCFGDIR=/etc/xinetd.d
		MYSQLSERV=mysql
		MARIADBSERV=mariadb
		RSYNCSERV=rsync
		RSYNCCFGDIR=/etc
		SAMBASERV=smbd
		SAMBACFGDIR=/etc/samba
		SYSLINUXDIR=/usr/lib/syslinux
		TFTPCFGDIR=/var/lib/tftpboot
		;;
	fedora|centos)
		DEPENDENCIES=( subversion httpd mod_ssl php php-ldap mysql-server mysql-devel mysql-devel.i686 php-mysql dhcp tftp-server tftp syslinux xinetd binutils gcc gcc-c++ glibc-devel glibc-devel.i686 glibc-static glibc-static.i686 libstdc++ libstdc++.i686 libstdc++-devel.i686 make wget doxygen graphviz ctorrent samba samba-client rsync unzip debootstrap schroot squashfs-tools python-crypto arp-scan )
		INSTALLEXTRADEPS=( 'rpm -Uv ftp://ftp.altlinux.org/pub/distributions/ALTLinux/5.1/branch/files/i586/RPMS/netpipes-4.2-alt1.i586.rpm' 
				   'pushd /tmp; wget http://download.bittornado.com/download/BitTornado-0.3.18.tar.gz; tar xvzf BitTornado-0.3.18.tar.gz; cd BitTornado-CVS; python setup.py install; ln -fs btlaunchmany.py /usr/bin/btlaunchmany; ln -fs bttrack.py /usr/bin/bttrack; popd' )
		if [ "$OSDISTRIB" == "centos" ]; then
			UPDATEPKGLIST='test rpm -q --quiet epel-release || echo -e "[epel]\nname=EPEL temporal\nmirrorlist=https://mirrors.fedoraproject.org/metalink?repo=epel-\$releasever&arch=\$basearch\nenabled=1\ngpgcheck=0" >/etc/yum.repos.d/epel.repo'
		fi
		INSTALLPKG="yum install -y"
		CHECKPKG="rpm -q --quiet \$package"
		SYSTEMD=$(which systemctl 2>/dev/null)
		if [ -n "$SYSTEMD" ]; then
			STARTSERVICE="eval systemctl start \$service.service"
			STOPSERVICE="eval systemctl stop \$service.service"
			ENABLESERVICE="eval systemctl enable \$service.service"
			DISABLESERVICE="eval systemctl disable \$service.service"
		else
			STARTSERVICE="eval service \$service start"
			STOPSERVICE="eval service \$service stop"
			ENABLESERVICE="eval chkconfig \$service on"
			DISABLESERVICE="eval chkconfig \$service off"
		fi
		APACHESERV=httpd
		APACHECFGDIR=/etc/httpd/conf.d
		APACHEOGSITE=opengnsys.conf
		APACHEUSER="apache"
		APACHEGROUP="apache"
		DHCPSERV=dhcpd
		DHCPCFGDIR=/etc/dhcp
		if firewall-cmd --state &>/dev/null; then
			FIREWALLSERV=firewalld
		else
			FIREWALLSERV=iptables
		fi
		INETDSERV=xinetd
		INETDCFGDIR=/etc/xinetd.d
		MYSQLSERV=mysqld
		MARIADBSERV=mariadb
		RSYNCSERV=rsync
		RSYNCCFGDIR=/etc
		SAMBASERV=smb
		SAMBACFGDIR=/etc/samba
		SYSLINUXDIR=/usr/share/syslinux
		TFTPSERV=tftp
		TFTPCFGDIR=/var/lib/tftpboot
		;;
	"") 	echo "ERROR: Unknown Linux distribution, please install \"lsb_release\" command."
		exit 1 ;;
	*) 	echo "ERROR: Distribution not supported by OpenGnSys."
		exit 1 ;;
esac

# Fichero de credenciales de acceso a MySQL.
TMPMYCNF=/tmp/.my.cnf.$$
}


# Modificar variables de configuración tras instalar paquetes del sistema.
function autoConfigurePost()
{
local f

# Configuraciones específicas para Samba y TFTP en Debian 6.
[ -z "$SYSTEMD" -a ! -e /etc/init.d/$SAMBASERV ] && SAMBASERV=samba
[ ! -e $TFTPCFGDIR ] && TFTPCFGDIR=/srv/tftp

# Configuraciones específicas para SELinux permisivo en distintas versiones.
[ -f /selinux/enforce ] && echo 0 > /selinux/enforce
for f in /etc/sysconfig/selinux /etc/selinux/config; do
	[ -f $f ] && perl -pi -e 's/SELINUX=enforcing/SELINUX=permissive/g' $f
done
selinuxenabled 2>/dev/null && setenforce 0 2>/dev/null
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
	ubuntu|linuxmint)	# Postconfiguación personalizada para Ubuntu.
		# Configuración para DHCP v3.
		DHCPVERSION=$(apt-cache show $(apt-cache pkgnames|egrep "dhcp.?-server$") | \
			      awk '/Version/ {print substr($2,1,1);}' | \
			      sort -n | tail -1)
		if [ $DHCPVERSION = 3 ]; then
			DEPENDENCIES=( ${DEPENDENCIES[@]/isc-dhcp-server/dhcp3-server} )
			DHCPSERV=dhcp3-server
			DHCPCFGDIR=/etc/dhcp3
		fi
		;;
	centos)	# Postconfiguación personalizada para CentOS.
		# Incluir repositorio de paquetes EPEL y paquetes específicos.
		DEPENDENCIES=( ${DEPENDENCIES[@]} epel-release procps )
		;;
	fedora)	# Postconfiguación personalizada para Fedora. 
		# Incluir paquetes específicos.
		DEPENDENCIES=( ${DEPENDENCIES[@]} libstdc++-static.i686 btrfs-progs procps-ng )
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
	local DATETIME=`getDateTime`
	echo "$1"
	echo "$DATETIME;$SSH_CLIENT;$1" >> $LOG_FILE
}

# Escribe a fichero y muestra mensaje de error
function errorAndLog()
{
	local DATETIME=`getDateTime`
	echo "ERROR: $1"
	echo "$DATETIME;$SSH_CLIENT;ERROR: $1" >> $LOG_FILE
}

# Escribe a fichero y muestra mensaje de aviso
function warningAndLog()
{
	local DATETIME=`getDateTime`
	echo "ERROR: $1"
	echo "$DATETIME;$SSH_CLIENT;Warning: $1" >> $LOG_FILE
}

# Comprueba si el elemento pasado en $2 está en el array $1
function isInArray()
{
	if [ $# -ne 2 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local deps
	local is_in_array=1
	local element="$2"

	echoAndLog "${FUNCNAME}(): checking if $2 is in $1"
	eval "deps=( \"\${$1[@]}\" )"

	# Copia local del array del parámetro 1.
	for (( i = 0 ; i < ${#deps[@]} ; i++ )); do
		if [ "${deps[$i]}" = "${element}" ]; then
			echoAndLog "isInArray(): $element found in array"
			is_in_array=0
		fi
	done

	if [ $is_in_array -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): $element NOT found in array"
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

# Recibe array con dependencias
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

	OLD_DEBIAN_FRONTEND=$DEBIAN_FRONTEND		# Debian/Ubuntu
	export DEBIAN_FRONTEND=noninteractive

	echoAndLog "${FUNCNAME}(): now $string_deps will be installed"
	eval $INSTALLPKG $string_deps
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error installing dependencies"
		return 1
	fi

	DEBIAN_FRONTEND=$OLD_DEBIAN_FRONTEND		# Debian/Ubuntu
	test grep -q "EPEL temporal" /etc/yum.repos.d/epel.repo 2>/dev/null ] || mv -f /etc/yum.repos.d/epel.repo.rpmnew /etc/yum.repos.d/epel.repo 2>/dev/null	# CentOS/RedHat EPEL

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
		warningAndLog "${FUNCNAME}(): file $file doesn't exists"
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

	local root_password="$1"
	echoAndLog "${FUNCNAME}(): checking connection to mysql..."
	# Componer fichero con credenciales de conexión a MySQL.
 	touch $TMPMYCNF
 	chmod 600 $TMPMYCNF
 	cat << EOT > $TMPMYCNF
[client]
user=root
password=$root_password
EOT
	# Borrar el fichero temporal si termina el proceso de instalación.
	trap "rm -f $TMPMYCNF" 0 1 2 3 6 9 15
 	# Comprobar conexión a MySQL.
 	echo "" | mysql --defaults-extra-file=$TMPMYCNF
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
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local database="$1"
	echoAndLog "${FUNCNAME}(): checking if $database exists..."
	echo "show databases" | mysql --defaults-extra-file=$TMPMYCNF | grep "^${database}$"
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
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local database="$1"
	echoAndLog "${FUNCNAME}(): checking if $database is empty..."
	num_tablas=`echo "show tables" | mysql --defaults-extra-file=$TMPMYCNF "${database}" | wc -l`
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
	if [ $# -ne 2 ]; then
		errorAndLog "${FNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local database="$1"
	local sqlfile="$2"
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
		if [ "${DEVICE[i]}" == "$DEFAULTDEV" ]; then
			sed -e "s/SERVERIP/${SERVERIP[i]}/g" \
			    -e "s/DBUSER/$OPENGNSYS_DB_USER/g" \
			    -e "s/DBPASSWORD/$OPENGNSYS_DB_PASSWD/g" \
				$sqlfile > $tmpfile
		fi
		let i++
	done
	mysql --defaults-extra-file=$TMPMYCNF --default-character-set=utf8 "${database}" < $tmpfile
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
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local database="$1"

	echoAndLog "${FUNCNAME}(): creating database..."
	mysqladmin --defaults-extra-file=$TMPMYCNF create $database
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while creating database $database"
		return 1
	fi
	echoAndLog "${FUNCNAME}(): database $database created"
	return 0
}


function mysqlCheckUserExists()
{
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local userdb="$1"

	echoAndLog "${FUNCNAME}(): checking if $userdb exists..."
	echo "select user from user where user='${userdb}'\\G" |mysql --defaults-extra-file=$TMPMYCNF mysql | grep user
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
	if [ $# -ne 3 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local database="$1"
	local userdb="$2"
	local passdb="$3"

	echoAndLog "${FUNCNAME}(): creating admin user ${userdb} to database ${database}"

	cat > $WORKDIR/create_${database}.sql <<EOF
GRANT USAGE ON *.* TO '${userdb}'@'localhost' IDENTIFIED BY '${passdb}' ;
GRANT ALL PRIVILEGES ON ${database}.* TO '${userdb}'@'localhost' WITH GRANT OPTION ;
FLUSH PRIVILEGES ;
EOF
	mysql --defaults-extra-file=$TMPMYCNF < $WORKDIR/create_${database}.sql
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
	echoAndLog "${FUNCNAME}(): Disabling Firewall: $FIREWALLSERV."
	if [ -n "$FIREWALLSERV" ]; then
		service=$FIREWALLSERV
		$STOPSERVICE; $DISABLESERVICE
	fi

	echoAndLog "${FUNCNAME}(): Checking OpenGnSys server conectivity."
	OPENGNSYS_SERVER=${OPENGNSYS_SERVER:-"www.opengnsys.es"}
	if which wget &>/dev/null; then
		wget --spider -q $OPENGNSYS_SERVER
	elif which curl &>/dev/null; then
		curl --connect-timeout 10 -s $OPENGNSYS_SERVER -o /dev/null
	else
		echoAndLog "${FUNCNAME}(): Cannot execute \"wget\" nor \"curl\"."
		return 1
	fi
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
			NETMASK[i]=$(LANG=C ifconfig $dev | \
						awk '/Mask/ {sub(/.*:/,"",$4); print $4}
						     /netmask/ {print $4}')
			NETBROAD[i]=$(ip -o addr show dev $dev | awk '$3~/inet$/ {print ($6)}')
			NETIP[i]=$(ip route | awk -v d="$dev" '{ if ($3==d) {sub (/\/.*/,""); print $1} }')
			ROUTERIP[i]=$(ip route | awk -v d="$dev" '$1~/default/ { if ($5==d) print $3 }')
			DEFAULTDEV=${DEFAULTDEV:-"$dev"}
		fi
		let i++
	done
	DNSIP=$(awk '/nameserver/ {print $2}' /etc/resolv.conf | head -n1)
	if [ -z "${NETIP[*]}" -o -z "${NETMASK[*]}" ]; then
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

	echoAndLog "${FUNCNAME}(): Default network device: $DEFAULTDEV."
}


############################################################
### Esqueleto para el Servicio pxe y contenedor tftpboot ###
############################################################

function tftpConfigure()
{
	echoAndLog "${FUNCNAME}(): Configuring TFTP service."
	# Habilitar TFTP y reiniciar Inetd.
	if [ -n "$TFTPSERV" ]; then
		if [ -f $INETDCFGDIR/$TFTPSERV ]; then
			perl -pi -e 's/disable.*/disable = no/' $INETDCFGDIR/$TFTPSERV
		else
			service=$TFTPSERV
			$ENABLESERVICE
		fi
	fi
	service=$INETDSERV
	$ENABLESERVICE; $STARTSERVICE

	# preparacion contenedor tftpboot
	cp -a $SYSLINUXDIR $TFTPCFGDIR/syslinux
	cp -a $SYSLINUXDIR/pxelinux.0 $TFTPCFGDIR
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
	pushd /tmp
	tftp -v 127.0.0.1 -c get pxelinux.0 /tmp/pxelinux.0 && echoAndLog "TFTP service is OK." || errorAndLog "TFTP service is down."
	popd
}


########################################################################
## Configuracion servicio NFS
########################################################################

# Configurar servicio NFS.
# ADVERTENCIA: usa variables globales NETIP y NETMASK!
function nfsConfigure()
{
	echoAndLog "${FUNCNAME}(): Config nfs server."
	backupFile /etc/exports

	nfsAddExport $INSTALL_TARGET/client ${NETIP}/${NETMASK}:ro,no_subtree_check,no_root_squash,sync
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while adding NFS client config"
		return 1
	fi

	nfsAddExport $INSTALL_TARGET/images ${NETIP}/${NETMASK}:rw,no_subtree_check,no_root_squash,sync,crossmnt
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while adding NFS images config"
		return 1
	fi

	nfsAddExport $INSTALL_TARGET/log/clients ${NETIP}/${NETMASK}:rw,no_subtree_check,no_root_squash,sync
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while adding logging client config"
		return 1
	fi

	nfsAddExport $INSTALL_TARGET/tftpboot ${NETIP}/${NETMASK}:ro,no_subtree_check,no_root_squash,sync
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while adding second filesystem for the PXE ogclient"
		return 1
	fi

	/etc/init.d/nfs-kernel-server restart
	exportfs -va
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while configure exports"
		return 1
	fi

	echoAndLog "${FUNCNAME}(): Added NFS configuration to file \"/etc/exports\"."
	return 0
}


# Añadir entrada en fichero de configuración del servidor NFS.
# Ejemplos:
#nfsAddExport /opt/opengnsys 192.168.0.0/255.255.255.0:ro,no_subtree_check,no_root_squash,sync
#nfsAddExport /opt/opengnsys 192.168.0.0/255.255.255.0
#nfsAddExport /opt/opengnsys 80.20.2.1:ro 192.123.32.2:rw
function nfsAddExport()
{
	if [ $# -lt 2 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi
	if [ ! -f /etc/exports ]; then
		errorAndLog "${FUNCNAME}(): /etc/exports don't exists"
		return 1
	fi

	local export="$1"
	local contador=0
	local cadenaexport

	grep "^$export" /etc/exports > /dev/null
	if [ $? -eq 0 ]; then
		echoAndLog "${FUNCNAME}(): $export exists in /etc/exports, omiting"
		return 0
	fi

	cadenaexport="${export}"
	for parametro in $*; do
		if [ $contador -gt 0 ]; then
			host=`echo $parametro | awk -F: '{print $1}'`
			options=`echo $parametro | awk -F: '{print $2}'`
			if [ "${host}" == "" ]; then
				errorAndLog "${FUNCNAME}(): host can't be empty"
				return 1
			fi
			cadenaexport="${cadenaexport}\t${host}"

			if [ "${options}" != "" ]; then
				cadenaexport="${cadenaexport}(${options})"
			fi
		fi
		let contador=contador+1
	done

	echo -en "$cadenaexport\n" >> /etc/exports

	echoAndLog "${FUNCNAME}(): add $export to /etc/exports"
	return 0
}


########################################################################
## Configuracion servicio Samba
########################################################################

# Configurar servicios Samba.
function smbConfigure()
{
	echoAndLog "${FUNCNAME}(): Configuring Samba service."

	backupFile $SAMBACFGDIR/smb.conf
	
	# Copiar plantailla de recursos para OpenGnSys
        sed -e "s/OPENGNSYSDIR/${INSTALL_TARGET//\//\\/}/g" \
		$WORKDIR/opengnsys/server/etc/smb-og.conf.tmpl > $SAMBACFGDIR/smb-og.conf
	# Configurar y recargar Samba"
	perl -pi -e "s/WORKGROUP/OPENGNSYS/; s/server string \=.*/server string \= OpenGnSys Samba Server/" $SAMBACFGDIR/smb.conf
	if ! grep -q "smb-og" $SAMBACFGDIR/smb.conf; then
		echo "include = $SAMBACFGDIR/smb-og.conf" >> $SAMBACFGDIR/smb.conf
	fi
	service=$SAMBASERV
	$ENABLESERVICE; $STARTSERVICE
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
## Configuracion servicio Rsync
########################################################################

# Configurar servicio Rsync.
function rsyncConfigure()
{
	echoAndLog "${FUNCNAME}(): Configuring Rsync service."

	backupFile $RSYNCCFGDIR/rsyncd.conf

	# Configurar acceso a Rsync.
	sed -e "s/CLIENTUSER/$OPENGNSYS_CLIENT_USER/g" \
		$WORKDIR/opengnsys/repoman/etc/rsyncd.conf.tmpl > $RSYNCCFGDIR/rsyncd.conf
	sed -e "s/CLIENTUSER/$OPENGNSYS_CLIENT_USER/g" \
	    -e "s/CLIENTPASSWORD/$OPENGNSYS_CLIENT_PASSWD/g" \
		$WORKDIR/opengnsys/repoman/etc/rsyncd.secrets.tmpl > $RSYNCCFGDIR/rsyncd.secrets
	chown root.root $RSYNCCFGDIR/rsyncd.secrets
	chmod 600 $RSYNCCFGDIR/rsyncd.secrets

	# Habilitar Rsync y reiniciar Inetd.
	if [ -n "$RSYNCSERV" ]; then
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
		service=$RSYNCSERV $ENABLESERVICE
		service=$INETDSERV $STARTSERVICE
	fi

	echoAndLog "${FUNCNAME}(): Added Rsync configuration."
	return 0
}

	
########################################################################
## Configuracion servicio DHCP
########################################################################

# Configurar servicios DHCP.
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
			sed -e "s/SERVERIP/${SERVERIP[i]}/g" \
			    -e "s/NETIP/${NETIP[i]}/g" \
			    -e "s/NETMASK/${NETMASK[i]}/g" \
			    -e "s/NETBROAD/${NETBROAD[i]}/g" \
			    -e "s/ROUTERIP/${ROUTERIP[i]}/g" \
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
	service=$DHCPSERV
	$ENABLESERVICE; $STARTSERVICE
	echoAndLog "${FUNCNAME}(): Sample DHCP configured in \"$DHCPCFGDIR\"."
	return 0
}


#####################################################################
####### Funciones específicas de la instalación de Opengnsys
#####################################################################

# Copiar ficheros del OpenGnSys Web Console.
function installWebFiles()
{
	local COMPATDIR f

	echoAndLog "${FUNCNAME}(): Installing web files..."
	# Copiar ficheros.
	cp -a $WORKDIR/opengnsys/admin/WebConsole/* $INSTALL_TARGET/www   #*/ comentario para Doxygen.
	if [ $? != 0 ]; then
		errorAndLog "${FUNCNAME}(): Error copying web files."
		exit 1
	fi
        find $INSTALL_TARGET/www -name .svn -type d -exec rm -fr {} \; 2>/dev/null
	# Descomprimir XAJAX.
	unzip -o $WORKDIR/opengnsys/admin/xajax_0.5_standard.zip -d $INSTALL_TARGET/www/xajax
	# Compatibilidad con dispositivos móviles.
	COMPATDIR="$INSTALL_TARGET/www/principal"
	for f in acciones administracion aula aulas hardwares imagenes menus repositorios softwares; do
		sed 's/clickcontextualnodo/clicksupnodo/g' $COMPATDIR/$f.php > $COMPATDIR/$f.device.php
	done
	cp -a $COMPATDIR/imagenes.device.php $COMPATDIR/imagenes.device4.php
	# Cambiar permisos para ficheros especiales.
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/www/images/{fotos,iconos}
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/www/tmp/

	echoAndLog "${FUNCNAME}(): Web files installed successfully."
}

# Configuración específica de Apache.
function installWebConsoleApacheConf()
{
	if [ $# -ne 2 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local path_opengnsys_base="$1"
	local path_apache2_confd="$2"
	local CONSOLEDIR=${path_opengnsys_base}/www

	if [ ! -d $path_apache2_confd ]; then
		errorAndLog "${FUNCNAME}(): path to apache2 conf.d can not found, verify your server installation"
		return 1
	fi

        mkdir -p $path_apache2_confd/{sites-available,sites-enabled}

	echoAndLog "${FUNCNAME}(): creating apache2 config file.."

	# Activar HTTPS.
	$APACHESSLMOD
	$APACHEENABLESSL
	$APACHEMAKECERT

	# Genera configuración de consola web a partir del fichero plantilla.
	if [ -n "$(apachectl -v | grep "2\.[0-2]")" ]; then
		# Configuración para versiones anteriores de Apache.
		sed -e "s/CONSOLEDIR/${CONSOLEDIR//\//\\/}/g" \
			$WORKDIR/opengnsys/server/etc/apache-prev2.4.conf.tmpl > $path_apache2_confd/$APACHESITESDIR/${APACHEOGSITE}
	else
		# Configuración específica a partir de Apache 2.4
		sed -e "s/CONSOLEDIR/${CONSOLEDIR//\//\\/}/g" \
			$WORKDIR/opengnsys/server/etc/apache.conf.tmpl > $path_apache2_confd/$APACHESITESDIR/${APACHEOGSITE}.conf
	fi
	$APACHEENABLEOG
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): config file can't be linked to apache conf, verify your server installation"
		return 1
	else
		echoAndLog "${FUNCNAME}(): config file created and linked, restarting apache daemon"
		service=$APACHESERV
		$ENABLESERVICE; $STARTSERVICE
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
	mkdir -p $TFTPCFGDIR
	ln -fs $TFTPCFGDIR $path_opengnsys_base/tftpboot
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

	# Mover el fichero de registro de instalación al directorio de logs.
	echoAndLog "${FUNCNAME}(): moving installation log file"
	mv $LOG_FILE $OGLOGFILE && LOG_FILE=$OGLOGFILE
	chmod 600 $LOG_FILE

	echoAndLog "${FUNCNAME}(): directory paths created"
	return 0
}

# Copia ficheros de configuración y ejecutables genéricos del servidor.
function copyServerFiles ()
{
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local path_opengnsys_base="$1"

	local SOURCES=( server/tftpboot \
			server/bin \
			repoman/bin \
			admin/Sources/Services/ogAdmServerAux
			admin/Sources/Services/ogAdmRepoAux
			installer/opengnsys_uninstall.sh \
			installer/opengnsys_update.sh \
			doc )
	local TARGETS=( tftpboot \
			bin \
			bin \
			sbin \
			sbin \
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
			warningAndLog "Unable to copy ${SOURCES[$i]} to $path_opengnsys_base/${TARGETS[$i]}"
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

function copyClientFiles()
{
	local errstatus=0

	echoAndLog "${FUNCNAME}(): Copying OpenGnSys Client files."
	cp -a $WORKDIR/opengnsys/client/shared/* $INSTALL_TARGET/client
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while copying client estructure"
		errstatus=1
	fi
	find $INSTALL_TARGET/client -name .svn -type d -exec rm -fr {} \; 2>/dev/null
	
	echoAndLog "${FUNCNAME}(): Copying OpenGnSys Cloning Engine files."
	mkdir -p $INSTALL_TARGET/client/lib/engine/bin
	cp -a $WORKDIR/opengnsys/client/engine/*.lib* $INSTALL_TARGET/client/lib/engine/bin
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


# Crear cliente OpenGnSys 1.0.2 y posteriores.
function clientCreate()
{
	local DOWNLOADURL="http://$OPENGNSYS_SERVER/downloads"
	local FILENAME=ogLive-precise-3.2.0-23-generic-r3257.iso	# 1.0.4-rc2
	#local FILENAME=ogLive-raring-3.8.0-22-generic-r3836.iso	# 1.0.5-rc3
	local TARGETFILE=$INSTALL_TARGET/lib/$FILENAME
	local TMPDIR=/tmp/${FILENAME%.iso}
 
	# Descargar cliente, si es necesario.
	if [ -s $PROGRAMDIR/$FILENAME ]; then
		echoAndLog "${FUNCNAME}(): Moving $PROGRAMDIR/$FILENAME file to $(dirname $TARGETFILE)"
		mv $PROGRAMDIR/$FILENAME $TARGETFILE
	else
		echoAndLog "${FUNCNAME}(): Loading Client"
		wget $DOWNLOADURL/$FILENAME -O $TARGETFILE
	fi
	if [ ! -s $TARGETFILE ]; then
		errorAndLog "${FUNCNAME}(): Error loading OpenGnSys Client"
		return 1
	fi
	# Montar imagen, copiar cliente ogclient y desmontar.
	echoAndLog "${FUNCNAME}(): Copying Client files"
	mkdir -p $TMPDIR
	mount -o loop,ro $TARGETFILE $TMPDIR
	cp -av $TMPDIR/ogclient $INSTALL_TARGET/tftpboot
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
	cp -av $INSTALL_TARGET/tftpboot/ogclient/ogvmlinuz* $INSTALL_TARGET/tftpboot
	cp -av $INSTALL_TARGET/tftpboot/ogclient/oginitrd.img* $INSTALL_TARGET/tftpboot

	echoAndLog "${FUNCNAME}(): Client generation success"
}


# Configuración básica de servicios de OpenGnSys
function openGnsysConfigure()
{
	local i=0
	local dev=""
	local CONSOLEURL

	echoAndLog "${FUNCNAME}(): Copying init files."
	cp -a $WORKDIR/opengnsys/admin/Sources/Services/opengnsys.init /etc/init.d/opengnsys
	cp -a $WORKDIR/opengnsys/admin/Sources/Services/opengnsys.default /etc/default/opengnsys
	echoAndLog "${FUNCNAME}(): Creating cron files."
	echo "* * * * *   root   [ -x $INSTALL_TARGET/bin/opengnsys.cron ] && $INSTALL_TARGET/bin/opengnsys.cron" > /etc/cron.d/opengnsys
	echo "* * * * *   root   [ -x $INSTALL_TARGET/bin/torrent-creator ] && $INSTALL_TARGET/bin/torrent-creator" > /etc/cron.d/torrentcreator
	echo "5 * * * *   root   [ -x $INSTALL_TARGET/bin/torrent-tracker ] && $INSTALL_TARGET/bin/torrent-tracker" > /etc/cron.d/torrenttracker
	echo "* * * * *   root   [ -x $INSTALL_TARGET/bin/deletepreimage ] && $INSTALL_TARGET/bin/deletepreimage" > /etc/cron.d/imagedelete

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
			CONSOLEURL="http://${SERVERIP[i]}/opengnsys"
			sed -e "s/SERVERIP/${SERVERIP[i]}/g" \
			    -e "s/DBUSER/$OPENGNSYS_DB_USER/g" \
			    -e "s/DBPASSWORD/$OPENGNSYS_DB_PASSWD/g" \
			    -e "s/DATABASE/$OPENGNSYS_DATABASE/g" \
			    -e "s/OPENGNSYSURL/${CONSOLEURL//\//\\/}/g" \
				$INSTALL_TARGET/www/controlacceso.php > $INSTALL_TARGET/www/controlacceso-$dev.php
			sed -e "s/SERVERIP/${SERVERIP[i]}/g" \
			    -e "s/OPENGNSYSURL/${CONSOLEURL//\//\\/}/g" \
				$WORKDIR/opengnsys/admin/Sources/Clients/ogAdmClient/ogAdmClient.cfg > $INSTALL_TARGET/client/etc/ogAdmClient-$dev.cfg
			if [ "$dev" == "$DEFAULTDEV" ]; then
				OPENGNSYS_CONSOLEURL="${CONSOLEURL/http:/https:}"
			fi
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
	service="opengnsys"
	$ENABLESERVICE; $STARTSERVICE
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
	echoAndLog "Installation log file:            $LOG_FILE"
	echoAndLog "Repository directory:             $INSTALL_TARGET/images"
	echoAndLog "DHCP configuration directory:     $DHCPCFGDIR"
	echoAndLog "TFTP configuration directory:     $TFTPCFGDIR"
	echoAndLog "Samba configuration directory:    $SAMBACFGDIR"
	echoAndLog "Web Console URL:                  $OPENGNSYS_CONSOLEURL"
	echoAndLog "Web Console access data:          specified in installer script"
	echo
	echoAndLog "Post-Installation Instructions:"
	echo       "==============================="
	echoAndLog "Firewall service has been disabled and SELinux mode set to"
	echoAndLog "   permissive during OpenGnSys installation. Please check"
	echoAndLog "   $FIREWALLSERV and SELinux configuration, if needed."
	echoAndLog "Review or edit all configuration files."
	echoAndLog "Insert DHCP configuration data and restart service."
	echoAndLog "Optional: Log-in as Web Console admin user."
	echoAndLog " - Review default Organization data and assign access to users."
	echoAndLog "Log-in as Web Console organization user."
	echoAndLog " - Insert OpenGnSys data (labs, computers, menus, etc)."
echo
}



#####################################################################
####### Proceso de instalación de OpenGnSys
#####################################################################

echoAndLog "OpenGnSys installation begins at $(date)"
pushd $WORKDIR

# Detectar datos iniciales de auto-configuración del instalador.
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
if [ -n "$INSTALLEXTRADEPS" ]; then
	echoAndLog "Installing extra dependencies"
	for (( i=0; i<${#INSTALLEXTRADEPS[*]}; i++ )); do
		eval ${INSTALLEXTRADEPS[i]}
	done
fi	

# Detectar datos de auto-configuración después de instalar paquetes.
autoConfigurePost

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

# Configuración de TFTP.
tftpConfigure

# Configuración de Samba.
smbConfigure
if [ $? -ne 0 ]; then
	errorAndLog "Error while configuring Samba server!"
	exit 1
fi

# Configuración de Rsync.
rsyncConfigure

# Configuración ejemplo DHCP.
dhcpConfigure
if [ $? -ne 0 ]; then
	errorAndLog "Error while copying your dhcp server files!"
	exit 1
fi

# Copiar ficheros de servicios OpenGnSys Server.
copyServerFiles ${INSTALL_TARGET}
if [ $? -ne 0 ]; then
	errorAndLog "Error while copying the server files!"
	exit 1
fi

# Instalar base de datos de OpenGnSys Admin.
isInArray notinstalled "mysql-server"
if [ $? -eq 0 ]; then
	# Habilitar gestor de base de datos (MySQL, si falla, MariaDB).
	service=$MYSQLSERV
	$ENABLESERVICE
	if [ $? != 0 ]; then
		service=$MARIADBSERV
		$ENABLESERVICE
	fi
	# Activar gestor de base de datos.
	$STARTSERVICE
	# Asignar clave del usuario "root".
	mysqlSetRootPassword "${MYSQL_ROOT_PASSWORD}"
else
	# Si ya está instalado el gestor de bases de datos, obtener clave de "root", 
	mysqlGetRootPassword
fi

mysqlTestConnection "${MYSQL_ROOT_PASSWORD}"
if [ $? -ne 0 ]; then
	errorAndLog "Error while connection to mysql"
	exit 1
fi
mysqlDbExists ${OPENGNSYS_DATABASE}
if [ $? -ne 0 ]; then
	echoAndLog "Creating Web Console database"
	mysqlCreateDb ${OPENGNSYS_DATABASE}
	if [ $? -ne 0 ]; then
		errorAndLog "Error while creating Web Console database"
		exit 1
	fi
else
	echoAndLog "Web Console database exists, ommiting creation"
fi

mysqlCheckUserExists ${OPENGNSYS_DB_USER}
if [ $? -ne 0 ]; then
	echoAndLog "Creating user in database"
	mysqlCreateAdminUserToDb ${OPENGNSYS_DATABASE} ${OPENGNSYS_DB_USER} "${OPENGNSYS_DB_PASSWD}"
	if [ $? -ne 0 ]; then
		errorAndLog "Error while creating database user"
		exit 1
	fi

fi

mysqlCheckDbIsEmpty ${OPENGNSYS_DATABASE}
if [ $? -eq 0 ]; then
	echoAndLog "Creating tables..."
	if [ -f $WORKDIR/$OPENGNSYS_DB_CREATION_FILE ]; then
		mysqlImportSqlFileToDb ${OPENGNSYS_DATABASE} $WORKDIR/$OPENGNSYS_DB_CREATION_FILE
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
 		mysqlImportSqlFileToDb ${OPENGNSYS_DATABASE} $WORKDIR/$OPENGNSYS_DB_UPDATE_FILE
 	else
 		echoAndLog "Database unchanged."
 	fi
fi
# Eliminar fichero temporal con credenciales de acceso a MySQL.
rm -f $TMPMYCNF

# copiando paqinas web
installWebFiles
# Generar páqinas web de documentación de la API
makeDoxygenFiles

# creando configuracion de apache2
installWebConsoleApacheConf $INSTALL_TARGET $APACHECFGDIR
if [ $? -ne 0 ]; then
	errorAndLog "Error configuring Apache for OpenGnSys Admin"
	exit 1
fi

popd

# Crear la estructura de los accesos al servidor desde el cliente (shared)
copyClientFiles
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
exit 0

