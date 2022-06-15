#!/bin/bash
#         importclient str_backupfile
#@file    importclient
#@brief   Importa los datos de OpenGnsys de un archivo de backup: dhcp, pxe, páginas de inicio y configuración de la consola.
#@param 1 str_backupfile fichero de backup (creado con exportclient)
#@exception 1 Error de formato
#@exception 2 Sólo ejecutable por usuario root
#@exception 3 Sin acceso al fichero de backup
#@exception 4 Sin acceso a la configuración de OpenGnsys
#@exception 5 Errores al importar o exportar de la bd auxiliar
#@exception 6 Errores al importar los archivos de actualización de la BD desde opengnsys.es
#@exception 7 El archivo de backup ha sido crearo con una versión incompatible de opengnsys_export. Usar 1.1.0-5594 o posterior.
#@note En las versiones de desarrollo (pre) no se modifica la estructura de la base de datos.
#@version 1.1.0 - Versión inicial.
#@author  Irina Gómez - ETSII Univ. Sevilla
#@date    2016-10-18
#@version 1.1.0 - Permite importar de versiones de OpenGnsys anteriores. Cambia la importación de la base de datos.
#@note    Incompatible con versiones de opengnsys_export.sh anteriores a esta fecha.
#@date    2018-02-14
#@version 1.1.1 - Importamos scripts Custom, PXE para UEFI y /etc/default/opengnsys.
#@date    2019-07-18
#*/ ##

# Variables globales.
PROG="$(basename $0)"

DATE=$(date +%Y%m%d)
PREFIX="opengnsys_export"
TMPDIR="/tmp/$PREFIX"
OPENGNSYS="/opt/opengnsys"
MYCNF=$(mktemp /tmp/.my.cnf.XXXXX)
CATALOG="ogAdmBD"
AUXCATALOG="og_import"
MYSQLFILE="$TMPDIR/$CATALOG.sql"
MYSQLBCK="$OPENGNSYS/doc/$CATALOG.sql-$DATE"

LOG_FILE=$OPENGNSYS/log/${PROG%.sh}.log
BRANCH="branches/main"
SVN_URL="https://github.com/opengnsys/OpenGnsys/$BRANCH/admin/Database"
DEFAULT_MYSQL_ROOT_PASSWORD="passwordroot"      # Clave por defecto root de MySQL

# Si se solicita, mostrar ayuda.
if [ "$*" == "help" ]; then
    cat << EOT
$PROG: Importa los datos de OpenGnsys desde un archivo de backup:
  dhcp, pxe, páginas de inicio y configuración de la consola.
    Formato: $PROG [--check] | [--exclude-logs] [--exclude-repos] [--exclude-users] Backup_File
    Opciones:
      --check: comprueba los datos incluidos en el fichero de backup (opcional)
      --exclude-logs: no restaura los ficheros de log (opcional)
      --exclude-repos: no recupera los repositorios definidos (opcional)
      --exclude-users: no recupera los usuarios definidos (opcional)
      Backup_File: camino del fichero de backup a restaurar
    Ejemplo: $PROG backup.tgz
EOT
    exit
fi


####### Funciones ##############################################

# Procesar parámetros de entrada.
function process_params() {
    local options error
    # Retrieve options.
    options=$(getopt -n "$PROG" -l check,exclude-logs,exclude-repos,exclude-users -o '' -- "$@") || error=1
    set -- $options
    # Process options and set flags.
    while [ "$1" ]; do
        case "$1" in
            --check)
                CHECK=1; shift ;;
            --exclude-logs)
                NOLOGS=1; shift ;;
            --exclude-repos)
                NOREPOS=1; shift ;;
            --exclude-users)
                NOUSERS=1; shift ;;
            --)
                shift; break ;;
            *)
                error=1; break ;;
        esac
    done
    [ $# -ne 1 ] && error=1
    if [ "$error" ]; then
        echo "$PROG: ERROR: Usage error: Type \"$PROG help\"" >&2
        exit 1
    fi
    # Backup file.
    eval BACKUPFILE="$1"
}

# Al salir elimina archivos y base de datos temporal
function clean()
{
        mysql --defaults-extra-file=$MYCNF  -e "DROP DATABASE IF EXISTS $AUXCATALOG"
        rm -f $MYCNF
}

function getDateTime()
{
        date "+%Y%m%d-%H%M%S"
}

# Escribe a fichero y muestra por pantalla
function echoAndLog()
{
        echo "      $1"
        DATETIME=`getDateTime`
        echo "$DATETIME;$SSH_CLIENT;$1" >> $LOG_FILE
}

