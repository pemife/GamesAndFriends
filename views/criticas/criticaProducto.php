<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
// use kartik\widgets\StarRating;

/* @var $this yii\web\View */
/* @var $model app\models\Criticas */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Critica de producto: ' . $producto->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Criticas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="criticas-criticaProducto">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'opinion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'valoracion')->input('range', [
            'min' => 0,
            'max' => 9,
            'style' => [
                'width' => '30%',
                'align' => 'left',
            ]
        ]) ?>

    <?= $form->field($model, 'usuario_id')->label(false)->hiddenInput(['value' => Yii::$app->user->id]) ?>

    <?= $form->field($model, 'producto_id')->label(false)->hiddenInput(['value' => $producto->id]) ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
