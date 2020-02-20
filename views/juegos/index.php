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
        <?= Html::a('Create Juegos', ['create'], ['class' => 'btn btn-success']) ?>
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
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{delete} {view} {update} {vermercado}',
              'buttons' => [
                'vermercado' => function ($url, $model, $key){
                  return Html::a(
                    '<span class="glyphicon glyphicon-shopping-cart"></span>',
                    ['ventas/ventas-juego', 'id' => $model->id],
                    ['title' => 'ver en mercado']
                  );
                },
              ],
            ],
        ],
    ]); ?>


</div>
