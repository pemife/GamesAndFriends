<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Ventas */

$this->title = 'Poner en Venta';
$this->params['breadcrumbs'][] = ['label' => 'Ventas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ventas-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_creaVenta', [
        'listaProductosUsuarioVenta' => $listaProductosVenta,
        'listaCopiasVenta' => $listaCopiasVenta,
        'model' => $model,
    ]) ?>

</div>
