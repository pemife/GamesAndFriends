<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ventas de ' . $nombreItem;
$this->params['breadcrumbs'][] = $this->title;

if($esProducto){
  $columns = [
    'producto.nombre',
    'producto.descripcion',
    'producto.stock',
    'producto.propietario.nombre:ntext:Propietario',
    [
        'class' => 'yii\grid\ActionColumn',
        'template' => '{view} {update} {delete}',
        'buttons' => [
            'update' => function ($url, $model, $key){
                if(Yii::$app->user->id == $model->vendedor->id) {
                    return Html::a(
                      '<span class="fas fa-pencil"></span>',
                      ['ventas/update', 'id' => $model->id],
                      ['title' => 'Actualizar']
                    );
                }
            },
            'delete' => function ($url, $model, $key){
                if(Yii::$app->user->id == $model->vendedor->id) {
                    return Html::a(
                        '<span class="fas fa-trash"></span>',
                        ['ventas/delete', 'id' => $model->id],
                        [
                            'title' => 'Eliminar',
                            'data-method' => 'POST',
                            'confirm' => 'Esta seguro de que quiere eliminar la venta?'
                        ]
                    );
                }
                return null;
            }
        ]
    ],
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
    [
        'class' => 'yii\grid\ActionColumn',
        'template' => '{view} {update} {delete}',
        'buttons' => [
            'update' => function ($url, $model, $key){
                if(Yii::$app->user->id == $model->vendedor->id) {
                    return Html::a(
                      '<span class="fas fa-pencil"></span>',
                      ['ventas/update', 'id' => $model->id],
                      ['title' => 'Actualizar']
                    );
                }
            },
            'delete' => function ($url, $model, $key){
                if(Yii::$app->user->id == $model->vendedor->id) {
                    return Html::a(
                        '<span class="fas fa-trash"></span>',
                        ['ventas/delete', 'id' => $model->id],
                        [
                            'title' => 'Eliminar',
                            'data-method' => 'POST',
                            'confirm' => 'Esta seguro de que quiere eliminar la venta?'
                        ]
                    );
                }
                return null;
            }
        ]
    ],
  ];
}
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
