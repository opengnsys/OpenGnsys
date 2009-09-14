#!/bin/bash

#####################################################################
####### Script instalador OpenGnsys
####### autor: Luis Guillén <lguillen@unizar.es>
#####################################################################


WORKDIR=/tmp/opengnsys_installer
LOG_FILE=$WORKDIR/installation.log

# Array con las dependencias
DEPENDENCIES=( subversion php5 mysql-server nfs-kernel-server dhcp3-server udpcast bittorrent apache2 php5 mysql-server php5-mysql tftpd-hpa syslinux tftp-hpa openbsd-inetd update-inetd )

INSTALL_TARGET=/opt/opengnsys

MYSQL_ROOT_PASSWORD="passwordroot"

# conexión al svn
SVN_URL=svn://www.informatica.us.es:3690/eac-hidra
SVN_USER=lguillen
SVN_PASSWORD=potupass

# Datos de base de datos
HIDRA_DATABASE=bdhidra
HIDRA_DB_USER=usuhidra
HIDRA_DB_PASSWD=passusuhidra
HIDRA_DB_CREATION_FILE=eac-hidra/branches/eac-hidra-us/Hidra/doc/hidra-bd.sql

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
	if [ $# -lt 2 ]; then
		errorAndLog "svnCheckoutCode(): invalid number of parameters"
		exit 1
	fi

	local url=$1
	local user=$2
	if [ $# -gt 2 ]; then
		local password=$3
	else
		local password=""
	fi

	echoAndLog "svnCheckoutCode(): downloading subversion code..."

	/usr/bin/svn co "${url}" --username "${user}" --password "${password}"
	if [ $? -ne 0 ]; then
		errorAndLog "svnCheckoutCode(): error getting code from ${url}, verify your user and password"
		return 1
	fi
	echoAndLog "svnCheckoutCode(): subversion code downloaded"
	return 0
}

#####################################################################
####### Funciones específicas de la instalación de Opengnsys
#####################################################################

function openGnsysInstallHidraApacheConf()
{
	if [ $# -ne 2 ]; then
		errorAndLog "openGnsysInstallHidraApacheConf(): invalid number of parameters"
		exit 1
	fi

	local path_opengnsys_base=$1
	local path_apache2_confd=$2
	local path_web_hidra=${path_opengnsys_base}/www

	echoAndLog "openGnsysInstallHidraApacheConf(): creating apache2 config file.."

	# genera configuración
	cat > $WORKDIR/apache.conf <<EOF
# Hidra web interface configuration for Apache

Alias /hidra ${path_web_hidra}

<Directory ${path_web_hidra}>
	Options -Indexes FollowSymLinks
	DirectoryIndex acceso.php
</Directory>
EOF

	if [ ! -d $path_apache2_confd ]; then
		errorAndLog "openGnsysInstallHidraApacheConf(): path to apache2 conf.d can not found, verify your server installation"
		rm -f $WORKDIR/apache.conf
		return 1
	fi
	cp $WORKDIR/apache.conf $path_opengnsys_base/etc
	ln -s $path_opengnsys_base/etc/apache.conf $path_apache2_confd/hidra.conf
	if [ $? -ne 0 ]; then
		errorAndLog "openGnsysInstallHidraApacheConf(): config file can't be linked to apache conf, verify your server installation"
		rm -f $WORKDIR/apache.conf
		return 1
	else
		echoAndLog "openGnsysInstallHidraApacheConf(): config file created and linked, restart your apache daemon"
		rm -f $WORKDIR/apache.conf
		return 0
	fi
}

# Crea la estructura base de la instalación de opengnsys
openGnsysInstallCreateDirs()
{
	if [ $# -ne 1 ]; then
		errorAndLog "openGnsysInstallCreateDirs(): invalid number of parameters"
		exit 1
	fi

	local path_opengnsys_base=$1

	echoAndLog "openGnsysInstallCreateDirs(): creating directory paths in $path_opengnsys_base"

	mkdir -p $path_opengnsys_base
	mkdir -p $path_opengnsys_base/bin
	mkdir -p $path_opengnsys_base/client
	mkdir -p $path_opengnsys_base/etc
	mkdir -p $path_opengnsys_base/lib
	mkdir -p $path_opengnsys_base/log
	mkdir -p $path_opengnsys_base/www
	mkdir -p $path_opengnsys_base/tftpboot/ogclients
	mkdir -p $path_opengnsys_base/images

	if [ $? -ne 0 ]; then
		errorAndLog "openGnsysInstallCreateDirs(): error while creating dirs. Do you have write permissions?"
		return 1
	fi

	echoAndLog "openGnsysInstallCreateDirs(): directory paths created"
	return 0
}

#####################################################################
####### Proceso de instalación
#####################################################################


# Proceso de instalación de opengnsys
declare -a notinstalled
checkDependencies DEPENDENCIES notinstalled
if [ $? -ne 0 ]; then
	installDependencies notinstalled
	if [ $? -ne 0 ]; then
		echoAndLog "Error while installing some dependeces, please verify your server installation before continue"
		exit 1
	fi
fi

isInArray notinstalled "mysql-server"
if [ $? -eq 0 ]; then
	mysqlSetRootPassword ${MYSQL_ROOT_PASSWORD}
fi

openGnsysInstallCreateDirs ${INSTALL_TARGET}
if [ $? -ne 0 ]; then
	errorAndLog "Error while creating directory paths!"
	exit 1
fi

svnCheckoutCode $SVN_URL $SVN_USER $SVN_PASSWORD
if [ $? -ne 0 ]; then
	errorAndLog "Error while getting code from svn"
	exit 1
fi

mysqlTestConnection ${MYSQL_ROOT_PASSWORD}
if [ $? -ne 0 ]; then
	errorAndLog "Error while connection to mysql"
	exit 1
fi
mysqlDbExists ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE}
if [ $? -ne 0 ]; then
	echoAndLog "Creating hidra database"
	mysqlCreateDb ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE}
	if [ $? -ne 0 ]; then
		errorAndLog "Error while creating hidra database"
		exit 1
	fi
else
	echoAndLog "Hidra database exists, ommiting creation"
fi

mysqlCheckUserExists ${MYSQL_ROOT_PASSWORD} ${HIDRA_DB_USER}
if [ $? -ne 0 ]; then
	echoAndLog "Creating user in database"
	mysqlCreateAdminUserToDb ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE} ${HIDRA_DB_USER} "${HIDRA_DB_PASS}"
	if [ $? -ne 0 ]; then
		errorAndLog "Error while creating hidra user"
		exit 1
	fi

