TEMPLATE	= lib
DESTDIR 	= ..

TARGET		= digitalclock

CONFIG		+= qt release warn_on build_all staticlib

QT += core gui

MOC_DIR 	= ../.moc

OBJECTS_DIR = ../.objs
TARGET 	= digitalclock

DEFINES 	+= HAVE_POSIX_OPENPT	    
#or DEFINES 	+= HAVE_GETPT

HEADERS 	= digitalclock.h

SOURCES 	= digitalclock.cpp



