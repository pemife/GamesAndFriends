<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Carro de compra';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="carrito-compra">

    <h1><?= Html::encode($this->title) ?></h1>

    <h3>Precio total: <?= Html::encode($precioTotal) ?></h3>

    <?= Html::a(
        'Procesar compra',
        ['copias/completar-compra'],
        [
            'class' => 'btn btn-success'
        ]
    ) ?>

    <!-- https://getbootstrap.com/docs/4.0/components/card/ -->
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'rowOptions' => [
            'itemscope' => true,
            'itemtype' => 'https://schema.org/VideoGame',
        ],
        'columns' => [
            [
                'attribute' => 'juego.urlImagen',
                'label' => 'Imagen',
                'format' => 'raw',
                'value' => function ($model) {
                    return
                    Html::a(
                        Html::img(
                            $model->juego->urlImagen,
                            [
                                'class' => 'rounded-circle mr-2',
                                'width' => 120,
                                'height' => 80,
                            ]
                        )
                    );
                }
            ],
            'juego.titulo',
            [
                'attribute' => 'plataforma.urlLogo',
                'label' => 'Plataforma',
                'format' => 'raw',
                'value' => function ($model) {
                    return
                    Html::img(
                        $model->plataforma->urlLogo,
                        [
                            'class' => 'rounded-circle mr-2',
                            'width' => 50,
                            'height' => 50,
                        ]
                    );
                }
            ],
            [
                'attribute' => 'cifra'
            ],
        ],
    ]); ?>

    <?= Yii::debug($dataProvider->getModels()) ?>

</div>
