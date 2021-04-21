<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;

use yii\helpers\Url;

use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Mercado de segunda mano';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ventas-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Poner en venta', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <h1>Juegos</h1>

    <?= GridView::widget([
        'dataProvider' => $copiasProvider,
        'rowOptions' => [
            'itemscope' => true,
            'itemtype' => 'https://schema.org/VideoGame',
        ],
        'columns' => [
            [
                'attribute' => 'copia.juego.titulo',
                'contentOptions' => ['itemprop' => 'name'],
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::encode($model->copia->juego->titulo) . '<br>' . Html::a(
                        Html::img($model->copia->juego->urlImagen, ['class' => 'mt-2', 'height' => 85, 'width' => 170, 'alt' => $model->copia->juego->titulo]),
                        ['ventas/view', 'id' => $model->id]
                    );
                }
            ],
            [
                'attribute' => 'vendedor.nombre',
                'label' => 'Vendedor',
                'format' => 'raw',
                'value' => function ($model) {
                    if (empty($model->vendedor_id)) {
                        return '<span class="text-danger">Eliminado</span>';
                    }
                    return Html::a($model->vendedor->nombre, ['usuarios/view', 'id' => $model->vendedor_id]);
                }
            ],
            'created_at:RelativeTime:En venta desde',
            [
                'label' => 'Generos',
                'value' => function ($model) {
                    return Html::encode(implode(', ', $model->copia->juego->generosNombres()));
                },
                'contentOptions' => ['itemprop' => 'genre']
            ],
            [
                'attribute' => 'precio',
                'contentOptions' => ['itemprop' => 'price'],
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::encode(
                        Yii::$app->formatter->asCurrency(
                            round($model->precio, 3),
                            'EUR',
                            [
                                NumberFormatter::ROUNDING_MODE => 2
                            ]
                        )
                    );
                }
            ],
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view} {vermercado} {update} {delete}',
              'buttons' => [
                'vermercado' => function ($url, $model, $key) {
                    return Html::a(
                        '<span class="fas fa-shopping-cart"></span>',
                        ['ventas/ventas-item', 'id' => $model->copia->juego->id, 'esProducto' => false],
                        ['title' => 'ventas de ' . $model->copia->juego->titulo]
                    );
                },
                'update' => function ($url, $model, $key) {
                    if (empty($model->vendedor_id)) {
                        return '';
                    }
                    if (Yii::$app->user->id == $model->vendedor->id) {
                        return Html::a(
                            '<span class="fas fa-pencil-alt"></span>',
                            ['ventas/update', 'id' => $model->id],
                            ['title' => 'Actualizar']
                        );
                    }
                    return '';
                },
                'delete' => function ($url, $model, $key) {
                    if (empty($model->vendedor_id)) {
                        return '';
                    }
                    if (Yii::$app->user->id == $model->vendedor->id) {
                        return Html::a(
                            '<span class="fas fa-trash-alt"></span>',
                            ['ventas/delete', 'id' => $model->id],
                            [
                              'title' => 'Eliminar',
                              'data-method' => 'POST',
                              'confirm' => 'Esta seguro de que quiere eliminar la venta?'
                            ]
                        );
                    }
                    return '';
                },
              ]
            ],
          ],
    ]); ?>

    <?= LinkPager::widget([
      'pagination' => $copiasProvider->getPagination(),
    ]); ?>

    <h1>Productos</h1>

    <?= Gridview::widget([
        'dataProvider' => $productosProvider,
        'rowOptions' => [
            'itemscope' => true,
            'itemtype' => 'http://schema.org/Product',
        ],
        'columns' => [
            [
                'attribute' => 'producto.nombre',
                'contentOptions' => ['itemprop' => 'name']
            ],
            [
                'attribute' => 'vendedor.nombre',
                'label' => 'Vendedor',
                'format' => 'raw',
                'value' => function ($model) {
                    if (empty($model->vendedor_id)) {
                        return '<span class="text-danger">Eliminado</span>';
                    }
                    return Html::a($model->vendedor->nombre, ['usuarios/view', 'id' => $model->vendedor_id]);
                }
            ],
            'created_at:RelativeTime:En venta desde',
            [
                'attribute' => 'precio',
                'contentOptions' => ['itemprop' => 'price']
            ],
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view} {vermercado} {update} {delete}',
              'buttons' => [
                'vermercado' => function ($url, $model, $key) {
                    return Html::a(
                        '<span class="fas fa-shopping-cart"></span>',
                        ['ventas/ventas-item', 'id' => $model->producto->id, 'esProducto' => true],
                        ['title' => 'ventas de ' . $model->producto->nombre]
                    );
                },
                'update' => function ($url, $model, $key) {
                    if (empty($model->vendedor_id)) {
                        return '';
                    }
                    if (Yii::$app->user->id == $model->vendedor->id) {
                        return Html::a(
                            '<span class="fas fa-pencil-alt"></span>',
                            ['ventas/update', 'id' => $model->id],
                            ['title' => 'Actualizar']
                        );
                    }
                },
                'delete' => function ($url, $model, $key) {
                    if (empty($model->vendedor_id)) {
                        return '';
                    }
                    if (Yii::$app->user->id == $model->vendedor->id) {
                        return Html::a(
                            '<span class="fas fa-trash-alt"></span>',
                            ['ventas/delete', 'id' => $model->id],
                            [
                              'title' => 'Eliminar',
                              'data-method' => 'POST',
                              'confirm' => 'Esta seguro de que quiere eliminar la venta?'
                            ]
                        );
                    }
                    return '';
                },
              ]
            ],
        ],
    ]); ?>

</div>
