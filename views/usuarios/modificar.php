<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Usuarios */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="usuarios-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'biografia')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'fechanac')->widget(DatePicker::classname(), [
      'options' => ['placeholder' => 'Introduzca su fecha de nacimiento'],
      'size' => 'sm',
      'pluginOptions' => [
          'autoclose'=> true,
          'format' => 'yyyy-mm-dd',
      ]
    ]) ?>

    <?php
        echo $model->fechanac;
        echo '<br>';
        echo date('d-m-y', strtotime($model->fechanac));
        echo '<br>';
        echo strtotime($model->fechanac);
        echo '<br>';
        echo date('d-m-y', strtotime(date('now')));
        echo '<br>';
        if(strtotime($model->fechanac) > date('y-m-d')){
            echo 'es mayor que la fecha de hoy';
        } else {
            echo 'es MENOR que la fecha de hoy';
        };
    ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
