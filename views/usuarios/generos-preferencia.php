<?php

use kartik\select2\Select2;
use yii\bootstrap4\Html;
use yii\widgets\ActiveForm;
?>

<div class="usuarios-form">

    <?php $form = ActiveForm::begin(); ?>

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

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>