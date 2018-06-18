#!/bin/bash

cd $(dirname "$0")

# Build package
dpkg-buildpackage -b -d

