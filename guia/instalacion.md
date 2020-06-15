# Instrucciones de instalación y despliegue

## En local

**Es necesario:**

- Git
- Composer
- PostgreSQL 12 o superior
- PHP 7.3 o superior
- Cuenta de PayPal
- Aplicación de PayPal Sandbox
- Cliente de Amazon S3 con contenido multimedia
- Email

**Instalación**

- Nos movemos al directorio donde queramos instalar la aplicación.

- Ejecutamos los siguientes comandos:

```git clone https://github.com/pemife/gamesandfriends```

```composer install```

- Cambiamos la dirección del correo en config/params.php

``` 'smtpUsername' => 'xxxxxxxxxxxxxx@xxxx.xxx'```

- Instalamos la base de datos con los siguientes comandos:
``` db/create.sh ```
``` db/load.sh ```
``` ./yii migrate ```

- Instanciar las variables de entorno correspondientes en el archivo ".env" (si no existe, lo creamos):

1. KEY: La clave de cliente de amazon s3
2. SECRET: El secreto del cliente de amazon s3
3. SMTP_PASS: Contraseña del correo que enviará las notificaciones
4. MEDIA: Esta variable permite que haya contenido multimedia cuando tiene el valor '1', y lo bloquea con el valor '0'
5. PCLIENTID: El id de la cuenta de paypal sandbox que recibirá las transacciones simuladas.

- Finalizamos con el comando ```make serve```, y entrando con el navegador a la direccion de "localhost:8080".

## En la nube

**Es necesario:**

- Heroku CLI

**Instalación**

- Accedemos al repositorio del proyecto, [Enlace a repo](https://github.com/pemife/gamesandfriends).
- *Forkeamos* el repositorio.
- Accedemos a heroku e iniciamos sesión, o creamos una cuenta si no la tenemos.
- Creamos una aplicación.
- Enlazamos con nuestro repositorio creado a partir del fork del proyecto.
- Habilitamos el addon "heroku postgres" para la base de datos.
- Declaramos las variables de entorno:

    1. KEY: La clave de cliente de amazon s3
    2. SECRET: El secreto del cliente de amazon s3
    3. SMTP_PASS: Contraseña del correo que enviará las notificaciones
    4. MEDIA: Esta variable permite que haya contenido multimedia cuando tiene el valor '1', y lo bloquea con el valor '0'
    5. PCLIENTID: El id de la cuenta de paypal sandbox que recibirá las transacciones simuladas.

- Hacemos un clone del repositorio en local
- Realizamos el comando ```heroku login```
- Por ultimo cargamos la base de datos con el comando ```heroku psql < db/gamesandfriends.sql```