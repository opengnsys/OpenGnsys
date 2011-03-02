#!/opt/opengnsys/bin/bash

# Para que no haya problemas con el interprete
ln -fs /opt/opengnsys/bin/bash /bin/bash

set -a

source /opt/opengnsys/etc/preinit/loadenviron.sh
for f in fileslinks.sh loadudeb.sh loadmodules.sh metadevs.sh; do
    $OGETC/preinit/$f
done
unset f

if [ -f $OGETC/init/$OG_IP.sh ]; then
    $OGETC/init/$OG_IP.sh

elif [ -f $OGETC/init/$OGGROUP.sh ]; then
    $OGETC/init/$OGGROUP.sh

elif [ -f $OGETC/init/default.sh ]; then
    $OGETC/init/default.sh

else
    echo "No se ha encontrado script de inicio"
    halt
fi
