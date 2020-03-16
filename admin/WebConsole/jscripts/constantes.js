// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Marzo005
// Nombre del fichero: constantes.js
// Descripción : 
//		Este fichero declara variables de uso comun
// *************************************************************************************************************************************************
// Código de los ambitos para comandos
var AMBITO_CENTROS=0x01;
var AMBITO_GRUPOSAULAS=0x02;
var AMBITO_AULAS=0x04;
var AMBITO_GRUPOSORDENADORES=0x08;
var AMBITO_ORDENADORES=0x10;

// Código del resto de ámbitos
var AMBITO_IMAGENES=0x20;
var AMBITO_PROCEDIMIENTOS=0x21;
var AMBITO_TAREAS=0x22;
var AMBITO_TRABAJOS=0x23;
var AMBITO_COMPONENTESHARD=0x24;
var AMBITO_COMPONENTESSOFT=0x25;
var AMBITO_PERFILESHARD=0x26;
var AMBITO_PERFILESSOFT=0x27;
var AMBITO_MENUS=0x28;
var AMBITO_SERVIDORESREMBO=0x29;
var AMBITO_SERVIDORESDHCP=0x30;
var AMBITO_RESERVAS=0x30;
var AMBITO_SOFTINCREMENTAL=0x31;
var AMBITO_RESERVAS=0x32;

// Código del resto de ambitos( grupos )
var AMBITO_GRUPOSIMAGENES=0x31;
var AMBITO_GRUPOSPROCEDIMIENTOS=0x32;
var AMBITO_GRUPOSTAREAS=0x33;
var AMBITO_GRUPOSTRABAJOS=0x34;
var AMBITO_GRUPOSCOMPONENTESHARD=0x35;
var AMBITO_GRUPOSCOMPONENTESSOFT=0x36;
var AMBITO_GRUPOSPERFILESHARD=0x37;
var AMBITO_GRUPOSPERFILESSOFT=0x38;
var AMBITO_GRUPOSMENUS=0x39;
var AMBITO_GRUPOSSERVIDORESREMBO=0x40;
var AMBITO_GRUPOSSERVIDORESDHCP=0x41;
var AMBITO_GRUPOSSOFTINCREMENTAL=0x43;
var AMBITO_GRUPOSRESERVAS=0x44;

// En la base de datos el id de los tipos de los grupos son
// tipo=65 -> grupos de repositorios
// tipo=70 -> grupos de imágenes monoliticas
// tipo=71 -> grupos de imágenes básicas
// tipo=72 -> grupos de imágenes incrementales

// Literales de los ambitos
var LITAMBITO_CENTROS="centros";
var LITAMBITO_AULAS="aulas";
var LITAMBITO_ORDENADORES="ordenadores";
var LITAMBITO_IMAGENES="imagenes";
var LITAMBITO_PROCEDIMIENTOS="procedimientos";
var LITAMBITO_TAREAS="tareas";
var LITAMBITO_TRABAJOS="trabajos";
var LITAMBITO_COMPONENTESHARD="componeneteshard";
var LITAMBITO_COMPONENTESSOFT="componenetessoft";
var LITAMBITO_PERFILESHARD="perfileshard";
var LITAMBITO_PERFILESSOFT="perfilessoft";
var LITAMBITO_MENUS="menus";
var LITAMBITO_SERVIDORESREMBO="servidoresrembo";
var LITAMBITO_SERVIDORESDHCP="servidoresrembo";
 var LITAMBITO_SOFTINCREMENTAL="softincremental";
 var LITAMBITO_RESERVAS="reservas";

// Literales de los ambitos ( Grupos )
var LITAMBITO_GRUPOSAULAS="gruposaulas";
var LITAMBITO_GRUPOSORDENADORES="gruposordenadores";
var LITAMBITO_GRUPOSIMAGENES="gruposimagenes";
var LITAMBITO_GRUPOSPROCEDIMIENTOS="gruposprocedimientos";
var LITAMBITO_GRUPOSTAREAS="grupostareas";
var LITAMBITO_GRUPOSTRABAJOS="grupostrabajos";
var LITAMBITO_GRUPOSCOMPONENTESHARD="gruposcomponenteshard";
var LITAMBITO_GRUPOSCOMPONENTESSOFT="gruposcomponentessoft";
var LITAMBITO_GRUPOSPERFILESHARD="gruposperfileshard";
var LITAMBITO_GRUPOSPERFILESSOFT="gruposperfilessoft";
var LITAMBITO_GRUPOSMENUS="gruposmenus";
var LITAMBITO_GRUPOSSERVIDORESREMBO="gruposervidorrembo";
var LITAMBITO_GRUPOSSERVIDORESDHCP="gruposervidordhcp";
var LITAMBITO_GRUPOSSOFTINCREMENTAL="grupossoftincremental";
var LITAMBITO_GRUPOSRESERVAS="gruposreservas";

// Código de los tipo de acciones
var EJECUCION_COMANDO=0x0001;
var EJECUCION_PROCEDIMIENTO=0x0002;
var EJECUCION_TAREA=0x0003;
var EJECUCION_RESERVA=0x0004;
var EJECUCION_AUTOEXEC=0x0005;

var ACCION_INICIADA=1; // Acción activa
var ACCION_DETENIDA=2; // Acción momentanemente parada
var ACCION_FINALIZADA=3; // Acción finalizada

var ACCION_SINRESULTADO=0; // Sin resultado
var ACCION_EXITOSA=1; // Finalizada con exito
var ACCION_FALLIDA=2; // Finalizada con errores

var LITACCION_FALLIDA="Acción CANCELADA manualmente";
var LITACCION_EXITOSA="Acción TERMINADA manualmente";
	
var corte_currentNodo=null;
var currentTipo=null;
var currentLitTipo=null;

var RESERVA_CONFIRMADA=1; // Reserva confirmada
var RESERVA_PENDIENTE=2; // Reserva pendiente
var RESERVA_DENEGADA=3; // Reserva denegada

var SUPERADMINISTRADOR=1; // administrador de la aplicación
var ADMINISTRADOR=2; // administrador de Centro
var OPERADOR=3; // operador de aula

// Código de los tipos de mensajes
var MSG_COMANDO=0x01; // Mensaje del tipo comando
var MSG_NOTIFICACION=0x02; // Respuesta a la ejecución un comando
var MSG_PETICION=0x03; // Petición de cualquier actuación
var MSG_RESPUESTA=0x04; // Respuesta a una petición
var MSG_INFORMACION=0x05; // Envío de cualquier información sin espera de confirmación o respuesta


// Tipos de imagenes
var IMAGENES_MONOLITICAS=0x01;
var IMAGENES_BASICAS=0x02;
var IMAGENES_INCREMENTALES=0x03;

// Relación tipos de imágenes con literales del tipo
var littipo = Array;
littipo[1]="monoliticas";
littipo[2]="basicas";
littipo[3]="incrementales";
