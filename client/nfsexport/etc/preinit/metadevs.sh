#!/bin/bash
#/**
#@file    metadevs.sh
#@brief   Script de inicio para detectar metadispositivos LVM y RAID.
#@todo    Pendiente detección de RAID.
#@note    Desglose del script "loadenviron.sh".
#@warning License: GNU GPLv3+
#@version 0.9
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2009-10-10
#*/


# Si está configurado OpenGNSys ...
if [ -n "$OPENGNSYS" ]; then
    echo "$MSG_DETECTLVMRAID"
    # Detectar metadispositivos LVM.
    vgchange -ay >/dev/null 2>&1
    # FIXME Detectar metadispositivos RAID.
else
    # FIXME Error: entorno de OpenGNSys no configurado.
    echo "Error: OpenGNSys environment is not configured."   # FIXME: definir mensaje.
    exit 1
fi

