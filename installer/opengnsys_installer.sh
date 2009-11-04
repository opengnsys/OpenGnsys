#!/bin/bash

#####################################################################
####### Script instalador OpenGnsys
####### autor: Luis Guillén <lguillen@unizar.es>
#####################################################################


WORKDIR=/tmp/opengnsys_installer
LOG_FILE=$WORKDIR/installation.log

# Array con las dependencias
DEPENDENCIES=( subversion php5 mysql-server nfs-kernel-server dhcp3-server udpcast bittorrent apache2 php5 mysql-server php5-mysql tftpd-hpa syslinux openbsd-inetd update-inetd build-essential cmake libmysqlclient15-dev qt4-qmake libqt4-dev )

INSTALL_TARGET=/opt/opengnsys

MYSQL_ROOT_PASSWORD="passwordroot"

# conexión al svn
SVN_URL=svn://www.informatica.us.es:3690/opengnsys/trunk

# Datos de base de datos
OPENGNSYS_DATABASE=ogBDAdmin
OPENGNSYS_DB_USER=usuog
OPENGNSYS_DB_PASSWD=passusuog
OPENGNSYS_DB_CREATION_FILE=opengnsys/admin/Database/ogBDAdmin.sql

USUARIO=`whoami`

if [ $USUARIO != 'root' ]
then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi


mkdir -p $WORKDIR
pushd $WORKDIR

#####################################################################
####### Algunas funciones útiles de propósito general:
#####################################################################
getDateTime()
{
        echo `date +%Y%m%d-%H%M%S`
}

# Escribe a fichero y muestra por pantalla
echoAndLog()
{
        echo $1
        FECHAHORA=`getDateTime`
        echo "$FECHAHORA;$SSH_CLIENT;$1" >> $LOG_FILE
}

errorAndLog()
{
        echo "ERROR: $1"
        FECHAHORA=`getDateTime`
        echo "$FECHAHORA;$SSH_CLIENT;ERROR: $1" >> $LOG_FILE
}