fi

mysqlCheckDbIsEmpty ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE}
if [ $? -eq 0 ]; then
	echoAndLog "Creating tables..."
	if [ -f $WORKDIR/$HIDRA_DB_CREATION_FILE ]; then
		mysqlImportSqlFileToDb ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE} $WORKDIR/$HIDRA_DB_CREATION_FILE
	else
		errorAndLog "Unable to locate $WORKDIR/$HIDRA_DB_CREATION_FILE!!"
		exit 1
	fi
fi

echoAndLog "Installing web files..."
# copiando paqinas web
cp -pr eac-hidra/branches/eac-hidra-us/Hidra/webhidra/* $INSTALL_TARGET/www   #*/ comentario para doxigen

# creando configuracion de apache2
openGnsysInstallHidraApacheConf $INSTALL_TARGET /etc/apache2/conf.d
if [ $? -ne 0 ]; then
	errorAndLog "Error while creating hidra apache config"
	exit 1
fi

popd
rm -rf $WORKDIR
echoAndLog "Process finalized!"


############################################################
########## Servicio pxe y contenedor tftpboot ##############
###########################################################
basetftp=/var/lib/tftpboot
basetftpaux=/tftpboot
basetftpog=/opt/opengnsys/tftpboot
# creamos los correspondientes enlaces hacia nuestro contenedor.
ln -s ${basetftp} ${basetftpog}
ln -s ${basetftpaux} ${basetftpog}

function TestPxe () {
	cd /tmp
	echo "comprobando servidio pxe ..... Espere"
	tftp -v localhost -c get pxelinux.0 /tmp/pxelinux.0 && echo "servidor tftp OK" || echo "servidor tftp KO"
	cd /
}
# reiniciamos demonio internet
/etc/init.d/openbsd-inetd start

