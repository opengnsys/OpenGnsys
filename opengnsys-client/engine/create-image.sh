#!/bin/bash
# Scirpt de ejemplo para clonar imagen.
# (puede usarse como base para el programa de creación de imágenes usado por OpenGNSys Admin).

# ...... procesar parámetros y más ............

# Obtener tamaño de la partición.
SIZE=$(ogGetPartitionSize $1 $2)
# Reducir el sistema de archvios.
REDSIZE=$(ogReduceFs $1 $2) || REDSIZE=$[SIZE+1]
[ $REDSIZE -lt $SIZE ] && ogSetPartitionSize $1 $2 $REDSIZE
# Crear la imagen.
ogCreateImage "$1" "$2" "$3" "$4" || ogRaiseError OG_ERR_IMAGE || exit $?
# Restaurar tamaño.
[ $REDSIZE -lt $SIZE ] && ogSetPartitionSize $1 $2 $SIZE

