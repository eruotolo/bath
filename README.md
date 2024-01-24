# Para crear los archivos php.ini

1 - docker-compose ps // esto me devuelve las imagenes que estan corriendo
2 - docker cp nombre-imagen-php:/usr/local/etc/php ./php
3 - desde la configuración de la imagen del php, llamamos al ini:
    - ./php/php.ini:/usr/local/etc/php/php.ini:ro


upload_max_filesize = 256M

max_input_time = -1

post_max_size = 256M

max_input_vars = 8000

max_execution_time = 500

memory_limit = 512M


# Datos Servidor Producción

Base de datos
User: cch94190_sistema
Database: cch94190_sistema