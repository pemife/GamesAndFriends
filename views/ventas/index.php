<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;

use yii\helpers\Url;

use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Mercado de segunda mano';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ventas-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Poner en venta', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <h1>Juegos</h1>

    <?= GridView::widget([
        'dataProvider' => $copiasProvider,
        'columns' => [
            'copia.juego.titulo',
            'vendedor.nombre:ntext:Vendedor',
            'created_at:RelativeTime:En venta desde',
            [
                'label' => 'Generos',
                'value' => function($model){
                    foreach ($model->copia->juego->etiquetas as $genero) {
                        $generos[] = $genero->nombre;
                    }

                    $cadenaGeneros = "";

                    if (!empty($generos)) {
                        $cadenaGeneros = implode(", ", $generos);
                    }

                    return $cadenaGeneros;
                }
            ],
            'precio',
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view} {vermercado} {update} {delete}',
              'buttons' => [
                'vermercado' => function ($url, $model, $key){
                  return Html::a(
                    '<span class="glyphicon glyphicon-shopping-cart"></span>',
                    ['ventas/ventas-item', 'id' => $model->copia->juego->id, 'esProducto' => false],
                    ['title' => 'ventas de ' . $model->copia->juego->titulo]
                  );
                },
                'update' => function ($url, $model, $key){
                    if(Yii::$app->user->id == $model->vendedor->id) {
                        return Html::a(
                          '<span class="glyphicon glyphicon-pencil"></span>',
                          ['ventas/update', 'id' => $model->id],
                          ['title' => 'Actualizar']
                        );
                    }
                },
                'delete' => function ($url, $model, $key){
                    if(Yii::$app->user->id == $model->vendedor->id) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
                            ['ventas/delete', 'id' => $model->id],
                            [
                              'title' => 'Eliminar',
                              'data-method' => 'POST',
                              'confirm' => 'Esta seguro de que quiere eliminar la venta?'
                            ]
                        );
                    }
                    return null;
                },
              ]
            ],
          ],
    ]); ?>

    <?= LinkPager::widget([
      'pagination' => $copiasProvider->getPagination(),
    ]); ?>

    <h1>Productos</h1>

    <?= Gridview::widget([
        'dataProvider' => $productosProvider,
        'columns' => [
            'producto.nombre',
            'vendedor.nombre:ntext:Vendedor',
            'created_at:RelativeTime:En venta desde',
            'precio',
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view} {vermercado} {update} {delete}',
              'buttons' => [
                'vermercado' => function ($url, $model, $key){
                  return Html::a(
                    '<span class="glyphicon glyphicon-shopping-cart"></span>',
                    ['ventas/ventas-item', 'id' => $model->producto->id, 'esProducto' => true],
                    ['title' => 'ventas de ' . $model->producto->nombre]
                  );
                },
                'update' => function ($url, $model, $key){
                    if(Yii::$app->user->id == $model->vendedor->id) {
                        return Html::a(
                          '<span class="glyphicon glyphicon-pencil"></span>',
                          ['ventas/update', 'id' => $model->id],
                          ['title' => 'Actualizar']
                        );
                    }
                },
                'delete' => function ($url, $model, $key){
                    if(Yii::$app->user->id == $model->vendedor->id) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
                            ['ventas/delete', 'id' => $model->id],
                            [
                              'title' => 'Eliminar',
                              'data-method' => 'POST',
                              'confirm' => 'Esta seguro de que quiere eliminar la venta?'
                            ]
                        );
                    }
                    return null;
                },
              ]
            ],
        ],
    ]); ?>

</div>
