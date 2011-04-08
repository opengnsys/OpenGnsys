#!/bin/bash
#/**
#@file   conframfs.sh
#@brief   Script de inicio para configurar el 2º FileSystem linkado.
#@version 1.0
#@author  Antonio J. Doblas Viso. Universidad de Málaga
#@date    2010-06-02
#*/
#sustituido por la funcion ogPostConfigureFS

MSG_LOADMODULES=${MSG_CONFRAMFS:-"."}
echo "$MSG_CONFRAMFS"

# configuramos el /etc/hostname.
HOSTNAME=$(ogGetHostname)
echo $HOSTNAME > /etc/hostname


#configuramos el /etc/hosts
IP=$(ogGetIpAddress)
echo "127.0.0.1       localhost" > /etc/hosts
echo "$IP              $HOSTNAME" >> /etc/hosts

### conft net
echo "auto lo " > /etc/network/interfaces
echo "iface lo inet loopback" >> /etc/network/interfaces


mkdir -p /var/run/network
cd /var/run/network
touch ifstate

/etc/init.d/networking restart
ifup lo
