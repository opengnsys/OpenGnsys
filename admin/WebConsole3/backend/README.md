opengnsys
=========

A Symfony project created on January 16, 2017, 8:47 am.


Instrucciones
-------------

php app/console doctrine:generate:entities Opengnsys/ServerBundle/Entity
php app/console doctrine:schema:update --dump-sql // --force


php app/console doctrine:mapping:import --em og_1 --force OpengnsysMigrationBundle xml


- Es necesario instalar la extensión de PHP XML
sudo apt-get install php7.0-xml


php app/console doctrine:mapping:import --force MigrationBundle xml

###### USER ######
// Crea un usuario: ususername / email / password
php app/console fos:user:create admin admin@localhost.localdomain admin

// Crea un usuario con el rol SUPER_ADMIN
php app/console fos:user:create admin --super-admin

// Crea un usuario con el check ENABLE false
php app/console fos:user:create testuser --inactive

// Activar o desactivar un usuario
php app/console fos:user:activate testuser
php app/console fos:user:deactivate testuser

// Asigna / Desasigna Rol a un usuario
php app/console fos:user:promote testuser ROLE_ADMIN
php app/console fos:user:demote testuser ROLE_ADMIN

// Asigna / Desasigna el rol SUPER_ADMIN a un usuario.
php app/console fos:user:promote testuser --super
php app/console fos:user:demote testuser --super

// Cambia la contraseña a un usuario
php app/console fos:user:change-password testuser newp@ssword


###### AUTH 2 ######
// Crear el id y secret del Auth2
php app/console opengnsys:oauth-server:client:create --grant-type="password" --grant-type="refresh_token" --grant-type="token" --grant-type="http://opengnsys.es/grants/og_client"


### TOKEN ###

# grant-type="password"
.../oauth/v2/token?client_id=CLIENT_ID&client_secret=CLIENT_SECRET&grant_type=password&username=USERNAME&password=PASSWORD
# Autenticacion en una sola petición, añade las credenciales del clientes y del usuario.

# grant-type="refresh_token"
.../oauth/v2/token?client_id=CLIENT_ID&client_secret=CLIENT_SECRET&grant_type=refresh_token&refresh_token=REFRESH_TOKEN
# Actualiza el access_token a partir de un refresh_token

# grant-type="token"
.../oauth/v2/auth?client_id=CLIENT_ID&redirect_uri=URL&response_type=token
# Es igual que authorization_code pero en un solo paso.