# comprueba si el elemento pasado en $2 esta en el array $1
isInArray()
{
	if [ $# -ne 2 ]; then
		errorAndLog "isInArray(): invalid number of parameters"
		exit 1
	fi

	echoAndLog "isInArray(): checking if $2 is in $1"
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
		echoAndLog "isInArray(): $elemento NOT found in array"
	fi

	return $is_in_array

}

#####################################################################
####### Funciones de manejo de paquetes Debian
#####################################################################

checkPackage()
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
checkDependencies()
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
installDependencies()
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

#####################################################################
####### Funciones para el manejo de bases de datos
#####################################################################

# This function set password to root
mysqlSetRootPassword()
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
mysqlGetRootPassword(){
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
		errorAndLog "mysqlImportSqlFileToDb(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local database=$2
	local sqlfile=$3

	if [ ! -f $sqlfile ]; then
		errorAndLog "mysqlImportSqlFileToDb(): Unable to locate $sqlfile!!"
		return 1
	fi

	echoAndLog "mysqlImportSqlFileToDb(): importing sql file to ${database}..."
	mysql -uroot -p"${root_password}" "${database}" < $sqlfile
	if [ $? -ne 0 ]; then
		errorAndLog "mysqlImportSqlFileToDb(): error while importing $sqlfile in database $database"
		return 1
	fi
	echoAndLog "mysqlImportSqlFileToDb(): file imported to database $database"
	return 0
}

# Crea la base de datos
function mysqlCreateDb()
{
	if [ $# -ne 2 ]; then
		errorAndLog "mysqlCreateDb(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local database=$2

	echoAndLog "mysqlCreateDb(): creating database..."
	mysqladmin -u root --password="${root_password}" create $database
	if [ $? -ne 0 ]; then
		errorAndLog "mysqlCreateDb(): error while creating database $database"
		return 1
	fi
	errorAndLog "mysqlCreateDb(): database $database created"
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

svnCheckoutCode()
{
	if [ $# -ne 1 ]; then
		errorAndLog "svnCheckoutCode(): invalid number of parameters"
		exit 1
	fi

	local url=$1

	echoAndLog "svnCheckoutCode(): downloading subversion code..."

	/usr/bin/svn co "${url}" opengnsys
	if [ $? -ne 0 ]; then
		errorAndLog "svnCheckoutCode(): error getting code from ${url}, verify your user and password"
		return 1
	fi
	echoAndLog "svnCheckoutCode(): subversion code downloaded"
	return 0
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
        chown -R www-data:www-data ${basetftp}
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

function nfsConfigure (){
        echoAndLog "nfsConfigure(): Sample NFS Configuration."
        local net_ip net_mask VARS
	VARS=$(route -n | awk '$2~/0\.0\.0\.0/ {print $1,$3}')
	read -e net_ip net_mask <<<"$VARS"
	if [ -z "$net_ip" -o -z "$net_mask" ]; then
		echoAndLog "nfsConfigure(): Network not detected."
		exit 1
	fi
        sed -e "s/NETIP/$net_ip/g" -e "s/NETMASK/$net_mask/g" $WORKDIR/opengnsys/server/NFS/exports  >> /etc/exports
	exportfs -va
        echoAndLog "nfsConfigure(): Sample NFS Configured in file \"/etc/exports\"."
}


########################################################################
## Configuracion servicio DHCP
########################################################################

function dhcpConfigure (){
        echoAndLog "dhcpConfigure(): Sample DHCP Configuration."
	#### PRUEBAS
        cp $WORKDIR/opengnsys/server/DHCP/dhcpd.conf /etc/dhcp3/dhcpd.conf

}


#####################################################################
####### Funciones específicas de la instalación de Opengnsys
#####################################################################

function openGnsysInstallWebConsoleApacheConf()
{
	if [ $# -ne 2 ]; then
		errorAndLog "openGnsysInstallWebConsoleApacheConf(): invalid number of parameters"
		exit 1
	fi

	local path_opengnsys_base=$1
	local path_apache2_confd=$2
	local path_web_console=${path_opengnsys_base}/www

	if [ ! -d $path_apache2_confd ]; then
		errorAndLog "openGnsysInstallWebConsoleApacheConf(): path to apache2 conf.d can not found, verify your server installation"
		return 1
	fi

    [ -d $path_apache2_confd/sites-available ] || mkdir $path_apache2_confd/sites-available
    [ -d $path_apache2_confd/sites-enabled ] || mkdir $path_apache2_confd/sites-enabled

	echoAndLog "openGnsysInstallWebConsoleApacheConf(): creating apache2 config file.."

	# genera configuración
	cat > $path_opengnsys_base/etc/apache.conf <<EOF
# Hidra web interface configuration for Apache

Alias /opengnsys ${path_web_console}

<Directory ${path_web_console}>
	Options -Indexes FollowSymLinks
	DirectoryIndex acceso.php
</Directory>
EOF


	ln -s $path_opengnsys_base/etc/apache.conf $path_apache2_confd/sites-available/opengnsys.conf
	ln -s $path_apache2_confd/sites-available/opengnsys.conf $path_apache2_confd/sites-enabled/opengnsys.conf
	if [ $? -ne 0 ]; then
		errorAndLog "openGnsysInstallWebConsoleApacheConf(): config file can't be linked to apache conf, verify your server installation"
		return 1
	else
		echoAndLog "openGnsysInstallWebConsoleApacheConf(): config file created and linked, restart your apache daemon"
		return 0
	fi
}

# Crea la estructura base de la instalación de opengnsys
function openGnsysInstallCreateDirs()
{
	if [ $# -ne 1 ]; then
		errorAndLog "openGnsysInstallCreateDirs(): invalid number of parameters"
		exit 1
	fi

	local path_opengnsys_base=$1

	echoAndLog "openGnsysInstallCreateDirs(): creating directory paths in $path_opengnsys_base"

	mkdir -p $path_opengnsys_base
	mkdir -p $path_opengnsys_base/admin/{autoexec,comandos,usuarios}
	mkdir -p $path_opengnsys_base/bin
	mkdir -p $path_opengnsys_base/client
	mkdir -p $path_opengnsys_base/etc
	mkdir -p $path_opengnsys_base/lib
	mkdir -p $path_opengnsys_base/log/clients
	mkdir -p $path_opengnsys_base/www
	mkdir -p $path_opengnsys_base/images
	ln -fs /var/lib/tftpboot $path_opengnsys_base
	ln -fs $path_opengnsys_base/log /var/log/opengnsys

	if [ $? -ne 0 ]; then
		errorAndLog "openGnsysInstallCreateDirs(): error while creating dirs. Do you have write permissions?"
		return 1
	fi

	echoAndLog "openGnsysInstallCreateDirs(): directory paths created"
	return 0
}

# Copia ficheros de configuración y ejecutables genéricos del servidor.
function openGnsysCopyServerFiles () {
	if [ $# -ne 1 ]; then
		errorAndLog "openGnsysCopyServerFiles(): invalid number of parameters"
		exit 1
	fi

	local path_opengnsys_base=$1

	local SOURCES=( client/boot/initrd-generator \
                        client/boot/upgrade-clients-udeb.sh \
                        client/boot/udeblist.conf )
	local TARGETS=( bin/initrd-generator \
                        bin/upgrade-clients-udeb.sh \
                        etc/udeblist.conf )

	if [ ${#SOURCES[@]} != ${#TARGETS[@]} ]; then
		errorAndLog "openGnsysCopyServerFiles(): inconsistent number of array items"
		exit 1
	fi

    echoAndLog "openGnsysCopyServerFiles(): copying files to server directories"

    pushd $WORKDIR/opengnsys
	local i
	for (( i = 0; i < ${#SOURCES[@]}; i++ )); do
		if [ -f "${SOURCES[$i]}" ]; then
			echoAndLog "copying ${SOURCES[$i]} to $path_opengnsys_base/${TARGETS[$i]}"
			cp -p "${SOURCES[$i]}" "${path_opengnsys_base}/${TARGETS[$i]}"
		fi
		if [ -d "${SOURCES[$i]}" ]; then
			echoAndLog "openGnsysCopyServerFiles(): copying content of ${SOURCES[$i]} to $path_opengnsys_base/${TARGETS[$i]}"
			cp -pr "${SOURCES[$i]}/*" "${path_opengnsys_base}/${TARGETS[$i]}"
		fi
	done
    popd
}

# Compilar los servicios de OpenGNsys
function servicesCompilation () {
	# Compilar OpenGNSys Server
	echoAndLog "servicesCompilation(): Compiling OpenGNSys Admin Server"
	pushd $WORKDIR/opengnsys/admin/Services/ogAdmServer
	make && make install
	# Compilar OpenGNSys Repository Manager
	echoAndLog "servicesCompilation(): Compiling OpenGNSys Repository Manager"
	popd
	pushd $WORKDIR/opengnsys/admin/Services/ogAdmRepo
	make && make install
	# Compilar OpenGNSys Client
	echoAndLog "servicesCompilation(): Compiling OpenGNSys Admin Client"
	popd
	pushd $WORKDIR/opengnsys/admin/Services/ogAdmClient
	make && mv ogAdmClient ../../../client/nfsexport/bin
	popd
	# Compilar OpenGNSys Client Browser
	echoAndLog "servicesCompilation(): Compiling OpenGNSys Client Browser"
	pushd $WORKDIR/opengnsys/client/browser
	cmake CMakeLists.txt && make && mv browser ../nfsexport/bin
	popd
}


####################################################################
### Funciones instalacion cliente opengnsys
####################################################################

function openGnsysClientCreate () {

	echoAndLog "openGnsysClientCreate(): Copying OpenGNSys Client files."
        cp -ar $WORKDIR/opengnsys/client/nfsexport/* $INSTALL_TARGET/client
        find $INSTALL_TARGET/client -name .svn -type d -exec rm -fr {} \; 2>/dev/null
	echoAndLog "openGnsysClientCreate(): Copying OpenGNSys Cloning Engine files."
        mkdir -p $INSTALL_TARGET/client/lib/engine/bin
        cp -ar $WORKDIR/opengnsys/client/engine/*.lib $INSTALL_TARGET/client/lib/engine/bin
        cp -ar $WORKDIR/opengnsys/client/engine/*.sh $INSTALL_TARGET/client/lib/engine/bin

	echoAndLog "openGnsysClientCreate(): Loading Kernel and Initrd files."
        $INSTALL_TARGET/bin/initrd-generator -t $INSTALL_TARGET/tftpboot/

	echoAndLog "openGnsysClientCreate(): Loading udeb files."
        $INSTALL_TARGET/bin/upgrade-clients-udeb.sh

}



#####################################################################
####### Proceso de instalación de OpenGNSys
#####################################################################

	
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

# Arbol de directorios de OpenGNSys.
openGnsysInstallCreateDirs ${INSTALL_TARGET}
if [ $? -ne 0 ]; then
	errorAndLog "Error while creating directory paths!"
	exit 1
fi

# Descarga del repositorio de código en directorio temporal
svnCheckoutCode $SVN_URL
if [ $? -ne 0 ]; then
	errorAndLog "Error while getting code from svn"
	exit 1
fi

# Compilar código fuente de los servicios de OpenGNSys.
servicesCompilation

# Configurando tftp
tftpConfigure
pxeTest

# Configuración NFS
nfsConfigure

# Configuración ejemplo DHCP
dhcpConfigure

# Copiar ficheros de servicios OpenGNSys Server.
openGnsysCopyServerFiles ${INSTALL_TARGET}
if [ $? -ne 0 ]; then
	errorAndLog "Error while copying the server files!"
	exit 1
fi

# Instalar Base de datos de OpenGNSys Admin.
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
	echoAndLog "Creating web console database"
	mysqlCreateDb ${MYSQL_ROOT_PASSWORD} ${OPENGNSYS_DATABASE}
	if [ $? -ne 0 ]; then
		errorAndLog "Error while creating hidra database"
		exit 1
	fi
else
	echoAndLog "Hidra database exists, ommiting creation"
fi

mysqlCheckUserExists ${MYSQL_ROOT_PASSWORD} ${OPENGNSYS_DB_USER}
if [ $? -ne 0 ]; then
	echoAndLog "Creating user in database"
	mysqlCreateAdminUserToDb ${MYSQL_ROOT_PASSWORD} ${OPENGNSYS_DATABASE} ${OPENGNSYS_DB_USER} "${OPENGNSYS_DB_PASSWD}"
	if [ $? -ne 0 ]; then
		errorAndLog "Error while creating hidra user"
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

# FIXME Configuración del web de OpenGNSys Admin
echoAndLog "Installing web files..."
# copiando paqinas web
cp -pr $WORKDIR/opengnsys/admin/WebConsole/* $INSTALL_TARGET/www   #*/ comentario para doxigen

# creando configuracion de apache2
openGnsysInstallWebConsoleApacheConf $INSTALL_TARGET /etc/apache2
if [ $? -ne 0 ]; then
	errorAndLog "Error while creating hidra apache config"
	exit 1
fi

popd

# Creando la estructura del cliente
openGnsysClientCreate

#rm -rf $WORKDIR
echoAndLog "Process finalized!"




