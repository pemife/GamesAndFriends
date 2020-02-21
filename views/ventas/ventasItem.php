<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

if($esProducto){
  $columns = [
    'producto.nombre',
    'producto.descripcion',
    'producto.stock',
    'producto.propietario.nombre',
    ['class' => 'yii\grid\ActionColumn'],
  ];
} else {
  $columns = [
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
  ];
}

$this->title = 'Ventas de ' . $nombreItem;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ventas-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Poner en venta', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <h1><?php echo $esProducto ? 'Productos' : 'Copias' ?></h1>

    <?= GridView::widget([
        'dataProvider' => $ventasProvider,
        'columns' => $columns,
    ]); ?>

</div>
