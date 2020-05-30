<?php

use kartik\select2\Select2;

use yii\bootstrap4\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Copias */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="copias-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'juego_id')->widget(Select2::className(),[
        'data' => $listaJuegos,
        'pluginOptions' => [
          'allowClear' => false,
        ]
      ]); ?>

    <?= $form->field($model, 'propietario_id')->hiddenInput([
          'readonly' => true,
          'value' => Yii::$app->user->identity->id,
    ])->label(false); ?>

    <?= $form->field($model, 'clave')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'plataforma_id')->widget(Select2::className(), [
        'data' => $listaPlataformas,
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
