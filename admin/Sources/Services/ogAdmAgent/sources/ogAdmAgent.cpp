// ********************************************************************************************************
// Servicio: ogAdmAgent
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Marzo-2010
// Fecha Última modificación: Marzo-2010
// Nombre del fichero: ogAdmAgent.cpp
// Descripción: Este fichero implementa el servicio agente del sistema. Revisa a intervalos
//				regulares la base de datos para comprobar si existen acciones programadas.
// ********************************************************************************************************
#include "ogAdmAgent.h"
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
BOOLEAN tomaConfiguracion(char* filecfg)
{
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
	buffer = (char*) reservaMemoria(lSize + 1); // Toma memoria para el buffer de lectura.
	if (buffer == NULL) { // No hay memoria suficiente para el buffer
		errorLog(modulo, 3, FALSE);
		return (FALSE);
	}
	fread(buffer, 1, lSize, fcfg); // Lee contenido del fichero
	buffer[lSize] = (char) NULL;
	fclose(fcfg);

	servidoradm[0] = (char) NULL; //inicializar variables globales
	puerto[0] = (char) NULL;
	usuario[0] = (char) NULL;
	pasguor[0] = (char) NULL;
	datasource[0] = (char) NULL;
	catalog[0] = (char) NULL;

	numlin = splitCadena(lineas, buffer, '\n');
	for (i = 0; i < numlin; i++) {
		splitCadena(dualparametro, lineas[i], '=');
		resul = strcmp(StrToUpper(dualparametro[0]), "SERVIDORADM");
		if (resul == 0)
			strcpy(servidoradm, dualparametro[1]);
		resul = strcmp(StrToUpper(dualparametro[0]), "PUERTO");
		if (resul == 0)
			strcpy(puerto, dualparametro[1]);
		resul = strcmp(StrToUpper(dualparametro[0]), "USUARIO");
		if (resul == 0)
			strcpy(usuario, dualparametro[1]);
		resul = strcmp(StrToUpper(dualparametro[0]), "PASSWORD");
		if (resul == 0)
			strcpy(pasguor, dualparametro[1]);
		resul = strcmp(StrToUpper(dualparametro[0]), "DATASOURCE");
		if (resul == 0)
			strcpy(datasource, dualparametro[1]);
		resul = strcmp(StrToUpper(dualparametro[0]), "CATALOG");
		if (resul == 0)
			strcpy(catalog, dualparametro[1]);
	}
	if (servidoradm[0] == (char) NULL) {
		errorLog(modulo, 4, FALSE); // Falta parámetro SERVIDORADM
		return (FALSE);
	}
	if (puerto[0] == (char) NULL) {
		errorLog(modulo, 5, FALSE); // Falta parámetro PUERTO
		return (FALSE);
	}
	if (usuario[0] == (char) NULL) {
		errorLog(modulo, 6, FALSE); // Falta parámetro USUARIO
		return (FALSE);
	}
	if (pasguor[0] == (char) NULL) {
		errorLog(modulo, 7, FALSE); // Falta parámetro PASSWORD
		return (FALSE);
	}
	if (datasource[0] == (char) NULL) {
		errorLog(modulo, 8, FALSE); // Falta parámetro DATASOURCE
		return (FALSE);
	}
	if (catalog[0] == (char) NULL) {
		errorLog(modulo, 9, FALSE); // Falta parámetro CATALOG
		return (FALSE);
	}
	return (TRUE);
}
// ________________________________________________________________________________________________________
//
// Función: diadelaSemana
//
//	Descripción:
//		Calcula el número del día de la semana que corresponde a una fecha
//	Parámetros:
//		- dia: Un día
//		- mes: Un mes
//		- anno: Un año
//	Devuelve:
//		El número del día de la semana: 1=Lunes, 2=martes ... 6=sábado  7=domingo
// ________________________________________________________________________________________________________

