<?php

use yii\grid\GridView;

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Mis ventas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ventas-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Poner en venta Producto/Copia', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <h1>Productos</h1>

    <?= GridView::widget([
        'dataProvider' => $misProductosProvider,
        'columns' => [
            'producto.nombre',
            'created_at:RelativeTime:En venta desde',
            'precio',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <br><br>
    <h1>Copias</h1>

    <?= GridView::widget([
        'dataProvider' => $misCopiasProvider,
        'columns' => [
            'copia.juego.titulo',
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
        ]
    ]); ?>


</div>
