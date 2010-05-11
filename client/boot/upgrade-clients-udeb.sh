#!/bin/bash
#@file    upgrade-clients-udeb.sh
#@brief   Actualiza los paquetes udeb que deben ser exportados a los clientes.
#@arg  \c distrib - nombre de la distribución de Ubuntu (karmic, jaunty, ...).
#@note    El script debe ser copiado a \c opengnsys/bin y el fichero de configuración a \c opengnsys/etc
#@note    Formato del fichero \c udeb.list :    {install|remove}:paquete


# Variables
PROG="$(basename $0)"
OPENGNSYS=${OPENGNSYS:-"/opt/opengnsys"}
DISTRIB=${1:-"jaunty"}
CFGFILE="$OPENGNSYS/etc/udeblist${1:+"-$1"}.conf"
OGUDEB="$OPENGNSYS/client/lib/udeb"
TMPUDEB="/tmp/udeb"
UDEBLIST="/etc/apt/sources.list.d/udeb.list"

#/// Comprobar fichero de configuración.
if [ ! -f "$CFGFILE" ]; then
    echo "$PROG: No existe el fichero de configuración \"$CFGFILE\"" >&2
    exit 1
fi
PACKAGES_INSTALL=$(awk -F: '$1~/install/ {print $2}' $CFGFILE)
PACKAGES_REMOVE=$(awk -F: '$1~/remove/ {print $2}' $CFGFILE)
if [ -z "$PACKAGES_INSTALL" ]; then
    echo "$PROG: No hay paquetes para descargar." >&2
    exit 2
fi

#/// Crear configuración para apt-get 
echo "deb http://archive.ubuntu.com/ubuntu/ $DISTRIB main/debian-installer" >$UDEBLIST
mkdir -p $TMPUDEB/partial
rm -f $TMPUDEB/*.udeb

#/// Descargar paquetes udeb, borrar los descartables y moverlos al NFS.
apt-get update
apt-get install -y -o dir::cache::archives=$TMPUDEB -d $PACKAGES_INSTALL
for i in $PACKAGES_REMOVE; do
    rm -f $TMPUDEB/${i}_*.udeb
done
rm -f $OGUDEB/*.udeb
mv $TMPUDEB/*.udeb $OGUDEB
rm -f $UDEBLIST