int diadelaSemana(WORD dia,WORD mes,WORD anno)
{
	int i,cont,dias_anuales;
	int desplazamiento_dias=6;
	int orddiasem;

	cont =0;
	for (i=1900;i<anno;i++){
		if (bisiesto(i)) dias_anuales=366; else	dias_anuales=365;
		cont+=dias_anuales;
	}
	for (i=1;i<mes;i++){
		if (i!=2)
			cont+=dias_meses[i];
		else{
			if (bisiesto(anno))
				cont+=29;
			else
				cont+=28;
		}
	}
	cont+=dia+desplazamiento_dias;
	orddiasem=(cont%7);
	if(orddiasem==0) orddiasem=7;
	return(orddiasem);
}
// ________________________________________________________________________________________________________
//
// Función: bisiesto
//
//	Descripción:
//		Calcula si un año es bisiesto o no lo es
//	Parámetros:
//		- anno: Un año
//	Devuelve:
//		TRUE si el año es bisiesto
//		FALSE si no es bisiesto
// ________________________________________________________________________________________________________

BOOLEAN bisiesto(WORD anno){
	return(anno%4==0);
}
// ________________________________________________________________________________________________________
//
// Función: semanadelMes
//
//	Descripción:
//		Calcula el número de semana perteneciente a un día del mes
//	Parámetros:
//		- ordiasem_1: Orden semanal (1,2...) del primer dia del mes que se pasa como parámetro
//		- diames: El mes concreto
//	Devuelve:
//		El número del día de la semana: 1=Lunes, 2=martes ... 6=sábado  7=domingo , de ese mes
// ________________________________________________________________________________________________________

int semanadelMes(int ordiasem_1,int diames)
{
	int nwdia,resto,cociente;

	nwdia=diames+ordiasem_1-1;
	cociente=nwdia/7;
	resto=nwdia%7;
	if(resto>0) cociente++;
	return(cociente);
}
// ________________________________________________________________________________________________________
//
// Función: buscaAccion
//
//	Descripción:
//		Busca en la base de datos, acciones programadas
//	Parámetros:
//		- db: Objeto base de datos (operativo)
//		- dia : Día actual del mes
//		- mes : mes en curso
//		- anno : Año en curso
//		- hora : Hora actual
//		- minutos : Minutos actuales
//		- diasemana : Dia de la semana 1=lunes,2=martes ... ( 0 Domingo)
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________

