-- phpMyAdmin SQL Dump
-- version 2.10.0.2
-- http://www.phpmyadmin.net
-- 
-- Servidor: localhost
-- Tiempo de generación: 28-10-2009 a las 12:33:31
-- Versión del servidor: 5.0.27
-- Versión de PHP: 5.1.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de datos: `bdhidra`
-- 

-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `acciones`
-- 

DROP TABLE IF EXISTS `acciones`;
CREATE TABLE IF NOT EXISTS `acciones` (
  `idaccion` int(11) NOT NULL auto_increment,
  `tipoaccion` int(11) NOT NULL default '0',
  `idtipoaccion` int(11) default NULL,
  `cateaccion` tinyint(4) default NULL,
  `ambito` tinyint(4) default NULL,
  `idambito` int(11) default NULL,
  `ambitskwrk` text,
  `fechahorareg` datetime NOT NULL default '0000-00-00 00:00:00',
  `fechahorafin` datetime NOT NULL default '0000-00-00 00:00:00',
  `parametros` text,
  `estado` char(1) default NULL,
  `resultado` char(1) default NULL,
  `idcentro` int(11) default NULL,
  `accionid` int(11) default NULL,
  `idnotificador` int(11) default NULL,
  PRIMARY KEY  (`idaccion`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `acciones`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `acciones_menus`
-- 

DROP TABLE IF EXISTS `acciones_menus`;
CREATE TABLE IF NOT EXISTS `acciones_menus` (
  `idaccionmenu` int(11) NOT NULL auto_increment,
  `tipoaccion` tinyint(4) NOT NULL default '0',
  `idtipoaccion` int(11) NOT NULL default '0',
  `idmenu` int(11) NOT NULL default '0',
  `tipoitem` tinyint(4) default NULL,
  `idurlimg` int(11) default NULL,
  `descripitem` varchar(250) default NULL,
  `orden` tinyint(4) default NULL,
  PRIMARY KEY  (`idaccionmenu`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `acciones_menus`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `aulas`
-- 

DROP TABLE IF EXISTS `aulas`;
CREATE TABLE IF NOT EXISTS `aulas` (
  `idaula` int(11) NOT NULL auto_increment,
  `nombreaula` varchar(100) NOT NULL default '',
  `idcentro` int(11) NOT NULL default '0',
  `urlfoto` varchar(250) default NULL,
  `cuadro_y` char(3) default NULL,
  `cuadro_x` char(3) default NULL,
  `cagnon` tinyint(1) default NULL,
  `pizarra` tinyint(1) default NULL,
  `grupoid` int(11) default NULL,
  `ubicacion` varchar(255) default NULL,
  `comentarios` text,
  `puestos` tinyint(4) default NULL,
  `horaresevini` tinyint(4) default NULL,
  `horaresevfin` tinyint(4) default NULL,
  PRIMARY KEY  (`idaula`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `aulas`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `campus`
-- 

DROP TABLE IF EXISTS `campus`;
CREATE TABLE IF NOT EXISTS `campus` (
  `idcampus` int(11) NOT NULL auto_increment,
  `nombrecampus` varchar(100) NOT NULL default '',
  `iduniversidad` int(11) default NULL,
  `urlmapa` varchar(255) default NULL,
  `cuadro_y` tinyint(3) default NULL,
  `cuadro_x` tinyint(3) default NULL,
  PRIMARY KEY  (`idcampus`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `campus`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `centros`
-- 

DROP TABLE IF EXISTS `centros`;
CREATE TABLE IF NOT EXISTS `centros` (
  `idcentro` int(11) NOT NULL auto_increment,
  `nombrecentro` varchar(100) NOT NULL default '',
  `identidad` int(11) default NULL,
  `comentarios` text,
  PRIMARY KEY  (`idcentro`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `centros`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `comandos`
-- 

DROP TABLE IF EXISTS `comandos`;
CREATE TABLE IF NOT EXISTS `comandos` (
  `idcomando` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) NOT NULL default '',
  `nfuncion1` varchar(250) default NULL,
  `nfuncion2` varchar(250) default NULL,
  `nfuncion4` varchar(250) default NULL,
  `nfuncion8` varchar(250) default NULL,
  `nfuncion10` varchar(250) default NULL,
  `urlimg` varchar(250) default NULL,
  `urlamb1` varchar(250) default NULL,
  `urlamb2` varchar(250) default NULL,
  `urlamb4` varchar(250) default NULL,
  `urlamb8` varchar(250) default NULL,
  `urlamb10` varchar(250) default NULL,
  `aplicambito` tinyint(4) default NULL,
  `visuparametros` varchar(250) default NULL,
  `parametros` varchar(250) default NULL,
  `comentarios` text,
  `interactivo` tinyint(1) default NULL,
  `ejecutor` char(1) default NULL,
  `activo` tinyint(1) NOT NULL,
  PRIMARY KEY  (`idcomando`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

-- 
-- Volcar la base de datos para la tabla `comandos`
-- 

INSERT INTO `comandos` (`idcomando`, `descripcion`, `nfuncion1`, `nfuncion2`, `nfuncion4`, `nfuncion8`, `nfuncion10`, `urlimg`, `urlamb1`, `urlamb2`, `urlamb4`, `urlamb8`, `urlamb10`, `aplicambito`, `visuparametros`, `parametros`, `comentarios`, `interactivo`, `ejecutor`, `activo`) VALUES 
(1, 'Arrancar', 'Arrancar', 'Arrancar', 'Arrancar', 'Arrancar', 'Arrancar', '', '../comandos/Arrancar.php', '../comandos/Arrancar.php', '../comandos/Arrancar.php', '../comandos/Arrancar.php', '../comandos/Arrancar.php', 31, 'iph', 'nfn;iph;mac', '', 1, '1', 1),
(2, 'Apagar', 'Apagar', 'Apagar', 'Apagar', 'Apagar', 'Apagar', '', '../comandos/Apagar.php', '../comandos/Apagar.php', '../comandos/Apagar.php', '../comandos/Apagar.php', '../comandos/Apagar.php', 31, 'iph', 'nfn;iph', '', 1, '2', 1),
(3, 'Restaurar Imagen', '', '', 'RestaurarImagen', 'RestaurarImagen', 'RestaurarImagen', '', '', '', '../comandos/RestaurarImagenAula.php', '../comandos/RestaurarImagenGrupoOrdenadores.php', '../comandos/RestaurarImagenOrdenador.php', 28, 'idi;par;iph', 'nfn;iph;par;idi;ifs;ifh;nem;idc;ida;swr', '', 1, '2', 1),
(4, 'Generar Perfil Software', '', '', '', '', 'CrearPerfilSoftware', '', '', '', '', '', '../comandos/CrearPerfilSoftware.php', 16, 'ifs;ifh;par', 'nfn;iph;par;ifs;ifh;nem;', '', 1, '2', 1),
(5, 'Reiniciar', 'Reiniciar', 'Reiniciar', 'Reiniciar', 'Reiniciar', 'Reiniciar', '', '../comandos/Reiniciar.php', '../comandos/Reiniciar.php', '../comandos/Reiniciar.php', '../comandos/Reiniciar.php', '../comandos/Reiniciar.php', 31, 'iph', 'nfn;iph', '', 1, '2', 1),
(12, 'Inventario Hardware', 'InventarioHardware', 'InventarioHardware', 'InventarioHardware', 'InventarioHardware', 'InventarioHardware', '', '../comandos/InventarioHardware.php', '../comandos/InventarioHardware.php', '../comandos/InventarioHardware.php', '../comandos/InventarioHardware.php', '../comandos/InventarioHardware.php', 31, 'iph', 'nfn;iph', '', 1, '2', 1),
(7, 'Ejecutar Script', 'ExecShell', 'ExecShell', 'ExecShell', 'ExecShell', 'ExecShell', '', '../comandos/EjecutarScripts.php', '../comandos/EjecutarScripts.php', '../comandos/EjecutarScripts.php', '../comandos/EjecutarScripts.php', '../comandos/EjecutarScripts.php', 31, 'iph;tis;dcr;scp', 'nfn;iph;tis;dcr;scp', '', 1, '2', 1),
(8, 'Particionar y formatear', '', '', 'ParticionaryFormatear', 'ParticionaryFormatear', 'ParticionaryFormatear', '', '', '', '../comandos/Configurar.php', '../comandos/Configurar.php', '../comandos/Configurar.php', 28, 'iph;ppa;lpa;hdc', 'nfn;iph;ppa;lpa;hdc', '', 1, '2', 1),
(9, 'Particionar y Formatear', '', '', 'ParticionaryFormatear', 'ParticionaryFormatear', 'ParticionaryFormatear', '', '', '', '../comandos/Particionar.php', '../comandos/Particionar.php', '../comandos/Particionar.php', 28, 'iph;ppa;lpa;hdc', 'nfn;iph;ppa;lpa;hdc', '', 1, '2', 0),
(10, 'Generar software Incremental', '', '', '', '', 'CrearSoftwareIncremental', '', '', '', '', '', '../comandos/CrearSoftIncremental.php', 16, 'ifs;ifh;par;icr', 'nfn;iph;par;ifs;ifh;nem;icr', '', 1, '2', 0),
(11, 'Activar Rembo Off Line', 'RemboOffline', 'RemboOffline', 'RemboOffline', 'RemboOffline', 'RemboOffline', '', '../comandos/RemboOffline.php', '../comandos/RemboOffline.php', '../comandos/RemboOffline.php', '../comandos/RemboOffline.php', '../comandos/RemboOffline.php', 31, 'iph', 'nfn;iph', '', 1, '2', 0),
(6, 'Tomar Configuracion', 'TomaConfiguracion', 'TomaConfiguracion', 'TomaConfiguracion', 'TomaConfiguracion', 'TomaConfiguracion', '', '../comandos/TomaConfiguracion.php', '../comandos/TomaConfiguracion.php', '../comandos/TomaConfiguracion.php', '../comandos/TomaConfiguracion.php', '../comandos/TomaConfiguracion.php', 31, 'iph', 'nfn;iph', '', 1, '2', 0);

-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `comandos_eng`
-- 

DROP TABLE IF EXISTS `comandos_eng`;
CREATE TABLE IF NOT EXISTS `comandos_eng` (
  `idcomando` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) NOT NULL default '',
  `nfuncion1` varchar(250) default NULL,
  `nfuncion2` varchar(250) default NULL,
  `nfuncion4` varchar(250) default NULL,
  `nfuncion8` varchar(250) default NULL,
  `nfuncion10` varchar(250) default NULL,
  `urlimg` varchar(250) default NULL,
  `urlamb1` varchar(250) default NULL,
  `urlamb2` varchar(250) default NULL,
  `urlamb4` varchar(250) default NULL,
  `urlamb8` varchar(250) default NULL,
  `urlamb10` varchar(250) default NULL,
  `aplicambito` tinyint(4) default NULL,
  `visuparametros` varchar(250) default NULL,
  `parametros` varchar(250) default NULL,
  `comentarios` text,
  `interactivo` tinyint(1) default NULL,
  `ejecutor` char(1) default NULL,
  PRIMARY KEY  (`idcomando`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `comandos_eng`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `configuraciones`
-- 

DROP TABLE IF EXISTS `configuraciones`;
CREATE TABLE IF NOT EXISTS `configuraciones` (
  `idconfiguracion` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) default NULL,
  `configuracion` text NOT NULL,
  PRIMARY KEY  (`idconfiguracion`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `configuraciones`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `entidades`
-- 

DROP TABLE IF EXISTS `entidades`;
CREATE TABLE IF NOT EXISTS `entidades` (
  `identidad` int(11) NOT NULL auto_increment,
  `nombreentidad` varchar(200) NOT NULL default '',
  `comentarios` text,
  `iduniversidad` int(11) default NULL,
  `grupoid` int(11) default NULL,
  PRIMARY KEY  (`identidad`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `entidades`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `estatus`
-- 

DROP TABLE IF EXISTS `estatus`;
CREATE TABLE IF NOT EXISTS `estatus` (
  `idestatus` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`idestatus`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

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
CREATE TABLE IF NOT EXISTS `grupos` (
  `idgrupo` int(11) NOT NULL auto_increment,
  `nombregrupo` varchar(250) NOT NULL default '',
  `grupoid` int(11) NOT NULL default '0',
  `tipo` tinyint(4) NOT NULL default '0',
  `idcentro` int(11) NOT NULL default '0',
  `iduniversidad` int(11) default NULL,
  `comentarios` text,
  PRIMARY KEY  (`idgrupo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `grupos`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `gruposordenadores`
-- 

DROP TABLE IF EXISTS `gruposordenadores`;
CREATE TABLE IF NOT EXISTS `gruposordenadores` (
  `idgrupo` int(11) NOT NULL auto_increment,
  `nombregrupoordenador` varchar(250) NOT NULL default '',
  `idaula` int(11) NOT NULL default '0',
  `grupoid` int(11) default NULL,
  `comentarios` text,
  PRIMARY KEY  (`idgrupo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Estructura de tabla para la tabla `hardwares`
-- 
DROP TABLE IF EXISTS `hardwares`;
CREATE TABLE IF NOT EXISTS `hardwares` (
  `idhardware` int(11) NOT NULL auto_increment,
  `idtipohardware` int(11) NOT NULL default '0',
  `descripcion` varchar(250) NOT NULL default '',
  `idcentro` int(11) NOT NULL default '0',
  `grupoid` int(11) default NULL,
  PRIMARY KEY  (`idhardware`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `iconos`
-- 

DROP TABLE IF EXISTS `iconos`;
CREATE TABLE IF NOT EXISTS `iconos` (
  `idicono` int(11) NOT NULL auto_increment,
  `urlicono` varchar(200) default NULL,
  `idtipoicono` int(11) default NULL,
  `descripcion` varchar(50) default NULL,
  PRIMARY KEY  (`idicono`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

-- 
-- Volcar la base de datos para la tabla `iconos`
-- 

INSERT INTO `iconos` (`idicono`, `urlicono`, `idtipoicono`, `descripcion`) VALUES 
(1, 'vga.gif', 1, 'Tarjeta grfica'),
(2, 'nic.gif', 1, 'Tarjeta de Red'),
(3, 'placabase.gif', 1, 'Placas base'),
(4, 'tsonido.gif', 1, 'Tarjeta de sonido'),
(5, 'camweb.gif', 1, 'Cmara web'),
(6, 'logow98.pcx', 2, 'Logo Windows 98'),
(7, 'logoredhat.pcx', 2, 'Logo Red Hat'),
(8, 'logow2000.pcx', 2, 'logo Windows 2000'),
(9, 'logoXP.pcx', 2, 'Logo Windows XP'),
(10, 'logodebian.pcx', 2, 'Logo Debian'),
(11, 'particionar.pcx', 2, 'Particionar'),
(12, 'ordenadoroff.pcx', 2, 'Ordenador apagado'),
(13, 'ordenadoron.pcx', 2, 'Ordenador encendido'),
(14, 'rembooffline.pcx', 2, 'Rembo Offline'),
(15, 'logolinux.pcx', 2, 'Logo General de Linux'),
(16, 'lock64.pcx', 2, 'candado lock');

-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `idiomas`
-- 

DROP TABLE IF EXISTS `idiomas`;
CREATE TABLE IF NOT EXISTS `idiomas` (
  `ididioma` int(11) NOT NULL auto_increment,
  `descripcion` varchar(100) default NULL,
  `nemonico` char(3) default NULL,
  PRIMARY KEY  (`ididioma`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- 
-- Volcar la base de datos para la tabla `idiomas`
-- 

INSERT INTO `idiomas` (`ididioma`, `descripcion`, `nemonico`) VALUES 
(1, 'Español', 'esp'),
(2, 'Ingles', 'eng'),
(3, 'Catalan', 'cat');

-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `imagenes`
-- 

DROP TABLE IF EXISTS `imagenes`;
CREATE TABLE IF NOT EXISTS `imagenes` (
  `idimagen` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) NOT NULL default '',
  `idperfilsoft` int(11) default NULL,
  `idcentro` int(11) default NULL,
  `comentarios` text,
  `grupoid` int(11) default NULL,
  PRIMARY KEY  (`idimagen`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `imagenes`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `imagenes_softincremental`
-- 

DROP TABLE IF EXISTS `imagenes_softincremental`;
CREATE TABLE IF NOT EXISTS `imagenes_softincremental` (
  `idimagen` int(11) NOT NULL default '0',
  `idsoftincremental` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Volcar la base de datos para la tabla `imagenes_softincremental`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `menus`
-- 

DROP TABLE IF EXISTS `menus`;
CREATE TABLE IF NOT EXISTS `menus` (
  `idmenu` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) NOT NULL default '',
  `idcentro` int(11) NOT NULL default '0',
  `urlimg` varchar(250) default NULL,
  `titulo` varchar(250) default NULL,
  `coorx` int(11) default NULL,
  `coory` int(11) default NULL,
  `modalidad` tinyint(4) default NULL,
  `scoorx` int(11) default NULL,
  `scoory` int(11) default NULL,
  `smodalidad` tinyint(4) default NULL,
  `comentarios` text,
  `grupoid` int(11) NOT NULL default '0',
  `htmlmenupub` varchar(250) default NULL,
  `htmlmenupri` varchar(250) default NULL,
  `resolucion` tinyint(4) default NULL,
  PRIMARY KEY  (`idmenu`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `menus`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `notificaciones`
-- 

DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `idnotificacion` int(11) NOT NULL auto_increment,
  `accionid` int(11) NOT NULL default '0',
  `idnotificador` int(11) default NULL,
  `fechahorareg` datetime default '0000-00-00 00:00:00',
  `resultado` char(1) default NULL,
  `descrinotificacion` text,
  `idaccion` int(11) default NULL,
  PRIMARY KEY  (`idnotificacion`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `notificaciones`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `ordenadores`
-- 

DROP TABLE IF EXISTS `ordenadores`;
CREATE TABLE IF NOT EXISTS `ordenadores` (
  `idordenador` int(11) NOT NULL auto_increment,
  `nombreordenador` varchar(100) default NULL,
  `ip` varchar(50) NOT NULL default '',
  `mac` varchar(12) default NULL,
  `idaula` int(11) default NULL,
  `idperfilhard` int(11) default NULL,
  `idservidordhcp` int(11) default NULL,
  `idservidorrembo` int(11) default NULL,
  `grupoid` int(11) default NULL,
  `idconfiguracion` int(11) default NULL,
  `idmenu` int(11) default NULL,
  `idparticion` int(11) default NULL,
  `cache` int(11) default NULL,
  PRIMARY KEY  (`idordenador`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `ordenadores`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `ordenador_imagen`
-- 

DROP TABLE IF EXISTS `ordenador_imagen`;
CREATE TABLE IF NOT EXISTS `ordenador_imagen` (
  `idordenador` int(11) NOT NULL default '0',
  `particion` int(11) NOT NULL default '0',
  `idimagen` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Volcar la base de datos para la tabla `ordenador_imagen`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `parametros`
-- 

DROP TABLE IF EXISTS `parametros`;
CREATE TABLE IF NOT EXISTS `parametros` (
  `idparametro` int(11) NOT NULL auto_increment,
  `nemonico` char(3) NOT NULL default '',
  `descripcion` varchar(250) NOT NULL default '',
  `nomidentificador` varchar(50) default NULL,
  `nomtabla` varchar(100) default NULL,
  `nomliteral` varchar(250) default NULL,
  `tipopa` tinyint(1) default NULL,
  PRIMARY KEY  (`idparametro`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

-- 
-- Volcar la base de datos para la tabla `parametros`
-- 

INSERT INTO `parametros` (`idparametro`, `nemonico`, `descripcion`, `nomidentificador`, `nomtabla`, `nomliteral`, `tipopa`) VALUES 
(1, 'iph', 'Ordenadores', '', '', '', 0),
(2, 'nfn', 'Nombre de la funcin a ejecutar en el servidor o en el cliente rembo', '', '', '', 0),
(3, 'eje', 'Ejecutor del comando (servidor o cliente rembo)', '', '', '', 0),
(4, 'par', 'Particin', '', '', '', 0),
(5, 'ifs', 'Perfil Software', 'idperfilsoft', 'perfilessoft', 'descripcion', 1),
(6, 'ifh', 'Perfil Hardware', 'idperfilhard', 'perfileshard', 'descripcion', 1),
(7, 'nem', 'Nemnico', '', '', '', 0),
(8, 'idc', 'Centro', 'idcentro', 'centros', '', 1),
(9, 'ida', 'Aula', 'idaula', 'aulas', 'nombreaula', 1),
(10, 'idi', 'Imagen', 'idimagen', 'imagenes', 'descripcion', 1),
(11, 'mac', 'Direccin Mac', '', '', '', 0),
(12, 'cmd', 'Identificador de un comando dentro de una tarea', 'idtareacomando', 'tareas_comando', '', 1),
(13, 'ppa', 'Particiones primarias', '', '', '', 0),
(14, 'lpa', 'Particiones Lgicas', '', '', '', 0),
(15, 'hdc', 'Particiones a formatear', '', '', '', 0),
(16, 'tis', 'Ttulo del Script', '', '', '', 0),
(17, 'scp', 'Cdigo rembo-C', '', '', '', 0),
(18, 'dcr', 'Descripcin', '', '', '', 0),
(19, 'icr', 'Software Incremental', 'idsoftincremental', 'softincrementales', 'descripcion', 1),
(20, 'scp', 'Cdigo Rembo-C', '', '', '', 0);

-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `particiones`
-- 

DROP TABLE IF EXISTS `particiones`;
CREATE TABLE IF NOT EXISTS `particiones` (
  `idparticion` int(11) NOT NULL auto_increment,
  `particion` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`idparticion`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `particiones`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `perfileshard`
-- 

DROP TABLE IF EXISTS `perfileshard`;
CREATE TABLE IF NOT EXISTS `perfileshard` (
  `idperfilhard` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) NOT NULL default '',
  `idcentro` int(11) NOT NULL default '0',
  `comentarios` text,
  `grupoid` int(11) default NULL,
  PRIMARY KEY  (`idperfilhard`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `perfileshard`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `perfileshard_hardwares`
-- 

DROP TABLE IF EXISTS `perfileshard_hardwares`;
CREATE TABLE IF NOT EXISTS `perfileshard_hardwares` (
  `idperfilhard` int(11) NOT NULL default '0',
  `idhardware` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Volcar la base de datos para la tabla `perfileshard_hardwares`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `perfileshard_perfilessoft`
-- 

DROP TABLE IF EXISTS `perfileshard_perfilessoft`;
CREATE TABLE IF NOT EXISTS `perfileshard_perfilessoft` (
  `idphardidpsoft` int(11) NOT NULL auto_increment,
  `idperfilhard` int(11) NOT NULL default '0',
  `idperfilsoft` int(11) NOT NULL default '0',
  PRIMARY KEY  (`idphardidpsoft`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `perfileshard_perfilessoft`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `perfilessoft`
-- 

DROP TABLE IF EXISTS `perfilessoft`;
CREATE TABLE IF NOT EXISTS `perfilessoft` (
  `idperfilsoft` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) NOT NULL default '',
  `idcentro` int(11) NOT NULL default '0',
  `comentarios` text,
  `grupoid` int(11) default NULL,
  PRIMARY KEY  (`idperfilsoft`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `perfilessoft`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `perfilessoft_softwares`
-- 

DROP TABLE IF EXISTS `perfilessoft_softwares`;
CREATE TABLE IF NOT EXISTS `perfilessoft_softwares` (
  `idperfilsoft` int(11) NOT NULL default '0',
  `idsoftware` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Volcar la base de datos para la tabla `perfilessoft_softwares`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `phard_psoft_softincremental`
-- 

DROP TABLE IF EXISTS `phard_psoft_softincremental`;
CREATE TABLE IF NOT EXISTS `phard_psoft_softincremental` (
  `idphardidpsoft` int(11) NOT NULL default '0',
  `idsoftincremental` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Volcar la base de datos para la tabla `phard_psoft_softincremental`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `procedimientos`
-- 

DROP TABLE IF EXISTS `procedimientos`;
CREATE TABLE IF NOT EXISTS `procedimientos` (
  `idprocedimiento` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) NOT NULL default '',
  `urlimg` varchar(250) default NULL,
  `idcentro` int(11) NOT NULL default '0',
  `comentarios` text,
  `grupoid` int(11) default '0',
  PRIMARY KEY  (`idprocedimiento`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `procedimientos`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `procedimientos_comandos`
-- 

DROP TABLE IF EXISTS `procedimientos_comandos`;
CREATE TABLE IF NOT EXISTS `procedimientos_comandos` (
  `idprocedimientocomando` int(11) NOT NULL auto_increment,
  `idprocedimiento` int(11) NOT NULL default '0',
  `orden` tinyint(4) default NULL,
  `idcomando` int(11) NOT NULL default '0',
  `parametros` text,
  PRIMARY KEY  (`idprocedimientocomando`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `procedimientos_comandos`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `programaciones`
-- 

DROP TABLE IF EXISTS `programaciones`;
CREATE TABLE IF NOT EXISTS `programaciones` (
  `idprogramacion` int(11) NOT NULL auto_increment,
  `tipoaccion` int(11) default NULL,
  `identificador` int(11) default NULL,
  `nombrebloque` varchar(255) default NULL,
  `annos` tinyint(4) default NULL,
  `meses` tinyint(4) default NULL,
  `diario` int(11) default NULL,
  `dias` tinyint(4) default NULL,
  `semanas` tinyint(4) default NULL,
  `horas` tinyint(4) default NULL,
  `ampm` tinyint(1) default NULL,
  `minutos` tinyint(4) default NULL,
  `segundos` tinyint(4) default NULL,
  `horasini` tinyint(4) default NULL,
  `ampmini` tinyint(1) default NULL,
  `minutosini` tinyint(4) default NULL,
  `horasfin` tinyint(4) default NULL,
  `ampmfin` tinyint(1) default NULL,
  `minutosfin` tinyint(4) default NULL,
  `suspendida` tinyint(1) default NULL,
  PRIMARY KEY  (`idprogramacion`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `programaciones`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `reservas`
-- 

DROP TABLE IF EXISTS `reservas`;
CREATE TABLE IF NOT EXISTS `reservas` (
  `idreserva` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) NOT NULL default '',
  `solicitante` varchar(250) default NULL,
  `email` varchar(50) default NULL,
  `idestatus` tinyint(4) NOT NULL default '0',
  `comentarios` text NOT NULL,
  `grupoid` int(11) NOT NULL default '0',
  `idcentro` int(11) NOT NULL default '0',
  `idaula` int(11) default NULL,
  `idimagen` int(11) default NULL,
  `idtarea` int(11) default NULL,
  `idtrabajo` int(11) default NULL,
  `estado` tinyint(4) default NULL,
  PRIMARY KEY  (`idreserva`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `reservas`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `reservastemporal`
-- 

DROP TABLE IF EXISTS `reservastemporal`;
CREATE TABLE IF NOT EXISTS `reservastemporal` (
  `idreservatemporal` int(11) NOT NULL auto_increment,
  `usuario` char(10) NOT NULL default '',
  `idreserva` int(11) NOT NULL default '0',
  `fecha` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`idreservatemporal`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `reservastemporal`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `servidoresdhcp`
-- 

DROP TABLE IF EXISTS `servidoresdhcp`;
CREATE TABLE IF NOT EXISTS `servidoresdhcp` (
  `idservidordhcp` int(11) NOT NULL auto_increment,
  `nombreservidordhcp` varchar(250) NOT NULL default '',
  `ip` varchar(15) NOT NULL default '',
  `passguor` varchar(50) default NULL,
  `pathdhcpconf` varchar(250) NOT NULL default '',
  `pathdhcpd` varchar(250) NOT NULL default '',
  `idcentro` int(11) default NULL,
  `grupoid` int(11) default NULL,
  `comentarios` text,
  PRIMARY KEY  (`idservidordhcp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `servidoresdhcp`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `servidoresrembo`
-- 

DROP TABLE IF EXISTS `servidoresrembo`;
CREATE TABLE `servidoresrembo` (
  `idservidorrembo` int(11) NOT NULL auto_increment,
  `nombreservidorrembo` varchar(250) NOT NULL default '',
  `ip` varchar(15) NOT NULL default '',
  `passguor` varchar(50) NOT NULL default '',
  `pathremboconf` varchar(250) NOT NULL default '',
  `pathrembod` varchar(250) NOT NULL default '',
  `pathpxe` varchar(250) NOT NULL,
  `idcentro` int(11) default NULL,
  `grupoid` int(11) default NULL,
  `comentarios` text,
  `puertorepo` int(11) NOT NULL,
  PRIMARY KEY  (`idservidorrembo`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- 
-- Volcar la base de datos para la tabla `servidoresrembo`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `softincrementales`
-- 

DROP TABLE IF EXISTS `softincrementales`;
CREATE TABLE IF NOT EXISTS `softincrementales` (
  `idsoftincremental` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) NOT NULL default '',
  `idcentro` int(11) default NULL,
  `comentarios` text,
  `grupoid` int(11) default NULL,
  PRIMARY KEY  (`idsoftincremental`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `softincrementales`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `softincremental_softwares`
-- 

DROP TABLE IF EXISTS `softincremental_softwares`;
CREATE TABLE IF NOT EXISTS `softincremental_softwares` (
  `idsoftincremental` int(11) NOT NULL default '0',
  `idsoftware` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Volcar la base de datos para la tabla `softincremental_softwares`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `softwares`
-- 

DROP TABLE IF EXISTS `softwares`;
CREATE TABLE IF NOT EXISTS `softwares` (
  `idsoftware` int(11) NOT NULL auto_increment,
  `idtiposoftware` int(11) NOT NULL default '0',
  `descripcion` varchar(250) NOT NULL default '',
  `idcentro` int(11) NOT NULL default '0',
  `urlimg` varchar(250) default NULL,
  `idtiposo` int(11) default NULL,
  `grupoid` int(11) default NULL,
  PRIMARY KEY  (`idsoftware`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `softwares`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `tareas`
-- 

DROP TABLE IF EXISTS `tareas`;
CREATE TABLE IF NOT EXISTS `tareas` (
  `idtarea` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) NOT NULL default '',
  `urlimg` varchar(250) default NULL,
  `idcentro` int(11) NOT NULL default '0',
  `comentarios` text,
  `grupoid` int(11) default '0',
  PRIMARY KEY  (`idtarea`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `tareas`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `tareas_comandos`
-- 

DROP TABLE IF EXISTS `tareas_comandos`;
CREATE TABLE IF NOT EXISTS `tareas_comandos` (
  `idtareacomando` int(11) NOT NULL auto_increment,
  `idtarea` int(11) NOT NULL default '0',
  `orden` tinyint(4) default NULL,
  `idcomando` int(11) NOT NULL default '0',
  `ambito` tinyint(4) default NULL,
  `idambito` int(11) default NULL,
  `parametros` text,
  PRIMARY KEY  (`idtareacomando`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `tareas_comandos`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `tipohardwares`
-- 

DROP TABLE IF EXISTS `tipohardwares`;
CREATE TABLE IF NOT EXISTS `tipohardwares` (
  `idtipohardware` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) NOT NULL default '',
  `urlimg` varchar(250) NOT NULL default '',
  `nemonico` char(3) NOT NULL,
  `pci` tinyint(1) NOT NULL,
  PRIMARY KEY  (`idtipohardware`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

-- 
-- Volcar la base de datos para la tabla `tipohardwares`
-- 

INSERT INTO `tipohardwares` (`idtipohardware`, `descripcion`, `urlimg`, `nemonico`, `pci`) VALUES 
(1, 'placas', '../images/iconos/placabase.gif', 'boa', 0),
(2, 'Camaras web', '../images/iconos/camweb.gif', '', 0),
(3, 'Tarjetas de Red', '../images/iconos/nic.gif', 'net', 0),
(4, 'Microprocesadores', '../images/iconos/micro.gif', 'cpu', 0),
(5, 'Memorias', '../images/iconos/confihard.gif', 'mem', 0),
(7, 'Tarjetas gráficas', '../images/iconos/vga.gif', 'vga', 0),
(8, 'discos', '../images/iconos/discoduro.gif', 'dis', 0),
(9, 'Dispositivos de sonido', '../images/iconos/tsonido.gif', 'aud', 0),
(10, 'Marca y modelo del equipo', '../images/iconos/confihard.gif', 'mod', 0),
(11, 'Modelo y version de la bios', '../images/iconos/confihard.gif', 'bio', 0),
(12, 'Modelo de grabadora o  grabadora de CD/DVD', '../images/iconos/confihard.gif', 'cdr', 0);

-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `tiposoftwares`
-- 

DROP TABLE IF EXISTS `tiposoftwares`;
CREATE TABLE IF NOT EXISTS `tiposoftwares` (
  `idtiposoftware` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) NOT NULL default '',
  `urlimg` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`idtiposoftware`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

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
CREATE TABLE IF NOT EXISTS `tiposos` (
  `idtiposo` int(11) NOT NULL auto_increment,
  `descripcion` varchar(50) NOT NULL default '',
  `nemonico` varchar(8) NOT NULL,
  `descripcionrmb` varchar(50) default NULL,
  `tipopar` varchar(50) default NULL,
  PRIMARY KEY  (`idtiposo`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

-- 
-- Volcar la base de datos para la tabla `tiposos`
-- 

INSERT INTO `tiposos` (`idtiposo`, `descripcion`, `nemonico`, `descripcionrmb`, `tipopar`) VALUES 
(1, 'Windows 98, Millenium', 'Win98', 'Windows 98 SE', 'FAT32'),
(2, 'Windows 2000 (Home,Profesional,Server)', 'Win2K', 'Windows 2000', 'NTFS'),
(3, 'Windows XP (Home,Profesional)', 'WinXP', 'Windows XP', 'NTFS'),
(4, 'Linux Ext2', 'Linux', 'Linux', 'EXT2'),
(5, 'Windows NT', 'WinNT', 'Windows NT', 'NTFS'),
(6, 'Windows 2003 Server', 'W2003', 'Windows 2003', 'NTFS'),
(7, 'MsDos, Windows 95', 'MsDos', 'MSDOS', 'BIGDOS'),
(8, 'Espacio Libre', 'EMPTY', 'EMPTY', 'EXT'),
(9, 'Linux Ext3', 'Linux', 'Linux', 'EXT3'),
(10, 'Partición desconocida', 'UNKNOWN', 'Descononocida', 'UNKNOWN'),
(11, 'Partición Caché', 'CACHE', 'Caché', 'CACHE'),
(12, 'Partición VFAT', 'VFAT', 'VFAT', 'VFAT');

-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `trabajos`
-- 

DROP TABLE IF EXISTS `trabajos`;
CREATE TABLE IF NOT EXISTS `trabajos` (
  `idtrabajo` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) default NULL,
  `idcentro` int(11) NOT NULL default '0',
  `urlimg` varchar(250) default NULL,
  `comentarios` text,
  `grupoid` int(11) default NULL,
  PRIMARY KEY  (`idtrabajo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `trabajos`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `trabajos_tareas`
-- 

DROP TABLE IF EXISTS `trabajos_tareas`;
CREATE TABLE IF NOT EXISTS `trabajos_tareas` (
  `idtrabajotarea` int(11) NOT NULL auto_increment,
  `idtrabajo` int(11) NOT NULL default '0',
  `orden` tinyint(4) default NULL,
  `idtarea` int(11) default NULL,
  `ambitskwrk` text,
  `parametros` text,
  PRIMARY KEY  (`idtrabajotarea`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `trabajos_tareas`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `universidades`
-- 

DROP TABLE IF EXISTS `universidades`;
CREATE TABLE IF NOT EXISTS `universidades` (
  `iduniversidad` int(11) NOT NULL auto_increment,
  `nombreuniversidad` varchar(200) NOT NULL default '',
  `comentarios` text,
  PRIMARY KEY  (`iduniversidad`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Volcar la base de datos para la tabla `universidades`
-- 

INSERT INTO `universidades` (`iduniversidad`, `nombreuniversidad`, `comentarios`) VALUES 
(1, 'Universidad de ...', '');

-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `urlimagesitems`
-- 

DROP TABLE IF EXISTS `urlimagesitems`;
CREATE TABLE IF NOT EXISTS `urlimagesitems` (
  `idurlimagesitems` int(11) NOT NULL auto_increment,
  `descripcion` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`idurlimagesitems`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Volcar la base de datos para la tabla `urlimagesitems`
-- 


-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `usuarios`
-- 

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `idusuario` int(11) NOT NULL auto_increment,
  `usuario` varchar(50) NOT NULL default '',
  `pasguor` varchar(50) NOT NULL default '',
  `nombre` varchar(200) default NULL,
  `email` varchar(200) default NULL,
  `idambito` int(11) default NULL,
  `ididioma` int(11) default NULL,
  `idtipousuario` tinyint(4) default NULL,
  PRIMARY KEY  (`idusuario`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Volcar la base de datos para la tabla `usuarios`
-- 

INSERT INTO `usuarios` (`idusuario`, `usuario`, `pasguor`, `nombre`, `email`, `idambito`, `ididioma`, `idtipousuario`) VALUES 
(1, 'usuog', 'passusuog', 'Usuario de la base de datos MySql', '', 0, 1, 1);
