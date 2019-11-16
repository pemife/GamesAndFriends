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

    <h1>Productos</h1>

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
                <?= Html::a('Retirar', ['/ventas/delete', 'id' => $venta->id], [
                      'class' => 'btn btn-danger',
                      'data' => [
                          'confirm' => '¿Seguro que quieres retirar esta copia?',
                          'method' => 'post',
                      ],
                  ]) ?>
            </td>
          </tr>
        <?php endforeach; ?>
    </table>

    <br><br>
    <h1>Copias</h1>

    <table class="table">
      <tr>
        <th>Copia</th>
        <th>Usuario</th>
        <th>En venta desde</th>
        <th>Precio 2ª Mano</th>
        <th>Acciones</th>
      </tr>
        <?php
            foreach ($misVentas as $venta):
                if($venta->copia === null){
                    continue;
                }
        ?>
          <tr>
            <td><?= $venta->copia->juego->titulo ? $venta->copia->juego->titulo : null; ?></td>
            <td><?= $venta->vendedor->nombre ?></td>
            <td><?= Yii::$app->formatter->asRelativeTime($venta->created_at) ?></td>
            <td><?= Yii::$app->formatter->asCurrency($venta->precio) ?></td>
            <td>
                <?= Html::a('Editar', ['/ventas/update', 'id' => $venta->id], ['class' => 'btn btn-info']) ?>
                <?= Html::a('Retirar', ['/ventas/delete', 'id' => $venta->id], [
                      'class' => 'btn btn-danger',
                      'data' => [
                          'confirm' => '¿Seguro que quieres retirar este producto?',
                          'method' => 'post',
                      ],
                  ]) ?>
            </td>
          </tr>
        <?php endforeach; ?>
    </table>


</div>
