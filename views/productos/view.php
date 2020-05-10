<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\DetailView;
use kartik\rating\StarRating as RatingStarRating;

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
                'class' => 'btn btn-danger mr-2',
                'data' => [
                    'confirm' => '¿Estas seguro de querer borrar este elemento?',
                    'method' => 'post',
                ],
            ]);
            echo Html::a('Poner en venta',
                [
                    'ventas/crea-venta-producto',
                    'productoId' => $model->id
                ],
                [
                    'class' => 'btn btn-success',
                    'hidden' => false,
                ]
            );
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
            [
                'attribute' => 'valoracion',
                'format' => 'raw',
                'value' => function ($model) {
                  return RatingStarRating::widget([
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
                        if (Yii::$app->user->id != $model->usuario->id) {
                            return "";
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
                    'delete' => function ($url, $model, $key){
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
                        if (Yii::$app->user->isGuest) {
                            return '';
                        };
                        
                        return Html::a('', ['criticas/reportar', 'cId' => $model->id], [
                            'class' => 'glyphicon glyphicon-fire',
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
