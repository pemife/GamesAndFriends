# Decisiones adoptadas

#### *Relaciones por "estados" (relaciones unidireccionales y bidireccionales).*

Al crear relaciones entre usuarios, quería facilitar como se representan los tipos de relaciones, por eso añadí los estados. Cada estado de la relación entre dos usuarios representa un tipo, donde *1* sería la amistad aceptada, *2* es la petición de amistad rechazada, *3* representa el bloqueo entre usuarios, y *4* representa una relacion de seguimiento (en la que debe participar un usuario crítico de juegos).

Al crear las relaciones de este modo, me di cuenta de que tenia que tener en cuenta que las relaciones de bloqueo y de seguimiento, son distintas de las otras relaciones. Estos dos tipos de relaciones, son relaciones unidireccionales, mientras que las relaciones de amistad y de rechazo son bidireccionales. Esto quiere decir que debia diferenciar los roles en estos tipos relacion, (_Bloqueador_ -> _bloqueado_ / _Seguidor_ -> _seguido_).


#### *Paypal token antes de crear transacción, se comprueba al terminar la transaccion.*

Para asegurar que la finalizacion de una compra se realizaba justo al terminar la transacción monetaria, introduzco una variable de token de autenticación, que se almacena en una cookie al crear la orden de paypal, y se comprueba que coincide con la variable enviada por post justo antes de finalizar la compra.


#### *Reventa bloqueada*

Las Copias y Productos que hayan participado en una venta finalizada, no pueden ser vendidos de nuevo.


#### *Las copias y productos en venta y retirada de inventario*

Las Copias y Productos que se encuentran en venta, tienen bloqueada la retirada del inventario del usuario y el borrado hasta que retiren la venta.


#### *Control sobre imagenes entre usuarios*

Para evitar el uso de imagenes inadecuadas, he decidido el uso de imagenes de usuario por defecto, con temáticas de videojuegos varios.

#### *Carrito de compra privado*

En uno de los requisitos funcionales, puse que los amigos pudieran verse el carrito de compra entre sí y también puse que el carrito fuera privado. Como estas dos funciones se contradicen, omití la función que permitía la visibilidad entre amigos.


#### *Simulación de claves*

Al crear la venta de primera mano de claves, al no tener un proveedor de claves de acceso a juegos digitales, cree una función que crea claves aleatorias a modo de simulación de lo que serían claves válidas.


#### *Simulación de pagos de Paypal*

El uso de paypal en esta página consiste en un proceso de transacciones simuladas, muy cercana al uso real de paypal. Uso un servicio llamado Paypal Sandbox, que consiste en pruebas con cuentas falsas, si quisieramos usar Paypal oficialmente, solo tendríamos que cambiar el enlace de pago a un servicio y cuenta reales con un enlace del servicio de Paypal Live.