<?php
use yii\bootstrap4\Html;
use yii\helpers\Url;

?>
<h1>Bienvenid@ <?= $nombre ?></h1>

<p>Si quieres verificar tu cuenta, pincha en
    <?= Html::a(
        'este',
        Url::to(
            [
                        'usuarios/verificar',
                        'token' => $token,
                    ],
            true
        )
)?> enlace</p>

<p>
    GamesandFriends1 es una pagina de compraventa de videojuegos digitales
    de segunda mano, asi que si tienes una clave de un juego que no usas,
    ¡Aqui podrás venderlo!.
</p>
