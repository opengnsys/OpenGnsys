#!/bin/bash
#         exportclient str_backupfile
#@file    exportclient
#@brief   Exporta los clientes de un archivo de backup: dhcp, pxe, páginas de inicio y configuración de la consola.
#@param 1 str_backupfile fichero de backup (creado con exportclient)
#@exception 1 Error de formato.
#@exception 2 Sólo ejecutable por usuario root.
#@exception 3 Sin acceso a la configuración de OpenGnsys.
#@exception 4 No existe el directorio de backup.
#@version 1.1.0 - Versión inicial.
#@author  Irina Gómez - ETSII Univ. Sevilla
#@date    2016-10-18
#*/ ##

# Variables globales.
PROG="$(basename $0)"

TMPDIR=/tmp/opengnsys_export
OPENGNSYS="/opt/opengnsys"
MYSQLFILE="$TMPDIR/ogAdmBD.sql"
MYSQLFILE2="$TMPDIR/usuarios.sql"

# Si se solicita, mostrar ayuda.
if [ "$*" == "help" ]; then
    echo -e "$PROG: Exporta los datos de OpenGnsys desde un archivo de backup:" \
           " dhcp, pxe, páginas de inicio y configuración de la consola.\n" \
           "    Formato: $PROG backup_file\n" \
           "    Ejemplo: $PROG backup.tgz"
    exit
fi

# Comprobar parámetros.
# Comprobamos número de parámetros
if [ $# -ne 1 ]; then
    echo "$PROG: ERROR: Error de formato: $PROG backup_file"
    exit 1
fi

if [ "$USER" != "root" ]; then
    echo "$PROG: Error: solo ejecutable por root." >&2
    exit 2
fi

# Comprobamos  acceso a ficheros de configuración
if ! [ -r $OPENGNSYS/etc/ogAdmServer.cfg ]; then
    echo "$PROG: ERROR: Sin acceso a la configuración de OpenGnSys." | tee -a $FILESAL
    exit 3
fi

# Comprobamos que exista el directorio para el archivo de backup
BACKUPDIR=$(realpath $(dirname $1) 2>/dev/null)
! [ $? -eq 0 ] && echo  "$PROG: Error: No existe el directorio para el archivo de backup" && exit 4
BACKUPFILE="$BACKUPDIR/$(basename $1)"

# Si existe el directorio auxiliar lo borramos
[ -d $TMPDIR ] && rm -rf $TMPDIR

# Creamos directorio auxiliar
echo "Creamos directorio auxiliar."
mkdir -p $TMPDIR
chmod 700 $TMPDIR

# Información de la versión
echo "Información de la versión."
cp $OPENGNSYS/doc/VERSION.txt $TMPDIR

# DHCP
echo "Copiamos Configuración del dhcp."
for DHCPCFGDIR in /etc/dhcp /etc/dhcp3; do
    [ -r $DHCPCFGDIR/dhcpd.conf ] && cp $DHCPCFGDIR/dhcpd.conf $TMPDIR
done

# TFTPBOOT
echo "Guardamos los ficheros PXE de los clientes."
cp -r $OPENGNSYS/tftpboot/menu.lst $TMPDIR

# Configuración de los clientes
echo "Guardamos la configuración de los clientes."
cp $OPENGNSYS/client/etc/engine.cfg $TMPDIR

# Páginas de inicio
echo "Guardamos las páginas de inicio."
cp -r $OPENGNSYS/www/menus $TMPDIR

# MYSQL: Excluimos las tablas del servidor de administración (entornos) y repositorios
echo "Exportamos la información de la base de datos."
source $OPENGNSYS/etc/ogAdmServer.cfg
mysqldump --opt -u $USUARIO -p$PASSWORD $CATALOG \
          --ignore-table=${CATALOG}.entornos \
          --ignore-table=${CATALOG}.repositorios \
          --ignore-table=${CATALOG}.usuarios > $MYSQLFILE
# Tabla usuario
mysqldump --opt --no-create-info -u $USUARIO -p$PASSWORD $CATALOG usuarios > $MYSQLFILE2

# IP SERVIDOR
echo $ServidorAdm > $TMPDIR/IPSERVER.txt

# Si existe ya archivo de blackup lo renombramos
[ -r $BACKUPFILE ] && mv $BACKUPFILE $BACKUPFILE-$(date +%Y%M%d)

# Empaquetamos los ficheros
echo "Creamos un archivo comprimido con los datos: $BACKUPFILE."
cd /tmp
tar -czvf $BACKUPFILE ${TMPDIR##*/} &>/dev/null
# Cambio permisos: sólo puede leerlo el root
chmod 600 $BACKUPFILE
