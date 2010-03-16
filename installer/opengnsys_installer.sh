#!/bin/bash

#####################################################################
####### Script instalador OpenGnsys
####### autor: Luis Guillén <lguillen@unizar.es>
#####################################################################



# Sólo ejecutable por usuario root
if [ "$(whoami)" != 'root' ]
then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi

# Comprobar si se ha descargado el paquete comprimido (USESVN=0) o sólo el instalador (USESVN=1).
PROGRAMDIR=$(readlink -e $(dirname "$0"))
if [ -d "$PROGRAMDIR/../installer" ]; then
    USESVN=0
else
    USESVN=1
    SVN_URL=svn://www.informatica.us.es:3690/opengnsys/trunk
fi

WORKDIR=/tmp/opengnsys_installer
mkdir -p $WORKDIR
pushd $WORKDIR

INSTALL_TARGET=/opt/opengnsys
LOG_FILE=/tmp/opengnsys_installation.log

# Array con las dependencias
DEPENDENCIES=( subversion apache2 php5 mysql-server php5-mysql nfs-kernel-server dhcp3-server udpcast bittorrent tftp-hpa tftpd-hpa syslinux openbsd-inetd update-inetd build-essential libmysqlclient15-dev wget doxygen graphviz bittornado )

MYSQL_ROOT_PASSWORD="passwordroot"

# Datos de base de datos
OPENGNSYS_DATABASE=ogBDAdmin
OPENGNSYS_DB_USER=usuog
OPENGNSYS_DB_PASSWD=passusuog
OPENGNSYS_DB_DEFAULTUSER=opengnsys
OPENGNSYS_DB_DEFAULTPASSWD=opengnsys
OPENGNSYS_DB_CREATION_FILE=opengnsys/admin/Database/ogBDAdmin.sql



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
		errorAndLog "checkPackage(): parameter required"
		exit 1
	fi
	echoAndLog "checkPackage(): checking if package $package exists"
	dpkg -L $package &>/dev/null
	if [ $? -eq 0 ]; then
		echoAndLog "checkPackage(): package $package exists"
		return 0
	else
		echoAndLog "checkPackage(): package $package doesn't exists"
		return 1
	fi
}

