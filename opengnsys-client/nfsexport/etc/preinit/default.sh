#!/opt/opengnsys/bin/bash

# Para que no haya problemas con el interprete
ln -fs /opt/opengnsys/bin/bash /bin/bash

source /opt/opengnsys/lib/engine/bin/loadenviron.sh

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
