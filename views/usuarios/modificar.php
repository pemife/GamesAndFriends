<?php

use yii\bootstrap4\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Usuarios */
/* @var $form yii\widgets\ActiveForm */
// echo $model->fechanac;
// echo '<br>' . strtotime($model->fechanac);
// echo '<br>' . date('Y-m-d');
// echo '<br>' . strtotime(date('Y-m-d'));
?>

<div class="usuarios-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'readonly' => true]) ?>

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
        // echo $model->fechanac;
        // echo '<br>';
        // echo strtotime($model->fechanac);
        // echo '<br>';
        // echo date('Y-m-d');
        // echo '<br>';
        // if(strtotime($model->fechanac) > strtotime(date('Y-m-d'))){
        //     echo '<p style="color:red;">es <b>mayor</b> que la fecha de hoy</p>';
        //     echo 'fechanac: ' . strtotime($model->fechanac) . '<br>ahora: ' . strtotime(date('Y-m-d'));
        // } else {
        //     echo '<p style="color:red;">es <b>MENOR</b> que la fecha de hoy</p>';
        //     echo 'fechanac: ' . strtotime($model->fechanac) . '<br>ahora: ' . strtotime(date('Y-m-d'));
        // };
    ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
