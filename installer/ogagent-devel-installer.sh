#!/bin/bash
#/**
#@file     ogagent-devel-installer.sh
#@brief    Script to download and prepare the environmnt to compile OGAgent packages.
#@warning  Some operations need "root" privileges.
#@note     This script will make the "ogagent" directory with 1.5 GiB approx.
#@version  1.0 - Initial version for OpenGnsys 1.1.0.
#@author   Ramón M. Gómez, ETSII Universidad de Sevilla
#@date     2016-04-07
#*/ ##


# Variables.
PROGDIR="$PWD/ogagent"
BRANCH="master"
SVNURL="https://github.com/opengnsys/OpenGnsys/branches/$BRANCH/admin/Sources/Clients/ogagent"

# Show prerequisites needed to build the environment.
mkdir -p $PROGDIR || exit 1
cat << EOT

OGAgent devoloping environment installation

Prerequisites:
- Install packages, if needed:
  - Subversion
  - Wine for 32-bit (Winetricks may be required)
  - Python 2.7 with pyqt4-dev-tools
  - realpath
  - dpkg-dev
  - rpmbuild
  - xar
Press [Enter] key when ready to continue.
EOT
read

# Importing OGAgent source code.
svn export --force $SVNURL $PROGDIR || exit 1
# Downloading Visual C++ Redistributable.
wget --unlink https://download.microsoft.com/download/5/B/C/5BC5DBB3-652D-4DCE-B14A-475AB85EEF6E/vcredist_x86.exe

# Update PyQt components.
pushd ogagent/src >/dev/null
./update.sh
popd >/dev/null

# Showing instructions to configure Wine.
cat << EOT

Manual actions:
- After all downloads, install Gecko for Wine, if needed.
- Press [Esc] key or "Cancel" button on Winetricks screen, if needed.
- Accept default settings for all other components.
- Uncheck all options on "Completing NSIS Setup" screen.
Press [Enter] key to init downloads. 

EOT
read

# Downloading and configuring Wine prerequisites.
pushd ogagent/windows >/dev/null
./py2exe-wine-linux.sh
cp -a build.bat ogagent.nsi ..
ln -s ../../.. wine/drive_c/ogagent
popd >/dev/null

# Download, compile and install bomutils.
mkdir -p ogagent/macos/downloads
svn export https://github.com/hogliux/bomutils.git/trunk ogagent/macos/downloads/bomutils
pushd ogagent/macos/downloads/bomutils >/dev/null
make && sudo make install
popd >/dev/null

# Build OGAgent for GNU/Linux.
pushd $PROGDIR/linux >/dev/null
sudo ./build-packages.sh
popd >/dev/null

# Build OGAgent for macOS.
pushd $PROGDIR/macos >/dev/null
./build-pkg.sh
popd >/dev/null

# Build OGAgent for Windows. 
pushd $PROGDIR/windows >/dev/null
./build-windows.sh
popd >/dev/null

# Showing instructions to rebuild OGAgent packages.
cat << EOT

How to rebuild OGAgent packages
-------------------------------
OGAgent project source code is available in $PROGDIR/src directory.

- Commands to update PyQt graphical components for OGAgnet:
    cd $PROGDIR/src
    ./update.sh

- Commands to rebuild Linux packages:
    cd $PROGDIR/linux
    sudo ./build-packages.sh

- Commands to rebuild macOS package:
    cd $PROGDIR/macos
    ./build-pkg.sh

- Commands to rebuild Windows installer:
    cd $PROGDIR/windows
    ./build-windows.sh

OGAgent packages will be created into $PROGDIR directory.

EOT