BOOLEAN buscaAccion(Database db,WORD dia,WORD mes,WORD anno,WORD hora,WORD minutos,WORD diasemana)
{
	char msglog[LONSTD], sqlstr[LONSQL];
	Table tbl;
	BYTE swampm,bitsemana;
	int ordsem,ordulsem,ordiasem_1,maxdias;
	int sesionprog;
	char modulo[] = "buscaAccion()";

	/* Año de comienzo */
	anno=anno-ANNOREF; //
	/* Preparación hora */
	if(hora>11){
		hora-=12;
		swampm=1; // Es P.M.
	}
	else
		swampm=0; // Es am
	/* Preparación semana */
	if(diasemana==0) diasemana=7; // El domingo

	// Cuestión semanas
	ordiasem_1=diadelaSemana(1,mes,anno+2009);
	ordsem=semanadelMes(ordiasem_1,dia); // Calcula el número de la semana
	if (mes!=2) // Toma el último día de ese mes
		maxdias=dias_meses[mes];
	else{
		if (bisiesto(anno+ANNOREF))
			maxdias=29;
		else
			maxdias=28;
	}
	ordulsem=semanadelMes(ordiasem_1,maxdias); // Calcula el número de la última semana
	bitsemana=HEX_semanas[ordsem];
	if(ordsem==ordulsem) // Si es la última semana del mes
		bitsemana|=HEX_semanas[6];

	sprintf(sqlstr,"SELECT DISTINCT idprogramacion,tipoaccion,identificador,sesion,idcentro,"\
					"tareas.descripcion as descritarea"\
					" FROM programaciones"\
					" LEFT OUTER JOIN tareas ON tareas.idtarea=programaciones.identificador"\
					" WHERE suspendida=0 "\
					" AND (annos & %d <> 0) "\
					" AND (meses & %d<>0) "\
					" AND ((diario & %d<>0) OR (dias & %d<>0) OR (semanas & %d<>0))"\
					" AND (horas & %d<>0) AND ampm=%d AND minutos=%d",\
					HEX_annos[anno],\
					HEX_meses[mes],\
					HEX_dias[dia],\
					HEX_diasemana[diasemana],\
					bitsemana,\
					HEX_horas[hora],\
					swampm,minutos);

	if (!db.Execute(sqlstr, tbl)) { // Error al leer
		errorLog(modulo, 21, FALSE);
		db.GetErrorErrStr(msglog);
		errorInfo(modulo, msglog);
		return (FALSE);
	}
	if(tbl.ISEOF()){
		return(TRUE);  // No hay acciones programadas
	}
	
	while(!tbl.ISEOF()){
		if(!tbl.Get("idprogramacion",idprogramacion)){
			tbl.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}
		if(!tbl.Get("tipoaccion",tipoaccion)){
			tbl.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}
		if(!tbl.Get("identificador",idtipoaccion)){
			tbl.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}
		if(!tbl.Get("sesion",sesionprog)){
			tbl.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}
		if(!tbl.Get("idcentro",idcentro)){
			tbl.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}		

		if(tipoaccion==EJECUCION_COMANDO){ // Es una programación de un comando
			return(ejecutarComando(db,idprogramacion,sesionprog));
		}
		else{
		
			if(tipoaccion==EJECUCION_TAREA){
				if(!tbl.Get("descritarea",descriaccion)){
					tbl.GetErrorErrStr(msglog);
					errorInfo(modulo, msglog);
					return (FALSE);
				}					
				return(ejecutarTarea(db,idprogramacion,idtipoaccion));
			}
			else{
				if(tipoaccion==EJECUCION_RESERVA){
					EjecutarReserva(idtipoaccion,db); // Es una programación de un trabajo
				}
			}
		}
		tbl.MoveNext();
	}
	return(TRUE);
}
// ________________________________________________________________________________________________________
//
// Función: ejecutarComando
//
//	Descripción:
//		Ejecuta un comando programado
//	Parámetros:
//		- db: Objeto base de datos (operativo)
//		- idcomando: Identificador del comando
//		- sesion: Sesión correspondiente al comando cuando se grabó en la tabla acciones
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________

BOOLEAN ejecutarComando(Database db,int idprogramacion,int sesion )
{
	struct tm* st;
	char msglog[LONSTD], sqlstr[LONSQL];
	char fechahorareg[24];
	char modulo[] = "ejecutarComando()";

	st = tomaHora();
	sprintf(fechahorareg,"%d/%d/%d %d:%d:%d", st->tm_year + 1900, st->tm_mon + 1,
			st->tm_mday, st->tm_hour, st->tm_min, st->tm_sec);

	sprintf(sqlstr,"UPDATE acciones SET estado=%d,idprogramacion=%d,fechahorareg='%s'"\
				" WHERE sesion=%d",	ACCION_INICIADA,idprogramacion,fechahorareg,sesion);

	if (!db.Execute(sqlstr)) { // Error al recuperar los datos
		errorLog(modulo, 21, FALSE);
		db.GetErrorErrStr(msglog);
		errorInfo(modulo, msglog);
		return (FALSE);
	}
	return(enviaPeticion(idprogramacion));
}
// ________________________________________________________________________________________________________
//
// Función: ejecutarProcedimiento
//
//	Descripción:
//		Ejecuta un procedimiento programado
//	Parámetros:
//		- db: Objeto base de datos (operativo)
//		- idprocedimiento: Identificador del procedimiento
//		- ambito: Ámbito de aplicación
//		- idambito: Identificador del ámbito
//		- restrambito: cadena con los identificadores de los ordenadores a los que se aplica la acción 
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________

