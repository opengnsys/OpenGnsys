// ********************************************************************************************************************************************************
// Aplicación HIDRA
// Copyright 2003-2005 José Manuel Alonso. Todos los derechos reservados.
// Fichero: registro.c
//	Descripción:
//		Este proyecto implementa el servicio hidra en un ordenador con plataforma windows NT. Este fichero aporta las funciones de 
//		manipulación del registro de Windows
// *********************************************************************************************************************************************************
#include "registrow.h"
//____________________________________________________________________________________________________________________________
//
BOOLEAN WriteRegistryString(HKEY hive,char *key,char *subkey,char *value)
{
  HKEY hk;
  DWORD dp,ret;
  int sz;

  sz=strlen(value)+1;
  if (RegCreateKeyEx(
      hive,
      key,
      0,
      "REG_SZ",
      REG_OPTION_NON_VOLATILE,
      KEY_WRITE,
      NULL,
      &hk,
      &dp)!=ERROR_SUCCESS)
    return FALSE;
  if ((ret=RegSetValueEx(hk,subkey,0,REG_SZ,(unsigned char *)value,sz))!=ERROR_SUCCESS)
  {
    SetLastError(ret);
    return FALSE;
  }
  RegCloseKey(hk);
  return TRUE;
}
// _____________________________________________________________________________________________________________
BOOLEAN WriteRegistryBytes(HKEY hive,char *key,char *subkey,void *value,int sz)
{
  HKEY hk;
  DWORD dp,ret;

  if (RegCreateKeyEx(
      hive,
      key,
      0,
      "REG_BINARY",
      REG_OPTION_NON_VOLATILE,
      KEY_WRITE,
      NULL,
      &hk,
      &dp)!=ERROR_SUCCESS)
    return FALSE;
  if ((ret=RegSetValueEx(hk,subkey,0,REG_BINARY,(BYTE*)value,sz))!=ERROR_SUCCESS)
  {
    SetLastError(ret);
    return FALSE;
  }
  RegCloseKey(hk);
  return TRUE;
}
// _____________________________________________________________________________________________________________
BOOLEAN WriteRegistryInteger(HKEY hive,char *key,char *subkey,DWORD value)
{
  HKEY hk;
  DWORD dp,ret;
  int sz;

  sz=sizeof(DWORD);
  if (RegCreateKeyEx(
      hive,
      key,
      0,
      "REG_DWORD",
      REG_OPTION_NON_VOLATILE,
      KEY_WRITE,
      NULL,
      &hk,
      &dp)!=ERROR_SUCCESS)
    return FALSE;
  if ((ret=RegSetValueEx(hk,subkey,0,REG_DWORD,(BYTE*)&value,sz))!=ERROR_SUCCESS)
  {
    SetLastError(ret);
    return FALSE;
  }
  RegCloseKey(hk);
  return TRUE;
}
// _____________________________________________________________________________________________________________
BOOLEAN ReadRegistryString(HKEY hive,char *key,char *subkey,char *value,int sz)
{
  HKEY  hk;
  DWORD type,ret;

  if (RegOpenKeyEx(
      hive,
      key,
      0,
      KEY_QUERY_VALUE,
      &hk)!=ERROR_SUCCESS)
  {
    return FALSE;
  }
  if ((ret=RegQueryValueEx(hk,subkey,0,&type,(unsigned char *)value,(DWORD*)&sz))!=ERROR_SUCCESS)
  {
    RegCloseKey(hk);
    SetLastError(ret);
    return FALSE;
  }
  RegCloseKey(hk);
  return TRUE;
}
// _____________________________________________________________________________________________________________
BOOLEAN ReadRegistryInteger(HKEY hive,char *key,char *subkey,DWORD *value)
{
  HKEY  hk;
  DWORD sz=sizeof(DWORD),type,ret;

  if (RegOpenKeyEx(
      hive,
      key,
      0,
      KEY_QUERY_VALUE,
      &hk)!=ERROR_SUCCESS)
  {
    return FALSE;
  }
  if ((ret=RegQueryValueEx(hk,subkey,0,&type,(LPBYTE)value,&sz))!=ERROR_SUCCESS)
  {
    RegCloseKey(hk);
    SetLastError(ret);
    return FALSE;
  }
  RegCloseKey(hk);
  return TRUE;
}
// _____________________________________________________________________________________________________________
BOOLEAN ReadRegistryShort(HKEY hive,char *key,char *subkey,short *value)
{
  HKEY  hk;
  DWORD sz=sizeof(DWORD),type,tmpvalue,ret;

  if (RegOpenKeyEx(
      hive,
      key,
      0,
      KEY_QUERY_VALUE,
      &hk)!=ERROR_SUCCESS)
  {
    return FALSE;
  }
  if ((ret=RegQueryValueEx(hk,subkey,0,&type,(LPBYTE)&tmpvalue,&sz))!=ERROR_SUCCESS)
  {
    RegCloseKey(hk);
    SetLastError(ret);
    return FALSE;
  }
  RegCloseKey(hk);
  *value=(short)tmpvalue;
  return TRUE;
}
// _____________________________________________________________________________________________________________
BOOLEAN DeleteRegistryValue(HKEY hive,char *key,char *subkey)
{
  HKEY  hk;
  DWORD ret;

  if (RegOpenKeyEx(
      hive,
      key,
      0,
      KEY_SET_VALUE,
      &hk)!=ERROR_SUCCESS)
  {
    return FALSE;
  }
  if ((ret=RegDeleteValue(hk,subkey))!=ERROR_SUCCESS)
  {
    RegCloseKey(hk);
    SetLastError(ret);
    return FALSE;
  }
  RegCloseKey(hk);
  return TRUE;
}
// _____________________________________________________________________________________________________________
BOOLEAN DeleteRegistryKey(HKEY hive,char *key)
{
  DWORD ret;

  if ((ret=RegDeleteKey(hive,key))!=ERROR_SUCCESS)
  {
    SetLastError(ret);
    return FALSE;
  }
  return TRUE;
}

