#!/bin/bash

cd $(dirname "$0")
top=`pwd`

[ -r ../src/VERSION ] && VERSION="$(cat ../src/VERSION)" || VERSION="1.1.0"
RELEASE="1"

# Debian based
dpkg-buildpackage -b -d

cat ogagent-template.spec | 
  sed -e s/"version 0.0.0"/"version ${VERSION}"/g |
  sed -e s/"release 1"/"release ${RELEASE}"/g > ogagent-$VERSION.spec
  
# Now fix dependencies for opensuse
cat ogagent-template.spec | 
  sed -e s/"version 0.0.0"/"version ${VERSION}"/g |
  sed -e s/"name ogagent"/"name ogagent-opensuse"/g |
  sed -e s/"PyQt4"/"python-qt4"/g |
  sed -e s/"libXScrnSaver"/"libXss1"/g > ogagent-opensuse-$VERSION.spec


# Right now, ogagent-xrdp-1.7.0.spec is not needed
for pkg in ogagent-$VERSION.spec ogagent-opensuse-$VERSION.spec; do
    
    rm -rf rpm
    for folder in SOURCES BUILD RPMS SPECS SRPMS; do
        mkdir -p rpm/$folder
    done
    
    rpmbuild -v -bb --clean --buildroot=$top/rpm/BUILD/$pkg-root --target noarch $pkg 2>&1
done

#rm ogagent-$VERSION
