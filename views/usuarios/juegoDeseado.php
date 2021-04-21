<?php

use yii\bootstrap4\Html;

$urlImagen = 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fcdn.icon-icons.com%2Ficons2%2F510%2FPNG%2F512%2Fgame-controller-b_icon-icons.com_50382.png&f=1&nofb=1¡';
?>

<h3><?= $model->juego->titulo ?></h3>
<div>
    
</div>
<div class="row">
    <div class="col-md-1">
        <h1><?= $model->orden ?></h1>
    </div>
    <div class="col-md-3">
        <img src="<?= $urlImagen ?>" width="150" height="125">
    </div>
    <div class="col-md-7">
        <p><?= $model->juego->descripcion ?></p>
    </div>
    <div class="col-md-1">
        <?= Html::a(
            '',
            [
                'ventas/ventas-item',
                'id' => $model->juego->id,
                'esProducto' => false
            ],
            [
                'class' => 'fas fa-shopping-cart',
                'title' => 'ver en mercado',
            ]
        ) ?>
        <?php
        if ($usuario->id == Yii::$app->user->id) {
            echo Html::a(
                '',
                [
                    'usuarios/borrar-deseos',
                    'jId' => $model->juego->id,
                    'uId' => Yii::$app->user->id
                ],
                [
                    'class' => 'fas fa-times-circle',
                    'title' => 'Borrar de tu lista de deseados',
                    'data-confirm' => '¿Estas seguro de que quieres borrarlo?',
                ]
            );
        }
        ?>
    </div>
</div>
<hr>