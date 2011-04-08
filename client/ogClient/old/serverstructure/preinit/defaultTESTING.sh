#!/bin/bash

# Para que no haya problemas con el interprete
#ln -fs /opt/opengnsys/bin/bash /bin/bash

set -a

#source /opt/opengnsys/etc/preinit/loadenviron.sh
source /opt/opengnsys/etc/preinit/loadenvironTESTING.sh
#for f in fileslinks.sh loadudeb.sh loadmodules.sh metadevs.sh; do
for f in loadmodulesTESTING.sh; do
    $OGETC/preinit/$f
done

if [ -f $OGETC/init/$OG_IP.sh ]; then
    $OGETC/init/$OG_IP.sh

elif [ -f $OGETC/init/$OGGROUP.sh ]; then
    $OGETC/init/$OGGROUP.sh

elif [ -f $OGETC/init/defaultTESTING.sh ]; then
    $OGETC/init/defaultTESTING.sh

else
    echo "No se ha encontrado script de inicio"
    halt
fi
