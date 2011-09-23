#!/bin/bash
dpkg-divert --local --rename --add /sbin/initctl 
ln -s /bin/true /sbin/initctl
#mv /etc/fstab /etc/fstab.original 2>/dev/null 
#mv /etc/mtab /etc/mtab.original 2>/dev/null 

#TODO: fichero etc/hosts
#TODO: fichero etc/resolv.conf
echo "ogClient" > /etc/hostname

#export PASSROOT=og
#dpkg-reconfigure passwd
#echo "root:$PASSROOT" | chpasswd


#for i in pts/0 pts/1 pts/2 pts/3 do
#
#
#done
#TODO: introducir mas consoluas para el acceso como root.
echo "pts/0" >> /etc/securetty
echo "pts/1" >> /etc/securetty
echo "pts/2" >> /etc/securetty
echo "pts/3" >> /etc/securetty