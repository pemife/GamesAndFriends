# Dificultades encontradas

**Token de autenticación de PayPal.**

Para finalizar las compras, creé una acción en el controlador de copias para procesar el carrito, y otra para asignar estas copias recién compradas, con el modelo de usuario del comprador.

Cuando el usuario pulsa el boton de PayPal, se ejecuta mediante Ajax, la acción de procesar el carrito, y si da algún error, no permite proceder a la compra. Pero si no ocurre ningún error, genera una token de autenticación que asigna a una cookie, y la pasa a la vista de la venta del carrito, donde se recoge con javascript.

Cuando se ha procesado el carrito correctamente, automaticamente se genera el proceso de pago con PayPal, y cuando este pago se realiza correctamente, realiza otra petición Ajax, donde se compara la token de autenticación en la cookie, con la token pasada por Post a la acción de finalización de la compra. Si los dos tokens coinciden, se procesa el asignado de estas copias nuevas en la propiedad del usuario.

Así controlo que estas dos acciones se ejecutan seguidamente, y que no hay manera posible de finalizar una compra, sin antes realizar el pago correctamente.


**Globalización de cliente de amazon S3.**

Al usar Amazon S3, para crear una Url firmada que permita ver las fotos, necesitaba una instancia de S3Client, para crear esta instancia necesitaba una serie de parámetros. En vista de que usaría Amazon S3 en muchos lugares de la página, decidí globalizar dichos parámetros, que serían los mismos en su mayoría.

Agregé los parametros para isntanciar el cliente en config/params.php, y al instanciar S3Client, no obtenia los parametros necesarios del constructor, porque se agregaban como un array de configuracion. Por lo tanto, no era posible la globalización. Decidí usar la clase S3Client solo en los modelos que requerieran de contenido multimedia proveniente de Amazon S3.


**Creación de ventas con copias o productos**

Al crear la tabla ventas, mi idea era que pudiera participar en ellas tanto copias de juegos como productos. Decidí crear una restricción en la base de datos, que consistiera en comprobar que las ventas tendrían tanto copias como productos en sus datos. Tendrían valores nulos alternados, de modo que cuando la venta es de un producto, el valor del id de la copia es nulo, y viceversa.


**Relaciones entre usuarios unidireccionales y bidireccionales**

Las relaciones entre usuarios están modeladas para que solo puedan existir dos instancias de la base de datos para cada pareja de usuarios. La amistad se define cuando dos usuarios tienen una relación con el estado "1", y no contienen ningún bloqueo entre ellos (una relación de estado "3").

La amistad, las peticiones de amistad, y las peticiones rechazadas, son relaciones bidireccionales, que no necesitan mas que una relación de cada tipo, para definir la relación. Sin embargo, las relaciones de bloqueo y seguimiento, son relaciones unidireccionales, que necesitan un rol para definirse, por lo que en este tipo de relaciones importa quien ha realizado el bloqueo o el seguimiento, por lo que se tiene en cuenta quien es el que ha instanciado la relación, (usuario 1, bloqueador/seguidor), y quien es el "objetivo" de la relación, (usuario2, bloqueado/seguido).


**Claves de juegos sin proveedor**

Como no tengo contratado un proveedor de claves de juegos digitales, he creado una función que genera claves aleatorias válidas, para cada nueva instancia de Copias.


**Orden de lista de deseos**

Para la lista de deseos de juegos del usuario, he establecido un orden, que se actualiza cada vez que se borra un elemento de la lista. Cuando se agrega un juego a la lista, toma el orden de último en la lista.


Los elementos de innovación de mi proyecto son Amazon Web Services, y PayPal.
