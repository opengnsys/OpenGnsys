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

    # FIXME Necesario temporalmente
    mkdir -p /usr/local/Trolltech/QtEmbedded-4.5.1/lib/
    ln -fs $OGLIB/fonts /usr/local/Trolltech/QtEmbedded-4.5.1/lib/fonts

    # Datos de dispositivos PCI en /etc
    ln -fs $OGLIB/pci.ids /etc
else
    # FIXME Error: entorno de OpenGNSys no configurado.
    echo "Error: OpenGnSys environment is not configured."   # FIXME: definir mensaje.
    exit 1
fi

