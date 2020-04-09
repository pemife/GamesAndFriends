<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Productos */

$this->title = $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Productos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="productos-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if($model->propietario_id == Yii::$app->user->id){
          echo Html::a('Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary mr-2']);
          echo Html::a('Borrar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
              'confirm' => '¿Estas seguro de querer borrar este elemento?',
              'method' => 'post',
            ],
          ]);
        } ?>
    </p>

    <?= Html::img('urlDeImagen', ['height' => 200, 'width' => 300]) ?>

    </br></br>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'descripcion:text',
            [
              'attribute' => 'propietario.nombre',
              'label' => 'Propietario',
              'format' => 'raw',
              'value' => Html::a(
                $model->propietario->nombre,
                ['usuarios/view', 'id' => $model->propietario->id]
              ),
            ]
        ],
    ]) ?>

    <h3>Críticas</h3>

    <p>
        <?php if($tieneProducto){
          echo Html::a('Opinar', ['criticas/critica-producto', 'producto_id' => $model->id], ['class' => 'btn btn-success']);
        } ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'usuario.nombre',
            'opinion',
            'valoracion',
        ]
    ]); ?>

</div>
