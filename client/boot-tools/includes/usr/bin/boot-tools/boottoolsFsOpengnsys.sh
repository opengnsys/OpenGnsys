#!/bin/bash
#TODO Comprobar si esta los source.

#svn checkout http://www.opengnsys.es/svn/branches/version1.0/client /tmp/opengnsys_installer/opengnsys/client/;
#svn checkout http://www.opengnsys.es/svn/branches/version2/  /tmp/opengnsys_installer/opengnsys2
#find /tmp/opengnsys_installer/ -name .svn -type d -exec rm -fr {} \; 2>/dev/null;
#apt-get -y --force-yes install  subversion
#export SVNURL="http://opengnsys.es/svn/branches/version1.0/client/"
#VERSIONSVN=$(LANG=C svn info $SVNURL | awk '/Revision:/ {print "r"$2}')

#VERSIONSVN=$(cat /tmp/versionsvn.txt)
VERSIONBOOTTOOLS="ogLive"

NAMEISOCLIENTFILE="/tmp/opengnsys_info_rootfs" 
NAMEHOSTCLIENTFILE="/tmp/opengnsys_chroot"

SVNCLIENTDIR=/tmp/opengnsys_installer/opengnsys/client/boot-tools
SVNCLIENTSTRUCTURE=/tmp/opengnsys_installer/opengnsys/client/shared
SVNCLIENTENGINE=/tmp/opengnsys_installer/opengnsys/client/engine
 
OGCLIENTMOUNT=""

OGCLIENTCFG=${OGCLIENTCFG:-/tmp/ogclient.cfg}
[ -f $OGCLIENTCFG ] && source $OGCLIENTCFG
OSDISTRIB=${OSDISTRIB:-$(lsb_release -is)}
OSCODENAME=${OSCODENAME:-$(lsb_release -cs)}
OSRELEASE=${OSRELEASE:-$(uname -a | awk '{print $3}')}
if [ -z "$OSARCH" ]; then
	uname -a | grep x86_64 > /dev/null  &&  OSARCH="amd64" || OSARCH="i386"
fi
OSHTTP=${OSHTTP:-"http://es.archive.ubuntu.com/ubuntu/"}

echo "$OSDISTRIB:$OSCODENAME:$OSRELEASE:$OSARCH:$OSHTTP"


LERROR=TRUE

echo "$FUNCNAME: Iniciando la personalización con datos del SVN "

# parseamos el apt.source de la distribución (en minúsculas)
sed -e "s/OSCODENAME/$OSCODENAME/g" ${SVNCLIENTDIR}/includes/etc/apt/sources.list.${OSDISTRIB,,} > ${SVNCLIENTDIR}/includes/etc/apt/sources.list
if [ $? -ne 0 ]
then 
	echo "$FUNCNAME(): Parsing apt.sources : ERROR"
	exit 1
fi



#damos permiso al directorio de scripts 
chmod -R 775 ${SVNCLIENTDIR}/includes/usr/bin/*

# los copiamos
cp -av ${SVNCLIENTDIR}/includes/* ${OGCLIENTMOUNT}/
mkdir -p ${OGCLIENTMOUNT}/opt/opengnsys/
cp -av ${SVNCLIENTSTRUCTURE}/* ${OGCLIENTMOUNT}/opt/opengnsys/
cp -av ${SVNCLIENTENGINE}/* ${OGCLIENTMOUNT}/opt/opengnsys/lib/engine/bin/

if [ $? -ne 0 ]
then 
	echo "$FUNCNAME(): Copying client data : ERROR"
	exit 1
fi


# Si no existe, copiar pci.ids.
[ -f $OGCLIENTMOUNT/etc/pci.ids ] || cp -va ${SVNCLIENTSTRUCTURE}/lib/pci.ids $OGCLIENTMOUNT/etc

# Dependencias Qt para el Browser.
mkdir -p $OGCLIENTMOUNT/usr/local/{etc,lib,plugins}
cp -av ${SVNCLIENTSTRUCTURE}/lib/qtlib/* $OGCLIENTMOUNT/usr/local/lib
cp -av ${SVNCLIENTSTRUCTURE}/lib/fonts $OGCLIENTMOUNT/usr/local/lib
cp -av ${SVNCLIENTSTRUCTURE}/lib/qtplugins/* $OGCLIENTMOUNT/usr/local/plugins
cp -av ${SVNCLIENTSTRUCTURE}/etc/*.qmap $OGCLIENTMOUNT/usr/local/etc

# Browser.
cp -av ${SVNCLIENTSTRUCTURE}/bin/browser $OGCLIENTMOUNT/bin
if [ $? -ne 0 ]; then 
	echo "$FUNCNAME(): Copying Browser : ERROR"
	exit 1
fi

# ogAdmClient.
cp -av ${SVNCLIENTSTRUCTURE}/bin/ogAdmClient $OGCLIENTMOUNT/bin
if [ $? -ne 0 ]; then 
	echo "$FUNCNAME(): Copying ogAdmClient: ERROR"
	exit 1
fi

# El fichero de configuración debe sustituir a los 2 ficheros (borrar las 2 líneas).
echo "${VERSIONBOOTTOOLS}-${OSCODENAME}-${OSRELEASE}-${VERSIONSVN}" > /$NAMEISOCLIENTFILE
echo "${VERSIONBOOTTOOLS}-${OSCODENAME}-${VERSIONSVN}" > $NAMEHOSTCLIENTFILE


history -c

