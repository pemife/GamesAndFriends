<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Ventas */

$this->title = 'Editar puesta en venta de copia: ';
$this->params['breadcrumbs'][] = ['label' => 'Ventas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';

date_default_timezone_set("Europe/Madrid");
$currentTimeStamp = date('Y-m-d H:i:s', time());
?>
<div class="ventas-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'created_at')->textInput(['readonly' => true, 'value' => $currentTimeStamp]); ?>

    <?= $form->field($model, 'copia_id')->dropDownList($listaCopiasVenta) ?>

    <?= $form->field($model, 'precio')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
