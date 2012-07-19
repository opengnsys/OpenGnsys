// ********************************************************************************************************************************************************
// Aplicación HIDRA
// Copyright 2003-2005 José Manuel Alonso. Todos los derechos reservados.
// Fichero: registro.h
//	Descripción:
//		Este proyecto implementa el servicio hidra en un ordenador con plataforma windows NT. Este fichero aporta las funciones de 
//		manipulación del registro de Windows
// *********************************************************************************************************************************************************
#include <windows.h>
#include <stdio.h>
#include <stdlib.h>

BOOLEAN WriteRegistryString(HKEY hive,char *key,char *subkey,char *value);
BOOLEAN WriteRegistryInteger(HKEY hive,char *key,char *subkey,DWORD value);
BOOLEAN WriteRegistryBytes(HKEY hive,char *key,char *subkey,void *value,int sz);
BOOLEAN ReadRegistryString(HKEY hive,char *key,char *subkey,char *value,int sz);
BOOLEAN ReadRegistryInteger(HKEY hive,char *key,char *subkey,DWORD *value);
BOOLEAN ReadRegistryShort(HKEY hive,char *key,char *subkey,short *value);
BOOLEAN DeleteRegistryValue(HKEY hive,char *key,char *subkey);
BOOLEAN DeleteRegistryKey(HKEY hive,char *key);