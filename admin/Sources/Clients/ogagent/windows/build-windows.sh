#!/bin/bash
export WINEARCH=win32
export WINEPREFIX=$(realpath $(dirname $0)/wine)
wine cmd /c c:\\ogagent\\build.bat
