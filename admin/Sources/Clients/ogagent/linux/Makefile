#!/usr/bin/make -f
# -*- makefile -*-

# Directories
SOURCEDIR := ../src
LIBDIR := $(DESTDIR)/usr/share/OGAgent
BINDIR := $(DESTDIR)/usr/bin
SBINDIR = $(DESTDIR)/usr/sbin
APPSDIR := $(DESTDIR)/usr/share/applications
CFGDIR := $(DESTDIR)/etc/ogagent
INITDIR := $(DESTDIR)/etc/init.d
XDGAUTOSTARTDIR := $(DESTDIR)/etc/xdg/autostart
KDEAUTOSTARTDIR := $(DESTDIR)/usr/share/autostart

PYC := $(shell find $(SOURCEDIR) -name '*.py[co]')
CACHES := $(shell find $(SOURCEDIR) -name '__pycache__')

clean:
	rm -rf $(PYC) $(CACHES) $(DESTDIR)

install-ogagent:
	rm -rf $(DESTDIR)
	mkdir -p $(LIBDIR)
	mkdir -p $(BINDIR)
	mkdir -p $(SBINDIR)
	mkdir -p $(APPSDIR)
	mkdir -p $(CFGDIR)
	mkdir -p $(XDGAUTOSTARTDIR)
	mkdir -p $(KDEAUTOSTARTDIR)
	mkdir -p $(LIBDIR)/img

	# Cleans up .pyc and cache folders
	rm -f $(PYC) $(CACHES)

	cp -r $(SOURCEDIR)/opengnsys $(LIBDIR)/opengnsys
	cp -r $(SOURCEDIR)/cfg $(LIBDIR)/cfg
	cp $(SOURCEDIR)/img/oga.png $(LIBDIR)/img

	cp $(SOURCEDIR)/OGAgentUser.py $(LIBDIR)
	# QT Dialogs & resources
	cp $(SOURCEDIR)/*_ui.py $(LIBDIR)
	cp $(SOURCEDIR)/OGAgent_rc.py $(LIBDIR)
	# Version file
	cp $(SOURCEDIR)/VERSION $(LIBDIR)

	# Autostart elements for gnome/kde
	cp desktop/OGAgentTool.desktop $(XDGAUTOSTARTDIR)
	cp desktop/OGAgentTool.desktop $(KDEAUTOSTARTDIR)

	# scripts
	cp scripts/ogagent $(BINDIR)
	cp scripts/OGAgentTool-startup $(BINDIR)
	cp scripts/OGAgentTool $(BINDIR)

	# Fix permissions
	chmod 755 $(BINDIR)/ogagent
	chmod 755 $(BINDIR)/OGAgentTool-startup
	chmod 755 $(LIBDIR)/OGAgentUser.py
	chmod 600 $(LIBDIR)/cfg/ogagent.cfg

	# If for red hat based, copy init.d
ifeq ($(DISTRO),rh)
	mkdir -p $(INITDIR)
	cp debian/ogagent.init $(INITDIR)/ogagent
	chmod +x $(INITDIR)/ogagent
	ln -fs /usr/share/OGAgent/cfg/ogagent.cfg $(CFGDIR)
	ln -fs /usr/share/OGAgent/cfg/ogclient.cfg $(CFGDIR)
endif

uninstall:
	rm -rf $(LIBDIR)
	# rm -f $(BINDIR)/ogagent
	rm -rf $(CFGDIR)
