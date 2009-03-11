-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 27-02-2009 a las 20:35:25
-- Versión del servidor: 5.0.67
-- Versión de PHP: 5.2.6-2ubuntu4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de datos: `eac`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `arranques`
--

CREATE TABLE IF NOT EXISTS `arranques` (
  `id_arranque` varchar(7) collate utf8_spanish_ci NOT NULL,
  `descripcion` varchar(50) collate utf8_spanish_ci default NULL,
  `kernel` varchar(50) collate utf8_spanish_ci NOT NULL,
  `append` varchar(200) collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`id_arranque`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcar la base de datos para la tabla `arranques`
--

INSERT INTO `arranques` (`id_arranque`, `descripcion`, `kernel`, `append`) VALUES
('1', '1disk   MBR', '', ''),
('11', '1disk 1part', '', ''),
('12', '1disk 2part', '', ''),
('13', '1disk 3part', '', ''),
('pxe', 'pxe EAC', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aulas`
--

CREATE TABLE IF NOT EXISTS `aulas` (
  `id_aula` tinyint(4) NOT NULL auto_increment,
  `descripcion` varchar(15) collate utf8_spanish_ci default NULL,
  `subred` varchar(11) collate utf8_spanish_ci default NULL,
  PRIMARY KEY  (`id_aula`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci AUTO_INCREMENT=3 ;

--
-- Volcar la base de datos para la tabla `aulas`
--

INSERT INTO `aulas` (`id_aula`, `descripcion`, `subred`) VALUES
(1, ' Complejo (Bibl', '172.17.9.0'),
(2, 'Complejo (Hemer', '172.17.9.0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caches`
--

CREATE TABLE IF NOT EXISTS `caches` (
  `ip` varchar(15) collate utf8_spanish_ci NOT NULL COMMENT 'ip de la maquina',
  `mount` varchar(15) collate utf8_spanish_ci NOT NULL COMMENT 'punto de montaje',
  `size` int(30) NOT NULL COMMENT 'tama?o en mb',
  PRIMARY KEY  (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcar la base de datos para la tabla `caches`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE IF NOT EXISTS `equipos` (
  `hostname` varchar(32) collate utf8_spanish_ci NOT NULL,
  `mac` varchar(18) collate utf8_spanish_ci NOT NULL,
  `ip` varchar(15) collate utf8_spanish_ci NOT NULL,
  `arranque` varchar(7) collate utf8_spanish_ci NOT NULL default 'pxe',
  `aula` tinyint(4) NOT NULL,
  `startpage` varchar(50) collate utf8_spanish_ci default 'default.sh',
  `vga` varchar(5) collate utf8_spanish_ci default '791',
  `acpi` enum('on','off') collate utf8_spanish_ci NOT NULL default 'on',
  `pci` enum('msi','nomsi') collate utf8_spanish_ci NOT NULL default 'msi',
  PRIMARY KEY  (`hostname`),
  UNIQUE KEY `mac` (`mac`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcar la base de datos para la tabla `equipos`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes`
--

CREATE TABLE IF NOT EXISTS `imagenes` (
  `ip` varchar(15) collate utf8_spanish_ci NOT NULL,
  `directory` varchar(100) collate utf8_spanish_ci NOT NULL,
  `name` varchar(50) collate utf8_spanish_ci NOT NULL,
  `size` int(15) NOT NULL,
  `info` text collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`ip`,`directory`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `ip` varchar(50) collate utf8_spanish_ci NOT NULL,
  `dia` date NOT NULL,
  `hora` varchar(8) collate utf8_spanish_ci NOT NULL,
  `tiempo_proceso` varchar(8) collate utf8_spanish_ci NOT NULL,
  `comando` varchar(100) collate utf8_spanish_ci NOT NULL,
  `parametros` varchar(300) collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`ip`,`dia`,`hora`,`comando`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas_servidor`
--

CREATE TABLE IF NOT EXISTS `tareas_servidor` (
  `comando` varchar(30) collate utf8_spanish_ci NOT NULL,
  `parametros` varchar(200) collate utf8_spanish_ci NOT NULL,
  `descripcion` varchar(15) collate utf8_spanish_ci default NULL,
  `ip` varchar(42) collate utf8_spanish_ci NOT NULL,
  `id_proceso` int(4) default NULL,
  `finalizado` int(4) NOT NULL,
  PRIMARY KEY  (`comando`,`parametros`,`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcar la base de datos para la tabla `tareas_servidor`
--

