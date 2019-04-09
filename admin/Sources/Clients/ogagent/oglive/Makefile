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
	
	mkdir $(LIBDIR)/img
	
	# Cleans up .pyc and cache folders
	rm -f $(PYC) $(CACHES)
	
	cp -r $(SOURCEDIR)/opengnsys $(LIBDIR)/opengnsys
	cp -r $(SOURCEDIR)/cfg $(LIBDIR)/cfg

	# scripts
	cp scripts/ogagent $(BINDIR)
	cp scripts/OGAgentTool-startup $(BINDIR)
	cp scripts/OGAgentTool $(BINDIR)
	
	# Fix permissions
	chmod 755 $(BINDIR)/ogagent
	chmod 755 $(BINDIR)/OGAgentTool-startup
	chmod 600 $(LIBDIR)/cfg/ogagent.cfg
	
	# If for red hat based, copy init.d
ifeq ($(DISTRO),rh)
	mkdir -p $(INITDIR)
	cp debian/ogagent.init $(INITDIR)/ogagent
	chmod +x $(INITDIR)/ogagent
	ln -fs /usr/share/OGAgent/cfg/ogagent.cfg $(CFGDIR)
	ln -fs /usr/share/OGAgent/cfg/ogclient.cfg $(CFGDIR)
endif
	
	# chmod 0755 $(BINDIR)/ogagent
uninstall:
	rm -rf $(LIBDIR)
	# rm -f $(BINDIR)/ogagent
	rm -rf $(CFGDIR)
