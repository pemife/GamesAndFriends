<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Productos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="productos-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Crear Productos', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => [
            'itemscope' => true,
            'itemtype' => 'http://schema.org/Product',
        ],
        'columns' => [
            [
              'attribute' => 'nombre',
              'contentOptions' => ['itemprop' => 'name']
            ],
            [
              'attribute' => 'descripcion',
              'format' => 'ntext',
              'contentOptions' => ['itemprop' => 'description']
            ],
            'stock',
            [
                'attribute' => 'propietario.nombre',
                'label' => 'Propietario',
                'format' => 'raw',
                'value' => function ($model) {
                    if (empty($model->propietario_id)) {
                        return '<span class="text-danger">Eliminado</span>';
                    }
                    return Html::encode($model->propietario->nombre);
                }
            ],
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view} {update} {delete} {vermercado}',
              'buttons' => [
                'vermercado' => function ($url, $model, $key) {
                    return Html::a(
                        '<span class="glyphicon glyphicon-shopping-cart"></span>',
                        ['ventas/ventas-item', 'id' => $model->id, 'esProducto' => true],
                        ['title' => 'ver en mercado']
                    );
                },
                'update' => function ($url, $model, $key) {
                    if (!Yii::$app->user->isGuest) {
                        if (Yii::$app->user->id == $model->propietario->id) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-pencil"></span>',
                                ['ventas/update', 'id' => $model->id],
                                ['title' => 'Actualizar']
                            );
                        }
                    }

                    return '';
                },
                'delete' => function ($url, $model, $key) {
                    if (!Yii::$app->user->isGuest) {
                        if (Yii::$app->user->id == $model->propietario->id) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-trash"></span>',
                                ['ventas/delete', 'id' => $model->id],
                                [
                                  'title' => 'Eliminar',
                                  'data-method' => 'POST',
                                  'confirm' => 'Esta seguro de que quiere eliminar la venta?'
                                ]
                            );
                        }
                    }
                    return '';
                },
              ],
            ],
        ],
    ]); ?>


</div>
