<?php

use yii\bootstrap4\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use kartik\rating\StarRating as RatingStarRating;
use yii\bootstrap4\Modal;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $model app\models\Juegos */

$this->title = $model->titulo;
$this->params['breadcrumbs'][] = ['label' => 'Juegos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

// Valores falsos para javascript
$usuarioHaCriticado = 0;
$tieneJuegoJs = $tieneJuego ? true : 0;

if (!Yii::$app->user->isGuest) {
    if ($tieneJuego) {
        foreach ($criticasProvider->getModels() as $critica) {
            if ($critica->usuario->id == Yii::$app->user->id) {
                $usuarioHaCriticado = true;
                break;
            }
        }
    }
}

$js = <<<SCRIPT
$(function() {
    if ($tieneJuegoJs && !$usuarioHaCriticado) {
        setTimeout(function() {
            $('#modalCritica').modal('show');
        }, 3000);
    }
});
SCRIPT;

$this->registerJs($js);
?>
<div class="juegos-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <span>
        <?= Html::img('urlDeImagen', ['height' => 200, 'width' => 300]) ?>
        <?php
        if ($precioMinimo != null) {
            ?>
                <h3>En venta desde <?= Html::encode($precioMinimo) ?>€</h3>
            <?php
        } else {
            ?>
                <h3>No hay ninguna copia en venta actualmente</h3>
            <?php
        }
        ?>
        <p>Valoraciones Positivas Globales: <?= Html::encode($valPosGlob) ?></p>
        <p>Valoraciones Positivas Recientes: <?= Html::encode($valPosRec) ?></p>
        <?= Html::a(
            'Ver en mercado',
            [
              'ventas/ventas-item',
              'id' => $model->id,
              'esProducto' => false
            ],
            ['class' => 'btn btn-success mr-2']
        ) ?>
        <?php
        if (!Yii::$app->user->isGuest) {
              echo Html::a(
                  'Añadir a lista de deseados',
                  [
                    'usuarios/anadir-deseos',
                    'uId' => Yii::$app->user->id,
                    'jId' => $model->id
                  ],
                  ['class' => 'btn btn-info',]
              );
        }
        ?>
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
            'fechalan:date',
            'dev',
            'publ',
            'cont_adul:boolean',
            'edad_minima',
            [
                'attribute' => 'etiquetas',
                'label' => 'Generos',
                'value' => Html::encode(implode(', ', $model->generosNombres())),
            ]
        ],
    ]) ?>

    <h3>Críticas</h3>

    <p>
        <?php
        if ($tieneJuego) {
            echo Html::a('Opinar', ['criticas/critica-juego', 'juego_id' => $model->id], ['class' => 'btn btn-success']);
        }
        ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $criticasProvider,
        'columns' => [
            'usuario.nombre',
            'opinion',
            [
              'attribute' => 'valoracion',
              'format' => 'raw',
              'value' => function ($model) {
                return RatingStarRating::widget([
                  'name' => 'rating_35',
                  'value' => Html::encode($model->valoracion),
                  'pluginOptions' => [
                    'displayOnly' => true,
                    'size' => 'm',
                    'showCaption' => false,
                  ]
                ]);
              }
            ],
            'last_update:Date',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete} {reportar}',
                'buttons' => [
                    'update' => function ($url, $model, $key) {
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
                    // https://www.w3schools.com/bootstrap/bootstrap_modal.asp
                    'reportar' => function ($url, $model, $action) {
                        if (Yii::$app->user->isGuest || Yii::$app->user->id == $model->usuario_id) {
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

    <h3>Juegos similares a <?= Html::encode($model->titulo) ?></h3>

    <div class="row">
        <?= ListView::widget([
            'dataProvider' => $similaresProvider,
            'itemOptions' => ['class' => 'item',],
            'summary' => '',
            'itemView' => function ($model, $key, $index, $widget) {
                ?>
                <div class="col-md-4">
                    <table class="border">
                        <tr>
                            <th class="border-bottom"><?= Html::a($model->titulo, ['view', 'id' => $model->id]) ?></th>
                        </tr>
                        <tr>
                            <td><?= Html::encode(implode(', ', $model->generosNombres())) ?></td>
                        </tr>
                    </table>
                </div>
                <?php
            }
        ]) ?>
    </div>

    <?php
    Modal::begin([
        'id' => 'modalCritica',
    ]);
    ?>

        <div id="contenidoModal">
            <p>No has hecho una critica de este juego; ¿Te gustaria hacerla ahora?</p>
            <br>
            <?= Html::a('Crear crítica', ['criticas/critica-juego', 'juego_id' => $model->id], ['class' => 'btn btn-success']) ?>
        </div>

    <?php
    Modal::end();
    ?>

</div>
