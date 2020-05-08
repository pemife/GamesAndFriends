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

$js = <<<SCRIPT
$(document).ready(function(){

});
$("[name='botonDeseos']").click(anadirDeseos);
function anadirDeseos(e){
  console.log(this.dataset.modelid);
  $.ajax({
    method: 'GET',
    url: '/index.php?r=usuarios/anadir-deseos',
    data: {jId: this.dataset.modelid, uId: $uId},
    success: function(result){
      if (result) {
        alert(result);
      } else {
        alert('NOOOOOOOOOOO');
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

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'titulo',
            'descripcion:ntext',
            'fechalan',
            'dev',
            'publ',
            'cont_adul:boolean',
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
    ]); ?>

</div>
