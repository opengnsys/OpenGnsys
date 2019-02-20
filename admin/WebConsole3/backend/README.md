opengnsys
=========

A Symfony project created on January 16, 2017, 8:47 am.


Instrucciones
-------------

php app/console doctrine:generate:entities Opengnsys/ServerBundle/Entity
php app/console doctrine:schema:update --dump-sql // --force


php app/console doctrine:mapping:import --em og_1 --force OpengnsysMigrationBundle xml