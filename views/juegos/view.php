<?php

use yii\bootstrap4\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use kartik\rating\StarRating as RatingStarRating;
use yii\bootstrap4\Modal;
use yii\widgets\ListView;
use Aws\S3\S3Client;

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

// Numero total de trailers
$totalTrailers = sizeof($model->trailers);

$js = <<<SCRIPT
$(function() {
    if ($tieneJuegoJs && !$usuarioHaCriticado) {
        setTimeout(function() {
            $('#modalCritica').modal('show');
        }, 3000);
    }

    $('.trailer').hide();
    $('[name="video1"]').show();

    $('.trailer').each(function () {
        this.volume = 0.2;
    });
});

$('.selector').click(function(e) {
    seleccionarTrailer(this.dataset.numerotrailer);
});

function pausaVideos() {
    $('.trailer').each(function() {
        this.pause();
    });
}

function seleccionarTrailer(numero) {
    pausaVideos();
    $('.trailer').hide();
    $('[name="video' + numero + '"]').show();
}
SCRIPT;

$css = <<<CSS
.descripcion {
    height: auto;
    overflow: hidden;
    text-overflow: ellipsis;
}

.trailer {
    width: 100%;
}
CSS;

$this->registerJs($js);
$this->registerCSS($css);
?>
<div class="juegos-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="container">
        <div class="row mt-4 bg-dark pt-4 pb-4 text-light">
            <div class="col-md-8 mt-2">
                <?php
                $count = 1;
                foreach ($model->trailers as $trailer) : ?>
                    <video class="trailer" controls name="video<?= $count ?>">
                        <source src="<?= $trailer ?>">
                    </video>
                    <?php $count++ ?>
                <?php endforeach; ?>
                <span class="d-flex justify-content-center">
                    <?php
                    $count = 1;
                    foreach ($model->trailers as $trailer) : ?>
                        <?= Html::radio('selector', $count > 1 ? false : true, [
                                'class' => 'selector mr-2',
                                'id' => 'selectorRadio' . $count,
                                'data' => [
                                    'numeroTrailer' => $count
                                ]
                            ]) ?>
                        <?php $count++ ?>
                    <?php endforeach; ?>
                </span>
            </div>
            <div class="col-md-4 mt-2">

                <?= Html::img($model->urlImagen, ['class' => 'img-fluid']) ?>

                <p class="descripcion mt-2">
                    <?= Html::encode($model->descripcion) ?>
                </p>

                <p>
                    Valoraciones Positivas Globales: <?= Html::encode($valPosGlob) ?><br>
                    Valoraciones Positivas Recientes: <?= Html::encode($valPosRec) ?>
                </p>

                <p>
                    Desarrolladora: <?= Html::encode($model->dev) ?><br>
                    Editora: <?= Html::encode($model->publ) ?>
                </p>

                <p>
                    Géneros: <?= Html::encode(implode(', ', $model->generosNombres())) ?>
                </p>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col">
            <?= Html::a(
                    'Ver en mercado',
                    [
                    'ventas/ventas-item',
                    'id' => $model->id,
                    'esProducto' => false
                    ],
                    ['class' => 'btn btn-success mr-2 mt-4']
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
                        ['class' => 'btn btn-info mr-2 mt-4',]
                    );
            }
            ?>
            <?php if (Yii::$app->user->id === 1) : ?>
                <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary mr-2 mt-4']) ?>
                <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger mr-2 mt-4',
                'data' => [
                    'confirm' => '¿Estas seguro de querer borrar este elemento?',
                    'method' => 'post',
                ],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col">
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
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{like}',
                'visible' => !Yii::$app->user->isGuest,
                'buttons' => [
                    'like' => function ($url, $model, $key) {
                        if (Yii::$app->user->isGuest || Yii::$app->user->id == $model->usuario->id) {
                            return '';
                        }

                        return Html::a(
                            '<span class=" glyphicon glyphicon-thumbs-up"></span>',
                            ['criticas/reportar', 'cId' => $model->id, 'esVotoPositivo' => true]
                        );
                    }
                ]
            ],
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
                        
                        return Html::a('', ['criticas/reportar', 'cId' => $model->id, 'esVotoPositivo' => false], [
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
    </div>

    <div class="row">
        <div class="col">
            <h3>Juegos similares a <?= Html::encode($model->titulo) ?></h3>

            <?= ListView::widget([
                'dataProvider' => $similaresProvider,
                'itemOptions' => ['class' => 'item'],
                'summary' => '',
                'itemView' => function ($model, $key, $index, $widget) {
                    ?>
                    <div class="col-md-4">
                        <table class="border">
                            <tr>
                                <?= Html::img($model->urlImagen, ['class' => 'img-fluid pr-2']) ?>
                            </tr>
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
