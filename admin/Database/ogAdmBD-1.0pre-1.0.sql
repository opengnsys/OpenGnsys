SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de datos: `ogBDAdmin`
--





ALTER TABLE `aulas` CHANGE `modp2p` `modp2p` ENUM( 'seeder', 'peer', 'leecher' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'peer';

ALTER TABLE `aulas` CHANGE `velmul` `velmul` SMALLINT( 6 ) NOT NULL DEFAULT '70';


ALTER TABLE `asistentes` DROP PRIMARY KEY ,
ADD PRIMARY KEY ( `idcomando` , `descripcion` ) ;

UPDATE `ogAdmBD`.`comandos` SET `activo` = '0' WHERE `comandos`.`idcomando` =10;

UPDATE `ogAdmBD`.`asistentes` SET `idcomando` = '8' ;
