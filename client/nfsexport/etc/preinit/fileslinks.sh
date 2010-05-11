#!/bin/bash
#/**
#@file    fileslinks.sh
#@brief   Script de inicio para copiar ficheros y deinir enlaces simbólicos.
#@note    Desglose del script "loadenviron.sh".
#@warning License: GNU GPLv3+
#@version 0.9
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2009-10-10
#*/


# Si está configurado OpenGNSys ...
if [ -n "$OPENGNSYS" ]; then
    echo "$MSG_MAKELINKS"
    # Para tener /bin/bash y no haya problemas
    ln -fs $OGBIN/bash /bin/bash

    # Crear directorio de bloqueos
    mkdir -p /var/lock

    # FIXME Directorio de tipos de letras para el browser.
    QTLIBS=$(grep qt_libspath $OGBIN/browser 2>/dev/null | cut -f2 -d=)
    QTLIBS=${QTLIBS:-"/usr/local/QtEmbedded-4.6.2/lib"}
    mkdir -p $QTLIBS
    ln -fs $OGLIB/fonts $QTLIBS

    # Datos de dispositivos PCI en /etc
    ln -fs $OGLIB/pci.ids /etc
else
    # FIXME Error: entorno de OpenGNSys no configurado.
    echo "Error: OpenGnSys environment is not configured."   # FIXME: definir mensaje.
    exit 1
fi

