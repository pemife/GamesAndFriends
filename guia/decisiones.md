# Decisiones adoptadas

#### **Relaciones por "estados" (relaciones unidireccionales y bidireccionales).**

Al crear las diferentes relaciones que podía haber entre usuarios, quise facilitar la manera en que se representaban este tipo de interacciones; por este motivo, añadí los diferentes estados que podían tener los usuarios. Cada estado de la relación entre dos usuarios representa un tipo, donde: *1* sería la amistad aceptada, *2* sería la petición de amistad rechazada, *3* representaría el bloqueo entre usuarios y *4* representaría una relación de seguimiento (en la que debe participar un usuario crítico de videojuegos).

Al crear las relaciones de este modo, me di cuenta de que debía tener en cuenta que las relaciones de bloqueo y de seguimiento eran distintas de las otras dos. Estos dos tipos de relaciones representan relaciones unidireccionales, mientras que las relaciones de amistad y de rechazo son bidireccionales. Esto se traducía en que debía diferenciar los roles en estos tipos relación, (_Bloqueador_ -> _bloqueado_ / _Seguidor_ -> _seguido_).


#### **Token de validación de compra.**

Para asegurar que la finalización de una compra se realizaba justo al terminar la transacción monetaria, introduzco una variable de token de autenticación -que se almacena en una cookie al crear la orden de Paypal- y se comprueba que coincide con la variable enviada por post justo antes de finalizar la compra.


#### **Reventa bloqueada**

Las copias de juegos y productos que hayan participado en una venta finalizada no pueden ser vendidos de nuevo.


#### **Las copias de juegos y productos en venta y retirada de inventario**

Las copias de juegos y productos que se encuentran en venta, tienen bloqueada tanto la acción de retirada del inventario por parte del usuario como la acción de borrado, hasta que retiren la venta.


#### **Control sobre imágenes entre usuarios**

Para evitar el uso de imágenes inadecuadas en la web, he decidido establecer imágenes de usuario por defecto relacionadas con la temática de diversos videojuegos.


#### **Carrito de compra privado**

En uno de los requisitos funcionales establecí que los amigos pudieran verse el carrito de compra entre sí, mientras que en otro de los requisitos puse que el carrito fuera privado. Como estas dos funciones se contradecían, omití la función que permitía la visibilidad del carrito de compra entre amigos.


#### **Simulación de claves**

Al permitir la venta de primera mano de claves de acceso a juegos digitales y no contar con un proveedor real de dichas claves, creé una función que genera claves aleatorias -a modo de simulación- de lo que serían claves válidas.


#### **Simulación de pagos de Paypal**

El uso de Paypal en esta página consiste en un proceso de transacciones simuladas muy cercana al uso real de Paypal. Uso un servicio llamado <<Paypal Sandbox>>, que consiste en una simulación de pagos con cuentas ficticias. Si quisiéramos usar Paypal oficialmente, solo tendríamos que cambiar el enlace de pago a un servicio y cuenta reales mediante un enlace del servicio de <<Paypal Live>>.