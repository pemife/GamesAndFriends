<?php

use yii\bootstrap4\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Ventas */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Venta de producto';
$this->params['breadcrumbs'][] = ['label' => 'Ventas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ventas-form">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'vendedor_id')->hiddenInput([
          'readonly' => true,
          'value' => Yii::$app->user->identity->id,
    ])->label(false);
    ?>

    <?= $form->field($model, 'producto_id')->widget(Select2::className(),[
        'data' => $listaProductosVenta,
        'options' => ['placeholder' => 'Introduzca un producto de su inventario'],
        'pluginOptions' => [
          'allowClear' => false,
        ],
      ])->label('Producto a vender'); ?>

    <?= $form->field($model, 'copia_id')->hiddenInput([
            'readonly' => true,
            'value' => null,
      ])->label(false); ?>

    <?= $form->field($model, 'precio') ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
