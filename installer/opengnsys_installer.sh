#!/bin/bash

#####################################################################
####### Script instalador OpenGnsys
####### autor: Luis Guillén <lguillen@unizar.es>
#####################################################################



####  AVISO: Editar configuración de acceso por defecto a la Base de Datos.
MYSQL_ROOT_PASSWORD="passwordroot"	# Clave root de MySQL
OPENGNSYS_DATABASE="ogAdmBD"		# Nombre de la base datos
OPENGNSYS_DB_USER="usuog"		# Usuario de acceso
OPENGNSYS_DB_PASSWD="passusuog"		# Clave del usuario

####  AVISO: NO EDITAR. 
#### configuración de acceso smb para clientes OG.
OPENGNSYS_CLIENT_USER="opengnsys"		# Nombre del usuario
OPENGNSYS_CLIENT_PASSWD="og"		# Clave del usuario opengnsys



# Sólo ejecutable por usuario root
if [ "$(whoami)" != 'root' ]
then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi

# Detectar sistema operativo del servidor (debe soportar LSB).
OSDISTRIB=$(lsb_release -is 2>/dev/null)
# Array con las dependencias que deben estar instaladas, según de la distribución detectada.
case "$OSDISTRIB" in
	Ubuntu) DEPENDENCIES=( subversion apache2 php5 libapache2-mod-php5 mysql-server php5-mysql nfs-kernel-server dhcp3-server bittorrent tftp-hpa tftpd-hpa syslinux openbsd-inetd update-inetd build-essential g++-multilib libmysqlclient15-dev wget doxygen graphviz bittornado ctorrent samba unzip netpipes debootstrap schroot squashfs-tools )
		;;
	*) 	echo "ERROR: Distribution not supported by OpenGnSys."
		exit 1 ;;
esac

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
        FECHAHORA=`getDateTime`
        echo "$FECHAHORA;$SSH_CLIENT;$1" >> $LOG_FILE
}

