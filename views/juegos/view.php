<?php

use yii\bootstrap4\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Juegos */

$this->title = $model->titulo;
$this->params['breadcrumbs'][] = ['label' => 'Juegos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="juegos-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <span>
      <?= Html::img('urlDeImagen', ['height' => 200, 'width' => 300]) ?>
      <h3>En venta desde <?= Html::encode($precioMinimo) ?>€</h3>
      <?= Html::a('Ver en mercado', ['ventas/ventas-juego', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </span>

    </br></br>

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

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'descripcion:ntext',
            'fechalan',
            'dev',
            'publ',
        ],
    ]) ?>


</div>
