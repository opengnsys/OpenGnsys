#!/bin/bash
#/**
#@file    fileslinks.sh
#@brief   Script de inicio para copiar ficheros y deinir enlaces simbólicos.
#@warning License: GNU GPLv3+
#@version 0.9
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2009-10-10
#@version 1.0
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2011-03-21
#*/


# Si está configurado OpenGnSys ...
if [ -n "$OPENGNSYS" ]; then
    echo "$MSG_MAKELINKS"

    # Shell BASH por defecto (para usar "runtest")
    ln -fs /bin/bash /bin/sh

    # Crear directorio de bloqueos
    mkdir -p /var/lock || mkdir -p /run/lock

#    # Directorio de tipos de letras para el browser.
    QTDIR="/usr/local"
 #   mkdir -p $QTDIR/lib
  #  ln -fs $OGLIB/fonts $QTDIR/lib

else
    # FIXME Error: entorno de OpenGNSys no configurado.
    echo "Error: OpenGnSys environment is not configured."   # FIXME: definir mensaje.
    exit 1
fi

