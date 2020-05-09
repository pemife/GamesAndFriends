<?php

use kartik\select2\Select2;

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Posts */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="posts-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'titulo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'media')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'desarrollo')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'juego_id')->widget(Select2::className(), [
        'data' => $listaJuegos,
        'options' => ['placeholder' => 'Introduzca el juego del que trata el post'],
        'pluginOptions' => [
          'allowClear' => false,
        ],
      ])->label('Juego del post'); ?>

    <?= $form->field($model, 'usuario_id')->hiddenInput([
      'readonly' => true,
      'value' => Yii::$app->user->identity->id,
      ])->label(false);
      ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
