#!/bin/bash
# Script de instalación de paquetes de software.

export LANGUAGE=C
export LC_ALL=C
export LANG=C
#LOG_FILE=/tmp/boot-tools-software.txt

#ADV
#Desactivamos upstart
dpkg-divert --local --rename --add /sbin/initctl 
ln -s /bin/true /sbin/initctl
#TEST desactivamos upstart
#apt-get update; apt-get install --no-install-recommends dbus; dbus-uuidgen > /var/lib/dbus/machine-id; dpkg-divert --local --rename --add /sbin/initctl; ln -s /bin/true /sbin/initctl

#cp /tmp/sources.list /etc/apt/sources.list
#Limpiamos y actualizamos los repositorios apt
apt-get clean
apt-get update
apt-get upgrade -y

#Desactivamos el hook del oginitrd.img para evitar problemas, al final de este escripts se activará
mv /etc/initramfs-tools/hooks/oghooks /etc/initramfs-tools/

# Preparamos el mtab  necesario para la instalacion correcta de paquetes.
echo "/dev/sda1 / ext4 rw,errors=remount-ro 0 0" > /etc/mtab

# Instalamos el kernel.
# Deteccion de la versión y kernel a usar
OGCLIENTCFG=${OGCLIENTCFG:-/tmp/ogclient.cfg}
[ -f $OGCLIENTCFG ] && source $OGCLIENTCFG
OSRELEASE=${OSRELEASE:-$(uname -a | awk '{print $3}')}
if [ -z "$OSARCH" ]; then
	uname -a | grep x86_64 > /dev/null && OSARCH="amd64" || OSARCH="i386"
fi
# inicio de la instalacion
if [ "$OSRELEASE" == "3.7.6-030706-generic" ]; then
	# Descargar e instalar Kernel 3.7.
	mkdir -p /tmp/kernel
	pushd /tmp/kernel
	apt-get -y --force-yes install wget crda libnl-3-200 libnl-genl-3-200 wireless-regdb
	wget http://kernel.ubuntu.com/~kernel-ppa/mainline/v3.7.6-raring/linux-image-3.7.6-030706-generic_3.7.6-030706.201302040006_$OSARCH.deb
	wget http://kernel.ubuntu.com/~kernel-ppa/mainline/v3.7.6-raring/linux-image-extra-3.7.6-030706-generic_3.7.6-030706.201302040006_$OSARCH.deb
	wget http://kernel.ubuntu.com/~kernel-ppa/mainline/v3.7.6-raring/linux-headers-3.7.6-030706-generic_3.7.6-030706.201302040006_$OSARCH.deb
	wget http://kernel.ubuntu.com/~kernel-ppa/mainline/v3.7.6-raring/linux-headers-3.7.6-030706_3.7.6-030706.201302040006_all.deb
	dpkg -i *.deb
	popd
	rm -fr /tmp/kernel
else
	# Instalar Kernel del repositorio de paquetes.
	apt-get -y --force-yes install linux-image-${OSRELEASE} linux-headers-${OSRELEASE}
	apt-get -y --force-yes install linux-image-extra-${OSRELEASE} 2>/dev/null
fi


#Eliminamos cualquier busybox previo:  antes del busybox
#apt-get -y --force-yes remove busybox
#apt-get -y --force-yes remove busybox-static

#estos paquetes ofrecen interaccion.
# si es actualización, ya existe el fichero /etc/ssh/ssh_config
apt-get -y install sshfs 
apt-get -y install console-data

#comenzamos con la instalación de los paquetes a instalar.
for group in `find /usr/bin/boot-tools/listpackages/ -name sw.*`
do
	echo "Instalando el grupo de paquetes almacenados en $group"
	for package in ` awk /^install/'{print $2}' $group `
	do
		echo -n $package
		#ADV
		#TEST
		#apt-get -y --force-yes install --no-install-recommends $package &>/dev/null
		apt-get -y --force-yes  install $package &>/dev/null 
		RETVAL=$?
		if [ $RETVAL == 0 ]
		then
			echo " : OK - Paquete instalado correctamente (codigo interno de apt-get $RETVAL)"
		else
			echo " : Error Paquete $package del grupo $group (codigo interno de apt-get $RETVAL) "
			echo "Pulse [Intro] para continuar"
			read
		fi
	done	
done

# Instalar módulos que algunos paquetes puedan tener pendientes de compilar.
echo "Instalando módulos adicionales con DKMS"
while read -e mod vers; do
	echo -n "Intalando módulo $mod v$vers"
	dkms install -m $mod -v $vers &>/dev/null
	RETVAL=$?
	if [ $RETVAL == 0 ]; then
		echo " : OK - Módulo instalado correctamente (codigo interno de dkms $RETVAL)"
	else
		echo " : Error módulo $mod (codigo interno de dkms $RETVAL) "
		echo "Pulse [Intro] para continuar"
		read
	fi
done < <(dkms status 2>/dev/null | awk -F, '$3~/added/ {print $1,$2}')

#Activamos el hook del oginitrd.img 
mv /etc/initramfs-tools/oghooks /etc/initramfs-tools/hooks/

# Dejamos el mtab como al principio
echo "   " > /etc/mtab


#ADV
#TEST
#apt-get install localepurge
#localepurge
#rm /var/lib/dbus/machine-id; rm /sbin/initctl; dpkg-divert --rename --remove /sbin/initctl; 
#FIN ADV

apt-get clean
apt-get autoclean
apt-get autoremove

history -c

