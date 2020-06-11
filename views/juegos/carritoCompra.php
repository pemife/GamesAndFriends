<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Carro de compra';
$this->params['breadcrumbs'][] = $this->title;

$js = <<<SCRIPT
paypal.Buttons({
    createOrder: function(data, actions) {
      return actions.order.create({
        purchase_units: [{
          amount: {
            value: '0.01'
          }
        }]
      });
    },
    onApprove: function(data, actions) {
      return actions.order.capture().then(function(details) {
        alert('Transaction completed by ' + details.payer.name.given_name);
      });
    }
}).render('#paypal-button-container'); // Display payment options on your web page


SCRIPT;

$precioTotal = 0;

foreach ($dataProvider->getModels() as $precio) {
    if ($precio->oferta != 1) {
        $precioTotal += $precio->cifra * $precio->oferta;
    } else {
        $precioTotal += $precio->cifra;
    }
}

?>

<div class="carrito-compra">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= Html::a(
        'Procesar compra',
        ['copias/completar-compra'],
        [
            'class' => 'btn btn-success'
        ]
    ) ?>

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
                                'class' => 'mr-2',
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
                'attribute' => 'cifra',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->oferta != 1.00) {
                        return
                        '<del>'
                        . Html::encode(
                            Yii::$app->formatter->asCurrency($model->cifra)
                        ) . '</del><br>'
                        . Html::encode(
                            Yii::$app->formatter->asCurrency(
                                round($model->cifra * $model->oferta, 3),
                                'EUR',
                                [
                                    NumberFormatter::ROUNDING_MODE => 2
                                ]
                            )
                        );
                    } else {
                        return Html::encode(
                            Yii::$app->formatter->asCurrency($model->cifra)
                        );
                    }
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{borrar}',
                'buttons' => [
                    'borrar' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
                            ['juegos/borrar-de-carrito', 'pId' => $model->id],
                            [
                                'title' => 'borrar del carrito',
                                'name' => 'botonDeseos',
                                'style' => [
                                    'color' => 'red',
                                ],
                                'data' => [
                                    'modelId' => $model->id,
                                ]
                            ]
                        );
                    }
                ]
            ]
        ],
    ]); ?>

    <h3>Precio total: <?= Html::encode(Yii::$app->formatter->asCurrency($precioTotal)) ?></h3>

    <div id="paypal-button-container"></div>
    <script src="https://www.paypal.com/sdk/js?client-id=<?= getenv('PCLIENTID') ?>&currency=EUR" data-sdk-integration-source="button-factory"></script>
    <script>
        paypal.Buttons({
            style: {
                shape: 'pill',
                color: 'gold',
                layout: 'vertical',
                label: 'paypal',
                
            },
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '1'
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    alert('Transaction completed by ' + details.payer.name.given_name + '!');
                });
            }
        }).render('#paypal-button-container');
    </script>

    <?= Yii::debug(Yii::$app->request->cookies->getValue('Carro-' . Yii::$app->user->id)) ?>

</div>