BOOLEAN ejecutarProcedimiento(Database db,int idprocedimiento,int ambito,int idambito,char* restrambito)
{	
	char msglog[LONSTD], sqlstr[LONSQL],*parametros;
	Table tbl;
	int procedimientoid,idcomando,lonprm;
	char modulo[] = "ejecutarProcedimiento()";
	
	sprintf(sqlstr,"SELECT idcomando,procedimientoid,parametros,length(parametros) as lonprm"\
					" FROM procedimientos_acciones"\
					" WHERE idprocedimiento=%d ORDER BY orden",idprocedimiento);

	if (!db.Execute(sqlstr, tbl)) { // Error al leer
		errorLog(modulo, 21, FALSE);
		db.GetErrorErrStr(msglog);
		errorInfo(modulo, msglog);
		return (FALSE);
	}
	
	if(tbl.ISEOF()){
		return(TRUE);  // No exustde tarea
	}
	while(!tbl.ISEOF()){
		if(!tbl.Get("procedimientoid",procedimientoid)){
			tbl.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}
		if(procedimientoid>0){ // Procedimiento recursivo
			if(!ejecutarProcedimiento(db,procedimientoid,ambito,idambito,restrambito)){
				return(false);
			}
		}
		else{
			if(!tbl.Get("lonprm",lonprm)){
				tbl.GetErrorErrStr(msglog);
				errorInfo(modulo, msglog);
				return (FALSE);
			}	
			parametros = reservaMemoria(lonprm+1); // Reserva para almacenar los parametros del procedimiento
			if (parametros == NULL) {
				errorLog(modulo, 3, FALSE);
				return (FALSE);
			}			
			if(!tbl.Get("parametros",parametros)){
				tbl.GetErrorErrStr(msglog);
				errorInfo(modulo, msglog);
				return (FALSE);
			}	
			if(!tbl.Get("idcomando",idcomando)){
				tbl.GetErrorErrStr(msglog);
				errorInfo(modulo, msglog);
				return (FALSE);
			}				

			if(!insertaComando(db,idcomando,parametros,idprocedimiento,ambito,idambito,restrambito))
				return(false);
		}
		tbl.MoveNext();
	}		
	return(TRUE);
}
// ________________________________________________________________________________________________________
//
// Función: ejecutarTarea
//
//	Descripción:
//		Ejecuta una tarea programada
//	Parámetros:
//		- db: Objeto base de datos (operativo)
//		- idtarea: Identificador de la tarea
//		- idprogramacion: Identificador de la programación
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________

