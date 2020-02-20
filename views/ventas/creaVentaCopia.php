<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Ventas */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ventas-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'vendedor_id')->hiddenInput([
          'readonly' => true,
          'value' => Yii::$app->user->identity->id,
    ])->label(false);
    ?>

    <?= $form->field($model, 'copia_id')->widget(Select2::className(),[
        'data' => $listaCopiasVenta,
        'options' => ['placeholder' => 'Introduzca una copia'],
        'pluginOptions' => [
          'allowClear' => false,
        ],
      ])->label('Copia a vender'); ?>

    <?= $form->field($model, 'producto_id')->hiddenInput([
            'readonly' => true,
            'value' => null,
      ])->label(false); ?>

    <?= $form->field($model, 'precio') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
