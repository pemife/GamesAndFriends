<?php

use kartik\select2\Select2;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Dropdown;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Copias */

$this->title = $model->juego->titulo;
$this->params['breadcrumbs'][] = ['label' => 'Copias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$js = <<<SCRIPT
$(function() {
});
$('#botonModal').click(function(){
    $('#modalRegalo').modal('show');
});
SCRIPT;

$this->registerJs($js);
?>
<div class="copias-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->user->id === 1) : ?>
      <p>
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary mr-2']) ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '¿Estas seguro de querer borrar este elemento?',
                    'method' => 'post',
                ],
            ]) ?>
      </p>
    <?php endif ?>

    <?php
    if (Yii::$app->user->id == $model->propietario->id) {
        echo Html::a('Regalar Copia', '#', [
            'class' => 'btn btn-success mb-3',
            'id' => 'botonModal',
        ]);
    }
    ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'juego.titulo',
            'propietario.nombre:text:Propietario',
            'plataforma.nombre:text:Plataforma',
            // 'enVenta',
        ],
    ]) ?>

    <?php
    Modal::begin([
        'id' => 'modalRegalo',
    ]);
    ?>

        <div id="contenidoModal">
            <h1>Regalar copia</h1>
            <?php
            $form = ActiveForm::begin([
                'method' => 'POST',
                'action' => 'regalar-copia'
            ]);

            $form->field($regalo, 'recipiente')->widget(Select2::className(), [
                
            ])
            ?>
        </div>

    <?php
    Modal::end();
    ?>

</div>