function errorAndLog()
{
        echo "      ERROR: $1"
        DATETIME=`getDateTime`
        echo "$DATETIME;$SSH_CLIENT;ERROR: $1" >> $LOG_FILE
}

function mysqlPassword()
{
    # Clave root de MySQL
    while : ; do
        echo -n -e "\\nEnter root password for MySQL (${DEFAULT_MYSQL_ROOT_PASSWORD}): ";
        read -r MYSQL_ROOT_PASSWORD
        if [ -n "${MYSQL_ROOT_PASSWORD//[a-zA-Z0-9]/}" ]; then # Comprobamos que sea un valor alfanumerico
                echo -e "\\aERROR: Must be alphanumeric, try again..."
        else
                # Si esta vacio ponemos el valor por defecto
                MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-$DEFAULT_MYSQL_ROOT_PASSWORD}"
                break
        fi
    done
    cat << EOT > $MYCNF
[client]
user=root
password=$MYSQL_ROOT_PASSWORD
EOT
}

# Actualización incremental de la BD (versión actual a actual+1, hasta final-1 a final).
function updateSqlFile()
{
        local DBDIR="$TMPDIR/Database"
        local file FILES=""

        echoAndLog "${FUNCNAME}(): looking for database updates"
        pushd $DBDIR >/dev/null
        # Bucle de actualización incremental desde versión actual a la final.
        for file in $CATALOG-*-*.sql; do
                case "$file" in
                        $CATALOG-$OLDVERSION-$NEWVERSION.sql)
                                # Actualización única de versión inicial y final.
                                FILES="$FILES $file"
                                break
                                ;;
                        $CATALOG-*-postinst.sql)
                                # Ignorar fichero específico de post-instalación.
                                ;;
                        $CATALOG-$OLDVERSION-*.sql)
                                # Actualización de versión n a n+1.
                                FILES="$FILES $file"
                                OLDVERSION="$(echo ${file%.*} | cut -f3 -d-)"
                                ;;
                        $CATALOG-*-$NEWVERSION.sql)
                                # Última actualización de versión final-1 a final.
                                if [ -n "$FILES" ]; then
                                        FILES="$FILES $file"
                                        break
                                fi
                                ;;
                esac
        done
        # Aplicar posible actualización propia para la versión final.
        file=$CATALOG-$NEWVERSION.sql
        if [ -n "$FILES" -o "$OLDVERSION" = "$NEWVERSION" ] && [ -r $file ]; then
                FILES="$FILES $file"
        fi

        popd >/dev/null
        if [ -n "$FILES" ]; then
                mysql --defaults-extra-file=$MYCNF  -e "CREATE DATABASE $AUXCATALOG" 
                [ $? != 0 ] && errorAndLog "${FUNCNAME}: Can't create database $AUXCATALOG" && exit 5
                mysql --defaults-extra-file=$MYCNF -D "$AUXCATALOG" < $MYSQLFILE &>/dev/null
                [ $? != 0 ] && errorAndLog "${FUNCNAME}: Can't import $MYSQLFILE in  $AUXCATALOG" && exit 5

                for file in $FILES; do
                        importSqlFile $DBDIR/$file
                done

                cp $MYSQLFILE $MYSQLFILE.backup
                mysqldump --defaults-extra-file=$MYCNF --opt $AUXCATALOG > $MYSQLFILE
                [ $? != 0 ] && errorAndLog "${FUNCNAME}: Can't export  $AUXCATALOG in  $MYSQLFILE" && exit 5

                mysql --defaults-extra-file=$MYCNF  -e "DROP DATABASE $AUXCATALOG" 
                echoAndLog "${FUNCNAME}(): sqlfile update"
        else
                echoAndLog "${FUNCNAME}(): sqlfile unchanged"
        fi
}


# Actualizar la base datos
function importSqlFile()
{
        local sqlfile="$1"

        if [ ! -r $sqlfile ]; then
                errorAndLog "${FUNCNAME}(): Unable to read $sqlfile!!"
                exit 5
        fi

        echoAndLog "${FUNCNAME}(): importing SQL file..."
        # Ejecutar actualización y borrar fichero de credenciales.
        mysql --defaults-extra-file=$MYCNF --default-character-set=utf8 -D "$AUXCATALOG" < $sqlfile
        if [ $? -ne 0 ]; then
                errorAndLog "${FUNCNAME}(): error importing ${sqlfile##*/} in temporal database"
                exit 5
        fi
        echoAndLog "${FUNCNAME}(): file ${sqlfile##*/} imported to temporal database"
        return 0
}

