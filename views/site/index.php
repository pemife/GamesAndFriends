<?php

/* @var $this yii\web\View */

use yii\bootstrap4\Html;

$this->title = 'GamesAndFriends';
?>
<div class="site-index">

    <div class="jumbotron">
        <img src="<?= $urlLogo ?>" alt="">

        <h1 class="mt-4">GAMES AND FRIENDS</h1>

        <p class="lead">Bienvenido a tu página de compra-venta de claves favorita.</p>

        <p><?= Html::a('¡Click aquí para ver las novedades!', ['juegos/novedades'], ['class' => 'btn btn-lg btn-success']) ?></p>
    </div>

    <div class="body-content">

        <div class="row text-justify">
            <div class="col-md-4">
                <h2 class="text-center mb-4">Juegos</h2>

                <p>
                    ¡Echa un vistazo a nuestra lista de juegos en venta!<br><br>Tenemos juegos para todas las plataformas y edades. Escoge tu
                    género favorito y... ¡compra el juego que desees al mejor precio!
                </p>

                <p class="text-center"><?= Html::a('Juegos', ['juegos/index'], ['class' => 'btn btn-primary']) ?></p>
            </div>
            <div class="col-md-4">
                <h2 class="text-center mb-4">Posts</h2>

                <p>
                    ¡Entra en la comunidad de GamesAndFriends!<br><br>Comenta tus juegos favoritos con tus mejores amigos, crea una guía 
                    para los jugadores más novatos o comparte las mejores maneras de jugar.<br><br>Opina sobre los juegos que tengas y si te votan
                    lo suficiente... ¡podrás ser crítico de juegos aquí en GamesAndFriends!
                </p>

                <p class="text-center"><?= Html::a('Posts', ['posts/index'], ['class' => 'btn btn-primary']) ?></p>
            </div>
            <div class="col-md-4">
                <h2 class="text-center mb-4">Productos</h2>

                <p>
                    ¡Compra merchandising de tus juegos favoritos!<br><br>¡Aquí en GamesAndFriends encontrarás lo que buscas! 
                    Consulta el repertorio de productos que están vendiendo nuestros usuarios. ¡Seguro que algo llama tu atención!
                </p>

                <p class="text-center"><?= Html::a('Productos', ['productos/index'], ['class' => 'btn btn-primary']) ?></p>
            </div>
        </div>

    </div>
</div>
