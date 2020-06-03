<?php

use yii\bootstrap4\Html;
use yii\widgets\ActiveForm;
use kartik\rating\StarRating as RatingStarRating;

/* @var $this yii\web\View */
/* @var $model app\models\Criticas */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Critica de producto: ' . $producto->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Criticas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- Dependencia de krajee starrating -->
<script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js" crossorigin="anonymous"></script>

<div class="criticas-criticaProducto">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'opinion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'valoracion')->widget(RatingStarRating::classname(), [
        'pluginOptions' => [
            'step' => 1,
            'min' => 0,
            'max' => 5,
            'starCaptions' => [
                1 => '1 Estrella',
                2 => '2 Estrellas',
                3 => '3 Estrellas',
                4 => '4 Estrellas',
                5 => '5 Estrellas',
            ],
            'starCaptionClasses' => [
                1 => 'text-danger',
                2 => 'text-warning',
                3 => 'text-info',
                4 => 'text-primary',
                5 => 'text-success'
            ],
            ]
    ]); ?>

    <?= $form->field($model, 'usuario_id')->label(false)->hiddenInput(['value' => Yii::$app->user->id]) ?>
    <?= $form->field($model, 'producto_id')->label(false)->hiddenInput(['value' => $producto->id]) ?>
    <?= $form->field($model, 'juego_id')->label(false)->hiddenInput(['value' => null]) ?>
    <?= $form->field($model, 'last_update')->label(false)->hiddenInput(['value' => date('Y-m-d')]) ?>


    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
