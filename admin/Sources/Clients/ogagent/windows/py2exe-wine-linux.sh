#!/bin/sh

# We need:
# * Wine (32 bit)
# * winetricks (in some distributions)

export WINEARCH=win32 WINEPREFIX=$PWD/wine WINEDEBUG=fixme-all
WINE=wine

download() {
    mkdir downloads
    # Get needed software
    cd downloads
    wget -nd https://www.python.org/ftp/python/2.7.14/python-2.7.14.msi -O python-2.7.msi
    wget -nd http://download.microsoft.com/download/7/9/6/796EF2E4-801B-4FC4-AB28-B59FBF6D907B/VCForPython27.msi
    wget -nd https://bootstrap.pypa.io/get-pip.py
    wget -nd http://sourceforge.net/projects/py2exe/files/py2exe/0.6.9/py2exe-0.6.9.win32-py2.7.exe/download -O py2exe-install.exe
    wget -nd http://prdownloads.sourceforge.net/nsis/nsis-3.0rc1-setup.exe?download -O nsis-install.exe
    wget -nd http://sourceforge.net/projects/pyqt/files/PyQt4/PyQt-4.11.4/PyQt4-4.11.4-gpl-Py2.7-Qt4.8.7-x32.exe/download -O pyqt-install.exe
    wget -nd http://nsis.sourceforge.net/mediawiki/images/d/d7/NSIS_Simple_Firewall_Plugin_1.20.zip
    cd ..
}

install_python() {
    if which winetricks &>/dev/null; then
        echo "Setting up wine prefix (using winetricks)"
        winetricks
    fi

    cd downloads
    echo "Installing python"
    $WINE msiexec /qn /i python-2.7.msi
    echo "Installing vc for python"
    $WINE msiexec /qn /i VCForPython27.msi

    echo "Installing py2exe (needs X)"
    $WINE py2exe-install.exe
    echo "Installing pyqt (needs X)"
    $WINE pyqt-install.exe
    echo "Installing nsis (needs X?)"
    $WINE nsis-install.exe

    cd ..
}

setup_pip() {
    echo "Seting up pip..."
    $WINE C:\\Python27\\python -m pip install --upgrade pip
}

install_packages() {
    echo "Installing pywin32"
    $WINE C:\\Python27\\python -m pip install pywin32
    echo "Installing required packages"
    $WINE C:\\Python27\\python -m pip install requests
    $WINE C:\\Python27\\python -m pip install six
    # Using easy_install instead of pip to install pycrypto
    $WINE C:\\Python27\\Scripts\\easy_install http://www.voidspace.org.uk/python/pycrypto-2.6.1/pycrypto-2.6.1.win32-py2.7.exe
    # Copy nsis required NSIS_Simple_Firewall_Plugin_1
    echo "Copying simple firewall plugin for nsis installer"
    unzip -o downloads/NSIS_Simple_Firewall_Plugin_1.20.zip SimpleFC.dll -d $WINEPREFIX/drive_c/Program\ Files/NSIS/Plugins/x86-ansi/
    unzip -o downloads/NSIS_Simple_Firewall_Plugin_1.20.zip SimpleFC.dll -d $WINEPREFIX/drive_c/Program\ Files/NSIS/Plugins/x86-unicode/
}

download
install_python
setup_pip
install_packages

