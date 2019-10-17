-- Fichero de instalación de la base de datos.

SET sql_mode = "NO_AUTO_VALUE_ON_ZERO";
SET GLOBAL sql_mode = TRIM(BOTH ',' FROM REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));
SET GLOBAL event_scheduler = ON;

--
-- Base de datos: `ogAdmBD`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acciones`
--

DROP TABLE IF EXISTS `acciones`;
CREATE TABLE `acciones` (
  `idaccion` int(11) NOT NULL AUTO_INCREMENT,
  `tipoaccion` smallint(6) NOT NULL DEFAULT '0',
  `idtipoaccion` int(11) NOT NULL DEFAULT '0', 
  `descriaccion` varchar(250) NOT NULL DEFAULT '',
  `idordenador` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(50) NOT NULL DEFAULT '',
  `sesion` int(11) NOT NULL DEFAULT '0',
  `idcomando` int(11) NOT NULL DEFAULT '0',
  `parametros` text,
  `fechahorareg` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `fechahorafin` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `estado` tinyint(1) NOT NULL DEFAULT '0',
  `resultado` tinyint(1) NOT NULL DEFAULT '0',
  `descrinotificacion` varchar(256) DEFAULT NULL,
  `ambito` smallint(6) NOT NULL DEFAULT '0',
  `idambito` int(11) NOT NULL DEFAULT '0',
  `restrambito` text,
  `idprocedimiento` int(11) NOT NULL DEFAULT '0',
  `idtarea` int(11) NOT NULL DEFAULT '0',
  `idcentro` int(11) NOT NULL DEFAULT '0',
  `idprogramacion` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idaccion`),
  KEY (`idordenador`),
  KEY (`idprocedimiento`),
  KEY (`idtarea`),
  KEY (`idprogramacion`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acciones_log`
--

DROP TABLE IF EXISTS `acciones_log`;
CREATE TABLE acciones_log LIKE acciones;
ALTER TABLE acciones_log ADD fecha_borrado DATETIME;
DELIMITER //
-- Trigger para guardar acciones antes de ser borradas.
CREATE TRIGGER registrar_acciones BEFORE DELETE ON acciones FOR EACH ROW BEGIN
	INSERT INTO acciones_log VALUES
		(OLD.idaccion, OLD.tipoaccion, OLD.idtipoaccion, OLD.descriaccion,
		OLD.idordenador, OLD.ip, OLD.sesion, OLD.idcomando, OLD.parametros,
		OLD.fechahorareg, OLD.fechahorafin, OLD.estado, OLD.resultado,
		OLD.descrinotificacion, OLD.ambito, OLD.idambito, OLD.restrambito,
		OLD.idprocedimiento, OLD.idtarea, OLD.idcentro, OLD.idprogramacion, NOW());
END//
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acciones_menus`
--

DROP TABLE IF EXISTS `acciones_menus`;
CREATE TABLE `acciones_menus` (
  `idaccionmenu` int(11) NOT NULL AUTO_INCREMENT,
  `tipoaccion` tinyint(4) NOT NULL DEFAULT '0',
  `idtipoaccion` int(11) NOT NULL DEFAULT '0',
  `idmenu` int(11) NOT NULL DEFAULT '0',
  `tipoitem` tinyint(4) DEFAULT NULL,
  `idurlimg` int(11) DEFAULT NULL,
  `descripitem` varchar(250) DEFAULT NULL,
  `orden` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`idaccionmenu`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores_centros`
--

DROP TABLE IF EXISTS `administradores_centros`;
CREATE TABLE `administradores_centros` (
  `idadministradorcentro` int(11) NOT NULL AUTO_INCREMENT,
  `idusuario` int(11) NOT NULL DEFAULT '0',
  `idcentro` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idadministradorcentro`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `administradores_centros` (`idadministradorcentro`, `idusuario`, `idcentro`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aulas`
--

DROP TABLE IF EXISTS `aulas`;
CREATE TABLE `aulas` (
  `idaula` int(11) NOT NULL AUTO_INCREMENT,
  `nombreaula` varchar(100) NOT NULL DEFAULT '',
  `idcentro` int(11) NOT NULL DEFAULT '0',
  `urlfoto` varchar(250) DEFAULT NULL,
  `cagnon` tinyint(1) DEFAULT NULL,
  `pizarra` tinyint(1) DEFAULT NULL,
  `grupoid` int(11) DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `comentarios` text,
  `puestos` smallint DEFAULT NULL,
  `idordprofesor` int(11) DEFAULT 0,
  `horaresevini` tinyint(4) DEFAULT NULL,
  `horaresevfin` tinyint(4) DEFAULT NULL,
  `modomul` tinyint(4) NOT NULL DEFAULT '0',
  `ipmul` varchar(16) NOT NULL DEFAULT '',
  `pormul` int(11) NOT NULL DEFAULT '0',
  `velmul` smallint(6) NOT NULL DEFAULT '70',
  `router` varchar( 30 ),
  `netmask` varchar( 30 ),
  `dns` varchar (30),
  `proxy` varchar (30),
  `ntp` varchar (30),
  `modp2p` enum('seeder','peer','leecher') DEFAULT 'peer',
  `timep2p` int(11) NOT NULL DEFAULT '60',
  `validacion` tinyint(1) DEFAULT '0',
  `paginalogin` varchar(100),
  `paginavalidacion` varchar(100),
  `inremotepc` tinyint DEFAULT '0',
  `oglivedir` varchar(50) NOT NULL DEFAULT 'ogLive',
  PRIMARY KEY (`idaula`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- Estructura de tabla para la tabla `asistentes`
--

DROP TABLE IF EXISTS `asistentes`;
CREATE TABLE `asistentes` (
  `idcomando` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(250) NOT NULL DEFAULT '',
  `pagina` varchar(256) NOT NULL DEFAULT '',
  `gestor` varchar(256) NOT NULL DEFAULT '',
  `funcion` varchar(64) NOT NULL DEFAULT '',
  `urlimg` varchar(250) DEFAULT NULL,
  `aplicambito` tinyint(4) DEFAULT NULL,
  `visuparametros` varchar(250) DEFAULT NULL,
  `parametros` varchar(250) DEFAULT NULL,
  `comentarios` text,
  `activo` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY ( `idcomando` , `descripcion` ) 
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;


INSERT INTO `asistentes` (`idcomando`, `descripcion`, `pagina`, `gestor`, `funcion`, `urlimg`, `aplicambito`, `visuparametros`, `parametros`, `comentarios`, `activo`) VALUES
('8', 'Asistente Clonacion Particiones Remotas', '../asistentes/AsistenteCloneRemotePartition.php', '../comandos/gestores/gestor_Comandos.php', 'EjecutarScript', ' ', '31', 'iph;tis;dcr;dsp', 'nfn;iph;tis;dcr;scp', ' ', '1'),
('8', 'Asistente Deploy de Imagenes', '../asistentes/AsistenteDeployImage.php', '../comandos/gestores/gestor_Comandos.php', 'EjecutarScript', ' ', '31', 'iph;tis;dcr;dsp', 'nfn;iph;tis;dcr;scp', ' ', '1'),
('8', 'Asistente UpdateCache con Imagenes', '../asistentes/AsistenteUpdateCache.php', '../comandos/gestores/gestor_Comandos.php', 'EjecutarScript', ' ', '31', 'iph;tis;dcr;dsp', 'nfn;iph;tis;dcr;scp', ' ', '0'),
('8', 'Asistente Restauracion de Imagenes', '../asistentes/AsistenteRestoreImage.php', '../comandos/gestores/gestor_Comandos.php', 'EjecutarScript', ' ', '31', 'iph;tis;dcr;dsp', 'nfn;iph;tis;dcr;scp', ' ', '0'),
('8', 'Asistente Particionado', '../asistentes/AsistenteParticionado.php', '../comandos/gestores/gestor_Comandos.php', 'EjecutarScript', ' ', '31', 'iph;tis;dcr;dsp', 'nfn;iph;tis;dcr;scp', ' ', '1');


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centros`
--

DROP TABLE IF EXISTS `centros`;
CREATE TABLE `centros` (
  `idcentro` int(11) NOT NULL AUTO_INCREMENT,
  `nombrecentro` varchar(100) NOT NULL DEFAULT '',
  `identidad` int(11) DEFAULT NULL,
  `comentarios` text,
  `directorio` varchar(50) DEFAULT '',
  PRIMARY KEY (`idcentro`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- Volcar la base de datos para la tabla `centros`
--
INSERT INTO `centros` (`idcentro`,`nombrecentro`,`identidad`,`comentarios`) VALUES 
 (1,'Unidad Organizativa (Default)',1,'Esta Unidad Organizativa se crea automáticamente en el proceso de instalación de OpenGnsys');


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comandos`
--

DROP TABLE IF EXISTS `comandos`;
CREATE TABLE `comandos` (
  `idcomando` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(250) NOT NULL DEFAULT '',
  `pagina` varchar(256) NOT NULL DEFAULT '',
  `gestor` varchar(256) NOT NULL DEFAULT '',
  `funcion` varchar(64) NOT NULL DEFAULT '',
  `urlimg` varchar(250) DEFAULT NULL,
  `aplicambito` tinyint(4) DEFAULT NULL,
  `visuparametros` varchar(250) DEFAULT NULL,
  `parametros` varchar(250) DEFAULT NULL,
  `comentarios` text,
  `activo` tinyint(1) NOT NULL DEFAULT '0',
  `submenu` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`idcomando`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Volcar la base de datos para la tabla `comandos`
--

INSERT INTO `comandos` (`idcomando`, `descripcion`, `pagina`, `gestor`, `funcion`, `urlimg`, `aplicambito`, `visuparametros`, `parametros`, `comentarios`, `activo`, `submenu`) VALUES
(1, 'Arrancar', '../comandos/Arrancar.php', '../comandos/gestores/gestor_Comandos.php', 'Arrancar', '', 31, '', 'nfn;iph;mac', '', 1, ''),
(2, 'Apagar', '../comandos/Apagar.php', '../comandos/gestores/gestor_Comandos.php', 'Apagar', '', 31, '', 'nfn;iph;mac', '', 1, ''),
(3, 'Restaurar Imagen', '../comandos/RestaurarImagen.php', '../comandos/gestores/gestor_Comandos.php', 'RestaurarImagen', '', 28, 'dsk;par;idi;nci;ipr;ptc', 'nfn;iph;mac;dsk;par;idi;nci;ipr;ifs;ptc', '', 1, ''),
(4, 'Crear Imagen', '../comandos/CrearImagen.php', '../comandos/gestores/gestor_Comandos.php', 'CrearImagen', '', 16, 'dsk;par;idi;nci;ipr;cpt', 'nfn;iph;mac;dsk;par;idi;nci;ipr;cpt;', '', 1, ''),
(5, 'Reiniciar', '../comandos/Reiniciar.php', '../comandos/gestores/gestor_Comandos.php', 'Reiniciar', '', 31, '', 'nfn;iph;mac;', '', 1, ''),
(6, 'Inventario Hardware', '../comandos/InventarioHardware.php', '../comandos/gestores/gestor_Comandos.php', 'InventarioHardware', '', 16, '', 'nfn;iph;mac;', '', 1, ''),
(7, 'Inventario Software', '../comandos/InventarioSoftware.php', '../comandos/gestores/gestor_Comandos.php', 'InventarioSoftware', '', 16, 'dsk;par', 'nfn;iph;mac;dsk;par', '', 1, ''),
(8, 'Ejecutar Script', '../comandos/EjecutarScripts.php', '../comandos/gestores/gestor_Comandos.php', 'EjecutarScript', '', 31, 'iph;tis;dcr;scp', 'nfn;iph;tis;dcr;scp', '', 1, ''),
(9, 'Iniciar Sesion', '../comandos/IniciarSesion.php', '../comandos/gestores/gestor_Comandos.php', 'IniciarSesion', '', 31, 'dsk;par', 'nfn;iph;dsk;par', '', 1, ''),
(10, 'Particionar y Formatear', '../comandos/Configurar.php', '../comandos/gestores/gestor_Comandos.php', 'Configurar', '', 28, 'dsk;cfg;', 'nfn;iph;mac;dsk;cfg;par;cpt;sfi;tam;ope', '', 1, ''),
(11, 'Eliminar Imagen Cache', '../comandos/EliminarImagenCache.php', '../comandos/gestores/gestor_Comandos.php', 'EliminarImagenCache', '', 31, 'iph;tis;dcr;scp', 'nfn;iph;tis;dcr;scp', '', 1, ''),
(12, 'Crear Imagen Basica', '../comandos/CrearImagenBasica.php', '../comandos/gestores/gestor_Comandos.php', 'CrearImagenBasica', '', 16, 'dsk;par;cpt;idi;nci;ipr;iph;bpi;cpc;bpc;rti;nba', 'nfn;dsk;par;cpt;idi;nci;ipr;iph;bpi;cpc;bpc;rti;nba', '', 1, 'Sincronizacion'),
(13, 'Restaurar Imagen Basica', '../comandos/RestaurarImagenBasica.php', '../comandos/gestores/gestor_Comandos.php', 'RestaurarImagenBasica', '', 28, 'dsk;par;idi;nci;ipr;iph;bpi;cpc;bpc;rti;nba;met', 'nfn;dsk;par;idi;nci;ipr;iph;bpi;cpc;bpc;rti;nba;met', '', 1, 'Sincronizacion'),
(14, 'Crear Software Incremental', '../comandos/CrearSoftIncremental.php', '../comandos/gestores/gestor_Comandos.php', 'CrearSoftIncremental', '', 16, 'dsk;par;idi;nci;ipr;idf;ncf;bpi;cpc;bpc;iph;rti;nba', 'nfn;dsk;par;idi;nci;ipr;idf;ncf;bpi;cpc;bpc;iph;rti;nba', '', 1, 'Sincronizacion'),
(15, 'Restaurar Software Incremental', '../comandos/RestaurarSoftIncremental.php', '../comandos/gestores/gestor_Comandos.php', 'RestaurarSoftIncremental', '', 28, 'dsk;par;idi;nci;ipr;idf;ncf;bpi;cpc;bpc;iph;rti;met;nba', 'nfn;dsk;par;idi;nci;ipr;idf;ncf;bpi;cpc;bpc;iph;rti;met;nba', '', 1, 'Sincronizacion'),
(16, 'Enviar mensaje', '../comandos/EnviarMensaje.php', '../comandos/gestores/gestor_Comandos.php', 'EnviarMensaje', '', 31, 'tit;msj', 'nfn;iph;tit;msj', '', 1, '');



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entidades`
--

DROP TABLE IF EXISTS `entidades`;
CREATE TABLE `entidades` (
  `identidad` int(11) NOT NULL AUTO_INCREMENT,
  `nombreentidad` varchar(200) NOT NULL DEFAULT '',
  `comentarios` text,
  `iduniversidad` int(11) DEFAULT NULL,
  `grupoid` int(11) DEFAULT NULL,
  `ogunit` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`identidad`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Volcar la base de datos para la tabla `entidades`
--

INSERT INTO `entidades` (`identidad`, `nombreentidad`, `comentarios`, `iduniversidad`, `grupoid`) VALUES
(1, 'Entidad (Default)', 'Esta Entidad se crea automáticamente en el proceso de instalación de OpenGnsys', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entornos`
--

DROP TABLE IF EXISTS `entornos`;
CREATE TABLE `entornos` (
  `identorno` int(11) NOT NULL AUTO_INCREMENT,
  `ipserveradm` varchar(50) NOT NULL DEFAULT '',
  `portserveradm` int(20) NOT NULL DEFAULT 2008,
  `protoclonacion` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`identorno`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Volcar la base de datos para la tabla `entornos`
--

INSERT INTO `entornos` (`identorno`, `ipserveradm`, `portserveradm`, `protoclonacion`) VALUES
(1, 'SERVERIP', 2008, 'UNICAST');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estatus`
--

DROP TABLE IF EXISTS `estatus`;
CREATE TABLE `estatus` (
  `idestatus` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`idestatus`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Volcar la base de datos para la tabla `estatus`
--

INSERT INTO `estatus` (`idestatus`, `descripcion`) VALUES
(1, 'P.D.I. ( Profesor)'),
(2, 'P.A.S.'),
(3, 'Doctor'),
(4, 'Alumno'),
(5, 'Otros');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos`
--

DROP TABLE IF EXISTS `grupos`;
CREATE TABLE `grupos` (
  `idgrupo` int(11) NOT NULL AUTO_INCREMENT,
  `nombregrupo` varchar(250) NOT NULL DEFAULT '',
  `grupoid` int(11) NOT NULL DEFAULT '0',
  `tipo` tinyint(4) NOT NULL DEFAULT '0',
  `idcentro` int(11) NOT NULL DEFAULT '0',
  `iduniversidad` int(11) DEFAULT NULL,
  `comentarios` text,
  PRIMARY KEY (`idgrupo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gruposordenadores`
--

DROP TABLE IF EXISTS `gruposordenadores`;
CREATE TABLE `gruposordenadores` (
  `idgrupo` int(11) NOT NULL AUTO_INCREMENT,
  `nombregrupoordenador` varchar(250) NOT NULL DEFAULT '',
  `idaula` int(11) NOT NULL DEFAULT '0',
  `grupoid` int(11) DEFAULT NULL,
  `comentarios` text,
  PRIMARY KEY (`idgrupo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hardwares`
--

DROP TABLE IF EXISTS `hardwares`;
CREATE TABLE `hardwares` (
  `idhardware` int(11) NOT NULL AUTO_INCREMENT,
  `idtipohardware` int(11) NOT NULL DEFAULT '0',
  `descripcion` varchar(250) NOT NULL DEFAULT '',
  `idcentro` int(11) NOT NULL DEFAULT '0',
  `grupoid` int(11) DEFAULT NULL,
  PRIMARY KEY (`idhardware`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `iconos`
--

DROP TABLE IF EXISTS `iconos`;
CREATE TABLE `iconos` (
  `idicono` int(11) NOT NULL AUTO_INCREMENT,
  `urlicono` varchar(200) DEFAULT NULL,
  `idtipoicono` int(11) DEFAULT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`idicono`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Volcar la base de datos para la tabla `iconos`
--

INSERT INTO `iconos` (`idicono`, `urlicono`, `idtipoicono`, `descripcion`) VALUES
(1, 'vga.gif', 1, 'Tarjeta gráfica'),
(2, 'nic.gif', 1, 'Tarjeta de Red'),
(3, 'placabase.gif', 1, 'Placas base'),
(4, 'tsonido.gif', 1, 'Tarjeta de sonido'),
(5, 'camweb.gif', 1, 'Cámara web'),
(6, 'logoXP.png', 2, 'Logo Windows XP'),
(7, 'logolinux.png', 2, 'Logo General de Linux'),
(8, 'particionar.png', 2, 'Particionar'),
(9, 'ordenadoroff.png', 2, 'Ordenador apagado'),
(10, 'ordenadoron.png', 2, 'Ordenador encendido'),
(11, 'usb.gif', 1, 'Mi icono usb'),
(12, 'ide.gif', 1, 'Controladores IDE'),
(13, 'dvdcd.gif', 1, 'Lectoras y grabadoras de DVD'),
(14, 'audio.gif', 1, 'Dispositivos de audio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `idiomas`
--

DROP TABLE IF EXISTS `idiomas`;
CREATE TABLE `idiomas` (
  `ididioma` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(100) DEFAULT NULL,
  `nemonico` char(3) DEFAULT NULL,
  PRIMARY KEY (`ididioma`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Volcar la base de datos para la tabla `idiomas`
--

INSERT INTO `idiomas` (`ididioma`, `descripcion`, `nemonico`) VALUES
(1, 'Español', 'esp'),
(2, 'English', 'eng'),
(3, 'Català', 'cat');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes`
--

DROP TABLE IF EXISTS `imagenes`;
CREATE TABLE `imagenes` (
  `idimagen` int(11) NOT NULL AUTO_INCREMENT,
  `nombreca` varchar(50) NOT NULL DEFAULT '',
  `revision` smallint UNSIGNED NOT NULL DEFAULT '0',
  `descripcion` varchar(250) NOT NULL DEFAULT '',
  `idperfilsoft` int(11) DEFAULT NULL,
  `idcentro` int(11) DEFAULT NULL,
  `comentarios` text,
  `grupoid` int(11) DEFAULT NULL,
  `idrepositorio` int(11) NOT NULL DEFAULT '0',
  `idordenador` int(11) NOT NULL DEFAULT '0',
  `numdisk` smallint NOT NULL DEFAULT '0',
  `numpar` smallint NOT NULL DEFAULT '0',
  `codpar` int(8) NOT NULL DEFAULT '0',
  `tipo` tinyint NULL,
  `imagenid` int NOT NULL DEFAULT '0',
  `ruta` varchar(250) NULL,
  `fechacreacion` datetime DEFAULT NULL,
  `inremotepc` tinyint DEFAULT '0',
  PRIMARY KEY (`idimagen`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menus`
--

DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
  `idmenu` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(250) NOT NULL DEFAULT '',
  `idcentro` int(11) NOT NULL DEFAULT '0',
  `idurlimg` int(11) NOT NULL DEFAULT '0',
  `titulo` varchar(250) DEFAULT NULL,
  `modalidad` tinyint(4) DEFAULT NULL,
  `smodalidad` tinyint(4) DEFAULT NULL,
  `comentarios` text,
  `grupoid` int(11) NOT NULL DEFAULT '0',
  `htmlmenupub` varchar(250) DEFAULT NULL,
  `htmlmenupri` varchar(250) DEFAULT NULL,
  `resolucion` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idmenu`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nombresos`
--

DROP TABLE IF EXISTS `nombresos`;
CREATE TABLE `nombresos` (
  `idnombreso` smallint(11) NOT NULL AUTO_INCREMENT,
  `nombreso` varchar(250) NOT NULL DEFAULT '',
  `idtiposo` int(11) DEFAULT '0',
  PRIMARY KEY (`idnombreso`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ogagent_queue`
--

DROP TABLE IF EXISTS `ogagent_queue`;
CREATE TABLE `ogagent_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientid` int(11) NOT NULL,
  `exectime` datetime DEFAULT NULL,
  `operation` varchar(25),
--  `parameters` varchar(100),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenadores`
--

DROP TABLE IF EXISTS `ordenadores`;
CREATE TABLE `ordenadores` (
  `idordenador` int(11) NOT NULL AUTO_INCREMENT,
  `nombreordenador` varchar(100) DEFAULT NULL,
  `numserie` varchar(25) DEFAULT NULL,
  `ip` varchar(16) NOT NULL DEFAULT '',
  `mac` varchar(12) DEFAULT NULL,
  `idaula` int(11) DEFAULT NULL,
  `idperfilhard` int(11) DEFAULT NULL,
  `idrepositorio` int(11) DEFAULT NULL,
  `grupoid` int(11) DEFAULT NULL,
  `idmenu` int(11) DEFAULT NULL,
  `cache` int(11) DEFAULT NULL,
  `router` varchar(16) NOT NULL DEFAULT '',
  `mascara` varchar(16) NOT NULL DEFAULT '',
  `idproautoexec` int(11) NOT NULL DEFAULT '0',
  `arranque` varchar(30) NOT NULL DEFAULT '00unknown',
  `netiface` enum('eth0','eth1','eth2') DEFAULT 'eth0',
  `netdriver` varchar(30) NOT NULL DEFAULT 'generic',
  `fotoord` varchar(250) NOT NULL DEFAULT 'fotoordenador.gif',
  `validacion` tinyint(1) DEFAULT '0',
  `paginalogin` varchar(100),
  `paginavalidacion` varchar(100),
  `agentkey` varchar(32),
  `oglivedir` varchar(50) NOT NULL DEFAULT 'ogLive',
  PRIMARY KEY (`idordenador`),
  KEY `idaulaip` (`idaula` ASC, `ip` ASC)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenadores_particiones`
--

DROP TABLE IF EXISTS `ordenadores_particiones`;
CREATE TABLE `ordenadores_particiones` (
  `idordenador` int(11) NOT NULL DEFAULT '0',
  `numdisk` smallint NOT NULL DEFAULT '0',
  `numpar` smallint NOT NULL DEFAULT '0',
  `codpar` int(8) NOT NULL DEFAULT '0',
  `tamano` int(11) NOT NULL DEFAULT '0',
  `uso` tinyint NOT NULL DEFAULT '0',
  `idsistemafichero` smallint(11) NOT NULL DEFAULT '0',
  `idnombreso` smallint(11) NOT NULL DEFAULT '0',
  `idimagen` int(11) NOT NULL DEFAULT '0',
  `revision` smallint UNSIGNED NOT NULL DEFAULT '0',
  `idperfilsoft` int(11) NOT NULL DEFAULT '0',
  `fechadespliegue` datetime NULL,
  `cache` text,
  UNIQUE KEY `idordenadornumdisknumpar` (`idordenador`,`numdisk`,`numpar`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parametros`
--

DROP TABLE IF EXISTS `parametros`;
CREATE TABLE `parametros` (
  `idparametro` int(11) NOT NULL AUTO_INCREMENT,
  `nemonico` char(3) NOT NULL DEFAULT '',
  `descripcion` text,
  `nomidentificador` varchar(64) NOT NULL DEFAULT '',
  `nomtabla` varchar(64) NOT NULL DEFAULT '',
  `nomliteral` varchar(64) NOT NULL DEFAULT '',
  `tipopa` tinyint(1) DEFAULT '0',
  `visual` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idparametro`),
  KEY (`nemonico`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=33 ;

--
-- Volcar la base de datos para la tabla `parametros`
--

INSERT INTO `parametros` (`idparametro`, `nemonico`, `descripcion`, `nomidentificador`, `nomtabla`, `nomliteral`, `tipopa`, `visual`) VALUES
(1, 'nfn', 'Nombre de la función o script a ejecutar en el cliente y que implementa el comando. Es posible que también els ervidor debo ejecutar la misma función como ocurre en el comando "Arrancar" y que implementa el comportamiento del comando en el servidor', '', '', '', 0, 0),
(2, 'iph', 'Dirección ip de los ordenadores a los que se envía el comando', '', '', '', 0, 0),
(3, 'ido', 'Identificadores de los ordenadores a los que se envía el comando', '', '', '', 0, 0),
(4, 'mac', 'Direcciones macs de los clientes a los que se le envía el comando', '', '', '', 0, 0),
(5, 'idc', 'Unidad organizativa', 'idcentro', 'centros', '', 1, 0),
(6, 'ida', 'Aula', 'idaula', 'aulas', 'nombreaula', 1, 0),
(18, 'cfg', 'Configuración', '', '', '', 2, 1),
(7, 'dsk', 'Disco', '', '', '', 0, 1),
(8, 'par', 'Partición', '', '', '', 0, 1),
(9, 'ifh', 'Perfil Hardware', 'idperfilhard', 'perfileshard', 'descripcion', 1, 1),
(10, 'ifs', 'Perfil Software', 'idperfilsoft', 'perfilessoft', 'descripcion', 1, 1),
(11, 'idi', 'Imagen', 'idimagen', 'imagenes', 'descripcion', 1, 1),
(12, 'nci', 'Nombre canónico', '', '', '', 0, 1),
(13, 'scp', 'Código a ejecutar en formato script', '', '', '', 0, 0),
(14, 'npc', 'Nombre del cliente', '', '', '', NULL, 0),
(15, 'che', 'Tamaño de la cache del cliente', '', '', '', NULL, 0),
(16, 'exe', 'Identificador del procedimiento que será el que ejecute el cliente al arrancar (Autoexec)', '', '', '', 0, 0),
(17, 'res', 'Respuesta del comando: Puede tomar los valores 1 o 2 en el caso de que la respuesta sea correcta o que haya un error al ejecutarse.', '', '', '', 0, 0),
(19, 'ipr', 'Repositorio', 'ip', 'repositorios', 'nombrerepositorio', 1, 1),
(20, 'cpt', 'Tipo partición', 'codpar', 'tipospar', 'tipopar', 1, 1),
(21, 'sfi', 'Sistema de fichero', 'nemonico', 'sistemasficheros', 'nemonico', 1, 0),
(22, 'tam', 'Tamaño', '', '', '', 0, 1),
(23, 'ope', 'Operación', ';', '', 'Sin operación;Formatear;Ocultar;Mostrar', 3, 1),
(24, 'nfl', 'Nombre del fichero que se envía o se recibe', '', '', '', 0, 0),
(25, 'hrd', 'Nombre del archivo de inventario hardware enviado por la red', '', '', '', 0, 0),
(26, 'sft', 'Nombre del archivo de inventario software enviado por la red', '', '', '', 0, 0),
(27, 'tpc', 'Tipo de cliente', '', '', '', 0, 0),
(28, 'scp', 'Código script', '', '', '', 4, 1),
(30, 'ptc', 'Protocolo de clonación', ';', '', ';Unicast;Multicast;Torrent', 0, 1),
(31, 'idf', 'Imagen Incremental', 'idimagen', 'imagenes', 'descripcion', 1, 1), 
(32, 'ncf', 'Nombre canónico de la Imagen Incremental', '', '', '', 0, 1), 
(33, 'bpi', 'Borrar imagen o partición previamente', '', '', '', 5, 1), 
(34, 'cpc', 'Copiar también en cache', '', '', '', 5, 1), 
(35, 'bpc', 'Borrado previo de la imagen en cache', '', '', '', 5, 1), 
(36, 'rti', 'Ruta de origen', '', '', '', 0, 1), 
(37, 'met', 'Método clonación', ';', '', 'Desde caché; Desde repositorio', 3, 1),
(38, 'nba', 'No borrar archivos en destino', '', '', '', 0, 1),
(39, 'tit', 'Título', '', '', '', 0, 1),
(40, 'msj', 'Contenido', '', '', '', 0, 1); 

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfileshard`

--

DROP TABLE IF EXISTS `perfileshard`;
CREATE TABLE `perfileshard` (
  `idperfilhard` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(250) NOT NULL DEFAULT '',
  `comentarios` text,
  `grupoid` int(11) DEFAULT NULL,
  `idcentro` int(11) NOT NULL DEFAULT '0',
  `winboot` enum( 'reboot', 'kexec' ) NOT NULL DEFAULT 'reboot',
  PRIMARY KEY (`idperfilhard`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfileshard_hardwares`
--

DROP TABLE IF EXISTS `perfileshard_hardwares`;
CREATE TABLE `perfileshard_hardwares` (
  `idperfilhard` int(11) NOT NULL DEFAULT '0',
  `idhardware` int(11) NOT NULL DEFAULT '0',
  KEY `idperfilhard` (`idperfilhard`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfilessoft`
--

DROP TABLE IF EXISTS `perfilessoft`;
CREATE TABLE `perfilessoft` (
  `idperfilsoft` int(11) NOT NULL AUTO_INCREMENT,
  `idnombreso` smallint(5) unsigned DEFAULT NULL,
  `descripcion` varchar(250) NOT NULL DEFAULT '',
  `comentarios` text,
  `grupoid` int(11) DEFAULT NULL,
  `idcentro` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idperfilsoft`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfilessoft_softwares`
--

DROP TABLE IF EXISTS `perfilessoft_softwares`;
CREATE TABLE `perfilessoft_softwares` (
  `idperfilsoft` int(11) NOT NULL DEFAULT '0',
  `idsoftware` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plataformas`
--

DROP TABLE IF EXISTS `plataformas`;
CREATE TABLE `plataformas` (
  `idplataforma` int(11) NOT NULL AUTO_INCREMENT,
  `plataforma` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`idplataforma`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

--
-- Volcar la base de datos para la tabla `plataformas`
--

INSERT INTO `plataformas` (`idplataforma`, `plataforma`) VALUES
(1, 'MsDos'),
(2, 'Windows'),
(3, 'Linux'),
(4, 'Mac'),
(5, 'OS');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `procedimientos`
--

DROP TABLE IF EXISTS `procedimientos`;
CREATE TABLE `procedimientos` (
  `idprocedimiento` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(250) NOT NULL DEFAULT '',
  `urlimg` varchar(250) DEFAULT NULL,
  `idcentro` int(11) NOT NULL DEFAULT '0',
  `comentarios` text,
  `grupoid` int(11) DEFAULT '0',
  PRIMARY KEY (`idprocedimiento`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `procedimientos_acciones`
--

DROP TABLE IF EXISTS `procedimientos_acciones`;
CREATE TABLE `procedimientos_acciones` (
  `idprocedimientoaccion` int(11) NOT NULL AUTO_INCREMENT,
  `idprocedimiento` int(11) NOT NULL DEFAULT '0',
  `orden` smallint(4) DEFAULT NULL,
  `idcomando` int(11) NOT NULL DEFAULT '0',
  `parametros` text,
  `procedimientoid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idprocedimientoaccion`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programaciones`
--

DROP TABLE IF EXISTS `programaciones`;
CREATE TABLE `programaciones` (
  `idprogramacion` int(11) NOT NULL AUTO_INCREMENT,
  `tipoaccion` int(11) DEFAULT NULL,
  `identificador` int(11) DEFAULT NULL,
  `nombrebloque` varchar(255) DEFAULT NULL,
  `annos` smallint DEFAULT NULL,
  `meses` smallint DEFAULT NULL,
  `diario` int(11) DEFAULT NULL,
  `dias` tinyint(4) DEFAULT NULL,
  `semanas` tinyint(4) DEFAULT NULL,
  `horas` smallint(4) DEFAULT NULL,
  `ampm` tinyint(1) DEFAULT NULL,
  `minutos` tinyint(4) DEFAULT NULL,
  `segundos` tinyint(4) DEFAULT NULL,
  `horasini` smallint(4) DEFAULT NULL,
  `ampmini` tinyint(1) DEFAULT NULL,
  `minutosini` tinyint(4) DEFAULT NULL,
  `horasfin` smallint(4) DEFAULT NULL,
  `ampmfin` tinyint(1) DEFAULT NULL,
  `minutosfin` tinyint(4) DEFAULT NULL,
  `suspendida` tinyint(1) DEFAULT NULL,
  `sesion` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idprogramacion`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `remotepc`
--

DROP TABLE IF EXISTS `remotepc`;
CREATE TABLE `remotepc` (
  `id` int(11) NOT NULL,
  `reserved` datetime DEFAULT NULL,
  `urllogin` varchar(100),
  `urllogout` varchar(100),
  `language` varchar(5),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repositorios`
--

DROP TABLE IF EXISTS `repositorios`;
CREATE TABLE `repositorios` (
  `idrepositorio` int(11) NOT NULL AUTO_INCREMENT,
  `nombrerepositorio` varchar(250) NOT NULL DEFAULT '',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `idcentro` int(11) DEFAULT NULL,
  `grupoid` int(11) DEFAULT NULL,
  `comentarios` text,
  `apikey` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`idrepositorio`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `repositorios` (`idrepositorio`,`nombrerepositorio`,`ip`,`idcentro`,`grupoid`,`comentarios`,`apikey`) VALUES
 (1,'Repositorio (Default)','SERVERIP',1,0,'','REPOKEY');


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sistemasficheros`
--

DROP TABLE IF EXISTS `sistemasficheros`;
CREATE TABLE `sistemasficheros` (
  `idsistemafichero` smallint(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) NOT NULL DEFAULT '',
  `nemonico` varchar(16) DEFAULT NULL,
  `codpar` int(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idsistemafichero`),
  UNIQUE KEY (`descripcion`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
INSERT INTO `sistemasficheros` (`idsistemafichero`, `descripcion`, `nemonico`, `codpar`) VALUES
 (1, 'EMPTY', 'EMPTY', 0),
 (2, 'CACHE', 'CACHE', 0),
 (3, 'BTRFS', 'BTRFS', 0),
 (4, 'EXT2', 'EXT2', 0),
 (5, 'EXT3', 'EXT3', 0),
 (6, 'EXT4', 'EXT4', 0),
 (7, 'FAT12', 'FAT12', 0),
 (8, 'FAT16', 'FAT16', 0),
 (9, 'FAT32', 'FAT32', 0),
 (10, 'HFS', 'HFS', 0),
 (11, 'HFSPLUS', 'HFSPLUS', 0),
 (12, 'JFS', 'JFS', 0),
 (13, 'NTFS', 'NTFS', 0),
 (14, 'REISERFS', 'REISERFS', 0),
 (15, 'REISER4', 'REISER4', 0),
 (16, 'UFS', 'UFS', 0),
 (17, 'XFS', 'XFS', 0),
 (18, 'EXFAT', 'EXFAT', 0),
 (19, 'LINUX-SWAP', 'LINUX-SWAP', 0),
 (20, 'F2FS', 'F2FS', 0),
 (21, 'NILFS2', 'NILFS2', 0);


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `softwares`
--

DROP TABLE IF EXISTS `softwares`;
CREATE TABLE `softwares` (
  `idsoftware` int(11) NOT NULL AUTO_INCREMENT,
  `idtiposoftware` int(11) NOT NULL DEFAULT '0',
  `descripcion` varchar(250) NOT NULL DEFAULT '',
  `idcentro` int(11) NOT NULL DEFAULT '0',
  `urlimg` varchar(250) DEFAULT NULL,
  `idtiposo` int(11) DEFAULT NULL,
  `grupoid` int(11) DEFAULT NULL,
  PRIMARY KEY (`idsoftware`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

DROP TABLE IF EXISTS `tareas`;
CREATE TABLE `tareas` (
  `idtarea` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(250) NOT NULL DEFAULT '',
  `urlimg` varchar(250) DEFAULT NULL,
  `idcentro` int(11) NOT NULL DEFAULT '0',
  `ambito` smallint(6) NOT NULL DEFAULT '0',
  `idambito` int(11) NOT NULL DEFAULT '0',
  `restrambito` text,
  `comentarios` text,
  `grupoid` int(11) DEFAULT '0',
  PRIMARY KEY (`idtarea`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas_acciones`
--

DROP TABLE IF EXISTS `tareas_acciones`;
CREATE TABLE `tareas_acciones` (
  `idtareaaccion` int(11) NOT NULL AUTO_INCREMENT,
  `idtarea` int(11) NOT NULL DEFAULT '0',
  `orden` smallint(6) NOT NULL DEFAULT '0',
  `idprocedimiento` int(11) NOT NULL DEFAULT '0',
  `tareaid` int(11) DEFAULT '0',
  PRIMARY KEY (`idtareaaccion`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipohardwares`
--

DROP TABLE IF EXISTS `tipohardwares`;
CREATE TABLE `tipohardwares` (
  `idtipohardware` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(250) NOT NULL DEFAULT '',
  `urlimg` varchar(250) NOT NULL DEFAULT '',
  `nemonico` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`idtipohardware`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- Volcar la base de datos para la tabla `tipohardwares`
--

INSERT INTO `tipohardwares` (`idtipohardware`, `descripcion`, `urlimg`, `nemonico`) VALUES
(1, 'Placas', '../images/iconos/placabase.gif', 'boa'),
(2, 'Dispositivos Multimedia', '../images/iconos/tsonido.gif', 'mul'),
(3, 'Tarjetas de Red', '../images/iconos/nic.gif', 'net'),
(4, 'Microprocesadores', '../images/iconos/micro.gif', 'cpu'),
(5, 'Memorias', '../images/iconos/confihard.gif', 'mem'),
(7, 'Tarjetas gráficas', '../images/iconos/vga.gif', 'vga'),
(8, 'Discos', '../images/iconos/discoduro.gif', 'dis'),
(9, 'Dispositivos de sonido', '../images/iconos/audio.gif', 'aud'),
(10, 'Marca y modelo del equipo', '../images/iconos/confihard.gif', 'mod'),
(11, 'Modelo y version de la bios', '../images/iconos/confihard.gif', 'bio'),
(12, 'Modelo de grabadora o  grabadora de CD/DVD', '../images/iconos/dvdcd.gif', 'cdr'),
(13, 'Controladores IDE', '../images/iconos/ide.gif', 'ide'),
(14, 'Controladores FireWire', '../images/iconos/confihard.gif', 'fir'),
(15, 'Controladores USB', '../images/iconos/usb.gif', 'usb'),
(16, 'Bus del Sistema', '../images/iconos/confihard.gif', 'bus'),
(17, 'Chasis del Sistema', '', 'cha'),
(18, 'Controladores de almacenamiento', '../images/iconos/almacenamiento.png', 'sto'),
(19, 'Tipo de proceso de arranque', '../images/iconos/arranque.png', 'boo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tiposoftwares`
--

DROP TABLE IF EXISTS `tiposoftwares`;
CREATE TABLE `tiposoftwares` (
  `idtiposoftware` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(250) NOT NULL DEFAULT '',
  `urlimg` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`idtiposoftware`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Volcar la base de datos para la tabla `tiposoftwares`
--

INSERT INTO `tiposoftwares` (`idtiposoftware`, `descripcion`, `urlimg`) VALUES
(1, 'Sistemas Operativos', '../images/iconos/so.gif'),
(2, 'Aplicaciones', '../images/iconos/aplicaciones.gif'),
(3, 'Archivos', '../images/iconos/archivos.gif');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tiposos`
--

DROP TABLE IF EXISTS `tiposos`;
CREATE TABLE `tiposos` (
  `idtiposo` int(11) NOT NULL AUTO_INCREMENT,
  `tiposo` varchar(250) NOT NULL DEFAULT '',
  `idplataforma` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idtiposo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

--
-- Volcar la base de datos para la tabla `tiposos`
--

INSERT INTO `tiposos` (`idtiposo`, `tiposo`, `idplataforma`) VALUES
(1, 'MsDos 6.0', 1),
(2, 'Windows 98', 2),
(3, 'Linux Ubuntu', 3),
(4, 'Mac', 0),
(5, 'OS', 0),
(17, 'Windows XP', 2),
(18, 'Windows Vista', 2),
(19, 'Linux Red Hat', 3),
(20, 'Windows 7', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipospar`
--

DROP TABLE IF EXISTS `tipospar`;
CREATE TABLE `tipospar` (
  `codpar` int(8) NOT NULL,
  `tipopar` varchar(250) NOT NULL DEFAULT '',
  `clonable` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `codpar` (`codpar`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Volcar la base de datos para la tabla `tipospar`
--

INSERT INTO `tipospar` (`codpar`, `tipopar`, `clonable`) VALUES
(0, 'EMPTY', 0),
(1, 'FAT12', 1),
(5, 'EXTENDED', 0),
(6, 'FAT16', 1),
(7, 'NTFS', 1),
(CONV('0B',16,10), 'FAT32', 1),
(CONV('11',16,10), 'HFAT12', 1),
(CONV('16',16,10), 'HFAT16', 1),
(CONV('17',16,10), 'HNTFS', 1),
(CONV('1B',16,10), 'HFAT32', 1),
(CONV('27',16,10), 'HNTFS-WINRE', 1),
(CONV('82',16,10), 'LINUX-SWAP', 0),
(CONV('83',16,10), 'LINUX', 1),
(CONV('8E',16,10), 'LINUX-LVM', 1),
(CONV('A5',16,10), 'FREEBSD', 1),
(CONV('A6',16,10), 'OPENBSD', 1),
(CONV('A9',16,10), 'NETBSD', 1),
(CONV('AF',16,10), 'HFS', 1),
(CONV('BE',16,10), 'SOLARIS-BOOT', 1),
(CONV('BF',16,10), 'SOLARIS', 1),
(CONV('CA',16,10), 'CACHE', 0),
(CONV('DA',16,10), 'DATA', 1),
(CONV('EE',16,10), 'GPT', 0),
(CONV('EF',16,10), 'EFI', 1),
(CONV('FB',16,10), 'VMFS', 1),
(CONV('FD',16,10), 'LINUX-RAID', 1),
(CONV('0700',16,10), 'WINDOWS', 1),
(CONV('0C01',16,10), 'WIN-RESERV', 1),
(CONV('2700',16,10), 'WIN-RECOV', 1),
(CONV('7F00',16,10), 'CHROMEOS-KRN', 1),
(CONV('7F01',16,10), 'CHROMEOS', 1),
(CONV('7F02',16,10), 'CHROMEOS-RESERV', 1),
(CONV('8200',16,10), 'LINUX-SWAP', 0),
(CONV('8300',16,10), 'LINUX', 1),
(CONV('8301',16,10), 'LINUX-RESERV', 1),
(CONV('8302',16,10), 'LINUX', 1),
(CONV('8E00',16,10), 'LINUX-LVM', 1),
(CONV('A500',16,10), 'FREEBSD-DISK', 0),
(CONV('A501',16,10), 'FREEBSD-BOOT', 1),
(CONV('A502',16,10), 'FREEBSD-SWAP', 0),
(CONV('A503',16,10), 'FREEBSD', 1),
(CONV('A504',16,10), 'FREEBSD', 1),
(CONV('A901',16,10), 'NETBSD-SWAP', 0),
(CONV('A902',16,10), 'NETBSD', 1),
(CONV('A903',16,10), 'NETBSD', 1),
(CONV('A904',16,10), 'NETBSD', 1),
(CONV('A905',16,10), 'NETBSD', 1),
(CONV('A906',16,10), 'NETBSD-RAID', 1),
(CONV('AB00',16,10), 'HFS-BOOT', 1),
(CONV('AF00',16,10), 'HFS', 1),
(CONV('AF01',16,10), 'HFS-RAID', 1),
(CONV('AF02',16,10), 'HFS-RAID', 1),
(CONV('BE00',16,10), 'SOLARIS-BOOT', 1),
(CONV('BF00',16,10), 'SOLARIS', 1),
(CONV('BF01',16,10), 'SOLARIS', 1),
(CONV('BF02',16,10), 'SOLARIS-SWAP', 0),
(CONV('BF03',16,10), 'SOLARIS-DISK', 1),
(CONV('BF04',16,10), 'SOLARIS', 1),
(CONV('BF05',16,10), 'SOLARIS', 1),
(CONV('CA00',16,10), 'CACHE', 0),
(CONV('EF00',16,10), 'EFI', 1),
(CONV('EF01',16,10), 'MBR', 0),
(CONV('EF02',16,10), 'BIOS-BOOT', 0),
(CONV('FB00',16,10), 'VMFS', 1),
(CONV('FB01',16,10), 'VMFS-RESERV', 1),
(CONV('FB02',16,10), 'VMFS-KRN', 1),
(CONV('FD00',16,10), 'LINUX-RAID', 1),
(CONV('FFFF',16,10), 'UNKNOWN', 1),
(CONV('10000',16,10), 'LVM-LV', 1),
(CONV('10010',16,10), 'ZFS-VOL', 1);


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `universidades`
--

DROP TABLE IF EXISTS `universidades`;
CREATE TABLE `universidades` (
  `iduniversidad` int(11) NOT NULL AUTO_INCREMENT,
  `nombreuniversidad` varchar(200) NOT NULL DEFAULT '',
  `comentarios` text,
  PRIMARY KEY (`iduniversidad`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Volcar la base de datos para la tabla `universidades`
--

INSERT INTO `universidades` (`iduniversidad`, `nombreuniversidad`, `comentarios`) VALUES
(1, 'Universidad (Default)', 'Esta Universidad se crea automáticamentese en el proceso de instalación de OpenGnsys');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `urlimagesitems`
--

DROP TABLE IF EXISTS `urlimagesitems`;
CREATE TABLE `urlimagesitems` (
  `idurlimagesitems` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`idurlimagesitems`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `urlimagesitems`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `idusuario` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL DEFAULT '',
  `pasguor` varchar(56) NOT NULL DEFAULT '',
  `nombre` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `ididioma` int(11) DEFAULT NULL,
  `idtipousuario` tinyint(4) DEFAULT NULL,
  `apikey` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`idusuario`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Volcar la base de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idusuario`, `usuario`, `pasguor`, `nombre`, `email`, `ididioma`, `idtipousuario`, `apikey`) VALUES
(1, 'DBUSER', SHA2('DBPASSWORD', 224), 'Usuario de la base de datos MySql', '', 1, 1, 'APIKEY');


