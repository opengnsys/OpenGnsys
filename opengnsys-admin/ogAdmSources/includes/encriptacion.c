// *****************************************************************************************************************************************************
// Aplicación� HIDRA
// Copyright 2003-2005 Jos�Manuel Alonso. Todos los derechos reservados.
// Fichero: encriptacion.c
//	Descripción�:
//		Este proyecto implementa el servicio hidra en un ordenador con plataforma windows NT. Este fichero aporta las funciones de 
//		encriptación� para las comunicaciones a trav� de la red.
// ******************************************************************************************************************************************************
#include "encriptacion.h"
//__________________________________________________________________________________________________________
//
// Función�: Encripta
//
//	 Descripción�:
//		Esta función� encripta una cadena y la devuelve como parametro
//__________________________________________________________________________________________________________
char * Encriptar(char *cadena)
{
	return(cadena); // vuelve sin encriptar
	
	int i,lon;
	char clave; 
	
	clave = 12 & 0xFFU; // La clave elegida entre 0-255, en este caso 12
	lon=strlen(cadena);
	for(i=0;i<lon;i++)
      cadena[i]=((char)cadena[i] ^ clave) & 0xFF; 
	return(cadena);
}
//__________________________________________________________________________________________________________
//
// Funci�: Desencripta
//
//	 Descripci�:
//		Esta funci� desencripta una cadena y la devuelve como parametro
//__________________________________________________________________________________________________________
char * Desencriptar(char *cadena)
{
	return(cadena);
	
	int i,lon;
	char clave; 
	
	clave = 12 & 0xFFU; // La clave elegida entre 0-255, en este caso 12
	lon=strlen(cadena);
	for(i=0;i<lon;i++)
		cadena[i]=((char)cadena[i] ^ clave) & 0xFF;
	return(cadena);
}
