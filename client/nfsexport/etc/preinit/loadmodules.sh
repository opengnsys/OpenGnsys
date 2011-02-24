#!/bin/bash
#/**
#@file    loadmodules.sh
#@brief   Script de inicio para cargar módulos complementarios del kernel.
#@version 1.0
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2010-01-26
#*/


MSG_LOADMODULES=${MSG_LOADMODULES:-"."}
echo "$MSG_LOADMODULES"

# Directorio principal de módulos del kernel.
MODULESDIR=/lib/modules/$(uname -r)/kernel

# Módulo del ratón.
insmod $MODULESDIR/drivers/input/mouse/psmouse.ko 2>/dev/null



