#!/bin/sh

# We need:
# * Wine (32 bit)
# * winetricks (in some distributions)

export WINEARCH=win32
WINE=wine

download() {
    mkdir downloads
    # Get needed software
    cd downloads
    wget -nd https://www.python.org/ftp/python/2.7.11/python-2.7.11.msi -O python-2.7.msi
    wget -nd http://download.microsoft.com/download/7/9/6/796EF2E4-801B-4FC4-AB28-B59FBF6D907B/VCForPython27.msi
    wget -nd https://bootstrap.pypa.io/get-pip.py
    wget -nd http://sourceforge.net/projects/pywin32/files/pywin32/Build%20220/pywin32-220.win32-py2.7.exe/download -O pywin32-install.exe
    wget -nd http://sourceforge.net/projects/py2exe/files/py2exe/0.6.9/py2exe-0.6.9.win32-py2.7.exe/download -O py2exe-install.exe
    wget -nd http://prdownloads.sourceforge.net/nsis/nsis-3.0rc1-setup.exe?download -O nsis-install.exe
    wget -nd http://sourceforge.net/projects/pyqt/files/PyQt4/PyQt-4.11.4/PyQt4-4.11.4-gpl-Py2.7-Qt4.8.7-x32.exe/download -O pyqt-install.exe
    wget -nd http://nsis.sourceforge.net/mediawiki/images/d/d7/NSIS_Simple_Firewall_Plugin_1.20.zip
    cd ..
}

install_python() {
    WINEPREFIX=`pwd`/wine
    export WINEPREFIX
    if which winetricks &>/dev/null; then
        echo "Setting up wine prefix (using winetricks)"
        winetricks
    fi
    
    cd downloads
    echo "Installing python"
    $WINE msiexec /qn /i python-2.7.msi
    echo "Installing vc for python"
    $WINE msiexec /qn /i VCForPython27.msi
    
    echo "Installing pywin32 (needs X)"
    $WINE pywin32-install.exe
    echo "Installing py2exe (needs X)"
    $WINE py2exe-install.exe
    echo "Installing pyqt"
    $WINE pyqt-install.exe
    echo "Installing nsis (needs X?)"
    $WINE nsis-install.exe
    
    cd ..
}

setup_pip() {
    echo "Seting up pip..."
    #mkdir $WINEPREFIX/drive_c/temp
    #cp downloads/get-pip.py $WINEPREFIX/drive_c/temp
    #cd $WINEPREFIX/drive_c/temp
    #$WINE c:\\Python27\\python.exe get-pip.py
    wine c:\\Python27\\python -m pip install --upgrade pip
}    

install_packages() {
    echo "Installing required packages"    
    wine c:\\Python27\\python -m pip install requests
    wine c:\\Python27\\python -m pip install pycrypto
    wine c:\\Python27\\python -m pip install six
    # Copy nsis required NSIS_Simple_Firewall_Plugin_1
    echo "Copying simple firewall plugin for nsis installer"
    unzip -o downloads/NSIS_Simple_Firewall_Plugin_1.20.zip SimpleFC.dll -d $WINEPREFIX/drive_c/Program\ Files/NSIS/Plugins/x86-ansi/
    unzip -o downloads/NSIS_Simple_Firewall_Plugin_1.20.zip SimpleFC.dll -d $WINEPREFIX/drive_c/Program\ Files/NSIS/Plugins/x86-unicode/
}

download
install_python
setup_pip
install_packages


