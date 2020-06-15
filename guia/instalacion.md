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
5. CLIENT_ID: 
CLIENT_SECRET=

## En la nube

Explicar.
