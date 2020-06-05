<?php

use yii\bootstrap4\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use kartik\rating\StarRating as RatingStarRating;
use yii\bootstrap4\Modal;
use yii\widgets\ListView;
use Aws\S3\S3Client;
use yii\bootstrap4\Dropdown;
use yii\helpers\Url;

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

// Url ajax carrito
$urlCarrito = Url::to(['juegos/anadir-carrito']);

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

$('.botonCompra').click(function(e) {

    $.ajax({
        method: 'GET',
        url: '$urlCarrito',
        data: {pId: this.dataset.pid},
          success: function(result){
            if (result) {
                //alert(result);
            } else {
                //alert('Ha ocurrido un error');
            }
        }
    });
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

.seAgita:hover {
  animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both;
  transform: translate3d(0, 0, 0);
  backface-visibility: hidden;
  perspective: 1000px;
}

@keyframes shake {
  10%, 90% {
    transform: translate3d(-1px, 0, 0);
  }
  
  20%, 80% {
    transform: translate3d(2px, 0, 0);
  }

  30%, 50%, 70% {
    transform: translate3d(-4px, 0, 0);
  }

  40%, 60% {
    transform: translate3d(4px, 0, 0);
  }
}
CSS;

$this->registerJs($js);
$this->registerCSS($css);
?>

<!-- Dependencia de krajee starrating -->
<script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js" crossorigin="anonymous"></script>

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
                <?= $totalTrailers == 0 ? Html::img($model->sinTrailers(), ['class' => 'img-fluid']) : '' ?>
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
            <div class="row">
                <div class="col">
                    <?= Html::a(
                        'Ventas de 2ª Mano',
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
                        <a href="#" data-toggle="dropdown" class="dropdown-toggle btn btn-info mt-4">Ofertas <b class="caret"></b></a>
                        <?= Dropdown::widget([
                            'items' => [
                                ['label' => '50%', 'url' => Url::to([
                                    'juegos/poner-oferta',
                                    'jId' => $model->id,
                                    'porcentaje' => 0.50
                                    ])
                                ],
                                ['label' => '55%', 'url' => Url::to([
                                    'juegos/poner-oferta',
                                    'jId' => $model->id,
                                    'porcentaje' => 0.55
                                    ])
                                ],
                                ['label' => '65%', 'url' => Url::to([
                                    'juegos/poner-oferta',
                                    'jId' => $model->id,
                                    'porcentaje' => 0.60
                                    ])
                                ],
                                ['label' => '60%', 'url' => Url::to([
                                    'juegos/poner-oferta',
                                    'jId' => $model->id,
                                    'porcentaje' => 0.65
                                    ])
                                ],
                                ['label' => '75%', 'url' => Url::to([
                                    'juegos/poner-oferta',
                                    'jId' => $model->id,
                                    'porcentaje' => 0.75
                                    ])
                                ],
                                ['label' => '80%', 'url' => Url::to([
                                    'juegos/poner-oferta',
                                    'jId' => $model->id,
                                    'porcentaje' => 0.80
                                    ])
                                ],
                                ['label' => '85%', 'url' => Url::to([
                                    'juegos/poner-oferta',
                                    'jId' => $model->id,
                                    'porcentaje' => 0.85
                                    ])
                                ],
                                ['label' => '90%', 'url' => Url::to([
                                    'juegos/poner-oferta',
                                    'jId' => $model->id,
                                    'porcentaje' => 0.9
                                    ])
                                ],
                            ]
                        ])
                        ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8 bg-dark text-light mt-4 ml-3">
                    <h4 class="pt-4 pl-4">
                        Comprar <?= Html::encode($model->titulo) ?>
                    </h4>
                    <p class="text-light pl-4">
                        Añadir al carro de compra<br>
                    </p>
                    <span class="d-flex justify-content-end">
                        <?php
                        $permiteCompra = false;
                        foreach ($model->precios as $precio) {
                            if ($precio->cifra == null) {
                                continue;
                            }
                            if (!$permiteCompra && $precio->oferta != 1.0) {
                                echo '<b>Oferta del ' . $precio->oferta * 100 . '%</b>';
                            }
                            $permiteCompra = true;
                        ?>
                            <?= Html::a(
                                Html::img(
                                    $precio->plataforma->urlLogo,
                                    [
                                        'class' => 'mr-2',
                                        'height' => 30,
                                        'width' => 30
                                    ]
                                )
                                . $precio->cifra * $precio->oferta . '€',
                                'javascript:void(0)',
                                [
                                    'class' => 'btn mr-2 mt-4 mb-4 text-light botonCompra',
                                    'style' => [
                                        'background-color' => $precio->plataforma->color
                                    ],
                                    'data' => [
                                        'pId' => $precio->id
                                    ]
                                ]
                            ) ?> 
                        <?php } ?>
                        <span class="mr-2 mt-4 mb-4">
                            <?= $permiteCompra ? '' : '¡No hay opciones de compra!' ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="row mb-2 mt-2">
                <div class="col-md-1">
                    <h3>Críticas</h3>
                </div>
                <div class="col-md-1 ml-4">
                    <?php
                    if ($tieneJuego) {
                        echo Html::a('Opinar', ['criticas/critica-juego', 'juego_id' => $model->id], ['class' => 'btn btn-success']);
                    }
                    ?>
                </div>
                
            </div>
                
            <?= GridView::widget([
            'dataProvider' => $criticasProvider,
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
                                '<span class=" glyphicon glyphicon-thumbs-up"></span>',
                                ['criticas/reportar', 'cId' => $model->id, 'esVotoPositivo' => true]
                            );
                        }
                    ]
                ],
                [
                    'attribute' => 'usuario.nombre',
                    'format' => 'raw',
                    'label' => 'Usuario',
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
                        // https://www.w3schools.com/bootstrap/bootstrap_modal.asp
                        'reportar' => function ($url, $model, $action) {
                            if (empty($model->usuario_id)) {
                                return '';
                            }
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
        <h3 class="col-md-12 mt-4 mb-2">Juegos similares a <?= Html::encode($model->titulo) ?></h3>

        <?= ListView::widget([
            'dataProvider' => $similaresProvider,
            'itemOptions' => ['class' => 'item'],
            'summary' => '',
            'itemView' => function ($model, $key, $index, $widget) {
                ?>
                <div class="col-md-3">
                    <table class="border">
                        <tr>
                            <?= Html::a(
                                Html::img(
                                    $model->urlImagen,
                                    ['class' => 'img-fluid']
                                ),
                                ['view', 'id' => $model->id]
                            ) ?>
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
<?= Yii::debug($model->oferta) ?>