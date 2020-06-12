<?php

use yii\grid\GridView;

use yii\bootstrap4\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Mis ventas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ventas-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Poner en venta Producto/Juego', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    
    <h1>Juegos</h1>

    <?= GridView::widget([
        'dataProvider' => $misCopiasProvider,
        'rowOptions' => [
            'itemscope' => true,
            'itemtype' => 'https://schema.org/VideoGame'
        ],
        'columns' => [
            [
                'attribute' => 'copia.juego.titulo',
                'label' => 'Juego',
                'contentOptions' => ['itemprop' => 'name']
            ],
            'created_at:RelativeTime:En venta desde',
            [
                'label' => 'Generos',
                'value' => function ($model) {
                    foreach ($model->copia->juego->etiquetas as $genero) {
                        $generos[] = $genero->nombre;
                    }

                    $cadenaGeneros = '';

                    if (!empty($generos)) {
                        $cadenaGeneros = implode(', ', $generos);
                    }

                    return $cadenaGeneros;
                },
                'contentOptions' => ['itemprop' => 'genre']
            ],
            [
                'attribute' => 'precio',
                'contentOptions' => ['itemprop' => 'price'],
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::encode(
                        Yii::$app->formatter->asCurrency(
                            round($model->precio, 3),
                            'EUR',
                            [
                                NumberFormatter::ROUNDING_MODE => 2
                            ]
                        )
                    );
                }
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ]
    ]); ?>

    <br><br>

    <h1>Productos</h1>

    <?= GridView::widget([
        'dataProvider' => $misProductosProvider,
        'rowOptions' => [
            'itemscope' => true,
            'itemtype' => 'https://schema.org/Product'
        ],
        'columns' => [
            [
                'attribute' => 'producto.nombre',
                'contentOptions' => ['itemprop' => 'name']
            ],
            'created_at:RelativeTime:En venta desde',
            [
                'attribute' => 'precio',
                'contentOptions' => ['itemprop' => 'price'],
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::encode(
                        Yii::$app->formatter->asCurrency(
                            round($model->precio, 3),
                            'EUR',
                            [
                                NumberFormatter::ROUNDING_MODE => 2
                            ]
                        )
                    );
                }
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
