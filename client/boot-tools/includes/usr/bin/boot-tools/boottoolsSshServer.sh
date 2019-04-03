#!/bin/bash
echo "comprobando directorio .ssh del root"
if [ ! -d /root/.ssh ]
then
    echo "creando directorio .ssh 600"
	mkdir -p /root/.ssh
	chmod 700 /root/.ssh
fi
echo "creando el fichero authorized_keys"
touch /root/.ssh/authorized_keys
chmod 600 /root/.ssh/authorized_keys

echo "importando la clave publica del servidor OG"
cat /tmp/id_rsa.pub

[ -f /tmp/id_rsa.pub ] && cat /tmp/id_rsa.pub >> /root/.ssh/authorized_keys || echo "no key publica og"
 
 history -c