#!/bin/bash
#/**
#@file    load2fs.sh
#@brief   Script de carga del 2nd Sistema de Archivos para el cliente OpenGNSys.
#@warning License: GNU GPLv3+
#@version 0.9
#@author  Antonio J. Doblas Viso, Unviersidad de Malaga.
#@date    2010-02-15
#*/
mkdir -p /opt/og2fs
mount /opt/opengnsys/og2ndFS /opt/og2fs -t ext3 -o loop -o ro
cp -R /opt/og2fs/etc/* /etc/   # */
mount /opt/og2fs/usr /usr
mount /opt/og2fs/lib /lib
export PATH=/opt/og2fs/sbin:$PATH
export PATH=/opt/og2fs/bin:$PATH
export PATH=$PATH:/opt/og2fs/opt/drbl/sbin:/usr/local/bin:/usr/local/sbin:/usr/bin:/usr/sbin:/bin:/sbin

# meter aqui el reboot del Boot.lib