# recibe array con dependencias
# por referencia deja un array con las dependencias no resueltas
# devuelve 1 si hay alguna dependencia no resuelta
function checkDependencies()
{
	if [ $# -ne 2 ]; then
		errorAndLog "checkDependencies(): invalid number of parameters"
		exit 1
	fi

	echoAndLog "checkDependencies(): checking dependences"
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
	echoAndLog "checkDependencies(): dependencies uncompleted: $uncompletedeps"
	return $uncompletedeps
}

# Recibe un array con las dependencias y lo instala
function installDependencies()
{
	if [ $# -ne 1 ]; then
		errorAndLog "installDependencies(): invalid number of parameters"
		exit 1
	fi
	echoAndLog "installDependencies(): installing uncompleted dependencies"

	# copia local del array del parametro 1
	local deps
	eval "deps=( \"\${$1[@]}\" )"

	local string_deps=""
	for (( i = 0 ; i < ${#deps[@]} ; i++ ))
	do
		string_deps="$string_deps ${deps[$i]}"
	done

	if [ -z "${string_deps}" ]; then
		errorAndLog "installDependencies(): array of dependeces is empty"
		exit 1
	fi

	OLD_DEBIAN_FRONTEND=$DEBIAN_FRONTEND
	export DEBIAN_FRONTEND=noninteractive

	echoAndLog "installDependencies(): now ${string_deps} will be installed"
	apt-get -y install --force-yes ${string_deps}
	if [ $? -ne 0 ]; then
		errorAndLog "installDependencies(): error installing dependencies"
		return 1
	fi

	DEBIAN_FRONTEND=$OLD_DEBIAN_FRONTEND
	echoAndLog "installDependencies(): dependencies installed"
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
		errorAndLog "mysqlSetRootPassword(): invalid number of parameters"
		exit 1
	fi

	local root_mysql=$1
	echoAndLog "mysqlSetRootPassword(): setting root password in MySQL server"
	/usr/bin/mysqladmin -u root password ${root_mysql}
	if [ $? -ne 0 ]; then
		errorAndLog "mysqlSetRootPassword(): error while setting root password in MySQL server"
		return 1
	fi
	echoAndLog "mysqlSetRootPassword(): root password saved!"
	return 0
}

# Si el servicio mysql esta ya instalado cambia la variable de la clave del root por la ya existente
function mysqlGetRootPassword(){
	local pass_mysql
	local pass_mysql2
	stty -echo
	echo "Existe un servicio mysql ya instalado"
	read -p  "Insertar clave de root de Mysql: " pass_mysql
	echo ""
	read -p "Confirmar clave:" pass_mysql2
	echo ""
	stty echo
	if [ "$pass_mysql" == "$pass_mysql2" ] ;then
		MYSQL_ROOT_PASSWORD=$pass_mysql
		echo "La clave es: ${MYSQL_ROOT_PASSWORD}"
		return 0
	else
		echo "Las claves no coinciden no se configura la clave del servidor de base de datos."
		echo "las operaciones con la base de datos daran error"
		return 1
	fi


}

# comprueba si puede conectar con mysql con el usuario root
function mysqlTestConnection()
{
	if [ $# -ne 1 ]; then
		errorAndLog "mysqlTestConnection(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	echoAndLog "mysqlTestConnection(): checking connection to mysql..."
	echo "" | mysql -uroot -p"${root_password}"
	if [ $? -ne 0 ]; then
		errorAndLog "mysqlTestConnection(): connection to mysql failed, check root password and if daemon is running!"
		return 1
	else
		echoAndLog "mysqlTestConnection(): connection success"
		return 0
	fi
}

# comprueba si la base de datos existe
function mysqlDbExists()
{
	if [ $# -ne 2 ]; then
		errorAndLog "mysqlDbExists(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local database=$2
	echoAndLog "mysqlDbExists(): checking if $database exists..."
	echo "show databases" | mysql -uroot -p"${root_password}" | grep "^${database}$"
	if [ $? -ne 0 ]; then
		echoAndLog "mysqlDbExists():database $database doesn't exists"
		return 1
	else
		echoAndLog "mysqlDbExists():database $database exists"
		return 0
	fi
}

function mysqlCheckDbIsEmpty()
{
	if [ $# -ne 2 ]; then
		errorAndLog "mysqlCheckDbIsEmpty(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local database=$2
	echoAndLog "mysqlCheckDbIsEmpty(): checking if $database is empty..."
	num_tablas=`echo "show tables" | mysql -uroot -p"${root_password}" "${database}" | wc -l`
	if [ $? -ne 0 ]; then
		errorAndLog "mysqlCheckDbIsEmpty(): error executing query, check database and root password"
		exit 1
	fi

	if [ $num_tablas -eq 0 ]; then
		echoAndLog "mysqlCheckDbIsEmpty():database $database is empty"
		return 0
	else
		echoAndLog "mysqlCheckDbIsEmpty():database $database has tables"
		return 1
	fi

}


function mysqlImportSqlFileToDb()
{
	if [ $# -ne 3 ]; then
		errorAndLog "${FNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local database=$2
	local sqlfile=$3

	if [ ! -f $sqlfile ]; then
		errorAndLog "${FUNCNAME}(): Unable to locate $sqlfile!!"
		return 1
	fi

	echoAndLog "${FUNCNAME}(): importing sql file to ${database}..."
	perl -pi -e "s/SERVERIP/$SERVERIP/g; s/DEFAULTUSER/$OPENGNSYS_DB_DEFAULTUSER/g; s/DEFAULTPASSWD/$OPENGNSYS_DB_DEFAULTPASSWD/g" $sqlfile
	mysql -uroot -p"${root_password}" "${database}" < $sqlfile
	if [ $? -ne 0 ]; then
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
		errorAndLog "mysqlCheckUserExists(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local userdb=$2

	echoAndLog "mysqlCheckUserExists(): checking if $userdb exists..."
	echo "select user from user where user='${userdb}'\\G" |mysql -uroot -p"${root_password}" mysql | grep user
	if [ $? -ne 0 ]; then
		echoAndLog "mysqlCheckUserExists(): user doesn't exists"
		return 1
	else
		echoAndLog "mysqlCheckUserExists(): user already exists"
		return 0
	fi

}

# Crea un usuario administrativo para la base de datos
function mysqlCreateAdminUserToDb()
{
	if [ $# -ne 4 ]; then
		errorAndLog "mysqlCreateAdminUserToDb(): invalid number of parameters"
		exit 1
	fi

	local root_password=$1
	local database=$2
	local userdb=$3
	local passdb=$4

	echoAndLog "mysqlCreateAdminUserToDb(): creating admin user ${userdb} to database ${database}"

	cat > $WORKDIR/create_${database}.sql <<EOF
GRANT USAGE ON *.* TO '${userdb}'@'localhost' IDENTIFIED BY '${passdb}' ;
GRANT ALL PRIVILEGES ON ${database}.* TO '${userdb}'@'localhost' WITH GRANT OPTION ;
FLUSH PRIVILEGES ;
EOF
	mysql -u root --password=${root_password} < $WORKDIR/create_${database}.sql
	if [ $? -ne 0 ]; then
		errorAndLog "mysqlCreateAdminUserToDb(): error while creating user in mysql"
		rm -f $WORKDIR/create_${database}.sql
		return 1
	else
		echoAndLog "mysqlCreateAdminUserToDb(): user created ok"
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

	local url=$1

	echoAndLog "${FUNCNAME}(): downloading subversion code..."

	svn export "${url}" opengnsys
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
	NETIP=$(netstat -nr | grep $MAINDEV | awk '$1!~/0\.0\.0\.0/ {print $1}')
	NETIP=${NETIP%% *}
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

function tftpConfigure() {
        echo "Configurando el servicio tftp"
        basetftp=/var/lib/tftpboot

        # reiniciamos demonio internet ????? porque ????
        /etc/init.d/openbsd-inetd start

        # preparacion contenedor tftpboot
        cp -pr /usr/lib/syslinux/ ${basetftp}/syslinux
        cp /usr/lib/syslinux/pxelinux.0 ${basetftp}
        # prepamos el directorio de la configuracion de pxe
        mkdir -p ${basetftp}/pxelinux.cfg
        cat > ${basetftp}/pxelinux.cfg/default <<EOF
DEFAULT pxe

LABEL pxe
KERNEL linux
APPEND initrd=initrd.gz ip=dhcp ro vga=788 irqpoll acpi=on
EOF
        # comprobamos el servicio tftp
        sleep 1
        testPxe
        ## damos perfimos de lectura a usuario web.
        chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP ${basetftp}
}

function testPxe () {
        cd /tmp
        echo "comprobando servicio pxe ..... Espere"
        tftp -v localhost -c get pxelinux.0 /tmp/pxelinux.0 && echo "servidor tftp OK" || echo "servidor tftp KO"
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
## Configuracion servicio DHCP
########################################################################

function dhcpConfigure()
{
        echoAndLog "${FUNCNAME}(): Sample DHCP Configuration."

	backupFile /etc/dhcp3/dhcpd.conf

        sed -e "s/SERVERIP/$SERVERIP/g" \
	    -e "s/NETIP/$NETIP/g" \
	    -e "s/NETMASK/$NETMASK/g" \
	    -e "s/NETBROAD/$NETBROAD/g" \
	    -e "s/ROUTERIP/$ROUTERIP/g" \
	    -e "s/DNSIP/$DNSIP/g" \
	    $WORKDIR/opengnsys/server/DHCP/dhcpd.conf > /etc/dhcp3/dhcpd.conf
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while configuring dhcp server"
		return 1
	fi

	/etc/init.d/dhcp3-server restart
        echoAndLog "${FUNCNAME}(): Sample DHCP Configured in file \"/etc/dhcp3/dhcpd.conf\"."
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
	# Cambiar permisos para ficheros especiales.
	chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP \
			$INSTALL_TARGET/www/includes \
			$INSTALL_TARGET/www/comandos/gestores/filescripts \
			$INSTALL_TARGET/www/images/iconos
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
function openGnsysInstallCreateDirs()
{
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local path_opengnsys_base=$1

	echoAndLog "${FUNCNAME}(): creating directory paths in $path_opengnsys_base"

	mkdir -p $path_opengnsys_base
	mkdir -p $path_opengnsys_base/admin/{autoexec,comandos,menus,usuarios}
	mkdir -p $path_opengnsys_base/bin
	mkdir -p $path_opengnsys_base/client
	mkdir -p $path_opengnsys_base/doc
	mkdir -p $path_opengnsys_base/etc
	mkdir -p $path_opengnsys_base/lib
	mkdir -p $path_opengnsys_base/log/clients
	mkdir -p $path_opengnsys_base/sbin
	mkdir -p $path_opengnsys_base/www
	mkdir -p $path_opengnsys_base/images
	ln -fs /var/lib/tftpboot $path_opengnsys_base
	ln -fs $path_opengnsys_base/log /var/log/opengnsys

	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error while creating dirs. Do you have write permissions?"
		return 1
	fi

	echoAndLog "${FUNCNAME}(): directory paths created"
	return 0
}

# Copia ficheros de configuración y ejecutables genéricos del servidor.
function openGnsysCopyServerFiles () {
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local path_opengnsys_base=$1

	local SOURCES=( client/boot/initrd-generator \
                        client/boot/upgrade-clients-udeb.sh \
                        client/boot/udeblist.conf  \
                        client/boot/udeblist-jaunty.conf  \
                        client/boot/udeblist-karmic.conf \
                        server/PXE/pxelinux.cfg/default \
                        doc )
	local TARGETS=( bin/initrd-generator \
                        bin/upgrade-clients-udeb.sh \
                        etc/udeblist.conf \
                        etc/udeblist-jaunty.conf  \
                        etc/udeblist-karmic.conf \
                        tftpboot/pxelinux.cfg/default \
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
			cp -p "${SOURCES[$i]}" "${path_opengnsys_base}/${TARGETS[$i]}"
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
	pushd $WORKDIR/opengnsys/admin/Services/ogAdmServer
	make && make install
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnSys Admin Server"
		hayErrores=1
	fi
	popd
	# Compilar OpenGnSys Repository Manager
	echoAndLog "${FUNCNAME}(): Compiling OpenGnSys Repository Manager"
	pushd $WORKDIR/opengnsys/admin/Services/ogAdmRepo
	make && make install
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnSys Repository Manager"
		hayErrores=1
	fi
	popd
	# Compilar OpenGnSys Client
	echoAndLog "${FUNCNAME}(): Compiling OpenGnSys Admin Client"
	pushd $WORKDIR/opengnsys/admin/Services/ogAdmClient
	make && mv ogAdmClient ../../../client/nfsexport/bin
	if [ $? -ne 0 ]; then
		echoAndLog "${FUNCNAME}(): error while compiling OpenGnSys Admin Client"
		hayErrores=1
	fi
	popd

	return $hayErrores
}


####################################################################
### Funciones instalacion cliente opengnsys
####################################################################

function openGnsysClientCreate()
{
	local OSDISTRIB OSCODENAME

	local hayErrores=0

	echoAndLog "${FUNCNAME}(): Copying OpenGnSys Client files."
        cp -ar $WORKDIR/opengnsys/client/nfsexport/* $INSTALL_TARGET/client
        find $INSTALL_TARGET/client -name .svn -type d -exec rm -fr {} \; 2>/dev/null
	echoAndLog "${FUNCNAME}(): Copying OpenGnSys Cloning Engine files."
        mkdir -p $INSTALL_TARGET/client/lib/engine/bin
        cp -ar $WORKDIR/opengnsys/client/engine/*.lib $INSTALL_TARGET/client/lib/engine/bin
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


# Configuración básica de servicios de OpenGnSys
function openGnsysConfigure()
{
	echoAndLog "openGnsysConfigure(): Copying init files."
	cp -p $WORKDIR/opengnsys/admin/Services/opengnsys.init /etc/init.d/opengnsys
	cp -p $WORKDIR/opengnsys/admin/Services/opengnsys.default /etc/default/opengnsys
	update-rc.d opengnsys defaults
	echoAndLog "openGnsysConfigure(): Creating OpenGnSys config file in \"$INSTALL_TARGET/etc\"."
	perl -pi -e "s/SERVERIP/$SERVERIP/g" $INSTALL_TARGET/etc/ogAdmServer.cfg
	perl -pi -e "s/SERVERIP/$SERVERIP/g" $INSTALL_TARGET/etc/ogAdmRepo.cfg
	echoAndLog "${FUNCNAME}(): Creating Web Console config file"
	OPENGNSYS_CONSOLEURL="http://$SERVERIP/opengnsys"
	perl -pi -e "s/SERVERIP/$SERVERIP/g; s/OPENGNSYSURL/${OPENGNSYS_CONSOLEURL//\//\\/}/g" $INSTALL_TARGET/www/controlacceso.php
	sed -e "s/SERVERIP/$SERVERIP/g" -e "s/OPENGNSYSURL/${OPENGNSYS_CONSOLEURL//\//\\/}/g" $WORKDIR/opengnsys/admin/Services/ogAdmClient/ogAdmClient.cfg > $INSTALL_TARGET/client/etc/ogAdmClient.cfg
	echoAndLog "openGnsysConfiguration(): Starting OpenGnSys services."
	/etc/init.d/opengnsys start
}


#####################################################################
#######  Función de resumen informativo de la instalación
#####################################################################

function installationSummary(){
	echo
	echoAndLog "OpenGnSys Installation Summary"
	echo       "=============================="
	echoAndLog "Project version:                  $(cat $INSTALL_TARGET/doc/VERSION.txt 2>/dev/null)"
	echoAndLog "Installation directory:           $INSTALL_TARGET"
	echoAndLog "Repository directory:             $INSTALL_TARGET/images"
	echoAndLog "TFTP configuracion directory:     /var/lib/tftpboot"
	echoAndLog "DHCP configuracion file:          /etc/dhcp3/dhcpd.conf"
	echoAndLog "NFS configuracion file:           /etc/exports"
	echoAndLog "Web Console URL:                  $OPENGNSYS_CONSOLEURL"
	echoAndLog "Web Console admin user:           $OPENGNSYS_DB_USER"
	echoAndLog "Web Console admin password:       $OPENGNSYS_DB_PASSWD"
	echoAndLog "Web Console default user:         $OPENGNSYS_DB_DEFAULTUSER"
	echoAndLog "Web Console default password:     $OPENGNSYS_DB_DEFAULTPASSWD"
	echo
	echoAndLog "Post-Installation Instructions:"
	echo       "==============================="
	echoAndLog "Review or edit all configuration files."
	echoAndLog "Insert DHCP configuration data and restart service."
	echoAndLog "Log-in as Web Console admin user."
	echoAndLog " - Review default Organization data and default user."
	echoAndLog "Log-in as Web Console organization user."
	echoAndLog " - Insert OpenGnSys data (rooms, computers, etc)."
echo
}



#####################################################################
####### Proceso de instalación de OpenGnSys
#####################################################################


echoAndLog "OpenGnSys installation begins at $(date)"

# Detectar parámetros de red por defecto
getNetworkSettings
if [ $? -ne 0 ]; then
	errorAndLog "Error reading default network settings."
	exit 1
fi

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
openGnsysInstallCreateDirs ${INSTALL_TARGET}
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

# Configurando tftp
tftpConfigure

# Configuración NFS
nfsConfigure
if [ $? -ne 0 ]; then
	errorAndLog "Error while configuring nfs server!"
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

# Creando la estructura del cliente
openGnsysClientCreate
if [ $? -ne 0 ]; then
	errorAndLog "Error creating clients"
	exit 1
fi

# Configuración de servicios de OpenGnSys
openGnsysConfigure

# Mostrar sumario de la instalación e instrucciones de post-instalación.
installationSummary

#rm -rf $WORKDIR
echoAndLog "OpenGnSys installation finished at $(date)"

