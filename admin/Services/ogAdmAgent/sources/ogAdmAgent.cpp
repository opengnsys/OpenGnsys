// *************************************************************************
// Aplicación: OPENGNSYS
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2006
// Nombre del fichero: ogAdmAgent.cpp
// Descripción :
//		
// ****************************************************************************
#include "ogAdmAgent.h"
#include "ogAdmLib.c"

//________________________________________________________________________________________________________
//
// Función: TomaConfiguracion
//
//		Descripción:
//			Esta función lee el fichero de configuración del programa hidralinuxcli y toma los parámetros
//		Parámetros:
//				- pathfilecfg : Ruta al fichero de configuración
//________________________________________________________________________________________________________
int TomaConfiguracion(char* pathfilecfg) {
	long lSize;
	char * buffer, *lineas[100], *dualparametro[2];
	char ch[2];
	int i, numlin, resul;

	if (pathfilecfg == NULL)
		return (FALSE); // Nombre del fichero en blanco

	Fconfig = fopen(pathfilecfg, "rb");
	if (Fconfig == NULL)
		return (FALSE);
	fseek(Fconfig, 0, SEEK_END); // Obtiene tamaño del fichero.
	lSize = ftell(Fconfig);
	rewind(Fconfig);
	buffer = (char*) malloc(lSize); // Toma memoria para el buffer de lectura.
	if (buffer == NULL)
		return (FALSE);
	fread(buffer, 1, lSize, Fconfig); // Lee contenido del fichero
	fclose(Fconfig);

	//inicializar
	IPlocal[0] = (char) NULL;
	servidorhidra[0] = (char) NULL;
	Puerto[0] = (char) NULL;

	usuario[0] = (char) NULL;
	pasguor[0] = (char) NULL;
	datasource[0] = (char) NULL;
	catalog[0] = (char) NULL;

	strcpy(ch, "\n");// caracter delimitador (salto de linea)
	numlin = split_parametros(lineas, buffer, ch);
	for (i = 0; i < numlin; i++) {
		strcpy(ch, "=");// caracter delimitador
		split_parametros(dualparametro, lineas[i], ch); // Toma primer nombre del parametro

		resul = strcmp(dualparametro[0], "IPhidra");
		if (resul == 0)
			strcpy(IPlocal, dualparametro[1]);

		resul = strcmp(dualparametro[0], "IPhidra");
		if (resul == 0)
			strcpy(servidorhidra, dualparametro[1]);

		resul = strcmp(dualparametro[0], "Puerto");
		if (resul == 0)
			strcpy(Puerto, dualparametro[1]);

		resul = strcmp(dualparametro[0], "Usuario");
		if (resul == 0)
			strcpy(usuario, dualparametro[1]);

		resul = strcmp(dualparametro[0], "PassWord");
		if (resul == 0)
			strcpy(pasguor, dualparametro[1]);

		resul = strcmp(dualparametro[0], "DataSource");
		if (resul == 0)
			strcpy(datasource, dualparametro[1]);

		resul = strcmp(dualparametro[0], "Catalog");
		if (resul == 0)
			strcpy(catalog, dualparametro[1]);
	}
	if (IPlocal[0] == (char) NULL) {
		RegistraLog("IPlocal, NO se ha definido este parámetro", false);
		return (FALSE);
	}
	if (servidorhidra[0] == (char) NULL) {
		RegistraLog("IPhidra, NO se ha definido este parámetro", false);
		return (FALSE);
	}
	if (Puerto[0] == (char) NULL) {
		RegistraLog("Puerto, NO se ha definido este parámetro", false);
		return (FALSE);
	}
	puerto = atoi(Puerto);

	if (usuario[0] == (char) NULL) {
		RegistraLog("Usuario, NO se ha definido este parámetro", false);
		return (FALSE);
	}
	if (pasguor[0] == (char) NULL) {
		RegistraLog("PassWord, NO se ha definido este parámetro", false);
		return (FALSE);
	}
	if (datasource[0] == (char) NULL) {
		RegistraLog("DataSource, NO se ha definido este parámetro", false);
		return (FALSE);
	}
	if (catalog[0] == (char) NULL) {
		RegistraLog("Catalog, NO se ha definido este parámetro", false);
		return (FALSE);
	}
	return (TRUE);
}
// _____________________________________________________________________________________________________________
// Función: busca_accion
//
//		 Descripción:
//			Esta Función busca en la base de datos, acciones programadas
//		Parametros:
//			- dia : Día actual del mes
//			- mes : mes en curso
//			- anno : Año en curso
//			- hora : Hora actual
//			- minutos : Minutos actuales
//			- diasemana : Dia de la semana 1=lunes,2=martes ... ( 0 Domingo)
// _____________________________________________________________________________________________________________
int busca_accion(WORD dia,WORD mes,WORD anno,WORD hora,WORD minutos,WORD diasemana)
{
	char sqlstr[1000],ErrStr[200];
	Database db,wdb;
	Table tbl;
	char parametros[LONGITUD_PARAMETROS];
	BYTE swampm,bitsemana;
	int tipoaccion,identificador;
	int ordsem,ordulsem,ordiasem_1,maxdias;
	anno=anno-2009; // Año de comienzo es 2004
	if(hora>11){
		hora-=12;
		swampm=1; // Es pm
	}
	else
		swampm=0; // Es am

	if(diasemana==0) diasemana=7; // El domingo

	// Cuestión semanas
	ordiasem_1=DiadelaSemana(1,mes,anno+2009);
	ordsem=SemanadelMes(ordiasem_1,dia); // Calcula el número de la semana
	if (mes!=2) // Toma el último día de ese mes
		maxdias=dias_meses[mes];
	else{
		if (bisiesto(anno+2009))
			maxdias=29;
		else
			maxdias=28;
	}
	ordulsem=SemanadelMes(ordiasem_1,maxdias); // Calcula el número de última semana

	bitsemana=HEX_semanas[ordsem];
	if(ordsem==ordulsem) // Si es la última semana del mes
		bitsemana|=HEX_semanas[6];

	if (!db.Open(usuario, pasguor, datasource, catalog)) { // error de conexion
			db.GetErrorErrStr(ErrStr);
			return (false);
	}
	sprintf(sqlstr,"SELECT DISTINCT tipoaccion,identificador FROM programaciones WHERE "\
					" suspendida=0 "\
					" AND (annos & %d <> 0) "\
					" AND (meses & %d<>0) "\
					" AND ((diario & %d<>0) OR (dias & %d<>0) OR (semanas & %d<>0))"\
					" AND (horas & %d<>0) AND ampm=%d AND minutos=%d",\
					HEX_annos[anno],\
					HEX_meses[mes],\
					HEX_dias[dia],\
					HEX_diasemana[diasemana],
					bitsemana,\
					HEX_horas[hora],\
					swampm,minutos);

	RegistraLog(sqlstr,false);

	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	if(tbl.ISEOF()){
		return(true);  // No hay acciones programadas
	}
	if (!wdb.Open(usuario, pasguor, datasource, catalog)) { // error de conexion
			db.GetErrorErrStr(ErrStr);
			return (false);
	}
	while(!tbl.ISEOF()){ // Busca entre todas las programaciones 
		if(!tbl.Get("tipoaccion",tipoaccion)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		if(!tbl.Get("identificador",identificador)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		if(tipoaccion==EJECUCION_TAREA){ // Es una programación de una tarea
			EjecutarTarea(identificador,0,0,0,wdb,parametros);
		}
		else{
			if(tipoaccion==EJECUCION_TRABAJO){
				EjecutarTrabajo(identificador,wdb,parametros); // Es una programación de un trabajo
			}
			else{
				if(tipoaccion==EJECUCION_RESERVA){
					EjecutarReserva(identificador,wdb,parametros); // Es una programación de un trabajo
				}
			}
		}
		tbl.MoveNext();
	}
	return(true);  
}
// _____________________________________________________________________________________________________________
// Función: bisiesto
//
//		Descripción:
//			Esta Función devuelve true si el año pasado como parámetro es bisiesto y false si no lo es
//		Parametros:
//			- anob : un año en formato aaaa
// _____________________________________________________________________________________________________________
bool bisiesto(WORD anob){
	return(anob%4==0);
}
// _____________________________________________________________________________________________________________
// Función: DiadelaSemana
//
//		Descripción:
//			Esta Función devuelve el número del día de la semana: 1=Lunes, 2=martes ... 6=sábado  7=domingo de una fecha determinada
//		Parametros:
//			- dia : Un día
//			- mes : Un mes
//			- anno : Un año
// _____________________________________________________________________________________________________________
int DiadelaSemana(WORD dia,WORD mes,WORD anno)
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
// _____________________________________________________________________________________________________________
// Función: SemanadelMes
//
//		Descripción:
//			Esta Función devuelve el número de semana perteneciente a un día de ese mes
//		Parámetros:
//			- ordiasem_1 : Orden semenal (1,2...) del dia del primer dia del mes que se pasa como parámetro
//			- diames : El mes concreto
// _____________________________________________________________________________________________________________
int SemanadelMes(int ordiasem_1,int diames)
{
	int nwdia,resto,cociente;

	nwdia=diames+ordiasem_1-1;
	cociente=nwdia/7;
	resto=nwdia%7;
	if(resto>0) cociente++;
	return(cociente);
}
// _____________________________________________________________________________________________________________
// Función: Pausa
//
//		Descripción:
//			Hace una pausa en segundos
//		Parametros:
//			- s : Segundos de pausa
// _____________________________________________________________________________________________________________
void Pausa(int s)
{
	int seg=0;
	clock_t comienzo;

    comienzo = clock();
    do{
		seg=(clock()-comienzo)/CLOCKS_PER_SEC;
	}while(seg<s);
}
// _____________________________________________________________________________________________________________
// Función: EjecutarTrabajo
//
//		Descripción:
//			Registra una acción (Trabajo y la envía para su ejecución
//		Parámetros:
//			- idtrabajo : Identificador del trabajo
//			- db: una conexion ADO operativa
//			- parametros: Parámetros de la acción
// _____________________________________________________________________________________________________________
int EjecutarTrabajo(int idtrabajo,Database db,char*parametros )
{
	char sqlstr[1000],ErrStr[200];
	Table tbl;
	int cont_tareas=0,lon;
	int  idtarea,idtrabajotarea,idcentro;
	char wambitrabajo[500],ambitrabajo[4000];
	char wparamtrabajo[20],paramtrabajo[1000];
	int  tbTareasidtarea[100],tbTareasidnotificador[100];
	char *tbTareasparametros[100],*tbTareasambitoambitskwrk[100];
	char ambitskwrk[500];

	ambitrabajo[0]=(char)NULL; // Inicialización
	strcpy(paramtrabajo,"tsk="); // Inicialización

	// recupera el identificador del Centro propietario de la tarea
	sprintf(sqlstr,"SELECT idcentro FROM trabajos WHERE idtrabajo=%d",idtrabajo);
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	if(tbl.ISEOF()) return(true);
	if(!tbl.Get("idcentro",idcentro)){ // Toma dato
		tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
		return(false);
	}
	// Recupera las tareas que forman parte del trabajo
	sprintf(sqlstr,"SELECT * FROM trabajos_tareas WHERE idtrabajo=%d ORDER by orden",idtrabajo);
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	if(tbl.ISEOF()) return(true);
	// Recorre trabajos-tareas
	while(!tbl.ISEOF()){ 	
		if(!tbl.Get("idtrabajotarea",idtrabajotarea)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		tbTareasidnotificador[cont_tareas]=idtrabajotarea;

		if(!tbl.Get("idtarea",idtarea)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		tbTareasidtarea[cont_tareas]=idtarea;

		if(!tbl.Get("parametros",parametros)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		lon=strlen(parametros);
		tbTareasparametros[cont_tareas]=(char*)malloc(lon);
		if(tbTareasparametros[cont_tareas]==NULL)
			return(false); // No hay memoria bastante
		strcpy(tbTareasparametros[cont_tareas],parametros);
		
		if(!tbl.Get("ambitskwrk",ambitskwrk)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		lon=strlen(ambitskwrk);
		tbTareasambitoambitskwrk[cont_tareas]=(char*)malloc(lon);
		strcpy(tbTareasambitoambitskwrk[cont_tareas],ambitskwrk);

		sprintf(wambitrabajo,"%s;",ambitskwrk);
		strcat(ambitrabajo,wambitrabajo);

		sprintf(wparamtrabajo,"%d;",idtrabajotarea);
		strcat(paramtrabajo,wparamtrabajo);

		cont_tareas++;
		tbl.MoveNext();
	}
	lon=strlen(ambitrabajo);
	ambitrabajo[lon-1]=(char)NULL; // Quita la coma final

	lon=strlen(paramtrabajo);
	paramtrabajo[lon-1]=(char)NULL; // Quita la coma final

	char fechareg[100];


	struct tm* st;
	st = TomaHora();
	sprintf(fechareg, "%d/%d/%d %d:%d:%d", st->tm_year + 1900, st->tm_mon + 1,
				st->tm_mday, st->tm_hour, st->tm_min, st->tm_sec);

	sprintf(sqlstr,"INSERT INTO acciones (tipoaccion,idtipoaccion,cateaccion,ambito,idambito,ambitskwrk,fechahorareg,estado,resultado,idcentro,parametros,accionid,idnotificador) VALUES (%d,%d,%d,0,0,'%s','%s','%s','%s',%d,'%s',0,0)",EJECUCION_TRABAJO,idtrabajo,PROCESOS,ambitrabajo,fechareg,ACCION_INICIADA,ACCION_SINERRORES,idcentro,paramtrabajo);
	if(!db.Execute(sqlstr)){ // Error al insertar
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	int accionid=0;
	// Toma identificador de la acción
	sprintf(sqlstr,"SELECT @@identity as identificador");
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	if(!tbl.ISEOF()){ // Si existe registro
		if(!tbl.Get("identificador",accionid)){
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
	}
	int i;
	// Insertar acciones:tareas
	for (i=0;i<cont_tareas;i++){
		if(!EjecutarTarea(tbTareasidtarea[i],accionid,tbTareasidnotificador[i],idcentro,db,parametros)){
			free(tbTareasparametros[i]);
			free(tbTareasambitoambitskwrk[i]);
			return(false);
		}
		free(tbTareasparametros[i]);
		free(tbTareasambitoambitskwrk[i]);
	}
	return(true);
}
// _____________________________________________________________________________________________________________
// Función: EjecutarTarea
//
//		Descripción:
//			Registra una acción (Tarea) y la envía para su ejecución
//		Parámetros:
//			- idtarea : Identificador de la tarea
//			- accionid: identificador del trabajo padre (si existe)
//			- idnotificador:  identificador del trabajo_tarea incluido en trabajo padre (si existe)
//			- idcentro: Centro propietario del trabjo padre (si existe este trabajo)
//			- db: una conexion ADO operativa
//			- parametros: Parámetros de la acción
// _____________________________________________________________________________________________________________
int EjecutarTarea(int idtarea,int accionid,int idnotificador,int idcentro,Database db,char *parametros )
{
	char sqlstr[1000],ErrStr[200],ambito;
	Table tbl;
	int cont_comandos=0,lon;
	int  idcomando,idambito,idtareacomando,accionidcmd;
	char wambitarea[20],ambitarea[4000];
	char wparamtarea[20],paramtarea[1000],pids[20];
	int  tbComandosidcomando[100],tbComandosambito[100],tbComandosidnotificador[100],tbComandosidambito[100];
	char *tbComandosparametros[100];

	ambitarea[0]=(char)NULL; // Inicialización
	strcpy(paramtarea,"cmd="); // Inicialización
	if(idcentro==0){
		// recupera el identificador del Centro propietario de la tarea
		sprintf(sqlstr,"SELECT idcentro FROM tareas WHERE idtarea=%d",idtarea);
		if(!db.Execute(sqlstr,tbl)){ // Error al leer
			db.GetErrorErrStr(ErrStr);
			return(false);
		}
		if(tbl.ISEOF()) return(true);
		if(!tbl.Get("idcentro",idcentro)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
	}
	// Recupera los comandos que forman parte de la tarea
	sprintf(sqlstr,"SELECT * FROM tareas_comandos WHERE idtarea=%d ORDER by orden",idtarea);
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	if(tbl.ISEOF()) return(true);
		
	// Recorre tareas-comandos
	while(!tbl.ISEOF()){ 	
		if(!tbl.Get("idcomando",idcomando)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		tbComandosidcomando[cont_comandos]=idcomando;

		if(!tbl.Get("ambito",ambito)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		tbComandosambito[cont_comandos]=ambito;

		if(!tbl.Get("idambito",idambito)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		tbComandosidambito[cont_comandos]=idambito;


		if(!tbl.Get("parametros",parametros)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}

		lon=strlen(parametros);
		tbComandosparametros[cont_comandos]=(char*)malloc(lon+20);
		if(tbComandosparametros[cont_comandos]==NULL)
			return(false); // No hay memoria bastante

		strcpy(tbComandosparametros[cont_comandos],parametros);
		
		if(!tbl.Get("idtareacomando",idtareacomando)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		tbComandosidnotificador[cont_comandos]=idtareacomando;

		sprintf(wambitarea,"%d:%d;",ambito,idambito);
		strcat(ambitarea,wambitarea);

		sprintf(wparamtarea,"%d;",idtareacomando);
		strcat(paramtarea,wparamtarea);

		cont_comandos++;
		tbl.MoveNext();
	}
	lon=strlen(ambitarea);
	ambitarea[lon-1]=(char)NULL; // Quita la coma final

	lon=strlen(paramtarea);
	paramtarea[lon-1]=(char)NULL; // Quita la coma final

	char fechareg[100];


	struct tm* st;
    st = TomaHora();
    sprintf(fechareg, "%d/%d/%d %d:%d:%d", st->tm_year + 1900, st->tm_mon + 1,
    			st->tm_mday, st->tm_hour, st->tm_min, st->tm_sec);

	sprintf(sqlstr,"INSERT INTO acciones (tipoaccion,idtipoaccion,cateaccion,ambito,idambito,ambitskwrk,fechahorareg,estado,resultado,idcentro,parametros,accionid,idnotificador) VALUES (%d,%d,%d,0,0,'%s','%s','%s','%s',%d,'%s',%d,%d)",EJECUCION_TAREA,idtarea,PROCESOS,ambitarea,fechareg,ACCION_INICIADA,ACCION_SINERRORES,idcentro,paramtarea,accionid,idnotificador);
	if(!db.Execute(sqlstr)){ // Error al insertar
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	accionid=0;
	// Toma identificador de la acción
	sprintf(sqlstr,"SELECT @@identity as identificador");
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	if(!tbl.ISEOF()){ // Si existe registro
		if(!tbl.Get("identificador",accionid)){
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
	}
	int i;
	// Insertar acciones:comandos
	for (i=0;i<cont_comandos;i++){
		st = TomaHora();
		sprintf(fechareg, "%d/%d/%d %d:%d:%d", st->tm_year + 1900, st->tm_mon
						+ 1, st->tm_mday, st->tm_hour, st->tm_min, st->tm_sec);

		sprintf(sqlstr,"INSERT INTO acciones (tipoaccion,idtipoaccion,cateaccion,ambito,idambito,fechahorareg,estado,resultado,idcentro,parametros,accionid,idnotificador) VALUES (%d,%d,%d,%d,%d,'%s','%s','%s',%d,'%s',%d,%d)",EJECUCION_COMANDO,tbComandosidcomando[i],PROCESOS,tbComandosambito[i],tbComandosidambito[i],fechareg,ACCION_EXITOSA,ACCION_SINERRORES,idcentro,tbComandosparametros[i],accionid,tbComandosidnotificador[i]);
		if(!db.Execute(sqlstr)){ // Error al insertar
			db.GetErrorErrStr(ErrStr);
			free(tbComandosparametros[i]);
			return(false);
		}

		// Toma identificador dela acción
		sprintf(sqlstr,"SELECT @@identity as identificador");
		if(!db.Execute(sqlstr,tbl)){ // Error al leer
			db.GetErrorErrStr(ErrStr);
			return(false);
		}

		if(!tbl.ISEOF()){ // Si existe registro
			if(!tbl.Get("identificador",accionidcmd)){
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
			}
		}
		sprintf(pids,"ids=%d\r",accionidcmd);
		strcat(tbComandosparametros[i],pids); // Le añade el identificador de la acción
		envia_comando(tbComandosparametros[i]);
		free(tbComandosparametros[i]);
	}
	return(true);
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
int EjecutarReserva(int idreserva,Database db,char*parametros )
{
	char sqlstr[1000],ErrStr[200];
	Table tbl;
	int idaccion;

	sprintf(sqlstr,"SELECT idtarea,idtrabajo FROM reservas WHERE idreserva=%d",idreserva);
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	if(tbl.ISEOF()){
		return(false);  // No hay acciones previas en la  reserva
	}

	if(!tbl.Get("idtarea",idaccion)){ // Toma dato
		tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
		return(false);
	}
	if(idaccion>0)
		EjecutarTarea(idaccion,0,0,0,db,parametros); // Es una reserva con tarea previa

	if(!tbl.Get("idtrabajo",idaccion)){ // Toma dato
		tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
		return(false);
	}
	if(idaccion>0)
		EjecutarTrabajo(idaccion,db,parametros); // Es una reserva con trabajo previo

	return(true);
}
// _____________________________________________________________________________________________________________
// Función: envia_comando
//
//		Descripción:
//			Envía un comando a la red. Para ello es necesario tener iniciado el servicio hidra.
//		Parámetros:
//			- parametros: Parámetros del comando
// _____________________________________________________________________________________________________________
int envia_comando(char* parametros)
{
	SOCKET sClient;
	TRAMA trama;

	sClient = AbreConexion(servidorhidra,puerto);
	if (sClient == (SOCKET)NULL)
		return(FALSE);

	trama.arroba='@';
	strncpy(trama.identificador,"JMMLCAMDJ",9);
	trama.ejecutor=parametros[0];
	strcpy(trama.parametros,(char*)&parametros[1]);
    return(manda_trama(sClient,&trama));
}

/// _____________________________________________________________________________________________________________
// Función: manda_trama
//
//		Descripción:
//			Esta Función envía una trama por la red (TCP)
//		Parámetros:
//			- sock : El socket del host al que se dirige la trama
//			- trama: El contenido de la trama
/// _____________________________________________________________________________________________________________
int manda_trama(SOCKET sock,TRAMA* trama)
{
	int nLeft,idx,ret;

	Encriptar((char*)trama);
	nLeft = strlen((char*)trama);
	idx = 0;
	while(nLeft > 0){
		ret = send(sock,(char*)&trama[idx], nLeft, 0);
		if (ret == 0)
			break;
		else
			if (ret == SOCKET_ERROR){
				RegistraLog("***AGENT***send() fallo al enviar trama:",true);
				return(FALSE);
			}
		nLeft -= ret;
		idx += ret;
	}
	return(TRUE);
}
//******************************************************************************************************************************************
// PROGRAMA PRINCIPAL
//******************************************************************************************************************************************
int main(int argc, char *argv[]) {

	struct tm* st;

	strcpy(szPathFileCfg, "ogAdmAgent.cfg");
	strcpy(szPathFileLog, "ogAdmAgent.log");
	int i;
	for (i = 1; (i + 1) < argc; i += 2) {
		if (argv[i][0] == '-') {
			switch (tolower(argv[i][1])) {
			case 'f':
				if (argv[i + 1] != NULL)
					strcpy(szPathFileCfg, argv[i + 1]);
				else {
					RegistraLog(
							"Fallo en los parámetros: Debe especificar el fichero de configuración del servicio",
							false);
					exit(EXIT_FAILURE);
				}
				break;
			case 'l':
				if (argv[i + 1] != NULL)
					strcpy(szPathFileLog, argv[i + 1]);
				else {
					RegistraLog(
							"Fallo en los parámetros: Debe especificar el fichero de log para el servicio",
							false);
					exit(EXIT_FAILURE);
				}
				break;
			default:
				RegistraLog(
						"Fallo de sintaxis en los parámetros: Debe especificar -f nombre_del_fichero_de_configuración_del_servicio",
						false);
				exit(EXIT_FAILURE);
				break;
			}
		}
	}
	if (szPathFileCfg == NULL) {
		printf("***Error. No se ha especificado fichero de configuración\n");
		exit(EXIT_FAILURE);
	}
	if (!TomaConfiguracion(szPathFileCfg)) { // Toma parametros de configuración
		RegistraLog(
				"El fichero de configuración contiene un error de sintaxis",
				false);
		exit(EXIT_FAILURE);
	}

	int pseg;
	while (TRUE){ 
		st = TomaHora();
		//pseg=1000*(65-st->tm_sec); // Calcula milisegundos de inactividad de la hebra
		pseg=65-st->tm_sec; // Calcula segundos de inactividad de la hebra
		sleep(pseg);

		// Toma la hora
		st = TomaHora();
		busca_accion(st->tm_mday,st->tm_mon+1,st->tm_year+1900,st->tm_hour,st->tm_min,st->tm_wday );
	}
}
	