# Comprobar los datos incluidos en el archivo de backup.
function checkArchive()
{
    local CONFIG FILES f
    local BACKUPFILE="$1"
    if [ -z "$(command -v jq)" ]; then
        echo "$PROG: Error: command not found, please install \"jq\"" >&2
        exit 2
    fi
    # Define content items.
    CONFIG=$(cat << EOT | jq '.[].detected=false'
[
  { "path": "/ogAdmBD.sql", "comment": "Database file" },
  { "path": "/VERSION.json", "comment": "Version file" },
  { "path": "/VERSION.txt", "comment": "Old version file" },
  { "path": "/ogliveinfo.json", "comment": "Installed ogLive info" },
  { "path": "/engine.json", "comment": "Engine config file" },
  { "path": "/engine.cfg", "comment": "Old engine config file" },
  { "path": "/default/opengnsys", "comment": "Default config file" },
  { "path": "/dhcpd.conf", "comment": "DHCP file" },
  { "path": "Custom", "comment": "Customization scripts" },
  { "path": "/menu.lst/", "comment": "PXE directory" },
  { "path": "/menus/", "comment": "Menu directory" },
  { "path": "/log/", "comment": "Log directory" }
]
EOT
    )
    # Turn on the detected flag if the item is in the archive.
    echo "Checking archive content..."
    FILES=$(tar tzf "$BACKUPFILE") || exit 1
    for f in $FILES; do
        CONFIG=$(jq '[ .[] as $p | if "'"${f#$PREFIX}"'" | endswith($p.path) then $p | .detected |= true else $p end ]' <<<"$CONFIG")
    done
    # Show comment and detected flag for each defined item.
    echo $CONFIG | jq -r '.[] as $f | $f.comment + ": " + (if $f.detected then "OK" else "FAIL" end)' | column -ts:
}


##################################################################

# Comprobamos parámetros de entrada.
process_params "$@"

# Comprobar parámetros.
if [ "$USER" != "root" ]; then
    echo "$PROG: Error: solo ejecutable por root." >&2
    exit 3
fi

# Comprobamos acceso al fichero de backup
if ! [ -r $BACKUPFILE ]; then
    echo "$PROG: ERROR: Sin acceso al fichero de backup." | tee -a $FILESAL
    exit 3
fi

# Comprobamos  acceso a ficheros de configuración
if ! [ -r $OPENGNSYS/etc/ogAdmServer.cfg ]; then
    echo "$PROG: ERROR: Sin acceso a la configuración de OpenGnsys." | tee -a $FILESAL
    exit 3
fi

# Si existe el directorio auxiliar lo borramos
[ -d $TMPDIR ] && rm -rf $TMPDIR

# Al salir borramos MYCNF y la db tamporal
trap "clean" 1 2 3 6 9 14 15 EXIT

# Comprobar contenido del archivo.
if [ "$CHECK" ]; then
    checkArchive $BACKUPFILE
    exit
fi

# Parámetros especiales de restauración.
[ "$NOLOGS" ] && TARPARAMS="--exclude=$PREFIX/log"

# Descomprimimos backup
tar -xvzf $BACKUPFILE --directory /tmp $TARPARAMS &>/dev/null

# Comprueba que opengnsys_export sea compatible
grep "CREATE TABLE.*usuarios" $MYSQLFILE &>/dev/null
if [ $? -ne 0 ]; then
    errorAndLog "Backup file created with old version opengnsys_export. Use version 1.1.0-5594 or later."
    exit 7
fi

# Comprobamos si es la misma versión
[ -f $TMPDIR/VERSION.txt ] && OLDVERSION=$(awk '{print $2}' $TMPDIR/VERSION.txt)
[ -f $TMPDIR/VERSION.json ] && OLDVERSION=$(jq -r '.version' $TMPDIR/VERSION.json)
NEWVERSION=$(jq -r '.version' $OPENGNSYS/doc/VERSION.json)
# FALTA: Comprobar que la versión OLD es menor que la NEW
if [ $OLDVERSION != $NEWVERSION ] ; then
    echo "La versión del servidor no coincide con la del backup."
    jq -r '[.project, .version, .codename] | join(" ")' $OPENGNSYS/doc/VERSION.json $TMPDIR/VERSION.json 2>/dev/null \
	    || cat $TMPDIR/VERSION.txt
    read -p "¿Quiere continuar? (y/n): " ANSWER
    if [ "${ANSWER^^}" != "Y" ]; then
        echo "Operación cancelada."
        exit 0
    fi
    # Nos bajamos los archivos de actualización de la base de datos
    svn export "$SVN_URL" $TMPDIR/Database
    [ $? -ne 0 ] && errorAndLog "$PROG: Error getting code from $SVN_URL" && exit 6
    
    # Solicitamos la clave de mysql.
    mysqlPassword
    DIFFVERSION=TRUE