function errorAndLog()
{
        echo "ERROR: $1"
        FECHAHORA=`getDateTime`
        echo "$FECHAHORA;$SSH_CLIENT;ERROR: $1" >> $LOG_FILE
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
	dpkg -s $package &>/dev/null | grep Status | grep -qw install
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

	echoAndLog "${FUNCNAME}(): now ${string_deps} will be installed"
	apt-get -y install --force-yes ${string_deps}
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

	local fichero=$1
	local fecha=`date +%Y%m%d`

	if [ ! -f $fichero ]; then
		errorAndLog "${FUNCNAME}(): file $fichero doesn't exists"
		return 1
	fi

	echoAndLog "${FUNCNAME}(): realizando backup de $fichero"

	# realiza una copia de la última configuración como last
	cp -p $fichero "${fichero}-LAST"

	# si para el día no hay backup lo hace, sino no
	if [ ! -f "${fichero}-${fecha}" ]; then
		cp -p $fichero "${fichero}-${fecha}"
	fi

	echoAndLog "${FUNCNAME}(): backup realizado"
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
	local status

	if [ ! -f $sqlfile ]; then
		errorAndLog "${FUNCNAME}(): Unable to locate $sqlfile!!"
		return 1
	fi

	echoAndLog "${FUNCNAME}(): importing sql file to ${database}..."
	chmod 600 $tmpfile
	sed -e "s/SERVERIP/$SERVERIP/g" -e "s/DBUSER/$OPENGNSYS_DB_USER/g" \
	    -e "s/DBPASSWORD/$OPENGNSYS_DB_PASSWD/g" $sqlfile > $tmpfile
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
	# Variables globales definidas:
	# - SERVERIP: IP local del servidor.
	# - NETIP:    IP de la red.
	# - NETMASK:  máscara de red.
	# - NETBROAD: IP de difusión de la red.
	# - ROUTERIP: IP del router.
	# - DNSIP:    IP del servidor DNS.

	local MAINDEV

        echoAndLog "${FUNCNAME}(): Detecting default network parameters."
	MAINDEV=$(ip -o link show up | awk '!/loopback/ {d=d$2} END {sub(/:.*/,"",d); print d}')
	if [ -z "$MAINDEV" ]; then
		errorAndLog "${FUNCNAME}(): Network device not detected."
		exit 1
	fi
	SERVERIP=$(ip -o addr show dev $MAINDEV | awk '$3~/inet$/ {sub (/\/.*/, ""); print ($4)}')
	NETMASK=$(LANG=C ifconfig $MAINDEV | awk '/Mask/ {sub(/.*:/,"",$4); print $4}')
	NETBROAD=$(ip -o addr show dev $MAINDEV | awk '$3~/inet$/ {print ($6)}')
	NETIP=$(netstat -nr | grep $MAINDEV | awk '$1!~/0\.0\.0\.0/ {if (n=="") n=$1} END {print n}')
	ROUTERIP=$(netstat -nr | awk '$1~/0\.0\.0\.0/ {print $2}')
	DNSIP=$(awk '/nameserver/ {print $2}' /etc/resolv.conf | head -n1)
	if [ -z "$NETIP" -o -z "$NETMASK" ]; then
		errorAndLog "${FUNCNAME}(): Network not detected."
		exit 1
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


############################################################
### Esqueleto para el Servicio pxe y contenedor tftpboot ###
############################################################

function tftpConfigure()
{
        local basetftp=/var/lib/tftpboot

        echoAndLog "${FUNCNAME}(): Configuring TFTP service."
        # reiniciamos demonio internet ????? porque ????
        /etc/init.d/openbsd-inetd start

        # preparacion contenedor tftpboot
        cp -ar /usr/lib/syslinux/ ${basetftp}/syslinux
        cp -a /usr/lib/syslinux/pxelinux.0 ${basetftp}
        # prepamos el directorio de la configuracion de pxe
        mkdir -p ${basetftp}/pxelinux.cfg
        cat > ${basetftp}/pxelinux.cfg/default <<EOF
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
        ## damos perfimos de lectura a usuario web.
        chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP ${basetftp}
}

function testPxe ()
{
        echoAndLog "${FUNCNAME}(): Checking TFTP service... please wait."
        cd /tmp
        tftp -v localhost -c get pxelinux.0 /tmp/pxelinux.0 && echoAndLog "TFTP service is OK." || errorAndLog "TFTP service is down."
        cd /
}


########################################################################
## Configuracion servicio NFS
########################################################################

# ADVERTENCIA: usa variables globales NETIP y NETMASK!
function nfsConfigure()
{
	echoAndLog "${FUNCNAME}(): Config nfs server."

	backupFile /etc/exports

	nfsAddExport /opt/opengnsys/client ${NETIP}/${NETMASK}:ro,no_subtree_check,no_root_squash,sync
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while adding nfs client config"
		return 1
	fi

	nfsAddExport /opt/opengnsys/images ${NETIP}/${NETMASK}:rw,no_subtree_check,no_root_squash,sync,crossmnt
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while adding nfs images config"
		return 1
	fi

	nfsAddExport /opt/opengnsys/log/clients ${NETIP}/${NETMASK}:rw,no_subtree_check,no_root_squash,sync
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while adding logging client config"
		return 1
	fi

	nfsAddExport /var/lib/tftpboot ${NETIP}/${NETMASK}:ro,no_subtree_check,no_root_squash,sync
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while adding second filesystem for the pxe ogclient"
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


# ejemplos:
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

	local export="${1}"
	local contador=0
	local cadenaexport

	grep "^${export}" /etc/exports > /dev/null
	if [ $? -eq 0 ]; then
		echoAndLog "${FUNCNAME}(): $export exists in /etc/exports, omiting"
		return 0
	fi

	cadenaexport="${export}"
	for parametro in $*
	do
		if [ $contador -gt 0 ]
		then
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
function smbConfigure()
{
	echoAndLog "${FUNCNAME}(): Configuring Samba service."

	backupFile /etc/samba/smb.conf
	
	# Copiar plantailla de recursos para OpenGnSys
        sed -e "s/OPENGNSYSDIR/${INSTALL_TARGET//\//\\/}/g" \
		$WORKDIR/opengnsys/server/etc/smb-og.conf.tmpl > /etc/samba/smb-og.conf
	# Configurar y recargar Samba"
	perl -pi -e "s/WORKGROUP/OPENGNSYS/; s/server string \=.*/server string \= OpenGnSys Samba Server/; s/^\; *include \=.*$/   include \= \/etc\/samba\/smb-og.conf/" /etc/samba/smb.conf
	/etc/init.d/smbd restart
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

	local DHCPSERVER=/etc/init.d/isc-dhcp-server
	DHCPCONFIG=/etc/dhcp/dhcpd.conf
	if [ ! -x $DHCPSERVER ]; then
		DHCPSERVER=/etc/init.d/dhcp3-server
		DHCPCONFIG=/etc/dhcp3/dhcpd.conf
	fi
	backupFile $DHCPCONFIG

	sed -e "s/SERVERIP/$SERVERIP/g" \
	    -e "s/NETIP/$NETIP/g" \
	    -e "s/NETMASK/$NETMASK/g" \
	    -e "s/NETBROAD/$NETBROAD/g" \
	    -e "s/ROUTERIP/$ROUTERIP/g" \
	    -e "s/DNSIP/$DNSIP/g" \
	    $WORKDIR/opengnsys/server/etc/dhcpd.conf.tmpl > $DHCPCONFIG
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while configuring DHCP server"
		return 1
	fi

	$DHCPSERVER restart
        echoAndLog "${FUNCNAME}(): Sample DHCP configured in file \"$DHCPCONFIG\"."
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


	# genera configuración
	cat > $path_opengnsys_base/etc/apache.conf <<EOF
# OpenGnSys Web Console configuration for Apache

Alias /opengnsys ${path_web_console}

<Directory ${path_web_console}>
	Options -Indexes FollowSymLinks
	DirectoryIndex acceso.php
</Directory>
EOF

	ln -fs $path_opengnsys_base/etc/apache.conf $path_apache2_confd/sites-available/opengnsys.conf
	ln -fs $path_apache2_confd/sites-available/opengnsys.conf $path_apache2_confd/sites-enabled/opengnsys.conf
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): config file can't be linked to apache conf, verify your server installation"
		return 1
	else
		echoAndLog "${FUNCNAME}(): config file created and linked, restarting apache daemon"
		/etc/init.d/apache2 restart
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
	chmod -R 775 $path_opengnsys_base/{log/clients,images,tftpboot/pxelinux.cfg,tftpboot/menu.lst}
	chown -R :$OPENGNSYS_CLIENT_USER $path_opengnsys_base/{log/clients,images,tftpboot/pxelinux.cfg,tftpboot/menu.lst}
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
### Funciones de compilación de códifo fuente de servicios
####################################################################

# Compilar los servicios de OpenGNsys
function servicesCompilation ()
{
	local hayErrores=0

	# Compilar OpenGnSys Server
	echoAndLog "${FUNCNAME}(): Compiling OpenGnSys Admin Server"
	pushd $WORKDIR/opengnsys/admin/Sources/Services/ogAdmServer
	make && make install
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnSys Admin Server"
		hayErrores=1
	fi
	popd
	# Compilar OpenGnSys Repository Manager
	echoAndLog "${FUNCNAME}(): Compiling OpenGnSys Repository Manager"
	pushd $WORKDIR/opengnsys/admin/Sources/Services/ogAdmRepo
	make && make install
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnSys Repository Manager"
		hayErrores=1
	fi
	popd
	# Compilar OpenGnSys Agent
	echoAndLog "${FUNCNAME}(): Compiling OpenGnSys Agent"
	pushd $WORKDIR/opengnsys/admin/Sources/Services/ogAdmAgent
	make && make install
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
	local hayErrores=0

	echoAndLog "${FUNCNAME}(): Copying OpenGnSys Client files."
	cp -ar $WORKDIR/opengnsys/client/shared/* $INSTALL_TARGET/client
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while copying client estructure"
		hayErrores=1
	fi
	find $INSTALL_TARGET/client -name .svn -type d -exec rm -fr {} \; 2>/dev/null
	
	echoAndLog "${FUNCNAME}(): Copying OpenGnSys Cloning Engine files."
	mkdir -p $INSTALL_TARGET/client/lib/engine/bin
	cp -ar $WORKDIR/opengnsys/client/engine/*.lib* $INSTALL_TARGET/client/lib/engine/bin
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while copying engine files"
		hayErrores=1
	fi
	
	if [ $hayErrores -eq 0 ]; then
		echoAndLog "${FUNCNAME}(): client copy files success."
	else
		errorAndLog "${FUNCNAME}(): client copy files with errors"
	fi

	return $hayErrores
}




# Crear antiguo cliente initrd para OpenGnSys 0.10
function openGnsysOldClientCreate()
{
	local OSCODENAME

	local hayErrores=0

	# Cargar Kernel, Initrd y paquetes udeb para la distribución del servidor (o por defecto).
	OSCODENAME=$(lsb_release -cs 2>/dev/null)
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
		echoAndLog "${FUNCNAME}(): Old client generation success."
	else
		errorAndLog "${FUNCNAME}(): Old client generation with errors"
	fi

	return $hayErrores
}


# Crear nuevo cliente OpenGnSys 1.0
function openGnsysClientCreate()
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
	echoAndLog "${FUNCNAME}(): Extranting Client files"
	tar xzvf $TMPFILE -C $INSTALL_TARGET/tftpboot
	rm -f $TMPFILE
	# Usar la versión más reciente del Kernel y del Initrd para el cliente.
	ln -f $(ls $INSTALL_TARGET/tftpboot/ogclient/vmlinuz-*|tail -1) $INSTALL_TARGET/tftpboot/ogclient/ogvmlinuz
	ln -f $(ls $INSTALL_TARGET/tftpboot/ogclient/initrd.img-*|tail -1) $INSTALL_TARGET/tftpboot/ogclient/oginitrd.img 
	# Establecer los permisos.
	chmod -R 755 $INSTALL_TARGET/tftpboot/ogclient
	chown -R :$OPENGNSYS_CLIENT_USER $INSTALL_TARGET/tftpboot/ogclient
	echoAndLog "${FUNCNAME}(): Client generation success"
}


# Configuración básica de servicios de OpenGnSys
function openGnsysConfigure()
{
	echoAndLog "${FUNCNAME}(): Copying init files."
	cp -p $WORKDIR/opengnsys/admin/Sources/Services/opengnsys.init /etc/init.d/opengnsys
	cp -p $WORKDIR/opengnsys/admin/Sources/Services/opengnsys.default /etc/default/opengnsys
	cp -p $WORKDIR/opengnsys/admin/Sources/Services/ogAdmRepoAux /opt/opengnsys/sbin/
	update-rc.d opengnsys defaults
	echoAndLog "${FUNCNAME}(): Creating cron files."
	echo "* * * * *   root   [ -x $INSTALL_TARGET/bin/torrent-creator ] && $INSTALL_TARGET/bin/torrent-creator" > /etc/cron.d/torrentcreator
	echo "5 * * * *   root   [ -x $INSTALL_TARGET/bin/torrent-tracker ] && $INSTALL_TARGET/bin/torrent-tracker" > /etc/cron.d/torrenttracker
	echoAndLog "${FUNCNAME}(): Creating OpenGnSys config file in \"$INSTALL_TARGET/etc\"."
	perl -pi -e "s/SERVERIP/$SERVERIP/g; s/DBUSER/$OPENGNSYS_DB_USER/g; s/DBPASSWORD/$OPENGNSYS_DB_PASSWD/g; s/DATABASE/$OPENGNSYS_DATABASE/g" $INSTALL_TARGET/etc/ogAdmServer.cfg
	perl -pi -e "s/SERVERIP/$SERVERIP/g" $INSTALL_TARGET/etc/ogAdmRepo.cfg
	perl -pi -e "s/SERVERIP/$SERVERIP/g; s/DBUSER/$OPENGNSYS_DB_USER/g; s/DBPASSWORD/$OPENGNSYS_DB_PASSWD/g; s/DATABASE/$OPENGNSYS_DATABASE/g" $INSTALL_TARGET/etc/ogAdmAgent.cfg
	chown root:root $INSTALL_TARGET/etc/{ogAdmServer.cfg,ogAdmAgent.cfg}
	chmod 600 $INSTALL_TARGET/etc/{ogAdmServer.cfg,ogAdmAgent.cfg}
	echoAndLog "${FUNCNAME}(): Creating Web Console config file"
	OPENGNSYS_CONSOLEURL="http://$SERVERIP/opengnsys"
	perl -pi -e "s/SERVERIP/$SERVERIP/g; s/DBUSER/$OPENGNSYS_DB_USER/g; s/DBPASSWORD/$OPENGNSYS_DB_PASSWD/g; s/DATABASE/$OPENGNSYS_DATABASE/g; s/OPENGNSYSURL/${OPENGNSYS_CONSOLEURL//\//\\/}/g" $INSTALL_TARGET/www/controlacceso.php
	chown $APACHE_RUN_USER:$APACHE_RUN_GROUP $INSTALL_TARGET/www/controlacceso.php
	chmod 600 $INSTALL_TARGET/www/controlacceso.php
	sed -e "s/SERVERIP/$SERVERIP/g" -e "s/OPENGNSYSURL/${OPENGNSYS_CONSOLEURL//\//\\/}/g" $WORKDIR/opengnsys/admin/Sources/Clients/ogAdmClient/ogAdmClient.cfg > $INSTALL_TARGET/client/etc/ogAdmClient.cfg
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
	local REVISION=$(LANG=C svn info $SVN_URL|awk '/Revision:/ {print "r"$2}')
	[ -f $VERSIONFILE ] || echo "OpenGnSys" >$VERSIONFILE
	perl -pi -e "s/($| r[0-9]*)/ $REVISION/" $VERSIONFILE

	# Mostrar información.
	echo
	echoAndLog "OpenGnSys Installation Summary"
	echo       "=============================="
	echoAndLog "Project version:                  $(cat $VERSIONFILE 2>/dev/null)"
	echoAndLog "Installation directory:           $INSTALL_TARGET"
	echoAndLog "Repository directory:             $INSTALL_TARGET/images"
	echoAndLog "DHCP configuration file:          $DHCPCONFIG"
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

# Detener servicios de OpenGnSys, si están activos previamente.
[ -f /etc/init.d/opengnsys ] && /etc/init.d/opengnsys stop

# Actualizar repositorios
apt-get update

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

# Configuración NFS
#### (descomentar las siguientes líneas para exportar servicios por NFS)
#nfsConfigure
#if [ $? -ne 0 ]; then
#	errorAndLog "Error while configuring nfs server!"
#	exit 1
#fi

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

# Crear la estructura del antiguo cliente initrd de OpenGnSys 0.10
#### (descomentar las siguientes líneas para generar cliente initrd)
#openGnsysOldClientCreate
#if [ $? -ne 0 ]; then
#	errorAndLog "Warning: cannot create old initrd client"
#fi

# Crear la estructura del cliente de OpenGnSys 1.0
openGnsysClientCreate
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

