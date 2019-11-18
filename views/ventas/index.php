<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'En venta';
$this->params['breadcrumbs'][] = $this->title;
?>
<style media="screen">
    * {
        box-sizing: border-box;
    }

    .column {
        float: left;
        width: 50%;
        padding: 10px;
    }

    .row:after {
        content: "";
        display: table;
        clear: both;
    }
</style>
<div class="ventas-index">

    <?php
        // var_dump(sizeof($dataProvider->getModels()));
    ?>

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Ventas', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <div class="row">
        <div class="column">
            <br>

            <input type="text" name="busquedaJuegosNombre" placeholder="Buscar juegos por nombre">

            <br><br>

            <h1>Juegos</h1>

            <br>

            <table class="table" bgcolor="#b5bcc9">
              <tr>
                <th>Copia</th>
                <th>Usuario</th>
                <th>En venta desde</th>
                <th>Precio 2ª Mano</th>
                <th>Acciones</th>
              </tr>
                <?php
                    foreach ($dataProvider->getModels() as $venta):
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

        <div class="column">
            <br>

            <input type="text" name="busquedaProductosNombre" placeholder="Buscar productos por nombre">

            <br><br>

            <h1>Productos</h1>

            <br>

            <table class="table" bgcolor="#c9bbb5">
              <tr>
                <th>Producto</th>
                <th>Usuario</th>
                <th>En venta desde</th>
                <th>Precio 2ª Mano</th>
                <th>Acciones</th>
              </tr>
                <?php
                    foreach ($dataProvider->getModels() as $venta):
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
        </div>
    </div>

</div>
