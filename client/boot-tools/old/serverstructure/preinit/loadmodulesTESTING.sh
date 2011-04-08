#!/bin/bash
#/**
#@file    loadmodules.sh
#@brief   Script de inicio para cargar módulos complementarios del kernel.
#@version 1.0
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2010-01-26
#*/


MSG_LOADMODULES=${MSG_LOADMODULES:-"."}
echo "$MSG_LOADMODULES para cliente full"

# Directorio principal de módulos del kernel.
MODULESDIR=/lib/modules/$(uname -r)/kernel

# Módulo del ratón.
insmod $MODULESDIR/drivers/input/mouse/psmouse.ko 2>/dev/null
# Módulos de discos.
#for m in $MODULESDIR/drivers/ata/*.ko; do
 #   insmod $m 2>/dev/null
#done
# Módulos de sistemas de archivos.
#for f in reiserfs jfs xfs hfs hfsplus; do
#for f in reiserfs xfs hfs hfsplus; do
#    insmod $MODULESDIR/fs/$f/$f.ko 2>/dev/null
#done

