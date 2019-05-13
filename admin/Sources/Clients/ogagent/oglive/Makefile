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
	ln -fs $(LIBDIR)/cfg/ogagent.cfg $(CFGDIR)
	ln -fs $(LIBDIR)/cfg/ogclient.cfg $(CFGDIR)

	cp scripts/ogagent $(BINDIR)
	chmod 755 $(BINDIR)/ogagent
	
uninstall:
	rm -rf $(LIBDIR)
	rm -f $(BINDIR)/ogagent
	rm -rf $(CFGDIR)
