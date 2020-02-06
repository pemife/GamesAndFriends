<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;

use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'En venta';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="ventas-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Ventas', ['create'], ['class' => 'btn btn-success']) ?>
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

                    $cadenaGeneros = implode(", ", $generos);

                    return $cadenaGeneros;
                }
            ],
            'precio',
            ['class' => 'yii\grid\ActionColumn'],
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
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
