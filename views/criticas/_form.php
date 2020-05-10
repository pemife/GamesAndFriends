<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
// use kartik\widgets\StarRating;

/* @var $this yii\web\View */
/* @var $model app\models\Criticas */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="criticas-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'opinion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'created_at')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'valoracion')->input('range', [
            'min' => 0,
            'max' => 9,
            'style' => [
                'width' => '30%',
                'align' => 'left',
            ]
        ]) ?>

    <?= $form->field($model, 'usuario_id')->textInput()->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'producto_id')->widget(Select2::className(), [
        'data' => $listaProductosUsuario,
        'pluginOptions' => [
          'allowClear' => false,
        ]
      ]); ?>

    <?= $form->field($model, 'juego_id')->textInput()->hiddenInput()->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