BOOLEAN ejecutarTarea(Database db, int idprogramacion, int idtarea)
{
	char msglog[LONSTD], sqlstr[LONSQL];
	Table tbl;
	int tareaid,ambito,idambito,idprocedimiento,lonrestrambito;
	char* restrambito;
	char modulo[] = "ejecutarTarea()";

	sprintf(sqlstr,"SELECT tareas_acciones.orden,tareas_acciones.idprocedimiento,tareas_acciones.tareaid,"\
					" tareas.ambito,tareas.idambito,tareas.restrambito,length(tareas.restrambito) as lonrestrambito"\
					" FROM tareas"\
					" INNER JOIN tareas_acciones ON tareas_acciones.idtarea=tareas.idtarea"\
					" WHERE tareas_acciones.idtarea=%d ORDER BY tareas_acciones.orden",idtarea);

	if (!db.Execute(sqlstr, tbl)) { // Error al leer
		errorLog(modulo, 21, FALSE);
		db.GetErrorErrStr(msglog);
		errorInfo(modulo, msglog);
		return (FALSE);
	}
	
	if(tbl.ISEOF()){
		return(TRUE);  // No existe tarea
	}	

	while(!tbl.ISEOF()){
		if(!tbl.Get("tareaid",tareaid)){
			tbl.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}
		if(tareaid>0){ // Tarea recursiva
			if(!ejecutarTarea(db,idprogramacion,tareaid)){
				return(false);
			}
		}
		else{
			if(!tbl.Get("ambito",ambito)){
				tbl.GetErrorErrStr(msglog);
				errorInfo(modulo, msglog);
				return (FALSE);
			}	
			if(!tbl.Get("idambito",idambito)){
				tbl.GetErrorErrStr(msglog);
				errorInfo(modulo, msglog);
				return (FALSE);
			}		
			if(!tbl.Get("lonrestrambito",lonrestrambito)){
				tbl.GetErrorErrStr(msglog);
				errorInfo(modulo, msglog);
				return (FALSE);
			}
			restrambito = reservaMemoria(lonrestrambito+1);
			if (restrambito == NULL) {
				errorLog(modulo, 3, FALSE);
				return (FALSE);
			}			
			if(!tbl.Get("restrambito",restrambito)){
				tbl.GetErrorErrStr(msglog);
				errorInfo(modulo, msglog);
				return (FALSE);
			}			
			RecopilaIpesMacs(db,ambito,idambito,restrambito); // Recopila Ipes del ámbito
			if(!tbl.Get("idprocedimiento",idprocedimiento)){
				tbl.GetErrorErrStr(msglog);
				errorInfo(modulo, msglog);
				return (FALSE);
			}				
			sesion=time(NULL);
			
			if(!ejecutarProcedimiento(db,idprocedimiento,ambito,idambito,restrambito))
				return(FALSE);
		}
		tbl.MoveNext();
	}		
	return(enviaPeticion(idprogramacion));	
}
// ________________________________________________________________________________________________________
//
// Función: ejecutarTarea
//
//	Descripción:
//		Registra un procedimiento para un ambito concreto
//	Parámetros:
//		- db: Objeto base de datos (operativo)
//		- idcomando: Identificador del comando
//		- idprocedimiento: Identificador del procedimiento
//		- ambito: Ámbito de aplicación
//		- idambito: Identificador del ámbito
//		- restrambito: cadena con los identificadores de los ordenadores a los que se aplica la acción 
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//________________________________________________________________________________________________________
BOOLEAN insertaComando(Database db,int idcomando,char*parametros,int idprocedimiento,int ambito,int idambito,char*restrambito)
{
	char msglog[LONSTD], sqlstr[LONSQL];
	struct tm* st;
	char *auxID[MAXIMOS_CLIENTES],*auxIP[MAXIMOS_CLIENTES];
	char fechahorareg[24];
	int i;
	char modulo[] = "insertaComando()";

	if(concli==0) return(TRUE); // No hay ordenadores en el ámbito

	st = tomaHora();
	sprintf(fechahorareg,"%d/%d/%d %d:%d:%d", st->tm_year + 1900, st->tm_mon + 1, st->tm_mday, st->tm_hour, st->tm_min, st->tm_sec);	

	splitCadena(auxID,cadenaid,',');
	splitCadena(auxIP,cadenaip,';');

	for (i=0;i<concli;i++){
		sprintf(sqlstr,"INSERT INTO acciones (idordenador,tipoaccion,idtipoaccion,descriaccion,ip,"\
						"sesion,idcomando,parametros,fechahorareg,estado,resultado,ambito,idambito,"\
						"restrambito,idprocedimiento,idcentro,idprogramacion)"\
						" VALUES (%s,%d,%d,'%s','%s',%d,%d,'%s','%s',%d,%d,%d,%d,'%s',%d,%d,%d)",\
						auxID[i],tipoaccion,idtipoaccion,descriaccion,auxIP[i],sesion,idcomando,parametros,fechahorareg,\
						ACCION_INICIADA,ACCION_SINRESULTADO,ambito,idambito,restrambito,idprocedimiento,idcentro,idprogramacion);
			if (!db.Execute(sqlstr)) { // Error al recuperar los datos
			errorLog(modulo, 21, FALSE);
			db.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}
	}
	return(TRUE);
}
// _____________________________________________________________________________________________________________
// Función: EjecutarReserva
//
//		Descripción:
//			Registra una acción (Tarea) y la envía para su ejecución
//		Parámetros:
//			- idreserva : Identificador de la reserva
//			- db: una conexion ADO operativa
//			- parametros: Parámetros de la acción
// _____________________________________________________________________________________________________________
BOOLEAN EjecutarReserva(int idreserva,Database db )
{


	return(true);
}
// _____________________________________________________________________________________________________________
// Función: enviaPeticion
//
//		Descripción:
//			Hace una petición al servidor para que actualice los ordenadores implicados en la programación
//		Parámetros:
//			- idprogramacion: Identificador de la programación
// _____________________________________________________________________________________________________________
BOOLEAN enviaPeticion(int idprogramacion)
{
	int lon;
	TRAMA *ptrTrama;
	SOCKET socket_c;
	char modulo[] = "enviaPeticion()";

	/* Envio de comandos a clientes */
	ptrTrama=(TRAMA *)reservaMemoria(sizeof(TRAMA));
	if (ptrTrama == NULL) { // No hay memoria suficiente para el bufer de las tramas
		errorLog(modulo, 3, FALSE);
		return(FALSE);
	}
	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=envioProgramacion\r"); // Nombre de la función a ejecutar en el servidor
	lon+=sprintf(ptrTrama->parametros+lon,"idp=%d\r",idprogramacion); // Configuración de los Sistemas Operativos del cliente

	if(!enviaMensaje(&socket_c,ptrTrama,MSG_PETICION)){
		errorLog(modulo,91,FALSE);
		return(FALSE);
	}
	return(TRUE);
}
// _____________________________________________________________________________________________________________
//
// Función: RecopilaIpesMacs
//
// Descripción : 
//		Recopila las IPes, las Macs y los identificadores de ordenadores de un ámbito determinado
//
// Especificaciones:
//		Esta Función recibe tres parámatros:
//			db : Un objeto Base de datos totalmente operativo
//			ambito:  Tipo de ámbito
//			idambito: Identificador del ámbito
//	Devuelve:
//		Todas los identificadores de ordenadores , las ipes y las macs de los ordenadores que componen el ámbito
//		Para ellos habrá que tener declarada tres variables globales :
//				cadenaid,cadenaip y cadenamac
// _____________________________________________________________________________________________________________

