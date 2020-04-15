<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Usuarios */

$this->title = 'Añadir item a inventario';
$this->params['breadcrumbs'][] = ['label' => 'Usuario', 'url' => ['view']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="usuarios-anadir-inventario">

    <h1><?= Html::encode($this->title) ?></h1>
    <h3>¿Que quieres añadir a tu inventario?</h3>
    <span>
        <?= Html::a('Producto', ['productos/create'], ['class' => 'btn btn-info' ]) ?>
        <?= Html::a('Juego', ['copias/create'], ['class' => 'btn btn-info']) ?>
    </span>

</div>
