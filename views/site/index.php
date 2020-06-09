<?php

/* @var $this yii\web\View */

use yii\bootstrap4\Html;

$this->title = 'GamesAndFriends';
?>
<div class="site-index">

    <div class="jumbotron">
        <img src="<?= $urlLogo ?>" alt="">

        <h1 class="mt-4">GAMES AND FRIENDS</h1>

        <p class="lead">Esta es Games And Friends, tu página de compra/venta de claves favorita.</p>

        <p><?= Html::a('¡Click aquí para ver las novedades!', ['juegos/novedades'], ['class' => 'btn btn-lg btn-success']) ?></p>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-xl-4">
                <h2>Juegos</h2>

                <p>
                    ¡Echa un vistazo a nuestra lista de juegos en venta!<br>Tenemos juegos para todas las plataformas. Echa un vistazo a tu
                    género favorito, y... ¡Compra el juego que desees en la mejor página de compra/venta de claves!
                </p>

                <p><?= Html::a('Juegos', ['juegos/index'], ['class' => 'btn btn-primary']) ?></p>
            </div>
            <div class="col-xl-4">
                <h2>Posts</h2>

                <p>
                    ¡Entra en la comunidad de GamesAndFriends!<br>Comenta tus juegos favoritos con tus mejores amigos, crea una guía de estos
                    para los jugadores mas novatos, o comenta las mejores maneras de jugar.<br>Opina sobre los juegos que tengas, y si te votan
                    lo suficiente... ¡Podrás ser crítico de juegos aquí en GamesAndFriends!
                </p>

                <p><?= Html::a('Posts', ['posts/index'], ['class' => 'btn btn-primary']) ?></p>
            </div>
            <div class="col-xl-4">
                <h2>Productos</h2>

                <p>
                    ¡Compra merchandising de tus juegos favoritos!<br>¡Aquí en GamesAndFriends tenemos de todo! Echa un vistazo al repertorio
                    de productos que estan vendiendo los usuarios. ¡Seguro que algo te convence!
                </p>

                <p><?= Html::a('Productos', ['productos/index'], ['class' => 'btn btn-primary']) ?></p>
            </div>
        </div>

    </div>
</div>