BOOLEAN RecopilaIpesMacs(Database db,int ambito,int idambito,char *restrambito)
{
	char sqlstr[LONSQL];

	concli=0;
	/* Reserva memoria al meno para caracter nulo */
	cadenaid=(char*) reservaMemoria(1);
	cadenaip=(char*) reservaMemoria(1);
	cadenamac=(char*) reservaMemoria(1);
	
	switch(ambito){
		case AMBITO_CENTROS :
			sprintf(sqlstr,"SELECT idcentro FROM centros WHERE idcentro=%d",idambito);
 			RecorreCentro(db,sqlstr);
			break;
		case AMBITO_GRUPOSAULAS :
			sprintf(sqlstr,"SELECT idgrupo FROM grupos WHERE idgrupo=%d AND tipo=%d",idambito,AMBITO_GRUPOSAULAS);
			RecorreGruposAulas(db,sqlstr);
			break;
		case AMBITO_AULAS :
			sprintf(sqlstr,"SELECT idaula FROM aulas WHERE idaula=%d",idambito);
			RecorreAulas(db,sqlstr);
			break;
		case AMBITO_GRUPOSORDENADORES :
			sprintf(sqlstr,"SELECT idgrupo FROM gruposordenadores WHERE idgrupo=%d",idambito);
			RecorreGruposOrdenadores(db,sqlstr);
			break;
		case AMBITO_ORDENADORES :
			sprintf(sqlstr,"SELECT ip,mac,idordenador FROM ordenadores WHERE idordenador=%d",idambito);
			RecorreOrdenadores(db,sqlstr);
			break;
		default: // Se trata de un conjunto aleatorio de ordenadores
			sprintf(sqlstr,"SELECT ip,mac,idordenador  FROM ordenadores WHERE idordenador IN (%s)",restrambito);
			RecorreOrdenadores(db,sqlstr);
			
	}
	return (TRUE);
}
//________________________________________________________________________________________________________

