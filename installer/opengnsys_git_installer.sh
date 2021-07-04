#!/bin/bash
#/**
#@file   opengnsys_git_installer.sh
#@brief  Script para la instalación del repositorio git (provisional)
#@return
#@exception 1 Sólo ejecutable por root
#@exception 2 No encuentra la clave pública dentro del ogLive por defecto (usar setsslkey)
#@note  Se crean dos repositorio git separados para linux y windows
#*/
OGDIR=/opt/opengnsys
OGDIRIMAGES=$OGDIR/images
BASEDIR=base.git
TMPGIT=/tmp/git
ENGINECFG=$OGDIR/client/etc/engine.cfg

PATH=$PATH:$OGDIR/bin
TFTPDIR=$OGDIR/tftpboot
INITRD=oginitrd.img
TMPDIR=/tmp/oglive$$
SSHUSER=opengnsys
SSHDIR="/home/$SSHUSER/.ssh"

# Control básico de errores.
if [ "$USER" != "root" ]; then
    echo "$PROG: Error: sólo ejecutable por root" >&2
    exit 1
fi

# Autenticación del usuario opengnsys con clave pública desde los ogLive
# Requiere que todos los ogLive tengan la misma clave publica (utilizar setsslkey)

# Tomamos la clave publica del cliente por defecto
OGLIVEDEFAULT=$(oglivecli list |awk -v NUM=$(oglivecli get-default) '{if ($1 == NUM) print $2}')
CLIENTINITRD="$TFTPDIR/$OGLIVEDEFAULT/$INITRD"

# Si me salgo con error borro el directorio temporal
trap "rm -rf $TMPDIR 2>/dev/null" 1 2 3 6 9 15 0

if [ -r "$CLIENTINITRD" ]; then
    mkdir -p $TMPDIR
    cd $TMPDIR || exit 3
    gzip -dc "$CLIENTINITRD" | cpio -im
    if [ -r scripts/ssl/id_rsa.pub ]; then
        PUBLICKEY=$(cat scripts/ssl/id_rsa.pub 2>/dev/null)
    fi
fi
# Si la clave publica no existe me salgo con error
if [ "$PUBLICKEY" == "" ]; then
    echo "No se encuentra clave pública dentro del ogLive:"
    echo "    Los oglive deben tener la misma clave pública (utilizar setsslkey)"
    exit 2
fi

[ -d $SSHDIR ] || mkdir -p $SSHDIR
echo $PUBLICKEY >> $SSHDIR/authorized_keys
chmod 400 $SSHDIR/authorized_keys
chown -R $SSHUSER:$SSHUSER $SSHDIR

# Configuramos el servicio ssh para que permita el acceso con clave pública
echo " Configuramos el servicio ssh para que permita el acceso con clave pública."
sed -i s/"^.*PubkeyAuthentication.*$"/"PubkeyAuthentication yes"/ /etc/ssh/sshd_config
systemctl reload ssh

# Instalamos git
apt install git

# Para que el usuario sólo pueda usar git (no ssh)
SHELL=$(which git-shell)
sudo usermod -s $SHELL opengnsys

# Configuramos git
git config --global user.name "OpenGnsys"
git config --global user.email "OpenGnsys@opengnsys.com"

# Creamos repositorio
echo "Creamos repositorio de GIT."
git init --bare $OGDIRIMAGES/$BASEDIR

# Clonamos y realizamos primer commit
git clone $OGDIRIMAGES/$BASEDIR $TMPGIT
cd $TMPGIT
git commit --allow-empty -m "Iniciamos repositorio."
git push

echo "Creamos los árboles de trabajo para linux y windows."
cd $OGDIRIMAGES/$BASEDIR
git branch windowsBare
git branch linuxBare

git worktree add $OGDIRIMAGES/windows.git windowsBare
git worktree add $OGDIRIMAGES/linux.git linuxBare

# No sé si es necesario. Probar a comentarlo
for DIR in windows.git linux.git; do
    cd $OGDIRIMAGES/$DIR
    BARE=${DIR%.git}Bare
    git commit --allow-empty -m "Iniciamos repositorio."
    git push --set-upstream origin $BARE
    git push
done

echo "Creamos los directorios de las ACL"
# Creamos los directorios para las ACL
mkdir $OGDIRIMAGES/WinAcl $OGDIRIMAGES/LinAcl

# Damos permiso al usurio opengnsys
for DIR in base.git linux.git windows.git LinAcl WinAcl; do
    chown -R opengnsys:opengnsys $OGDIRIMAGES/$DIR
done
