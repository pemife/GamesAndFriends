<?php

use yii\bootstrap4\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Copias */

$this->title = $model->juego->titulo;
$this->params['breadcrumbs'][] = ['label' => 'Copias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
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

    <?php if (Yii::$app->user->id === $model->propietario_id) {
        echo Html::a('Poner en venta',
            [
                'ventas/crea-venta-item',
                'cId' => $model->id,
                'pId' => false,
            ],
            [
                'class' => 'btn btn-success mb-4',
                'hidden' => false,
            ]
        );
        }
    ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'juego.titulo',
            [
                'attribute' => 'propietario.nombre:text:Propietario',
                'label' => 'Propietario',
                'format' => 'raw',
                'value' => function ($model) {
                    if (empty($model->propietario_id)) {
                        return '<span class="text-danger">Eliminado</span>';
                    }
                    return Html::encode($model->propietario->nombre);
                }
            ],
            'plataforma.nombre:text:Plataforma',
            'estado'
        ],
    ]) ?>

</div>