BOOLEAN RecorreCentro(Database db, char* sqlstr)
{
	char msglog[LONSTD];
	Table tbl;
	int idcentro;
	char modulo[] = "RecorreCentro()";
	
	if (!db.Execute(sqlstr, tbl)) { // Error al leer
		errorLog(modulo, 21, FALSE);
		db.GetErrorErrStr(msglog);
		errorInfo(modulo, msglog);
		return (FALSE);
	}
	if(!tbl.ISEOF()){
		if(!tbl.Get("idcentro",idcentro)){
			tbl.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}		
		sprintf(sqlstr,"SELECT idgrupo FROM grupos WHERE idcentro=%d AND grupoid=0  AND tipo=%d",idcentro,AMBITO_GRUPOSAULAS);
		RecorreGruposAulas(db,sqlstr);
		sprintf(sqlstr,"SELECT idaula FROM aulas WHERE idcentro=%d AND grupoid=0",idcentro);
		RecorreAulas(db,sqlstr);
	}
	return (TRUE);
}
//________________________________________________________________________________________________________

BOOLEAN RecorreGruposAulas(Database db, char* sqlstr)
{
	char msglog[LONSTD];
	Table tbl;
	int idgrupo;
	char modulo[] = "RecorreGruposAulas()";
	
	if (!db.Execute(sqlstr, tbl)) { // Error al leer
		errorLog(modulo, 21, FALSE);
		db.GetErrorErrStr(msglog);
		errorInfo(modulo, msglog);
		return (FALSE);
	}
	while(!tbl.ISEOF()){
		if(!tbl.Get("idgrupo",idgrupo)){
			tbl.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}		
		sprintf(sqlstr,"SELECT idgrupo FROM grupos WHERE grupoid=%d AND tipo=%d",idgrupo,AMBITO_GRUPOSAULAS);
		RecorreGruposAulas(db,sqlstr);
		sprintf(sqlstr,"SELECT idaula FROM aulas WHERE  grupoid=%d",idgrupo);
		RecorreAulas(db,sqlstr);
		tbl.MoveNext();
	}
	return (TRUE);
}
//________________________________________________________________________________________________________

BOOLEAN RecorreAulas(Database db, char* sqlstr)
{
	char msglog[LONSTD];
	Table tbl;
	int idaula;
	char modulo[] = "RecorreAulas()";

	if (!db.Execute(sqlstr, tbl)) { // Error al leer
		errorLog(modulo, 21, FALSE);
		db.GetErrorErrStr(msglog);
		errorInfo(modulo, msglog);
		return (FALSE);
	}
	while(!tbl.ISEOF()){
		if(!tbl.Get("idaula",idaula)){
			tbl.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}
		sprintf(sqlstr,"SELECT idgrupo FROM gruposordenadores WHERE idaula=%d AND grupoid=0",idaula);
		RecorreGruposOrdenadores(db,sqlstr);
		sprintf(sqlstr,"SELECT ip,mac,idordenador FROM ordenadores WHERE  idaula=%d AND grupoid=0",idaula);
		RecorreOrdenadores(db,sqlstr);
		tbl.MoveNext();
	}
	return (TRUE);
}
//________________________________________________________________________________________________________

