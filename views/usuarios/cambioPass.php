<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Cambiar contraseña del usuario ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nombre, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Cambiar Contraseña';

?>

<div class="usuarios-form">

  <h3><?= Html::encode($this->title) ?></h3>

    <?php $form = ActiveForm::begin() ?>

    <?= $form->field($model, 'nombre')->hiddenInput(['value' => $model->nombre])->label(false) ?>

    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'password_repeat')->passwordInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->hiddenInput(['value' => $model->email])->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', [
          'class' => 'btn btn-success'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
