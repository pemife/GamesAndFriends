<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Mis Ventas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ventas-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Crear venta', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php //var_dump(Yii::$app->user->id) ?>

    <table class="table">
      <tr>
        <th>Producto</th>
        <th>Usuario</th>
        <th>En venta desde</th>
        <th>Precio 2ª Mano</th>
        <th>Acciones</th>
      </tr>
        <?php
            foreach ($misVentas as $venta):
                if($venta->producto === null){
                    continue;
                }
        ?>
          <tr>
            <td><?= $venta->producto->nombre ? $venta->producto->nombre : null; ?></td>
            <td><?= $venta->vendedor->nombre ?></td>
            <td><?= Yii::$app->formatter->asRelativeTime($venta->created_at) ?></td>
            <td><?= Yii::$app->formatter->asCurrency($venta->precio) ?></td>
            <td>
                <?= Html::a('Editar', ['/ventas/update', 'id' => $venta->id], ['class' => 'btn btn-info']) ?>
                <?= Html::a('Editar', ['/ventas/delete', 'id' => $venta->id], ['class' => 'btn btn-danger']) ?>
            </td>
          </tr>
        <?php endforeach; ?>
    </table>


</div>
