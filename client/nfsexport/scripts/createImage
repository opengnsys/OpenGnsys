#!/bin/bash
# create-image.sh - Scirpt de ejemplo para crear una imagen de un sistema de archivos.
# (puede usarse como base para el programa de creación de imágenes usado por OpenGNSys Admin).

TIME1=$SECONDS
PROG="$(basename $0)"
if [ $# -ne 4 ]; then
    ogRaiseError $OG_ERR_FORMAT "Formato: $PROG ndisco nparticion REPO|CACHE imagen"
    exit $?
fi

# Porcentaje para la barra de progreso del Browser
echo [0,100]
# Mostrar información.
echo "[0] $PROG: Origen=$PART, Destino=$IMGFILE"

# Obtener información de los parámetros de entrada.
PART=$(ogDiskToDev "$1" "$2") || exit $?
IMGDIR=$(ogGetParentPath "$3" "$4")
# Si no existe, crear subdirectorio de la imagen.
if [ $? != 0 ]; then
    echo "[5] Crear subdirectorio de la imagen \"$3 $(dirname "$4")."
    ogMakeDir "$3" $(dirname "$4")
    IMGDIR=$(ogGetParentPath "$3" "$4") || exit $?
fi
IMGFILE="$IMGDIR/$(basename $4).img"
# Renombrar el fichero de imagen si ya existe.
if [ -f "$IMGFILE" ]; then
    echo "[10] Renombrar \"$IMGFILE\" por \"$IMGFILE.ant\"."
    mv "$IMGFILE" "$IMGFILE.ant"
fi

# Obtener tamaño de la partición.
SIZE=$(ogGetPartitionSize "$1" "$2")
# Reducir el sistema de archvios.
echo "[15]: Reducir sistema de archivos."
REDSIZE=$(ogReduceFs $1 $2) || REDSIZE=$[SIZE+1]
if [ $REDSIZE -lt $SIZE ]; then
    echo "[25] Redimensionar partición a $REDSIZE KB."
    ogSetPartitionSize $1 $2 $REDSIZE
fi
# Crear la imagen.
echo "[40] Crear imagen."
ogCreateImage "$@"
EXITCODE=$?
# Restaurar tamaño.
if [ $REDSIZE -lt $SIZE ]; then
    echo "[85] Redimensionar partición a $SIZE KB."
    ogSetPartitionSize $1 $2 $SIZE
    echo "[90] Extender sistema de archivos."
    ogExtendFs $1 $2
fi
TIME=$[SECONDS-TIME1]
echo "[100] Duración de la operación $[TIME/60]m $[TIME%60]s"
exit $EXITCODE

