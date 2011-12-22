#!/bin/bash
echo "comprobando directorio .ssh del root"
if [ ! -d /root/.ssh ]
then
    echo "creando directorio .ssh 600"
	mkdir -p /root/.ssh
	chmod 700 /root/.ssh
fi


echo "comprobando el fichero authorized_keys .ssh del root"
if [ ! -f /root/.ssh/authorized_keys ]
then
	echo "creando el fichero authorized_keys"
	touch /root/.ssh/authorized_keys
	chmod 600 /root/.ssh/authorized_keys
fi

ssh-keygen -q -f /root/.ssh/id_rsa -N ""
cat /root/.ssh/id_rsa.pub >> /root/.ssh/authorized_keys

## TODO: exportamos la publica a los repos
cp /root/.ssh/id_rsa.pub /tmp/rsa.ogclient.pub

history -c