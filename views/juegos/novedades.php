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
.slider-holder
{
    width: 800px;
    height: 400px;
    background-color: yellow;
    margin-left: auto;
    margin-right: auto;
    margin-top: 0px;
    text-align: center;
    overflow: hidden;
}

.image-holder
{
    width: 2400px;
    background-color: red;
    height: 400px;
    clear: both;
    position: relative;
    
    -webkit-transition: left 1s;
    -moz-transition: left 1s;
    -o-transition: left 1s;
    transition: left 1s;
}

.slider-image
{
    float: left;
    margin: 0px;
    padding: 0px;
    position: relative;
}

#slider-image-1:target ~ .image-holder
{
    left: 0px;
}

#slider-image-2:target ~ .image-holder
{
    left: -800px;
}

#slider-image-3:target ~ .image-holder
{
    left: -1600px;
}

.button-holder
{
    position: relative;
    top: -20px;
}

.slider-change
{
    display: inline-block;
    height: 10px;
    width: 10px;
    border-radius: 5px;
    background-color: brown;
}
CSS;
$this->registerCSS($css);

// No es mas que un array de URLs de prueba mas adelante lo suprimiré
$imagenesJuego = [
  'https://i.ytimg.com/vi/hWE7MrcekGY/maxresdefault.jpg',
  'https://i.ytimg.com/vi/pR6Op9xBcfY/maxresdefault.jpg',
  'https://mmogamerstore.com/wp-content/uploads/2018/04/ss_counter-stike-global-offensive_00.jpg',
  'https://i.ytimg.com/vi/hWE7MrcekGY/maxresdefault.jpg',
  'https://i.ytimg.com/vi/pR6Op9xBcfY/maxresdefault.jpg',
  'https://mmogamerstore.com/wp-content/uploads/2018/04/ss_counter-stike-global-offensive_00.jpg',
  'https://i.ytimg.com/vi/hWE7MrcekGY/maxresdefault.jpg',
  'https://i.ytimg.com/vi/pR6Op9xBcfY/maxresdefault.jpg',
  'https://mmogamerstore.com/wp-content/uploads/2018/04/ss_counter-stike-global-offensive_00.jpg',
];
?>
<div class="juegos-novedades">
    <style>
        .imagenesJuegos {display:none;}
    </style>

    <h2>Novedades</h2>

    <!-- http://qnimate.com/creating-a-slider-using-html-and-css-only/ -->
    <div class="slider-holder mb-4 mt-4">
        <?php
        $contador = 1;
        foreach ($juegosProvider->getModels() as $juego) {
        ?>

            <span id="slider-image-<?= $contador ?>"></span>
            <?php $contador++ ?>

        <?php } ?>

        <div class="image-holder">
            <?php
            $contador = 0;
            foreach ($juegosProvider->getModels() as $juego) { ?>
                <img src="<?= $imagenesJuego[$contador] ?>" class="slider-image" width="800" height="400"/>
                <?php $contador++ ?>
            <?php } ?>
        </div>
        <div class="button-holder">
            <?php
            $contador = 1;
            foreach ($juegosProvider->getModels() as $juego) {
            ?>

                <a href="#slider-image-<?= $contador ?>" class="slider-change"></a>
                <?php $contador++ ?>

            <?php } ?>
        </div>
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
                        '#',
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
                        '#',
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
                            '#',
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

</div>
