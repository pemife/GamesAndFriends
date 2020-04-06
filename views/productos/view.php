<?php

use yii\helpers\Html;
use yii\widgets\ListView;

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
          echo Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary mr-2']);
          echo Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
              'confirm' => '¿Estas seguro de querer borrar este elemento?',
              'method' => 'post',
            ],
          ]);
        } ?>
    </p>

    <?= Html::img('urlDeImagen', ['height' => 200, 'width' => 300]) ?>
    <p>
      <span style="font-weight: bold;">Descripcion:</span>
      </br>
      <?= Html::encode($model->descripcion) ?>
    </p>
    <p>
      <span style="font-weight: bold;">Propietario:</span>
      </br>
      <?= Html::a($model->propietario->nombre, ['usuarios/view', 'id' => $model->propietario_id]) ?>
    </p>

    <h3>Críticas</h3>

    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_productosCriticas'
    ]); ?>

</div>
