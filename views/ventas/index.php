<?php

use yii\helpers\Html;
use yii\grid\GridView;

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

    <?= Gridview::widget([
        'dataProvider' => $copiasProvider,
        'columns' => [
            'copia.juego.titulo',
            'vendedor.nombre:ntext:Vendedor',
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
            'precio'

        ],
    ]); ?>

    <h1>Productos</h1>

    <?= Gridview::widget([
        'dataProvider' => $productosProvider,
        'columns' => [
            'producto.nombre',
            // [
            //     'label' => 'Generos',
            //     'value' => function($model){
            //         var_dump($model);
            //         $generos = "";
            //         // foreach ($model->copia->juego->etiquetas as $genero) {
            //         //     $generos += $genero->nombre;
            //         // }
            //         return $model->copia->id;
            //     }
            // ],

        ],
    ]); ?>

</div>
