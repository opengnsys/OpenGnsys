#!/bin/bash

# Cargar entorno de OpenGnSys
set -a
source /opt/opengnsys/etc/preinit/loadenviron.sh

# Scripts de inicio.
for f in fileslinks loadmodules metadevs mountrepo poweroff otherservices; do
    $OGETC/preinit/$f.sh
done
unset f

if [ -f $OGETC/init/$IPV4ADDR.sh ]; then
    $OGETC/init/$OG_IP.sh

elif [ -f $OGETC/init/$OGGROUP.sh ]; then
    $OGETC/init/$OGGROUP.sh

elif [ -f $OGETC/init/default.sh ]; then
    $OGETC/init/default.sh

else
    echo "No se ha encontrado script de inicio"
    halt
fi
