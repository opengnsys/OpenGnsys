// ********************************************************************************************************
// Servicio: ogAdmRepo
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Marzo-2010
// Fecha Última modificación: Marzo-2010
// Nombre del fichero: ogAdmRepo.cpp
// Descripción :Este fichero implementa el servicio de administración general del sistema
// ********************************************************************************************************
#include "ogAdmRepo.h"
#include "ogAdmLib.c"
//________________________________________________________________________________________________________
//	Función: tomaConfiguracion
//
//	Descripción:
//		Lee el fichero de configuración del servicio
//	Parámetros:
//		filecfg : Ruta completa al fichero de configuración
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error 
//________________________________________________________________________________________________________
BOOLEAN tomaConfiguracion(char* filecfg) {
	char modulo[] = "tomaConfiguracion()";

	if (filecfg == NULL || strlen(filecfg) == 0) {
		errorLog(modulo, 1, FALSE); // Fichero de configuración del servicio vacío
		return (FALSE);
	}
	FILE *fcfg;
	long lSize;
	char * buffer, *lineas[MAXPRM], *dualparametro[2];
	int i, numlin, resul;

	fcfg = fopen(filecfg, "rt");
	if (fcfg == NULL) {
		errorLog(modulo, 2, FALSE); // No existe fichero de configuración del servicio
		return (FALSE);
	}

	fseek(fcfg, 0, SEEK_END);
	lSize = ftell(fcfg); // Obtiene tamaño del fichero.
	rewind(fcfg);
	buffer = (char*) reservaMemoria(lSize+1); // Toma memoria para el buffer de lectura.
	if (buffer == NULL) { // No hay memoria suficiente para el buffer
		errorLog(modulo, 3, FALSE);
		return (FALSE);
	}
	fread(buffer, 1, lSize, fcfg); // Lee contenido del fichero
	buffer[lSize]=(char) NULL;
	fclose(fcfg);

	iplocal[0] = (char) NULL; //inicializar variables globales
	puerto[0] = (char) NULL;

	numlin = splitCadena(lineas, buffer, '\n');
	for (i = 0; i < numlin; i++) {
		splitCadena(dualparametro, lineas[i], '=');
		resul = strcmp(StrToUpper(dualparametro[0]), "IPLOCAL");
		if (resul == 0)
			strcpy(iplocal, dualparametro[1]);
		resul = strcmp(StrToUpper(dualparametro[0]), "PUERTO");
		if (resul == 0)
			strcpy(puerto, dualparametro[1]);
	}
	if (iplocal[0] == (char) NULL) {
		errorLog(modulo, 4, FALSE); // Falta parámetro IPLOCAL
		return (FALSE);
	}
	if (puerto[0] == (char) NULL) {
		errorLog(modulo, 5, FALSE); // Falta parámetro PUERTO
		return (FALSE);
	}
	return (TRUE);
}
// ________________________________________________________________________________________________________
// Función: gestionaTrama
//
//		Descripción:
//			Procesa las tramas recibidas .
//		Parametros:
//			- s : Socket usado para comunicaciones
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
BOOLEAN gestionaTrama(SOCKET *socket_c)
{
	TRAMA* ptrTrama;
	int i, res;
	char *nfn;
	char modulo[] = "gestionaTrama()";

	ptrTrama=recibeTrama(socket_c);
	if (ptrTrama){
		INTROaFINCAD(ptrTrama);
		nfn = copiaParametro("nfn",ptrTrama); // Toma dirección/es IP
		for (i = 0; i < MAXIMAS_FUNCIONES; i++) { // Recorre funciones que procesan las tramas
			res = strcmp(tbfuncionesRepo[i].nf, nfn);
			if (res == 0) { // Encontrada la función que procesa el mensaje
				return (tbfuncionesRepo[i].fptr(socket_c, ptrTrama)); // Invoca la función
			}
		}
	}
	else
		errorLog(modulo, 17, FALSE); // Error en la recepción
	return (TRUE);
}
// ********************************************************************************************************
// PROGRAMA PRINCIPAL (SERVICIO)
// ********************************************************************************************************
int main(int argc, char *argv[])
{
	SOCKET socket_r; // Socket donde escucha el servidor
	SOCKET socket_c; // Socket de los clientes que se conectan
	socklen_t iAddrSize;
	struct sockaddr_in local, cliente;
	char modulo[] = "main()";

	/*--------------------------------------------------------------------------------------------------------
		Validación de parámetros de ejecución y lectura del fichero de configuración del servicio
	 ---------------------------------------------------------------------------------------------------------*/
	if (!validacionParametros(argc, argv,1)) // Valida parámetros de ejecución
		exit(EXIT_FAILURE);

	if (!tomaConfiguracion(szPathFileCfg)) { // Toma parametros de configuracion
		exit(EXIT_FAILURE);
	}
	/*--------------------------------------------------------------------------------------------------------
		Carga del catálogo de funciones que procesan las tramas (referencia directa por puntero a función)
	 ---------------------------------------------------------------------------------------------------------*/
	int cf = 0;

	cf++;


	/*--------------------------------------------------------------------------------------------------------
		Creación y configuración del socket del servicio
	 ---------------------------------------------------------------------------------------------------------*/
	socket_r = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP); // Crea socket del servicio
	if (socket_r == SOCKET_ERROR) { // Error al crear el socket del servicio
		errorLog(modulo, 13, TRUE);
		exit(EXIT_FAILURE);
	}

	local.sin_addr.s_addr = htonl(INADDR_ANY); // Configura el socket del servicio
	local.sin_family = AF_INET;
	local.sin_port = htons(atoi(puerto));

	if (bind(socket_r, (struct sockaddr *) &local, sizeof(local))== SOCKET_ERROR) { // Enlaza socket
		errorLog(modulo, 14, TRUE);
		exit(EXIT_FAILURE);
	}

	listen(socket_r, 250); // Pone a escuchar al socket
	iAddrSize = sizeof(cliente);
	/*--------------------------------------------------------------------------------------------------------
		Bucle para acceptar conexiones
	 ---------------------------------------------------------------------------------------------------------*/
	infoLog(1); // Inicio de sesión
	while(TRUE) {
		socket_c = accept(socket_r, (struct sockaddr *) &cliente, &iAddrSize);
		if (socket_c == INVALID_SOCKET) {
			errorLog(modulo, 15, TRUE);
			exit(EXIT_FAILURE);
		}
		if(!gestionaTrama(&socket_c)){
			errorLog(modulo, 39, TRUE);
			break;
		}
		close(socket_c);
	}
	/*--------------------------------------------------------------------------------------------------------
		Fin del servicio
	 ---------------------------------------------------------------------------------------------------------*/
	close(socket_r);
	exit(EXIT_SUCCESS);
}
