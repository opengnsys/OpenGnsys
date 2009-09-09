#!/bin/bash

SVNROOT=$HOME/projects/opengnsys
OGROOT=/opt/opengnsys/trunk
TFTPBOOT=/var/lib/tftpboot

function arguments_parser
{
#    if [ $UID != 0 ]; then
#        echo "No tiene permisos suficientes para ejecutar este script"
#        exit -1
#    fi

    while [ $# -ne 0 ];do
        case $1 in
            ("-t")
            shift
            if [ $# -eq 0 ];then
                echo "Error parseando argumentos"
                return -1
            else
                TFTPBOOT=$1
                shift
            fi
            ;;

            ("-s")
            shift
            if [ $# -eq 0 ];then
                echo "Error parseando argumentos"
                return -1
            else
                SVNROOT=$1
                shift
            fi
            ;;
        esac
    done

    echo $TFTPBOOT
    echo $SVNROOT
}

function create_file_system
{
    mkdir -p $OGROOT
    cp -rf $SVNROOT/opengnsys-client/nfsexports/ $OGROOT/client

    mkdir -p $TFTPBOOT
    mkdir -p /etc/opengnsys/
    mkdir -p /var/log/opengnsys/clients/
    ln -s $TFTPBOOT $OGROOT/tftpboot/
    ln -s /etc/opengnsys/ /opt/opengnsys/etc/
    ln -s /var/log/opengnsys/ $OGROOT/log/

    mkdir -p $OGROOT/bin
    mkdir -p $OGROOT/lib
    mkdir -p $OGROOT/images
}

function tftpboot
{
    cd $OGROOT/tftpboot/

    if [ $? = 1 ]; then
        mkdir -p pxelinux.cfg/
        cd pxelinux.cfg
        echo "default install" > default
        echo "label install" >> default
        echo "    kernel linux" >> default
        echo "    append vga=788 initrd=initrd.gz acpi=on" >> default
        cd ..

        $SVNROOT/opengnsys-client/boot/initrd-generator
    fi
}

function nfsexport
{
    sleep 1
}

arguments_parser $@
