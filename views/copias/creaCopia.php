<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Copias */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="copias-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'juego_id')->dropDownList($listaJuegos)->label('Juego a vender') ?>

    <?= $form->field($model, 'poseedor_id')->hiddenInput([
          'readonly' => true,
          'value' => Yii::$app->user->identity->id,
    ])->label(false); ?>

    <?= $form->field($model, 'clave')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'plataforma_id')->dropDownList($listaPlataformas) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
