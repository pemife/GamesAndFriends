<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Productos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="productos-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Crear Productos', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'nombre',
            'descripcion:ntext',
            'stock',
            'propietario.nombre:ntext:Propietario',
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view} {update} {delete} {vermercado}',
              'buttons' => [
                'vermercado' => function ($url, $model, $key){
                  return Html::a(
                    '<span class="glyphicon glyphicon-shopping-cart"></span>',
                    ['ventas/ventas-item', 'id' => $model->id, 'esProducto' => true],
                    ['title' => 'ver en mercado']
                  );
                },
              ],
            ],
        ],
    ]); ?>


</div>
