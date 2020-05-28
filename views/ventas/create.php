<?php

use yii\bootstrap4\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Ventas */

$this->title = 'Poner en Venta';
$this->params['breadcrumbs'][] = ['label' => 'Ventas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ventas-create" id="formularioVentas">

    <h1><?= Html::encode($this->title) ?></h1>
    <h3>¿Que quieres vender?</h3>
    <span>
        <?= Html::a('Producto', ['ventas/crea-venta-producto', 'productoId' => 0], ['class' => 'btn btn-info' ]) ?>
        <?= Html::a('Juego', ['ventas/crea-venta-copia'], ['class' => 'btn btn-info']) ?>
    </span>

</div>
