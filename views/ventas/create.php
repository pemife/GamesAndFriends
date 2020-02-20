<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Ventas */

$this->title = 'Poner en Venta';
$this->params['breadcrumbs'][] = ['label' => 'Ventas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ventas-create" id="formularioVentas">

    <h1>¿Que quieres vender?</h1>
    <span>
        <?= Html::a('Producto', '/ventas/crea-venta-producto/', ['class' => 'btn btn-info' ]) ?>
        <?= Html::a('Juego', '/ventas/crea-venta-copia/', ['class' => 'btn btn-info']) ?>
    </span>

</div>
