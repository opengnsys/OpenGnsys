#!/bin/bash

cd $(dirname "$0")
top=`pwd`

[ -r ../src/VERSION ] && VERSION="$(cat ../src/VERSION)" || VERSION="1.1.0"
RELEASE="1"

# Debian based
dpkg-buildpackage -b -d

