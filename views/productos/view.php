<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;
use yii\widgets\DetailView;
use kartik\rating\StarRating;

/* @var $this yii\web\View */
/* @var $model app\models\Productos */

$this->title = $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Productos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<!-- Dependencia de krajee starrating -->
<script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js" crossorigin="anonymous"></script>

<div class="productos-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->propietario_id == Yii::$app->user->id) {
            echo Html::a('Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary mr-2']);
            echo Html::a('Borrar', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger mr-2',
                'data' => [
                    'confirm' => '¿Estas seguro de querer borrar este elemento?',
                    'method' => 'post',
                ],
            ]);
            echo Html::a('Poner en venta',
                [
                    'ventas/crea-venta-item',
                    'cId' => false,
                    'pId' => $model->id
                ],
                [
                    'class' => 'btn btn-success',
                    'hidden' => false,
                ]
            );
        } ?>
    </p>

    <?= Html::img($model->urlImagen, ['height' => 200, 'width' => 300]) ?>

    </br></br>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'descripcion:text',
            [
                'attribute' => 'propietario.nombre',
                'label' => 'Propietario',
                'format' => 'raw',
                'value' => function ($model) {
                    if (!empty($model->propietario_id)) {
                        return Html::a(
                            $model->propietario->nombre,
                            ['usuarios/view', 'id' => $model->propietario->id]
                        );
                    }
                    return '';
                }
            ]
        ],
    ]) ?>

    <h3>Críticas</h3>

    <p>
        <?php if ($tieneProducto) {
            echo Html::a('Opinar', ['criticas/critica-producto', 'producto_id' => $model->id], ['class' => 'btn btn-success']);
        } ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{like}',
                'visible' => !Yii::$app->user->isGuest,
                'buttons' => [
                    'like' => function ($url, $model, $key) {
                        if (empty($model->usuario_id)) {
                            return '';
                        }
                        if (Yii::$app->user->isGuest || Yii::$app->user->id == $model->usuario->id) {
                            return '';
                        }

                        return Html::a(
                            '<span class="glyphicon glyphicon-thumbs-up"></span>',
                            ['criticas/reportar', 'cId' => $model->id, 'esVotoPositivo' => true],
                            ['title' => 'me gusta']
                        );
                    }
                ]
            ],
            [
                'attribute' => 'usuario.nombre',
                'label' => 'Propietario',
                'format' => 'raw',
                'value' => function ($model) {
                    if (empty($model->usuario_id)) {
                        return '<span class="text-danger">Eliminado</span>';
                    }
                    return Html::encode($model->usuario->nombre);
                }
            ],
            'opinion',
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
                'template' => '{update} {delete} {reportar}',
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        if (empty($model->usuario_id)) {
                            return '';
                        }
                        if (Yii::$app->user->id != $model->usuario->id) {
                            return '';
                        }
                        return Html::a(
                            '<span class="glyphicon glyphicon-pencil"></span>',
                            [
                                '/criticas/update',
                                'id' => $model->id,
                            ],
                            [
                                'title' => 'editar crítica',
                            ]
                        );
                    },
                    'delete' => function ($url, $model, $key) {
                        if (empty($model->usuario_id)) {
                            return '';
                        }
                        if (Yii::$app->user->id != $model->usuario->id) {
                            return '';
                        }
                        return Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
                            [
                                'criticas/delete',
                                'id' => $model->id,
                            ],
                            [
                                'data' => [
                                  'method' => 'post',
                                  'confirm' => '¿Estas seguro de borrar la crítica?(Esta accion no se puede deshacer)',
                                ],
                                'title' => 'borrar crítica',
                            ]
                        );
                    },
                    'reportar' => function ($url, $model, $action) {
                        if (empty($model->usuario_id)) {
                            return '';
                        }
                        if (Yii::$app->user->isGuest) {
                            return '';
                        };
                        
                        return Html::a('', ['criticas/reportar', 'cId' => $model->id, 'esVotoPositivo' => false], [
                            'class' => 'glyphicon glyphicon-exclamation-sign',
                            'title' => 'Reportar critica',
                            'style' => [
                                'color' => 'red',
                            ],
                            'data-confirm' => '¿Confirmas querer reportar la crítica?',
                        ]);
                    }
                ]
            ],
        ]
    ]); ?>

</div>
