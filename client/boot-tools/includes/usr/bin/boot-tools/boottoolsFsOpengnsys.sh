#!/bin/bash

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
OSRELEASE=${OSRELEASE:-$(uname -r)}
OSARCH=${OSARCH:-$(dpkg --print-architecture)}
OSHTTP=${OSHTTP:-"http://es.archive.ubuntu.com/ubuntu/"}

echo "$OSDISTRIB:$OSCODENAME:$OSRELEASE:$OSARCH:$OSHTTP"


LERROR=TRUE

echo "$FUNCNAME: Iniciando la personalización con datos del repositorio"

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
mkdir -p ${OGCLIENTMOUNT}/opt/opengnsys/lib/engine/bin/
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

# Browser y ogAdmClient.
[ -x ${SVNCLIENTSTRUCTURE}/bin/browser ] && cp -av ${SVNCLIENTSTRUCTURE}/bin/browser $OGCLIENTMOUNT/bin
[ -x ${SVNCLIENTSTRUCTURE}/bin/ogAdmClient ] && cp -av ${SVNCLIENTSTRUCTURE}/bin/ogAdmClient $OGCLIENTMOUNT/bin

# El fichero de configuración debe sustituir a los 2 ficheros (borrar las 2 líneas).
echo "${VERSIONBOOTTOOLS}-${OSCODENAME}-${OSRELEASE}-${GITRELEASE}" > /$NAMEISOCLIENTFILE
echo "${VERSIONBOOTTOOLS}-${OSCODENAME}-${GITRELEASE}" > $NAMEHOSTCLIENTFILE


history -c