fi

# MYSQL
echo "   * Importamos informacion mysql."
source $OPENGNSYS/etc/ogAdmServer.cfg
# Crear fichero temporal de acceso a la BD
if [ ! -r $MYCNF ]; then
    chmod 600 $MYCNF
    trap "rm -f $MYCNF" 1 2 3 6 9 15
    cat << EOT > $MYCNF
[client]
user=$USUARIO
password=$PASSWORD
EOT
fi

# Si la versión es diferente usamos una tabla auxiliar para actualizar el .sql
[ "$DIFFVERSION" == TRUE ] &&  updateSqlFile

# Eliminamos las tablas que no importamos: repositorios, entorno
#     definimos usuario creador de los "triggers,
#     añadimos los usuarios, sólo si no existen, y
#     definimos valores adecuados por defecto.
# Excluding repository table import, if needed.
[ "$NOREPOS" ] && sed -i -e '/Table structure.* `repositorios`/,/Table structure/d' $MYSQLFILE
[ "$NOUSERS" ] && sed -i -e '/INSERT INTO `usuarios`/s/^/\/*/' \
                         -e '/INSERT INTO `usuarios`/s/$/*\//' \
                         $MYSQLFILE
sed -i -e '/Table structure.* `entornos`/,/Table structure/d' \
       -e '/Table structure.*`usuarios`/,/CHARSET/d' \
       -e '/usuarios/s/IGNORE//g' \
       -e '/usuarios/s/^INSERT INTO .usuarios. /\nALTER TABLE usuarios ADD UNIQUE (usuario);\n\nINSERT IGNORE INTO `usuarios` /g' \
       -e "s/\(DEFINER=\`\)[^\`]*\(\`.* TRIGGER\)/\1$USUARIO\2/" \
       -e "s/\(\` [a-z]*int([0-9]*) NOT NULL\),/\1 DEFAULT 0,/" \
       -e "s/\(\` [a-z]*char([0-9]*) NOT NULL\),/\1 DEFAULT '',/" \
       -e "s/\(\` datetime NOT NULL DEFAULT \)'0000-00-00 00:00:00',/\1'1970-01-01 00:00:00',/" \
       -e "s/\` text NOT NULL,/\` text,/" \
       $MYSQLFILE

# Copia de seguridad del estado de la base de datos
mysqldump --defaults-extra-file=$MYCNF --opt $CATALOG > $MYSQLBCK
chmod 400 $MYSQLBCK
# Importamos los datos nuevos
mysql --defaults-extra-file=$MYCNF -D "$CATALOG" < $MYSQLFILE &>/dev/null
[ $? -ne 0 ] && echo "ERROR: Error al importar la información de la base de datos."

# Copiamos los archivos a su sitio correcto
# default/opengnsys
echo "   * Guardamos la configuración de /etc/default."
mv /etc/default/opengnsys /etc/default/opengnsys-$DATE
cp $TMPDIR/default/opengnsys /etc/default/opengnsys

# DHCP
echo "   * Componemos la configuración del dhcp."
for DHCPCFGDIR in /etc/dhcp /etc/dhcp3; do
    if [ -r $DHCPCFGDIR/dhcpd.conf ]; then
        # Tomamos las variables globales de la configuración actual y las declaraciones host del backup
        # Inicio declaraciones host
        OLDHOSTINI=$(grep -n -m1 -e "^[[:blank:]]*host" -e "^#[[:blank:]]*host" $TMPDIR/dhcpd.conf|cut -d: -f1)
        let BEFOREHOST=$(grep -n -m1 -e "^[[:blank:]]*host" -e "^#[[:blank:]]*host" $DHCPCFGDIR/dhcpd.conf| cut -d: -f1)-1
        # Copia de seguridad de la configuración anterior
        cp $DHCPCFGDIR/dhcpd.conf $DHCPCFGDIR/dhcpd.conf-LAST
        mv $DHCPCFGDIR/dhcpd.conf $DHCPCFGDIR/dhcpd.conf-$DATE
        # Nuevo fichero
        sed ${BEFOREHOST}q $DHCPCFGDIR/dhcpd.conf-LAST > $DHCPCFGDIR/dhcpd.conf
        sed -n -e "$OLDHOSTINI,\$p" $TMPDIR/dhcpd.conf >> $DHCPCFGDIR/dhcpd.conf
        break
    fi
