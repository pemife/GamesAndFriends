<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Ventas */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ventas-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php

      // var_dump($listaProductosVenta);

    ?>

    <?= $form->field($model, 'vendedor_id')->hiddenInput([
          'readonly' => true,
          'value' => Yii::$app->user->identity->id,
    ])->label(false);
    ?>

    <?= $form->field($model, 'producto_id')->dropDownList($listaProductosVenta)->label('Producto a vender') ?>

    <?= $form->field($model, 'copia_id')->dropDownList($listaCopiasVenta)->label('Copia a vender') ?>

    <?= $form->field($model, 'precio') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
