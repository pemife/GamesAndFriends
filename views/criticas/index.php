<?php

use kartik\rating\StarRating;
use yii\bootstrap4\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Criticas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="criticas-index">

    <h1><?= Html::encode($this->title) ?></h1>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'usuario.nombre',
            [
                'label' => 'Item',
                'value' => function ($model, $key, $index, $column) {
                    return empty($model->juego_id) ? $model->producto->nombre : $model->juego->titulo;
                }
            ],
            'opinion:ntext',
            'last_update',
            [
                'attribute' => 'valoracion',
                'format' => 'raw',
                'value' => function ($model) {
                    return StarRating::widget([
                        'name' => 'rating_35',
                        'value' => $model->valoracion,
                        'pluginOptions' => [
                        'displayOnly' => true,
                        'size' => 'm',
                        'showCaption' => false,
                        ]
                    ]);
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {vermercado}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        $esProducto = empty($model->juego_id);
                        $itemId = $esProducto ? $model->producto_id : $model->juego_id;
                        $accion = $esProducto ? 'productos/view' : 'juegos/view';
                        return Html::a(
                            '<span class="glyphicon glyphicon-eye-open"></span>',
                            [$accion, 'id' => $itemId, 'esProducto' => $esProducto],
                            ['title' => 'ver en mercado']
                        );
                    },
                    'vermercado' => function ($url, $model, $key) {
                        $esProducto = empty($model->juego_id);
                        $itemId = $esProducto ? $model->producto_id : $model->juego_id;
                        return Html::a(
                            '<span class="glyphicon glyphicon-shopping-cart"></span>',
                            ['ventas/ventas-item', 'id' => $itemId, 'esProducto' => $esProducto],
                            ['title' => 'ver en mercado']
                        );
                    },
                ]
            ],
        ],
    ]); ?>


</div>
