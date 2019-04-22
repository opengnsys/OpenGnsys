#!/bin/bash

find /tmp/opengnsys_installer/ -name .svn -type d -exec rm -fr {} \; 2>/dev/null;

#plymouth
apt-get -y install plymouth plymouth-theme-script


#plymoutyh
update-alternatives --install /lib/plymouth/themes/default.plymouth default.plymouth /lib/plymouth/themes/opengnsys/opengnsys.plymouth 100
update-alternatives --set default.plymouth /lib/plymouth/themes/opengnsys/opengnsys.plymouth 

mkdir -p /etc/initramfs-tools/conf.d
echo "FRAMEBUFFER=y" > /etc/initramfs-tools/conf.d/splash 

history -c
