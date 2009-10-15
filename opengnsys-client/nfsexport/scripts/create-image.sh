#!/bin/bash
# Scirpt de ejemplo para clonar imagen.
# (puede usarse como base para el programa de creación de imágenes usado por OpenGNSys Admin).

PROG="$(basename $0)"
if [ $# -ne 4 ]; then
    ogRaiseError $OG_ERR_FORMAT "Formato: $PROG ndisco nparticion REPO|CACHE imagen"
    exit $?
fi

# Procesar parámetros de entrada
PART=$(ogDiskToDev "$1" "$2") || exit $?
IMGDIR=$(ogGetParentPath "$3" "$4") || exit $?
IMGFILE="$IMGDIR/$(basename $4).img"
# Renombrar el fichero de imagen si ya existe.
if [ -f "$IMGFILE" ]; then
    mv "$IMGFILE" "$IMGFILE.ant"
fi
# Mostrar información.
ogEcho info "$PROG: Origen=$PART, Destino=$IMGFILE"

# Obtener tamaño de la partición.
SIZE=$(ogGetPartitionSize "$1" "$2") || exit $?
# Reducir el sistema de archvios.
REDSIZE=$(ogReduceFs $1 $2) || REDSIZE=$[SIZE+1]
[ $REDSIZE -lt $SIZE ] && ogSetPartitionSize $1 $2 $REDSIZE
# Crear la imagen.
ogCreateImage "$1" "$2" "$3" "$4" || ogRaiseError $OG_ERR_IMAGE || exit $?
mv "$IMGFILE.000" "$IMGFILE"
# Restaurar tamaño.
[ $REDSIZE -lt $SIZE ] && ogSetPartitionSize $1 $2 $SIZE

