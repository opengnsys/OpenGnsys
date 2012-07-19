#!/bin/bash
# Instalación del ticket 513: Wake-On-Lan por IP en Unicast.

# Declaración de variables. 
TICKET="513-WOL-Unicast";
SVNURLSOURCE="http://opengnsys.es/svn/branches/version1.0-tickets/Resueltos/wake_on_lan_por_IP_unicast_ticket513/";
BASEDIR="/opt/opengnsys/";
TICKETDIR="${BASEDIR}tickets/";
TARGETDIR="${TICKETDIR}${TICKET}/";
LISTTOBACKUP="${TICKETDIR}${TICKET}-BACKUP.txt";
FILEBACKUP="${TICKETDIR}${TICKET}-BACKUP.tgz";
SVNURLSOURCEBASE="http://opengnsys.es/svn/tags/opengnsys-1.0.4/admin/Sources/";

#TODO comprobar version de opengnsys.

echo "Parando los servios."
/etc/init.d/opengnsys stop
sleep 5

mkdir -p $TARGETDIR;
echo "Descargando base de los sources"
svn export --force $SVNURLSOURCEBASE $TARGETDIR/admin/Sources;
if [ ! -d $TARGETDIR/admin/Sources ]; then
	echo "Error de acceso a los ficheros fuente" >&2
	exit 1
fi
echo "Descando modificaciones del ticket"
svn export --force $SVNURLSOURCE $TARGETDIR/;
find $TARGETDIR -name .svn -type d -exec rm -fr {} \;

## Especifico de este tiket
mv  ${TARGETDIR}/admin/WebConsole ${TARGETDIR}/www/

echo "Generando fichero de los archivos involucrados en $LISTTOBACKUP "
find  $TARGETDIR   -type f | egrep -v 'Sources|installer-info' | awk -F"$TARGETDIR" '{print $2}'  > $LISTTOBACKUP;

#compilamos el ogAdmServer
cd ${TARGETDIR}/admin/Sources/Services/ogAdmServer
make
mkdir -p ${TARGETDIR}sbin/
cp -a ${TARGETDIR}admin/Sources/Services/ogAdmServer/ogAdmServer ${BASEDIR}sbin/.
echo "sbin/ogAdmServer" >> $LISTTOBACKUP;
##

echo "Creando backup en $FILEBACKUP "
cd $BASEDIR;
[ -f $FILEBACKUP ] ||   tar czvf $FILEBACKUP -T $LISTTOBACKUP  2>/dev/null;

echo "Copiando estructura de opengnsys"
cp -av ${TARGETDIR}/[^installer][^admin]* ${BASEDIR};

echo "Inicando los servicios"
/etc/init.d/opengnsys start
sleep 5

