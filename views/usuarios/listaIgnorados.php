<?php
// use yii\helpers\Html;

use yii\helpers\Html;
use yii\widgets\ListView;

$this->title = 'Lista de juegos ignorados';
?>

<h1>Lista de ignorados de <?= $usuario->nombre ?></h1>

<div id="listaJuegos">

    <?= ListView::widget([
        'dataProvider' => $ignoradosProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
            $urlImagen = 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fcdn.icon-icons.com%2Ficons2%2F510%2FPNG%2F512%2Fgame-controller-b_icon-icons.com_50382.png&f=1&nofb=1¡';
            ?>
            <h3><?= $model->juego->titulo ?></h3>
            <div class="row">
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
                            'class' => 'glyphicon glyphicon-shopping-cart',
                            'title' => 'ver en mercado',
                        ]
                    ) ?>
                <?php
                if ($model->usuario->id == Yii::$app->user->id) {
                    echo Html::a(
                        '',
                        [
                            'usuarios/borrar-ignorados',
                            'jId' => $model->juego->id,
                            'uId' => Yii::$app->user->id
                        ],
                        [
                            'class' => 'glyphicon glyphicon-remove-circle',
                            'title' => 'Borrar de tu lista de ignorados',
                            'data-confirm' => '¿Estas seguro de que quieres borrarlo?',
                        ]
                    );
                }
                ?>
                </div>
            </div>
            <hr>
            <?php
        },
        ]) ?>
</div>
