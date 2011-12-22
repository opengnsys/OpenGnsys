#!/bin/bash
export LANGUAGE=C
export LC_ALL=C
export LANG=C
#LOG_FILE=/tmp/boot-tools-software.txt

#Desactivamos upstart
dpkg-divert --local --rename --add /sbin/initctl 
ln -s /bin/true /sbin/initctl

#cp /tmp/sources.list /etc/apt/sources.list
#Limpiamos y actualizamos los repositorios apt
apt-get clean
apt-get update

#Desactivamos el hook del oginitrd.img para evitar problemas.
mv /etc/initramfs-tools/hooks/oghooks /etc/initramfs-tools/

echo " /dev/sda1 / ext4 rw,errors=remount-ro 0 0   " > /etc/mtab


#Instalamos el kernel.
export OSDISTRIB=$(lsb_release -i | awk -F: '{sub(/\t/,""); print $2}') 2>/dev/null
export OSCODENAME=$(cat /etc/lsb-release | grep CODENAME | awk -F= '{print $NF}')
export OSRELEASE=$(uname -a | awk '{print $3}')
uname -a | grep x86_64 > /dev/null  &&  export OSARCH=amd64 || export OSARCH=i386
export OSHTTP="http://es.archive.ubuntu.com/ubuntu/"
# software Kernel
apt-get -y --force-yes install linux-image-${OSRELEASE} linux-headers-${OSRELEASE} linux-image-$RELEASE 

#Eliminamos cualquier busybox previo:  antes del busybox.
apt-get -y --force-yes remove busybox

#estos paquetes ofrecen interaccion.
# si es actualización, ya existe el fichero /etc/ssh/ssh_config
apt-get -y install sshfs 

apt-get -y install console-data

for group in `find /usr/bin/boot-tools/listpackages/ -name sw.*`
do
	echo "Instalando el grupo de paquetes almacenados en $group"
	for package in ` awk /^install/'{print $2}' $group `
	do
		echo -n $package
		apt-get -y --force-yes  install $package &>/dev/null
		RETVAL=$?
		if [ $RETVAL == 0 ]
		then
			echo " : OK - Paquete instalado correctamente (codigo interno de apt-get $RETVAL)"
		else
			echo " : Error Paquete $package del grupo $group (codigo interno de apt-get $RETVAL) "
			echo "Pulse pause para continuar"
			read
		fi
	done	
done


#Activamos el hook del oginitrd.img 
mv /etc/initramfs-tools/oghooks /etc/initramfs-tools/hooks/

echo "   " > /etc/mtab

apt-get clean
apt-get autoclean
apt-get autoremove

history -c