##preparcion contendor tftpboot
	cp -pr /usr/lib/syslinux/ ${basetftpboot}/syslinux
	cp /usr/lib/syslinux/pxelinux.0 $basetftpboot
# enlazamos los clientes ogclients al contenedor tftpboo
	ln -s /opt/opengnsys/tftpboot/ogclients ${basetftpboot}/ogclients
# prepamos el directorio de la configuracion de pxe
	mkdir -p ${basetftpboot}/pxelinux.cfg
	touch ${basetftpboot}/pxelinux.cfg/default


	# comprobamos el servicio tftp
sleep 1
TestPxe

## damos perfimos de lectura a usuario web.
chown -R www-data:www-data /var/lib/tftpboot

#########################################################
#########################################################
## continuando la integracion modulo EAC ################
########################################################

WORKDIR=/tmp/opengnsys_installer
pushd $WORKDIR

#EAC_DATABASE=eac
#EAC_DB_USER=eac
#EAC_DB_PASSWD=eac
EAC_DB_CREATION_FILE=eac-hidra/branches/eac-hidra-uma/EAC/admin/config/database/TablasEacforHidra.sql

### borrar cuando subversion actualizado
cp -pr /root/workspace/eac-hidra/branches/eac-hidra-uma/EAC/webeac/ /tmp/opengnsys_installer/eac-hidra/branches/eac-hidra-uma/EAC/
cp /root/workspace/eac-hidra/branches/eac-hidra-uma/EAC/admin/config/database/TablasEacforHidra.sql /tmp/opengnsys_installer/eac-hidra/branches/eac-hidra-uma/EAC/admin/config/database/TablasEacforHidra.sql
cp -pr /root/workspace/eac-hidra/branches/eac-hidra-uma/EAC/webeac/barramenu.php /tmp/opengnsys_installer/eac-hidra/branches/eac-hidra-uma/EAC/webeac/barramenu.php
###



#mysqlDbExists ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE}
#if [ $? -ne 0 ]; then
#	echoAndLog "Creating eac database"
#	mysqlCreateDb ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE}
#	if [ $? -ne 0 ]; then
#		errorAndLog "Error while updating HIDRA database"
#		exit 1
#	fi
#else
#	echoAndLog "hidra database exists, ommiting creation"
#fi

#mysqlCheckUserExists ${MYSQL_ROOT_PASSWORD} ${HIDRA_DB_USER}
#if [ $? -ne 0 ]; then
#	echoAndLog "Creating user in database hidra"
#	mysqlCreateAdminUserToDb ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE} ${HIDRA_DB_USER} "${EAC_DB_PASS}"
#	if [ $? -ne 0 ]; then
#		errorAndLog "Error while creating eac user"
#		exit 1
#	fi

#fi

#mysqlCheckDbIsEmpty ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE}
#if [ $? -eq 0 ]; then
#	echoAndLog "Creating tables..."
#	if [ -f $WORKDIR/$EAC_DB_CREATION_FILE ]; then
		mysqlImportSqlFileToDb ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE} $WORKDIR/$EAC_DB_CREATION_FILE
#	else
#		errorAndLog "Unable to locate $WORKDIR/$EAC_DB_CREATION_FILE!!"
#		exit 1
#	fi
#fi

echoAndLog "Installing web files..."
# copiando paqinas web
cp -pr eac-hidra/branches/eac-hidra-uma/EAC/webeac $INSTALL_TARGET/www/principal/  #*/
cp -pr eac-hidra/branches/eac-hidra-uma/EAC/webeac/barramenu.php $INSTALL_TARGET/www/barramenu.php  #*/

#configurar mysql
echo " comprobar de mysql /etc/mysql/my.cnf"
echo "[mysqld]"
echo "bin-address = 0.0.0.0 "
echo "skip-name-resolve"


### copiando doc a /opt/opengnsys
#cp -pr eac-hidra/trunk/opengnsys-doc/* $path_opengnsys_base #*/
cp /tmp/opengnsys_installer/eac-hidra/trunk/opengnsys-doc/* /opt/opengnsys

# copiar los modelos de startpages /opt/opengnsys/client/etc/startpages
popd
