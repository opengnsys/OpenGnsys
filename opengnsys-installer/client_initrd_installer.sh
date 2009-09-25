#!/bin/bash

TFTPBOOT=/var/lib/tftpboot
OGROOT=/opt/opengnsys

function arguments_parser
{
    while [ $# -gt 0 ];do
        case $1 in
            ("-t")
            shift
            if [ $# -eq 0 ];then
                echo "Error parseando argumentos"
                exit -1
            else
                OGROOT=$1
                shift
            fi
            ;;

            ("-s")
            shift
            if [ $# -eq 0 ]; then
                echo "Error parseando argumentos"
			exit -1
            else
                SVNROOT=$1
                shift
            fi
            ;;

            ("-u")
            shift
            UPDATE=true
            ;;
        esac
    done
}

function checking
{
    if [ $UID != 0 ]; then
        echo "No tiene permisos suficientes para ejecutar este script"
        exit -1
    fi
    if [ -z $SVNROOT ]; then
           echo "Necesito saber la ruta de las fuentes del proyecto."
           echo "$0 -s /ruta/hacia/las/fuentes"
           echo "Tambien puedes editar el script y anyadirlo manualmente."
           exit -1
    else
       if [ ! -d $SVNROOT/opengnsys-admin ] ||
          [ ! -d $SVNROOT/opengnsys-client ] ||
          [ ! -d $SVNROOT/opengnsys-doc ] ||
          [ ! -d $SVNROOT/opengnsys-repoman ] ||
          [ ! -d $SVNROOT/opengnsys-installer ] ||
          [ ! -d $SVNROOT/opengnsys-server ] ; then
           echo "La ruta dada para las fuentes del proyecto son incorrectas"
           exit -1;
       fi
    fi
}

function install_necesary_packages
{
    apt-get install pxe dhcp3-server tftpd-hpa nfs-kernel-server
}

function create_file_system
{
    mkdir -p $TFTPBOOT

    mkdir -p $OGROOT

    mkdir -p $OGROOT/bin
    mkdir -p $OGROOT/lib
    mkdir -p $OGROOT/images
    mkdir -p $OGROOT/client
    mkdir -p $OGROOT/client/lib/engine/bin

    mkdir -p /etc/opengnsys
    mkdir -p /var/log/opengnsys/clients

    ln -s $TFTPBOOT $OGROOT/tftpboot
    ln -s /etc/opengnsys/ $OGROOT/etc
    ln -s /var/log/opengnsys/ $OGROOT/log

    cp -ar $SVNROOT/opengnsys-client/nfsexport/* $OGROOT/client
    cp -ar $SVNROOT/opengnsys-client/engine/*.lib $OGROOT/client/lib/engine/bin
}

function install_dhcpd
{
    cat $SVNROOT/opengnsys-server/DHCP/dhcpd.conf >> /etc/dhcp3/dhcpd.conf
    /etc/init.d/dhcp3-server restart
    echo "Revise el archivo /etc/dhcp3/dhcpd.conf para configurarlo para su red"
}

function install_tftpboot
{
    mkdir -p $OGROOT/tftpboot/pxelinux.cfg/
    cd $OGROOT/tftpboot/pxelinux.cfg/
    cat $SVNROOT/opengnsys-server/PXE/pxelinux.cfg/default >> default
    $SVNROOT/opengnsys-client/boot/initrd-generator
    cd -
}

function install_nfsexport
{
    cat $SVNROOT/opengnsys-server/NFS/exports >> /etc/exports
    /etc/init.d/nfs-kernel-server restart

    echo "Revise el archivo /etc/exports para configurarlo para su red"
}

arguments_parser $@
checking

if [ -z $UPDATE ]; then
    install_necesary_packages
    create_file_system
    install_dhcpd
    install_tftpboot
    install_nfsexport
else
    create_file_system
fi
