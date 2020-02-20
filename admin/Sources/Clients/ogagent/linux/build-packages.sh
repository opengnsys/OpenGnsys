#!/bin/bash

cd $(dirname "$0")
top=$(pwd)

VERSION="$(cat ../src/VERSION 2>/dev/null)" || VERSION="1.1.1"
RELEASE="1"

# Debian based
dpkg-buildpackage -b -d

# Fix version number.
sed -e "s/version 0.0.0/version ${VERSION}/g" \
    -e "s/release 1/release ${RELEASE}/g" ogagent-template.spec > ogagent-$VERSION.spec
  
# Now fix dependencies for opensuse
sed -e "s/name ogagent/name ogagent-opensuse/g" \
    -e "s/version 0.0.0/version ${VERSION}/g" \
    -e "s/release 1/release ${RELEASE}/g" \
    -e "s/chkconfig//g" \
    -e "s/initscripts/insserv/g" \
    -e "s/PyQt4/python-qt4/g" \
    -e "s/libXScrnSaver/libXss1/g" ogagent-template.spec > ogagent-opensuse-$VERSION.spec


# Right now, ogagent-xrdp-1.7.0.spec is not needed
for pkg in ogagent-$VERSION.spec ogagent-opensuse-$VERSION.spec; do
    
    rm -rf rpm
    for folder in SOURCES BUILD RPMS SPECS SRPMS; do
        mkdir -p rpm/$folder
    done
    
    rpmbuild -v -bb --clean --buildroot=$top/rpm/BUILD/$pkg-root --target noarch $pkg 2>&1
done

#rm ogagent-$VERSION
