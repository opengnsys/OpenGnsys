#!/bin/bash
##########################################################
#####Configurador para el servidor linux v0.0.7 que albergara Advanced Deploy enViorenment###########
# Liberado bajo licencia GPL <http://www.gnu.org/licenses/gpl.html>################
############# 2008 Antonio Jesús Doblas Viso##########################
########### Universidad de Malaga (Spain)############################
##########################################################
if [ $# = 0 ]
then
	echo "configurador del servicio tftpboot, dhcpd y nfs para el entrono EAC"
	echo "admite como parametro:  str_IP str_netmask str_gateway str_subnet str_broadcast str_dns str_hostname"
	echo "ejemplo: EAC_conf_server.sh 172.16.72.242 255.255.255.0 172.16.72.254 172.16.72.255 150.214.40.11 EACadi"
	exit
fi
if [ $# = 7 ]
then
##########
	iphost=$1	
	ipmask=$2
	ipgateway=$3
	ipsubnet=$4
	ipbroadcast=$5
	ipdns=$6
	name=$7
	ip3octetos=`echo $1 | awk -F. '{print $1 "." $2 "." $3}'`
	
	

	echo "configurando la interfaz de rez para $iphost con netmask $ipmask y hostname $name"

cat > /etc/network/interfaces <<EOF
auto lo
iface lo inet loopback
auto eth0
iface eth0 inet static
address $iphost
netmask $ipmask
gateway $ipgateway
EOF

	/etc/init.d/networking restart

	echo "configurando /etc/hosts.allow para $ipsubred"
cat > /etc/hosts.allow <<EOF
all:$ipsubred/$ipmask
EOF

	echo "configurando /etc/hosts "
cat > /etc/hosts <<EOF
127.0.0.1		localhost
$iphost		$name
EOF

	echo "configurando /etc/hostname "
cat > /etc/hostname <<EOF
$name
EOF
	/etc/init.d/hostname.sh start
	
echo "configurando el servidor dns"
cat > /etc/resolv.conf <<EOF
nameserver $ipdns
EOF

	echo "configurando el servidor dhcp"
cat > /etc/dhcp3/dhcpd.conf <<EOF
option routers                  $ipgateway;
option broadcast-address        $ipbroadcast;
option subnet-mask              $ipmask;
option domain-name-servers      $ipdns;
ddns-update-style none;
autoritative;
subnet $ipsubnet netmask $ipmask  {
range ${ip3octetos}.20 ${ip3octetos}.30;
next-server ${iphost};
filename "pxelinux.0";
host r60 {
        hardware ethernet 00:13:77:66:4e:60;
        fixed-address $ip3octetos.152;
        }
}
EOF

	/etc/init.d/dhcp3-server restart

	echo "configurando el servidor nfs"
cat > /etc/exports <<EOF
/var/EAC/nfsroot/stable ${ipsubnet}/${ipmask}(ro,no_subtree_check,no_root_squash,async)
/var/EAC/admin ${ipsubnet}/${ipmask}(ro,no_subtree_check,no_root_squash,sync)
/var/EAC/hdimages ${ipsubnet}/${ipmask}(rw,no_subtree_check,no_root_squash,sync)
/var/EAC/hosts ${ipsubnet}/${ipmask}(rw,no_subtree_check,no_root_squash,sync)
EOF
	/etc/init.d/nfs-kernel-server restart


	echo "configuramos el fichero default del pxelinux.cfg"
cat > /tftpboot/pxelinux.cfg/default <<EOF
DEFAULT pxe

LABEL pxe
KERNEL nfsrootstable/vmlinuz-2.6.27-7-server
#APPEND root=/dev/nfs initrd=nfsrootstable/initrd.img-2.6.27-7-server nfsroot=${iphost}:/var/EAC/nfsroot/stable ip=dhcp ro vga=791 lba acpi=off pci=nomsi allowed_drive_mask=0
APPEND root=/dev/nfs initrd=nfsrootstable/initrd.img-2.6.27-7-server nfsroot=${iphost}:/var/EAC/nfsroot/stable ip=dhcp ro vga=788 irqpoll nolapic  acpi=off pci=nomsi

label dos
  kernel floppies/memdisk
  append initrd=/floppies/dos.img

LABEL 1 
LOCALBOOT 0

LABEL 11
kernel syslinux/chain.c32
append hd0 1


LABEL 12
kernel syslinux/chain.c32
append hd0 2

LABEL 13
kernel syslinux/chain.c32
append hd0 3

LABEL 2
kernel syslinux/chain.c32
append hd1 0


LABEL mbr
	LOCALBOOT 0

PROMPT 1
TIMEOUT 18
EOF
fi
