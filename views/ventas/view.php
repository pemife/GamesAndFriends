<?php

use yii\bootstrap4\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Ventas */

$this->title = empty($model->producto_id) ? $model->copia->juego->titulo : 'Venta de ' . $model->producto->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Ventas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="ventas-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php
        if ($model->vendedor_id == Yii::$app->user->id) {
            echo Html::a('Retirar del mercado', ['delete', 'id' => $model->id], [
              'class' => 'btn btn-danger',
              'data' => [
                'confirm' => '¿Estas seguro de querer borrar este elemento?',
                'method' => 'post',
              ],
            ]);
        }
        ?>
    </p>

    <?= Html::img(empty($model->producto_id) ? $model->copia->juego->urlImagen : $model->producto->urlImagen, ['height' => 200, 'width' => 300]) ?>
    </br></br>
    <p>
        <span style="font-weight: bold;">Vendedor: </span>
        <?= Html::a(
            $model->vendedor ? ($model->vendedor->nombre) : 'Eliminado',
            $model->vendedor ? [
              'usuarios/view',
              'id' => $model->vendedor_id
            ] : 'javascript:void(0)'
        ) ?>
    </p>
    <p>
        <span style="font-weight: bold;">Precio: </span>
        <?= Html::encode(
            Yii::$app->formatter->asCurrency(
                round($model->precio, 3),
                'EUR',
                [
                    NumberFormatter::ROUNDING_MODE => 2
                ]
            )
        ) ?>
    </p>
    <?php if (!Yii::$app->user->isGuest && Yii::$app->user->id != $model->vendedor_id) {
        echo Html::a('Solicitar compra', ['solicitar-compra', 'idVenta' => $model->id], [
          'class' => 'btn btn-success',
        ]);
    } ?>

</div>