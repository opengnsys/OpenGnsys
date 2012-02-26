UPDATE ogAdmBD.entornos SET ipserveradm = 'SERVERIP' WHERE ipserveradm = '' LIMIT 1;
UPDATE ogAdmBD.parametros SET tipopa = '1' WHERE idparametro = 30;
UPDATE ogAdmBD.idiomas SET descripcion = 'English' WHERE ididioma = 2;
UPDATE ogAdmBD.idiomas SET descripcion = 'Català' WHERE ididioma = 3;

