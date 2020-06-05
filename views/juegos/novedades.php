<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\JuegosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Novedades';
$this->params['breadcrumbs'][] = $this->title;

$uId = Yii::$app->user->isGuest ? 0 : Yii::$app->user->id;
$url1 = Url::to(['usuarios/anadir-ignorados']);
$url2 = Url::to(['juegos/novedades']);
$js = <<<SCRIPT

$("[name='botonDeseos']").click(anadirDeseos);
function anadirDeseos(e){
  e.preventDefault();
  // console.log(this.dataset.modelid);
  $.ajax({
    method: 'GET',
    url: '/index.php?r=usuarios/anadir-deseos',
    data: {jId: this.dataset.modelid, uId: $uId},
    success: function(result){
      if (result) {
        alert(result);
      } else {
        alert('Ha ocurrido un error');
      }
    }
  });
}
$("[name='botonIgnorados']").click(anadirIgnorados);
function anadirIgnorados(e){
  e.preventDefault();
  // console.log(this.dataset.modelid);
  $.ajax({
    method: 'GET',
    url: '$url1',
    data: {jId: this.dataset.modelid, uId: $uId},
    success: function(result){
      if (result) {
        window.location = '$url2';
      } else {
        alert('Ha ocurrido un error al añadir ignorados');
      }
    }
  });
}
SCRIPT;

$this->registerJS($js);

$css = <<<CSS
.carousel {
  width: 70% ;
  margin-left: auto ;
  margin-right: auto ;
}

.imagenJuego {
  text-align: center;
  height: 40% ;
  opacity: 0.6;
  transition: 0.3s;
}

.imagenJuego:hover {opacity: 1}

CSS;

$this->registerCSS($css);
?>
<div class="juegos-novedades">
    <style>
        .imagenesJuegos {display:none;}
    </style>

    <h2>Novedades</h2>

    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
      <ol class="carousel-indicators">
        <?php for ($i = 0; $i < $juegosProvider->count; $i++) : ?>
          <li data-target="#carouselExampleIndicators" data-slide-to="<?= $i ?>" <?= $i == 0 ? 'class="active"' : '' ?>></li>
        <?php endfor; ?>
      </ol>
        <div class="carousel-inner">
        <?php
        $esPrimero = true;
        foreach ($juegosProvider->getModels() as $juego) : ?>
          <div class="carousel-item<?= $esPrimero ? ' active' : '' ?>">
            <?php
            echo Html::a(
                Html::img($juego->urlImagen, [
                  'class' => 'd-block w-100 imagenJuego',
                  'alt' => $juego->titulo
                ]),
                [
                  'juegos/view', 'id' => $juego->id
                ]
            );
            $esPrimero = false;
            ?>

          </div>
        <?php endforeach; ?>
      </div>
      <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
      </a>
    </div>

    <?= GridView::widget([
        'dataProvider' => $juegosProvider,
        'rowOptions' => [
            'itemscope' => true,
            'itemtype' => 'https://schema.org/VideoGame',
        ],
        'columns' => [
            [
              'attribute' => 'titulo',
              'contentOptions' => ['itemprop' => 'name']
            ],
            [
              'attribute' => 'fechalan',
              'format' => 'date',
              'contentOptions' => ['itemprop' => 'datePublished']
            ],
            [
              'attribute' => 'dev',
              'contentOptions' => ['itemprop' => 'creator']
            ],
            [
              'attribute' => 'publ',
              'contentOptions' => ['itemprop' => 'publisher']
            ],
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view} {vermercado} {anadirDeseos} {ignorar}',
              'buttons' => [
                'vermercado' => function ($url, $model, $key) {
                    return Html::a(
                        '<span class="glyphicon glyphicon-shopping-cart"></span>',
                        ['ventas/ventas-item', 'id' => $model->id, 'esProducto' => false],
                        ['title' => 'ver en mercado']
                    );
                },
                'anadirDeseos' => function ($url, $model, $key) {
                    if (Yii::$app->user->isGuest) {
                        return '';
                    }

                    return Html::a(
                        '<span class="glyphicon glyphicon-heart"></span>',
                        'javascript:void(0)',
                        [
                          'title' => 'añadir a tu lista de deseos',
                          'name' => 'botonDeseos',
                          'data' => [
                            'modelId' => $model->id,
                          ]
                        ]
                    );
                },
                'ignorar' => function ($url, $model, $key) {
                    if (Yii::$app->user->isGuest) {
                        return '';
                    }

                    return Html::a(
                        '<span class="glyphicon glyphicon-warning-sign"></span>',
                        'javascript:void(0)',
                        [
                          'title' => 'Ignorar juego',
                          'name' => 'botonIgnorados',
                          'style' => [
                              'color' => 'red',
                          ],
                          'data' => [
                            'modelId' => $model->id,
                          ]
                        ]
                    );
                },
              ],
            ],
        ],
    ]); ?>

<?php
if (!Yii::$app->user->isGuest) {
    echo '<h2>Novedades Recomendadas</h2>';

    echo GridView::widget([
            'dataProvider' => $recomendacionesProvider,
            'rowOptions' => [
                'itemscope' => true,
                'itemtype' => 'https://schema.org/VideoGame',
            ],
            'columns' => [
                [
                  'attribute' => 'titulo',
                  'contentOptions' => ['itemprop' => 'name']
                ],
                [
                  'attribute' => 'fechalan',
                  'format' => 'date',
                  'contentOptions' => ['itemprop' => 'datePublished']
                ],
                [
                  'attribute' => 'dev',
                  'contentOptions' => ['itemprop' => 'creator']
                ],
                [
                  'attribute' => 'publ',
                  'contentOptions' => ['itemprop' => 'publisher']
                ],
                [
                  'class' => 'yii\grid\ActionColumn',
                  'template' => '{view} {vermercado} {anadirDeseos}',
                  'buttons' => [
                    'vermercado' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-shopping-cart"></span>',
                            ['ventas/ventas-item', 'id' => $model->id, 'esProducto' => false],
                            ['title' => 'ver en mercado']
                        );
                    },
                    'anadirDeseos' => function ($url, $model, $key) {
                        if (Yii::$app->user->isGuest) {
                            return '';
                        }
    
                        return Html::a(
                            '<span class="glyphicon glyphicon-heart"></span>',
                            'javascript:void(0)',
                            [
                              'title' => 'añadir a tu lista de deseos',
                              'name' => 'botonDeseos',
                              'data' => [
                                'modelId' => $model->id,
                              ]
                            ]
                        );
                    },
                  ],
                ],
            ],
      ]);
}

?>

<?= Yii::debug(Yii::$app->session) ?>

</div>
