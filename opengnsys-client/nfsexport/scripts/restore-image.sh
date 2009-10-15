#!/bin/bash
# Scirpt de ejemplo para restaurar una imagen.
# (puede usarse como base para el programa de restauración de imágenes usado por OpenGNSys Admin).

PROG="$(basename $0)"
if [ $# -ne 4 ]; then
    ogRaiseError $OG_ERR_FORMAT "Formato: $PROG ndisco nparticion REPO|CACHE imagen"
    exit $?
fi

# Procesar parámetros de entrada
IMGFILE=$(ogGetPath "$1" "$2") || exit $?
PART=$(ogDiskToDev "$3" "$4") || exit $?
# Mostrar información.
ogEcho info "$PROG: Origen=$PART, Destino=$IMGFILE"

# Restaurar la imagen.
ogRestoreImage "$1" "$2" "$3" "$4" || exit $?
# Restaurar tamaño.
ogExtendFs $1 $2

