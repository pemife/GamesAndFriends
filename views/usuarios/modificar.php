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

    <?= $form->field($model, 'pay_token')->textInput(['maxlength' => true]) ?>
    <p>Para mas información sobre PayPal Sandbox pinche <a href="https://pemife.github.io/GamesAndFriends/manual.html">este</a> enlace</p>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
