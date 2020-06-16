<?php

use app\models\Usuarios;
use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Ventas */

$this->title = empty($model->producto_id) ? $model->copia->juego->titulo : 'Venta de ' . $model->producto->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Ventas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$urlProcesarVenta = Url::to(['ventas/procesar'], true);
$urlFinalVenta = Url::to(['ventas/finalizar-venta'], true);
?>
<div class="ventas-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php
        if ($model->vendedor_id == Yii::$app->user->id) {
            echo Html::a('Retirar del mercado', ['delete', 'id' => $model->id], [
              'class' => 'btn btn-danger',
              'data' => [
                'confirm' => '¿Estas seguro de querer borrar este elemento?',
                'method' => 'post',
              ],
            ]);
        }
        ?>
    </p>

    <?= Html::img(empty($model->producto_id) ? $model->copia->juego->urlImagen : $model->producto->urlImagen, ['height' => 200, 'width' => 300]) ?>
    </br></br>
    <p>
        <span style="font-weight: bold;">Vendedor: </span>
        <?= Html::a(
            $model->vendedor ? ($model->vendedor->nombre) : 'Eliminado',
            $model->vendedor ? [
              'usuarios/view',
              'id' => $model->vendedor_id
            ] : 'javascript:void(0)'
        ) ?>
    </p>
    <p>
        <span style="font-weight: bold;">Precio: </span>
        <?= Html::encode(
            Yii::$app->formatter->asCurrency(
                round($model->precio, 3),
                'EUR',
                [
                    NumberFormatter::ROUNDING_MODE => 2
                ]
            )
        ) ?>
    </p>
    <?php if (!Yii::$app->user->isGuest) : ?>
        <?php $usuario = Usuarios::findOne(Yii::$app->user->id) ?>
        <?php if (Yii::$app->user->id != $model->vendedor_id && !empty($usuario->pay_token)) : ?>
            <div id="paypal-button-container"></div>
            <script src="https://www.paypal.com/sdk/js?client-id=<?= $model->vendedor->pay_token ?>&currency=EUR" data-sdk-integration-source="button-factory"></script>
            <script>
                token = '';
                paypal.Buttons({
                    style: {
                        shape: 'pill',
                        color: 'gold',
                        layout: 'vertical',
                        label: 'paypal',
                        
                    },

                    // Procesa el carro antes de la transaccion, para confimar que no hay errores
                    createOrder: function(data, actions) {
                        $.ajax({
                            method: 'GET',
                            url: '<?= $urlProcesarVenta ?>',
                            data: {idVenta: <?= $model->id ?>, idComprador: <?= $usuario->id ?>},
                            success: function(result){
                                if (result) {
                                    token = result;
                                } else {
                                    window.location.href = '<?= Url::to(['site/home']) ?>';
                                }
                            }
                        });

                        return actions.order.create({
                            purchase_units: [{
                                amount: {
                                    value: <?= round($model->precio, 2, PHP_ROUND_HALF_DOWN) ?>
                                }
                            }]
                        });
                    },

                    // Si la transaccion se hace correctamente, entonces finaliza la compra
                    onApprove: function(data, actions) {
                        return actions.order.capture().then(function(details) {
                            $.ajax({
                                method: 'POST',
                                url: '<?= $urlFinalVenta ?>',
                                data: {authtoken: token, idVenta: <?= $model->id ?>, idComprador: <?= $usuario->id ?>},
                                success: function(result){
                                    if (result) {
                                        window.location.href = result;
                                    }
                                }
                            });
                        });
                    }
                }).render('#paypal-button-container');
            </script>
        <?php endif; ?>
    <?php endif; ?>

</div>