#!/bin/bash
# create-image.sh - Scirpt de ejemplo para crear una imagen de un sistema de archivos.
# (puede usarse como base para el programa de creación de imágenes usado por OpenGNSys Admin).

TIME1=$SECONDS
PROG="$(basename $0)"
if [ $# -ne 4 ]; then
    ogRaiseError $OG_ERR_FORMAT "Formato: $PROG ndisco nparticion REPO|CACHE imagen"
    exit $?
fi

# Obtener información de los parámetros de entrada.
PART=$(ogDiskToDev "$1" "$2") || exit $?
IMGDIR=$(ogGetParentPath "$3" "$4") || exit $?
IMGFILE="$IMGDIR/$(basename $4).img"
# Renombrar el fichero de imagen si ya existe.
if [ -f "$IMGFILE" ]; then
    ogEcho info "Renombrar \"$IMGFILE\" por \"$IMGFILE.ant\"."
    mv "$IMGFILE" "$IMGFILE.ant"
fi
# Mostrar información.
ogEcho info "$PROG: Origen=$PART, Destino=$IMGFILE"

# Obtener tamaño de la partición.
SIZE=$(ogGetPartitionSize "$1" "$2")
# Reducir el sistema de archvios.
ogEcho info "$PROG: reducir sistema de archivos."
REDSIZE=$(ogReduceFs $1 $2) || REDSIZE=$[SIZE+1]
if [ $REDSIZE -lt $SIZE ]; then
    ogEcho info "$PROG: redimensionar partición a $REDSIZE KB."
    ogSetPartitionSize $1 $2 $REDSIZE
fi
# Crear la imagen.
ogEcho info "$PROG: Crear imagen."
ogCreateImage "$@"
EXITCODE=$?
# Restaurar tamaño.
if [ $REDSIZE -lt $SIZE ]; then
    ogEcho info "$PROG: redimensionar partición a $SIZE KB."
    ogSetPartitionSize $1 $2 $SIZE
    ogEcho info "$PROG: extender sistema de archivos."
    ogExtendFs $1 $2
fi
TIME=$[SECONDS-TIME1]
ogEcho info "$PROG: Duración de la operación $[TIME/60]m $[TIME%60]s"
exit $EXITCODE

