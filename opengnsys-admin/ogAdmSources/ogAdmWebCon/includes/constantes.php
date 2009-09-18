<?php

// C�igo de los ambitos para comandos
$AMBITO_CENTROS=0x01;
$AMBITO_GRUPOSAULAS=0x02;
$AMBITO_AULAS=0x04;
$AMBITO_GRUPOSORDENADORES=0x08;
$AMBITO_ORDENADORES=0x10;


// C�igo del resto de �bitos
$AMBITO_IMAGENES=0x20;
$AMBITO_PROCEDIMIENTOS=0x21;
$AMBITO_TAREAS=0x22;
$AMBITO_TRABAJOS=0x23;
$AMBITO_COMPONENTESHARD=0x24;
$AMBITO_COMPONENTESSOFT=0x25;
$AMBITO_PERFILESHARD=0x26;
$AMBITO_PERFILESSOFT=0x27;
$AMBITO_MENUS=0x28;
$AMBITO_SERVIDORESREMBO=0x29;
$AMBITO_SERVIDORESDHCP=0x30;
 $AMBITO_SOFTINCREMENTAL=0x31;
 $AMBITO_RESERVAS=0x32; 

// C�igo del resto de ambitos( grupos )
$AMBITO_GRUPOSIMAGENES=0x32;
$AMBITO_GRUPOSPROCEDIMIENTOS=0x33;
$AMBITO_GRUPOSTAREAS=0x34;
$AMBITO_GRUPOSTRABAJOS=0x35;
$AMBITO_GRUPOSCOMPONENTESHARD=0x36;
$AMBITO_GRUPOSCOMPONENTESSOFT=0x37;
$AMBITO_GRUPOSPERFILESHARD=0x38;
$AMBITO_GRUPOSPERFILESSOFT=0x39;
$AMBITO_GRUPOSMENUS=0x40;
$AMBITO_GRUPOSSERVIDORESREMBO=0x41;
$AMBITO_GRUPOSSERVIDORESDHCP=0x42;
$AMBITO_GRUPOSSOFTINCREMENTAL=0x43;
$AMBITO_GRUPOSRESERVAS=0x44;
$AMBITO_GRUPOSENTIDADES=0x45;

// Literales de los ambitos
$LITAMBITO_CENTROS="centros";
$LITAMBITO_AULAS="aulas";
$LITAMBITO_ORDENADORES="ordenadores";
$LITAMBITO_IMAGENES="imagenes";
$LITAMBITO_PROCEDIMIENTOS="procedimientos";
$LITAMBITO_TAREAS="tareas";
$LITAMBITO_TRABAJOS="trabajos";
$LITAMBITO_TIPOHARDWARES="tipohardwares";
$LITAMBITO_COMPONENTESHARD="componeneteshard";
$LITAMBITO_COMPONENTESSOFT="componenetessoft";
$LITAMBITO_PERFILESHARD="perfileshard";
$LITAMBITO_PERFILESSOFT="perfilessoft";
$LITAMBITO_MENUS="menus";
$LITAMBITO_SERVIDORESREMBO="servidoresrembo";
$LITAMBITO_SERVIDORESDHCP="servidoresdhcp";
 $LITAMBITO_SOFTINCREMENTAL="softincremental";
 $LITAMBITO_RESERVAS="reservas";
 $LITAMBITO_ADMINISTRACION="administracion";
 $LITAMBITO_UNIVERSIDADES="universidades";
 $LITAMBITO_ENTIDADES="entidades";
 $LITAMBITO_USUARIOS="usuarios";
// Literales de los ambitos ( Grupos )
$LITAMBITO_GRUPOSAULAS="gruposaulas";
$LITAMBITO_GRUPOSORDENADORES="gruposordenadores";
$LITAMBITO_GRUPOSIMAGENES="gruposimagenes";
$LITAMBITO_GRUPOSPROCEDIMIENTOS="gruposprocedimientos";
$LITAMBITO_GRUPOSTAREAS="grupostareas";
$LITAMBITO_GRUPOSTRABAJOS="grupostrabajos";
$LITAMBITO_GRUPOSCOMPONENTESHARD="gruposcomponenteshard";
$LITAMBITO_GRUPOSCOMPONENTESSOFT="gruposcomponentessoft";
$LITAMBITO_GRUPOSPERFILESHARD="gruposperfileshard";
$LITAMBITO_GRUPOSPERFILESSOFT="gruposperfilessoft";
$LITAMBITO_GRUPOSMENUS="gruposmenus";
$LITAMBITO_GRUPOSSERVIDORESREMBO="gruposervidorrembo";
$LITAMBITO_GRUPOSSERVIDORESDHCP="gruposervidordhcp";
$LITAMBITO_GRUPOSSOFTINCREMENTAL="grupossoftincremental";
$LITAMBITO_GRUPOSRESERVAS="gruposreservas";
$LITAMBITO_GRUPOSENTIDADES="gruposentidades";

