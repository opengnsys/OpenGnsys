-- Cambios para gestión de Multicast

ALTER TABLE `ordenadores`
   ADD COLUMN `modomul` TINYINT(4) NOT NULL,
   ADD COLUMN `ipmul`   VARCHAR(16) NOT NULL,
   ADD COLUMN `pormul`  INT(11) NOT NULL,
   ADD COLUMN `velmul`  SMALLINT(6) NOT NULL;

