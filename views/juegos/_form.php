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

    <?php echo "";
        // echo $form->field($generos)->widget(Select2::classname(), [
        //     'name' => 'Generos',
        //     'data' => $generosProvider,
        //     'size' => Select2::SMALL,
        //     'options' => ['placeholder' => 'Introduce los géneros del juego', 'multiple' => true],
        //     'pluginOptions' => [
        //         'allowClear' => false,
        //         'tags' => true,
        //     ],
        // ]);
    ?>

    <?= $form->field($model, 'dev')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'publ')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cont_adul')->checkbox(['checked' => false]) ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
