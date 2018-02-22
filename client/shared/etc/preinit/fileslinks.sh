#!/bin/bash
#/**
#@file    fileslinks.sh
#@brief   Script de inicio para copiar ficheros y deinir enlaces simbólicos.
#@warning License: GNU GPLv3+
#@version 0.9
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2009-10-10
#@version 1.0.5 - Enlace para librería libmac.
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2012-06-28
#*/


# Si está configurado OpenGnsys ...
if [ -n "$OPENGNSYS" ]; then
    echo "${MSG_MAKELINKS:-.}"

    # Shell BASH por defecto (para usar "runtest")
    ln -fs /bin/bash /bin/sh 2>/dev/null

    # Enlace a la librería libmac para ld-mac.
    [ -f /usr/lib/libmac.so ] || ln -fs $OGLIB/libmac.so /usr/lib 2>/dev/null

    # Crear directorio de bloqueos
    mkdir -p /var/lock 2>/dev/null || mkdir -p /run/lock

    # Crear ficheros temporales.
    touch $OGLOGCOMMAND $OGLOGCOMMAND.tmp $OGLOGSESSION /tmp/menu.tmp
    chmod 777 $OGLOGCOMMAND $OGLOGCOMMAND.tmp $OGLOGSESSION /tmp/menu.tmp

    # Enlaces para Qt Embeded.
    QTDIR="/usr/local"
    mkdir -p $QTDIR/{etc,lib,plugins}
    for i in $OGLIB/qtlib/*; do
        [ -f $QTDIR/lib/$i ] || ln -fs $i $QTDIR/lib 2>/dev/null
    done
    for i in $OGLIB/qtplugins/*; do
        [ -f $QTDIR/plugins/$i ] || ln -fs $i $QTDIR/plugins 2>/dev/null
    done
    for i in $OGETC/*.qmap; do
        [ -f $QTDIR/etc/$i ] || ln -fs $i $QTDIR/etc 2>/dev/null
    done

else
    # FIXME Error: entorno de OpenGnsys no configurado.
    echo "Error: OpenGnsys environment is not configured."   # FIXME: definir mensaje.
    exit 1
fi

