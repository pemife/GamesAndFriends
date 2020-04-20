<?php

use app\models\Usuarios;
use app\models\Ventas;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

$venta = Ventas::findOne($ventaId);

$nombreItem = $venta->esProducto() ? $venta->producto->nombre : $venta->copia->juego->titulo;
$usuario = Usuarios::findOne(Yii::$app->user->id);

?>
<h1>Venta de <?= $nombreItem ?></h1>

<?=
DetailView::widget([
    'model' => $venta,
    'attributes' => [
        'created_at',
        'vendedor.nombre',
        'precio',
        $venta->esProducto() ? 'producto.nombre' : 'copia.juego.titulo',
    ],
]);

?>

<?=
DetailView::widget([
    'model' => $usuario,
    'attributes' => [
        'nombre',
        'email',
    ],
]);
?>

<p>Para confirmar la venta del producto a este usuario, pulsa el siguiente enlace</p>
<?= Html::a(
    'Confirmar venta',
    Url::to(
        [
                    'ventas/finalizar-venta',
                    'idVenta' => $venta->id,
                ],
        true
    ),
    ['class' => 'btn btn-danger']
); ?>
