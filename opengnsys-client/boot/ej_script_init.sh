#!/opt/opengnsys/bin/bash

export DHCP_SERVER=`grep -h dhcp-server-identifier /var/lib/dhcp3/dhclient.* | sed 's/[^0-9]*\(.*\);/\1/' | head -1`
export IP_servidor=$DHCP_SERVER
export IP=`grep -h fixed-address /var/lib/dhcp3/dhclient.* | sed 's/[^0-9]*\(.*\);/\1/' | head -1`

export PROJECT=/opt/opengnsys
export OGBIN=$PROJECT/bin
export OGETC=$PROJECT/etc
export OGLIB=$PROJECT/lib
export OGLOG=$PROJECT/log
export OGIMAGES=$PROJECT/images

mount -t nfs -onolock $DHCP_SERVER:/opt/opengnsys/log/clients $OGLOG
mount -t nfs -onolock $DHCP_SERVER:/opt/opengnsys/images $OGIMAGES

export PATH=$OGBIN:$OGLIB/engine/bin:$PATH
export LD_LIBRARY_PATH=$OGLIB:$LD_LIBRARY_PATH

insmod $OGLIB/modules/psmouse.ko
mkdir -p /usr/local/Trolltech/QtEmbedded-4.5.1/lib/
ln -s $OGLIB/fonts /usr/local/Trolltech/QtEmbedded-4.5.1/lib/fonts

. ATA.lib

bash
