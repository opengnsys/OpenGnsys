#!/bin/bash
# Scirpt de ejemplo para restaurar una imagen.
# (puede usarse como base para el programa de restauración de imágenes usado por OpenGNSys Admin).

PROG="$(basename $0)"
if [ $# -ne 4 ]; then
    ogRaiseError $OG_ERR_FORMAT "Formato: $PROG ndisco nparticion REPO|CACHE imagen"
    exit $?
fi

# Procesar parámetros de entrada
if [ "$1" == "CACHE" -o "$1" == "cache" ]; then
    # Si la imagen no está en la caché, copiarla del repositorio.
    IMGDIR=$(ogGetParentPath "$1" "$2") || exit $?
    IMGFILE=$(ogGetPath "$1" "$2")
    if [ -z "$IMGFILE" ]; then
        echo "Copiando imagen \"$2\" del repositorio a caché local"
        ogCopyFile "repo" "$2" "$IMGDIR" || exit $?
        IMGFILE=$(ogGetPath "cache" "$2") || exit $?
    fi
else
    IMGFILE=$(ogGetPath "$1" "$2") || exit $?
fi
PART=$(ogDiskToDev "$3" "$4") || exit $?

# Mostrar información.
ogEcho info "$PROG: Origen=$IMGFILE, Destino=$PART"
# Restaurar la imagen.
ogRestoreImage "$1" "$2" "$3" "$4" || exit $?
# Restaurar tamaño.
ogExtendFs $1 $2

