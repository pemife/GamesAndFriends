<?php

use yii\bootstrap4\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CriticasSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="criticas-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'opinion') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'valoracion') ?>

    <?= $form->field($model, 'usuario_id') ?>

    <?php // echo $form->field($model, 'producto_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
