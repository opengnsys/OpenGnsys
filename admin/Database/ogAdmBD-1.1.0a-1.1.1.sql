### Fichero de actualización de la base de datos.
# OpenGnsys 1.1.0, 1.1.0a - OpenGnsys 1.1.1
#use ogAdmBD

# Nuevos tipos de particiones.
INSERT INTO tipospar (codpar, tipopar, clonable) VALUES
        (CONV('27',16,10), 'HNTFS-WINRE', 1)
        ON DUPLICATE KEY UPDATE
                codpar=VALUES(codpar), tipopar=VALUES(tipopar), clonable=VALUES(clonable);

# Añadir campo para incluir PC de profesor de aula (ticket #816).
ALTER TABLE aulas
	ADD idordprofesor INT(11) DEFAULT 0 AFTER puestos;

# Borrar campos sin uso del antiguo servicio ogAdmRepo (ticket #875).
ALTER TABLE repositorios
	DROP passguor,
	DROP puertorepo;

