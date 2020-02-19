<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Ventas */

$this->title = 'Poner en Venta';
$this->params['breadcrumbs'][] = ['label' => 'Ventas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$js = <<<SCRIPT

$(document).ready(function(){
  // $('#ventaDeProducto').hide();
});

SCRIPT;
$this->registerJS($js);
?>
<div class="ventas-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'vendedor_id')->hiddenInput([
          'readonly' => true,
          'value' => Yii::$app->user->identity->id,
    ])->label(false);
    ?>

    <div id="ventaDeProducto">
      <?= $form->field($model, 'producto_id')->widget(Select2::className(),[
        'data' => $listaProductosVenta,
        'options' => [
          'placeholder' => 'Introduzca un producto',
        ],
        'pluginOptions' => [
          'allowClear' => false,
        ],
        ])->label('Producto a vender'); ?>
    </div>

    <div id="ventaDeCopia">
      <?= $form->field($model, 'copia_id')->widget(Select2::className(),[
        'data' => $listaCopiasVenta,
        'options' => ['placeholder' => 'Introduzca una copia'],
        'pluginOptions' => [
          'allowClear' => false,
        ]
        ])->label('Copia a vender'); ?>
    </div>

    <?= $form->field($model, 'precio') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