BOOLEAN  RecorreGruposOrdenadores(Database db, char* sqlstr)
{
	char msglog[LONSTD];
	Table tbl;
	int idgrupo;
	char modulo[] = "RecorreGruposOrdenadores()";

	if (!db.Execute(sqlstr, tbl)) { // Error al leer
		errorLog(modulo, 21, FALSE);
		db.GetErrorErrStr(msglog);
		errorInfo(modulo, msglog);
		return (FALSE);
	}
	while(!tbl.ISEOF()){
		if(!tbl.Get("idgrupo",idgrupo)){
			tbl.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}
		sprintf(sqlstr,"SELECT idgrupo FROM gruposordenadores WHERE grupoid=%d",idgrupo);
		RecorreGruposOrdenadores(db,sqlstr);
		sprintf(sqlstr,"SELECT ip,mac,idordenador FROM ordenadores WHERE grupoid=%d",idgrupo);
		RecorreOrdenadores(db,sqlstr);
		tbl.MoveNext();
	}
	return (TRUE);
}
//________________________________________________________________________________________________________

BOOLEAN RecorreOrdenadores(Database db, char* sqlstr)
{
	char msglog[LONSTD];
	Table tbl;
	int idordenador,o,p,m,lon;
	char ido[16],ip[LONIP],mac[LONMAC];
	char modulo[] = "RecorreOrdenadores()";

	if (!db.Execute(sqlstr, tbl)) { // Error al leer
		errorLog(modulo, 21, FALSE);
		db.GetErrorErrStr(msglog);
		errorInfo(modulo, msglog);
		return (FALSE);
	}
	o=p=m=0;
	while(!tbl.ISEOF()){
		if(!tbl.Get("idordenador",idordenador)){
			tbl.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}	
		if(!tbl.Get("ip",ip)){
			tbl.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}	
		if(!tbl.Get("mac",mac)){
			tbl.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			return (FALSE);
		}
		sprintf(ido,"%d",idordenador);
		lon=strlen(ido);
		if(lon>16) lon=16;
		cadenaid=(char*) ampliaMemoria(cadenaid,o+lon+1);
		memcpy(&cadenaid[o],ido,lon);
		o+=lon;
		cadenaid[o++]=',';
		
		lon=strlen(ip);
		if(lon>16) lon=LONIP;
		cadenaip=(char*) ampliaMemoria(cadenaip,p+lon+1);
		memcpy(&cadenaip[p],ip,lon);
		p+=lon;
		cadenaip[p++]=';';

		lon=strlen(mac);
		if(lon>16) lon=LONMAC;
		cadenamac=(char*) ampliaMemoria(cadenamac,m+lon+1);
		memcpy(&cadenamac[m],mac,lon);
		m+=lon;
		cadenamac[m++]=';';
		
		concli++;
		tbl.MoveNext();
	}
	if(o>0) o--;
	if(p>0) p--;
	if(m>0) m--;
	cadenaid[o]='\0';
	cadenaip[p]='\0';
	cadenamac[m]='\0';

	return (TRUE);
}
// ********************************************************************************************************
// PROGRAMA PRINCIPAL (SERVICIO)
// ********************************************************************************************************
int main(int argc, char *argv[])
{
	int pseg;
	char msglog[LONSTD];
	struct tm* st;
	Database db;
	char modulo[] = "main()";

	/* Validación de parámetros de ejecución y lectura del fichero de configuración del servicio */

	if (!validacionParametros(argc, argv, 5)) // Valida parámetros de ejecución
		exit(EXIT_FAILURE);

	if (!tomaConfiguracion(szPathFileCfg)) { // Toma parametros de configuracion
		exit(EXIT_FAILURE);
	}
	
	/* Bucle principal del servicio */

	while (TRUE){
		st = tomaHora();
		pseg=65-st->tm_sec; // Calcula segundos de inactividad de la hebra
		sleep(pseg);

		// Toma la hora
		st = tomaHora();

		if (!db.Open(usuario, pasguor, datasource, catalog)) { // Error de conexion
			errorLog(modulo, 20, FALSE);
			db.GetErrorErrStr(msglog);
			errorInfo(modulo, msglog);
			exit(EXIT_FAILURE);
		}
		buscaAccion(db,st->tm_mday,st->tm_mon+1,st->tm_year+1900,st->tm_hour,st->tm_min,st->tm_wday );
		db.Close(); // Cierra conexión
	}
	exit(EXIT_SUCCESS);
}

	
