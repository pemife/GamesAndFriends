<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\JuegosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Juegos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="juegos-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if(Yii::$app->user->id === 1){ ?>
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
              'template' => '{view} {vermercado}',
              'buttons' => [
                'vermercado' => function ($url, $model, $key){
                  return Html::a(
                    '<span class="glyphicon glyphicon-shopping-cart"></span>',
                    ['ventas/ventas-item', 'id' => $model->id, 'esProducto' => false],
                    ['title' => 'ver en mercado']
                  );
                },
              ],
            ],
        ],
    ]); ?>


</div>