// C�igo de los tipo de acciones
$EJECUCION_PROCEDIMIENTO=0x0000;
$EJECUCION_COMANDO=0x0001;
$EJECUCION_TAREA=0x0002;
$EJECUCION_TRABAJO=0x0003;
$EJECUCION_RESERVA=0x0004;

// C�igo de los tipo de notificadores
$NOTIFICADOR_ORDENADOR=0x0001;
$NOTIFICADOR_COMANDO=0x0002;
$NOTIFICADOR_TAREA=0x0003;

// Categorias de sucesos
$PROCESOS=0x01;
$INFORMACIONES=0x02;
$NOTIFICACIONES=0x03;

// C�igo de los tipos de notificaciones
$RESPUESTA_EJECUCION_COMANDO=0x0001;
$RESPUESTA_EJECUCION_TAREA=0x0002;
$RESPUESTA_EJECUCION_TRABAJO=0x0003;
$RESPUESTA_EJECUCION_PETICION=0x0004;

// C�igo de los tipos de items de los mens de clientes
$ITEM_PUBLICO=0x0001;
$ITEM_PRIVADO=0x0002;

//Codificaci� de los resultados de las acciones

$ACCION_EXITOSA='1'; // Finalizada con exito
$ACCION_FALLIDA='2'; // Finalizada con errores
$ACCION_TERMINADA='3'; // Finalizada manualmente con indicacion de exito 
$ACCION_ABORTADA='4'; // Finalizada manualmente con indicacion de errores 
$ACCION_SINERRORES='5'; // Activa y sin ningn error
$ACCION_CONERRORES='6'; // Activa y con algn error

//Codificaci� de los estados de las acciones

$ACCION_DETENIDA='0'; // Acci� momentanemente parada
$ACCION_INICIADA='1'; // Acci� activa
$ACCION_FINALIZADA='2'; // Acci� finalizada

// Nombrey path del fichero de intercambio de parametros entre p�inas 
//  Ha sido necesario porque cuando los parametros enviados execed�n de cierta longitud
//  ocurria una excepci� al llamar a la p�ina por GET.

$fileparam="../includes/PRM_".$usuario;

// M�ima longitud de los parametros enviados entre p�inas
$MAXLONPRM=16000;
$MAXLONVISUSCRIPT =1024; // longitud Maxima de visualizaci� del script en las colas de acciones
$MAXSIZEFILERBC=100000; // longitud Maxima de los fichero de script enviados como comandos ejecuci� de script
$LONCABECERA=11; // Longitud de la cabera de las tramas "@JMMLCAMDJe"  Donde e es el ejecutor
$tbTiposParticiones="";
$tbTiposParticiones[0]="EMPTY";
$tbTiposParticiones[1]="BIGDOS";
$tbTiposParticiones[2]="FAT32";
$tbTiposParticiones[3]="NTFS";
$tbTiposParticiones[4]="EXT2";
$tbTiposParticiones[5]="EXT3";
$tbTiposParticiones[6]="EXT4";
$tbTiposParticiones[7]="LINUX-SWAP";
$tbTiposParticiones[8]="CACHE";
$tbTiposParticiones[9]="VFAT";
$tbTiposParticiones[10]="UNKNOW";


//Codificaci� de los estados de las reservas
$RESERVA_CONFIRMADA=1; // Reserva confirmada
$RESERVA_PENDIENTE=2; // Reserva pendiente
$RESERVA_DENEGADA=3; // Reserva denegada

$SUPERADMINISTRADOR=1; // administrador de la aplicaci�
$ADMINISTRADOR=2; // administrador de Centro
$OPERADOR=3; // operador de aula

?>