done

# TFTP
echo "   * Guardamos los ficheros PXE de los clientes."
for BOOTLOADER in menu.lst grub; do
    if [ -d $TMPDIR/$BOOTLOADER ]; then
        mkdir -p $OPENGNSYS/tftpboot/$BOOTLOADER-$DATE
        mv $OPENGNSYS/tftpboot/$BOOTLOADER/{01-*,templates,examples} $OPENGNSYS/tftpboot/$BOOTLOADER-$DATE 2>/dev/null
        cp -r $TMPDIR/$BOOTLOADER/{01-*,templates,examples}  $OPENGNSYS/tftpboot/$BOOTLOADER 2>/dev/null
        chown -R www-data:www-data $OPENGNSYS/tftpboot/$BOOTLOADER
    fi
done

if [ -f $OPENGNSYS/tftpboot/menu.lst/templates/01 ]; then
    echo "   * Cambio del nombre de las plantillas PXE para compatibilidad con UEFI."
    BIOSPXEDIR="$OPENGNSYS/tftpboot/menu.lst/templates"
    mv $BIOSPXEDIR/01 $BIOSPXEDIR/10
    sed -i "s/\bMBR\b/1hd/" $BIOSPXEDIR/10

    # Cambiamos el valor en la base de datos. Si no lo hacemos desaparecen de las columnas del NetBootAvanzado.
    mysql --defaults-extra-file=$MYCNF -D "$CATALOG" -e "update ordenadores set arranque='10' where arranque='01';" &>/dev/null
    [ $? -ne 0 ] && echo "ERROR: Error al modificar nombre de las plantilla '10' en la base de datos."
fi

# Configuración de los clientes
echo "   * Guardamos la configuración de los clientes."
mv $OPENGNSYS/client/etc/engine.cfg $OPENGNSYS/client/etc/engine.cfg-$DATE
cp $TMPDIR/engine.cfg $OPENGNSYS/client/etc/engine.cfg

# Páginas de inicio
echo "   * Guardamos las páginas de inicio."
mv $OPENGNSYS/www/menus $OPENGNSYS/www/menus-$DATE
cp -r $TMPDIR/menus $OPENGNSYS/www

# Script personalizados
echo "   * Guardamos los scripts personalizados."
if ls $OPENGNSYS/client/scripts/*Custom &>/dev/null; then
    mkdir -p $OPENGNSYS/client/scripts/Custom-$DATE
    mv $OPENGNSYS/client/scripts/*Custom $OPENGNSYS/client/scripts/Custom-$DATE
fi
cp -r $TMPDIR/*Custom $OPENGNSYS/client/scripts &>/dev/null

# Log files.
if [ -d $TMPDIR/log ]; then
    echo "   * Guardamos los ficheros de log."
    cp -a $TMPDIR/log/* $OPENGNSYS/log
fi

if [ -f $TMPDIR/ogliveinfo.json ]; then
    mv $OPENGNSYS/etc/ogliveinfo.json $OPENGNSYS/etc/ogliveinfo.json-$DATE
    cp $TMPDIR/ogliveinfo.json $OPENGNSYS/etc
fi

echo -e "Se ha terminado de importar los datos del backup. \n\nSe han realizado copias de seguridad de los archivos antiguos:" 
echo    "  - /etc/default/opengnsys-$DATE"
echo    "  - $DHCPCFGDIR/dhcpd.conf-$DATE"
echo    "  - $OPENGNSYS/etc/ogliveinfo.json-$DATE"
echo    "  - $OPENGNSYS/tftpboot/menu.lst-$DATE"
echo    "  - $OPENGNSYS/tftpboot/grub-$DATE"
echo    "  - $OPENGNSYS/client/etc/engine.cfg-$DATE"
echo    "  - $OPENGNSYS/client/scripts/Custom-$DATE"
echo    "  - $OPENGNSYS/www/menus-$DATE"
echo -e "  - $MYSQLBCK \n"

echo "Hay que revisar la configuración del dhcp. En la consola es necesario configurar los valores de las ips de repositorios, servidores ntp, etc y lanzar el \"netBoot Avanzado\" a todas las aulas"

echo "Es necesario probar todos los procedimientos y en caso de error borrarlos y generarlos de nuevo."

oglivecli check &>/dev/null || \
    echo "AVISO: Ejecutar como root \"oglivecli check\" para comprobar si hay que instalar algún cliente ogLive."
