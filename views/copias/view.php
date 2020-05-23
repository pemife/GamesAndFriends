<?php

use kartik\select2\Select2;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Copias */

$this->title = $model->juego->titulo;
$this->params['breadcrumbs'][] = ['label' => 'Copias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$url = Url::to(['usuarios/array-amigos']);
$url2 = Url::to(['copias/regalar-copia']);
$url3 = Url::to(['usuarios/view', 'id' => Yii::$app->user->id]);
$copiaId = $model->id;
$js = <<<SCRIPT
$(function() {
});
$('#botonModal').click(function(e){
    e.preventDefault();
    $.ajax({
        method: 'GET',
        url: '$url',
        data: {},
        success: function(result){
          if (result) {
            if (result.length == 0) {
                alert('No tienes amigos con los que hacer regalos!');
            } else {
                result.forEach(function(amigo, index) {
                    $('#selectAmigos').append(
                        '<option value="' + amigo['id'] + '">' + amigo['nombre'] + '</option>'
                    );
                });
                $('#modalRegalo').modal('show');
            }
          } else {
            alert('Ha ocurrido un error con el menú de regalos');
          }
        }
    });
});

$('#botonRegalar').click(function(e){
    e.preventDefault();
    var uId = $('#selectAmigos').val();
    var cId = $copiaId;
    $.ajax({
        method: 'POST',
        url: '$url2',
        data: {cId: cId, uId: uId},
        success: function(result){
          if (result) {
            window.location = '$url3';
          } else {
            alert('Ha ocurrido un error con el menú de regalos');
          }
        }
    });
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
            <hr>
            <h3>Selecciona un amigo:</h3>
            <?php
            echo Html::dropDownList('listaAmigos', null, [], ['class' => 'dropdown', 'id' => 'selectAmigos']);
            ?>
            <hr>
            <?= Html::a('Regalar', '#', ['class' => 'btn btn-success', 'id' => 'botonRegalar']) ?>
        </div>

    <?php
    Modal::end();
    ?>

</div>
