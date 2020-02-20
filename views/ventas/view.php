<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Ventas */

$this->title = empty($model->producto_id) ? $model->copia->juego->titulo : 'Venta de ' . $model->producto->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Ventas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="ventas-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if($model->vendedor_id == Yii::$app->user->id){
            echo Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']);

            echo Html::a('Delete', ['delete', 'id' => $model->id], [
              'class' => 'btn btn-danger',
              'data' => [
                'confirm' => '¿Estas seguro de querer borrar este elemento?',
                'method' => 'post',
              ],
            ]);
          }
        ?>
    </p>

    <?= Html::img('urlDeImagen', ['height' => 200, 'width' => 300]) ?>
    </br></br>
    <p>
      <span style="font-weight: bold;">Vendedor: </span> <?= Html::a($model->vendedor->nombre, ['usuarios/view', 'id' => $model->vendedor_id]) ?>
    </p>
    <p>
      <span style="font-weight: bold;">Precio: </span> <?= Html::encode($model->precio) ?>
    </p>
    <?php if(!Yii::$app->user->isGuest){
      echo Html::a('Comprar', ['comprar', 'id' => $model->id], [
        'class' => 'btn btn-success',
        'data' => [
          'confirm' => '¿Estas seguro de que quieres comprarlo?',
          'method' => 'post',
        ],
      ]);
    } ?>

</div>
