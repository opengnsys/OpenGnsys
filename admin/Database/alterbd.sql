SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de datos: `ogBDAdmin`
--


UPDATE `ogAdmBD`.`comandos` SET `activo` = '0' WHERE `comandos`.`idcomando` =10;



ALTER TABLE `asistentes` DROP PRIMARY KEY ,
ADD PRIMARY KEY ( `idcomando` , `descripcion` ) ;


UPDATE `ogAdmBD`.`asistentes` SET `idcomando` = '8' ;