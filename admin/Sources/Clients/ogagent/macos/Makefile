#!/usr/bin/make -f
# -*- makefile -*-

# Directories
SOURCEDIR := ../src
DESTDIR := /usr/local
LIBDIR := $(DESTDIR)/share/OGAgent
BINDIR := $(DESTDIR)/bin
SBINDIR = $(DESTDIR)/sbin
#APPSDIR := $(DESTDIR)/usr/share/applications
CFGDIR := $(DESTDIR)/etc/ogagent
INITDIR := /Library/LaunchDaemons
#XDGAUTOSTARTDIR := $(DESTDIR)/etc/xdg/autostart
#KDEAUTOSTARTDIR := $(DESTDIR)/usr/share/autostart

PYC := $(shell find $(SOURCEDIR) -name '*.py[co]')
CACHES := $(shell find $(SOURCEDIR) -name '__pycache__')

clean:
	rm -rf $(PYC) $(CACHES)

install:
	dependencies
	install-ogagent

dependencies:
	easy_install pip	# Si no existe pip.
	# comprobar versión de six, descargar e instalar con easy_install.
	easy_install netifaces

install-ogagent:
	mkdir -p $(LIBDIR)
	mkdir -p $(BINDIR)
	mkdir -p $(SBINDIR)
	mkdir -p $(CFGDIR)
	
	mkdir -p $(LIBDIR)/img
	
	# Cleans up .pyc and cache folders
	rm -f $(PYC) $(CACHES)
	
	cp -r $(SOURCEDIR)/opengnsys $(LIBDIR)/opengnsys
	cp -r $(SOURCEDIR)/cfg $(LIBDIR)/cfg
	cp $(SOURCEDIR)/img/oga.png $(LIBDIR)/img

	cp $(SOURCEDIR)/OGAgentUser.py $(LIBDIR)
	# QT Dialogs & resources
	#cp $(SOURCEDIR)/*_ui.py $(LIBDIR)
	#cp $(SOURCEDIR)/OGAgent_rc.py $(LIBDIR)
	
	# Autostart elements for gnome/kde
	#cp desktop/OGAgentTool.desktop $(XDGAUTOSTARTDIR)
	#cp desktop/OGAgentTool.desktop $(KDEAUTOSTARTDIR)
	
	# scripts
	cp scripts/ogagent $(BINDIR)
	cp scripts/OGAgentTool-startup $(BINDIR)
	cp scripts/OGAgentTool $(BINDIR)
	cp scripts/es.opengnsys.ogagent.plist $(INITDIR)
	
	# Fix permissions
	chmod 755 $(BINDIR)/ogagent
	chmod 755 $(BINDIR)/OGAgentTool-startup
	chmod 755 $(LIBDIR)/OGAgentUser.py
	chmod 600 $(LIBDIR)/cfg/ogagent.cfg
	
	ln -fs $(LIBDIR)/cfg/ogagent.cfg $(CFGDIR)
	ln -fs $(LIBDIR)/cfg/ogclient.cfg $(CFGDIR)
	
uninstall:
	rm -rf $(LIBDIR)
	rm -f $(BINDIR)/ogagent $(BINDIR)/OGAgent-Tool*
	rm -rf $(CFGDIR)
