<?php

use app\models\Usuarios;
use app\models\Ventas;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

$venta = Ventas::findOne($idVenta);

$nombreItem = $venta->esProducto() ? $venta->producto->nombre : $venta->copia->juego->titulo;
$usuario = Usuarios::findOne(Yii::$app->user->id);

?>
<h1>Venta de <?= $nombreItem ?></h1>

<div class="row">
    <div class="col">
        <?=
        DetailView::widget([
            'model' => $venta,
            'attributes' => [
                $venta->esProducto() ? 'producto.nombre' : 'copia.juego.titulo',
                'vendedor.nombre',
                'created_at:Relativetime',
                'precio',
            ],
        ]);
        ?>
    </div>
    <div class="col">
        <?=
        DetailView::widget([
            'model' => $usuario,
            'attributes' => [
                'nombre',
                'email',
            ],
        ]);
        ?>
    </div>
</div>


<p>Para confirmar la venta del producto a este usuario, pulsa el siguiente enlace</p>
<?= Html::a(
            'Confirmar venta',
            Url::to(
                [
                    'ventas/finalizar-venta',
                    'idVenta' => $venta->id,
                    'idComprador' => $idComprador,
                ],
                true
            ),
            ['class' => 'btn btn-danger']
        ); ?>
