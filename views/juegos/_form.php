<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Juegos */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="juegos-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'titulo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descripcion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'fechalan')->widget(DatePicker::classname(), [
      'options' => ['placeholder' => 'Introduzca fecha de lanzamiento'],
      'size' => 'sm',
      'pluginOptions' => [
          'autoclose'=> true,
          'format' => 'yyyy-mm-dd'
      ]
    ]) ?>

    <?= $form->field($model, 'etiquetas')->widget(Select2::classname(), ([
        'name' => 'Generos',
        'data' => $generosArray,
        'size' => Select2::SMALL,
        'options' => ['placeholder' => 'Introduce los géneros del juego', 'multiple' => true],
        'pluginOptions' => [
            'allowClear' => false,
            'tags' => true,
        ],
    ]))->label('Géneros') ?>

    <?= $form->field($model, 'dev')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'publ')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'edad_minima')->widget(Select2::classname(), ([
        'name' => 'Generos',
        'data' => $edadesValidas,
        'size' => Select2::SMALL,
        'options' => ['placeholder' => 'Introduce la edad minima'],
        'pluginOptions' => [
            'allowClear' => false,
        ],
    ]))->label('Edad mínima') ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
