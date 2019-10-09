%define _topdir %(echo $PWD)/rpm
%define name ogagent
%define version 0.0.0
%define release 1
%define buildroot %{_topdir}/%{name}-%{version}-%{release}-root

BuildRoot: %{buildroot} 
Name: %{name}
Version: %{version}
Release: %{release}
Summary: OpenGnsys Agent for Operating Systems
License: BSD3
Group: Admin
Requires: chkconfig initscripts python-six python-requests PyQt4 libXScrnSaver
Vendor: OpenGnsys Project
URL: https://opengnsys.es/
Provides: ogagent

%define _rpmdir ../
%define _rpmfilename %%{NAME}-%%{VERSION}-%%{RELEASE}.%%{ARCH}.rpm


%install
curdir=`pwd`
cd ../..
make DESTDIR=$RPM_BUILD_ROOT DISTRO=rh install-ogagent
cd $curdir

%clean
rm -rf $RPM_BUILD_ROOT
curdir=`pwd`
cd ../..
make DESTDIR=$RPM_BUILD_ROOT DISTRO=rh clean
cd $curdir


%post
systemctl enable ogagent.service > /dev/null 2>&1

%preun
systemctl disable ogagent.service > /dev/null 2>&1
systemctl stop ogagent.service > /dev/null 2>&1

%postun
# $1 == 0 on uninstall, == 1 on upgrade for preun and postun (just a reminder for me... :) )
if [ $1 -eq 0 ]; then
    rm -rf /etc/ogagent
    rm /var/log/ogagent.log
fi
# And, posibly, the .pyc leaved behind on /usr/share/OGAgent
rm -rf /usr/share/OGAgent > /dev/null 2>&1

%description
This package provides the required components to allow this machine to work on an environment managed by OpenGnsys.

%files
%defattr(-,root,root)
/etc/ogagent
/etc/xdg/autostart/OGAgentTool.desktop
/etc/init.d/ogagent
/usr/bin/OGAgentTool-startup
/usr/bin/ogagent
/usr/bin/OGAgentTool
/usr/share/OGAgent/*
/usr/share/autostart/OGAgentTool.desktop
