#!/bin/bash
#/**
#@file    fileslinks.sh
#@brief   Script de inicio para copiar ficheros y deinir enlaces simbólicos.
#@warning License: GNU GPLv3+
#@version 0.9
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2009-10-10
#@version 1.0.5 - Enlace para librería libmac (obsoleto en versión 1.1.1).
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2012-06-28
#@version 1.1.2 - Autenticación con clave pública para SSH
#@author  Irina Gómez, ETSII Universidad de Sevilla
#@date    2019-09-25
#*/


# Si está configurado OpenGnsys ...
if [ -n "$OPENGNSYS" ]; then
    echo "${MSG_MAKELINKS:-.}"

    # Shell BASH por defecto (para usar "runtest")
    ln -fs /bin/bash /bin/sh 2>/dev/null

    # Crear directorio de bloqueos
    mkdir -p /var/lock 2>/dev/null || mkdir -p /run/lock

    # Crear ficheros temporales.
    touch $OGLOGCOMMAND $OGLOGCOMMAND.tmp $OGLOGSESSION /tmp/menu.tmp
    chmod 777 $OGLOGCOMMAND $OGLOGCOMMAND.tmp $OGLOGSESSION /tmp/menu.tmp

    # Enlaces para Qt Embeded.
    QTDIR="/usr/local"
    mkdir -p $QTDIR/{etc,lib,plugins}
    for i in $OGLIB/qtlib/* $OGLIB/fonts; do
        [ -f $QTDIR/lib/$i ] || ln -fs $i $QTDIR/lib 2>/dev/null
    done
    for i in $OGLIB/qtplugins/*; do
        [ -f $QTDIR/plugins/$i ] || ln -fs $i $QTDIR/plugins 2>/dev/null
    done
    for i in $OGETC/*.qmap; do
        [ -f $QTDIR/etc/$i ] || ln -fs $i $QTDIR/etc 2>/dev/null
    done

    # Autenticación con clave pública para SSH
    [ -f /scripts/ssl/authorized_keys ] && cp /scripts/ssl/* /root/.ssh

else
    # FIXME Error: entorno de OpenGnsys no configurado.
    echo "Error: OpenGnsys environment is not configured."   # FIXME: definir mensaje.
    exit 1
fi

