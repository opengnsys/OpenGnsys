# OpenGnSys 1.0.6, 1.0.6a - 1.0.6b
#use ogAdmBD

ALTER TABLE ordenadores
	ADD INDEX idaulaip (idaula ASC, ip ASC);

# Añadir nuevos sistemas de ficheros (ticket #758)
INSERT INTO sistemasficheros (idsistemafichero, nemonico, descripcion) VALUES
	(19, 'LINUX-SWAP', 'LINUX-SWAP')
	ON DUPLICATE KEY UPDATE
		idsistemafichero=VALUES(idsistemafichero), nemonico=VALUES(nemonico), descripcion=VALUES(descripcion);

