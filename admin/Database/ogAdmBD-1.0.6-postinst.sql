### Procedimiento para actualización de la base de datos.
# Postinstalación de OpenGnSys 1.0.5
#use ogAdmBD

# Nota: retocar el fichero para sustituir KERNELVERSION por la versión del 
#       Kernel del cliente ogLive con el formato V.RR (V=versión, RR=revisión).

# Cambiar parámetro de resolución de pantalla para Kernel anteriores a 3.7.
UPDATE menus
	SET resolucion = CASE resolucion 
				WHEN 'uvesafb:1152x864-16' THEN '355'
				WHEN 'uvesafb:800x600-16' THEN '788'
				WHEN 'uvesafb:800x600-24' THEN '789'
				WHEN 'uvesafb:1024x768-16' THEN '791'
				WHEN 'uvesafb:1024x768-24' THEN '792'
				WHEN 'uvesafb:1280x1024-16' THEN '794'
				WHEN 'uvesafb:1280x1024-24' THEN '795'
				WHEN 'uvesafb:1600x1200-16' THEN '798'
				WHEN 'uvesafb:1600x1200-24' THEN '799'
				WHEN NULL or '0' THEN '788'
				ELSE resolucion
			 END
			 WHERE KERNELVERSION < 3.07;

# Cambiar parámetro de resolución de pantalla para Kernel 3.7 o superior.
UPDATE menus
	SET resolucion = CASE resolucion 
				WHEN '355' THEN 'uvesafb:1152x864-16'
				WHEN '788' THEN 'uvesafb:800x600-16'
				WHEN '789' THEN 'uvesafb:800x600-24'
				WHEN '791' THEN 'uvesafb:1024x768-16'
				WHEN '792' THEN 'uvesafb:1024x768-24'
				WHEN '794' THEN 'uvesafb:1280x1024-16'
				WHEN '795' THEN 'uvesafb:1280x1024-24'
				WHEN '798' THEN 'uvesafb:1600x1200-16'
				WHEN '799' THEN 'uvesafb:1600x1200-24'
				WHEN NULL or '0' THEN 'uvesafb:800x600-16'
				ELSE resolucion
			 END
			 WHERE KERNELVERSION >= 3.07;

