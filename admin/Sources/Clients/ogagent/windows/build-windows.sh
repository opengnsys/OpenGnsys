#!/bin/bash
cd "$(dirname "$0")"
export WINEARCH=win32
export WINEPREFIX=$PWD/wine
grep -o "[0-9]*\.[0-9]*\.[0-9]*" ../src/VERSION > VERSION
wine cmd /c c:\\ogagent\\build.bat
chmod -x ../OGAgentSetup*.exe
