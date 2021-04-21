<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\JuegosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Juegos';
$this->params['breadcrumbs'][] = $this->title;

$uId = Yii::$app->user->isGuest ? 0 : Yii::$app->user->id;
$url = Url::to(['usuarios/anadir-deseos']);
$url2 = Url::to(['usuarios/anadir-ignorados']);
$url3 = Url::to(['juegos/index']);

$js = <<<SCRIPT
$(document).ready(function(){

});
$("[name='botonDeseos']").click(anadirDeseos);
function anadirDeseos(e){
  e.preventDefault();
  $.ajax({
    method: 'GET',
    url: '$url',
    data: {jId: this.dataset.modelid, uId: $uId},
    success: function(result){
      if (result) {
        alert(result);
      } else {
        alert('Ha ocurrido un error al añadir deseos');
      }
    }
  });
}
$("[name='botonIgnorados']").click(anadirIgnorados);
function anadirIgnorados(e){
  e.preventDefault();
  console.log(this.dataset.modelid);
  $.ajax({
    method: 'GET',
    url: '$url2',
    data: {jId: this.dataset.modelid, uId: $uId},
    success: function(result){
      if (result) {
        window.location = '$url3';
      } else {
        alert('Ha ocurrido un error al añadir ignorados');
      }
    }
  });
}
SCRIPT;
$this->registerJs($js);
?>
<div class="juegos-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if (Yii::$app->user->id === 1) { ?>
            <?= Html::a('Crear Juegos', ['create'], ['class' => 'btn btn-success']) ?>
        <?php } ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <!-- https://getbootstrap.com/docs/4.0/components/card/ -->
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => [
            'itemscope' => true,
            'itemtype' => 'https://schema.org/VideoGame',
        ],
        'columns' => [
            [
              'attribute' => 'titulo',
              'contentOptions' => ['itemprop' => 'name'],
              'format' => 'raw',
              'value' => function ($model) {
                  return Html::encode($model->titulo) . '<br>' . Html::a(
                      Html::img($model->urlImagen, ['class' => 'mt-2', 'height' => 85, 'width' => 170, 'alt' => $model->titulo]),
                      ['juegos/view', 'id' => $model->id]
                  );
              }
            ],
            [
              'attribute' => 'fechalan',
              'format' => 'date',
              'label' => 'Lanzado',
              'contentOptions' => ['itemprop' => 'datePublished']
            ],
            'edad_minima',
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view} {vermercado} {anadirDeseos} {ignorar}',
              'buttons' => [
                'vermercado' => function ($url, $model, $key) {
                    return Html::a(
                        '<span class="fas fa-shopping-cart"></span>',
                        ['ventas/ventas-item', 'id' => $model->id, 'esProducto' => false],
                        ['title' => 'ver en mercado']
                    );
                },
                'anadirDeseos' => function ($url, $model, $key) {
                    if (Yii::$app->user->isGuest) {
                        return '';
                    }

                    return Html::a(
                        '<span class="fas fa-heart"></span>',
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
                        '<span class="fas fa-exclamation-triangle"></span>',
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

</div>
