<?php

use yii\helpers\Html;
use yii\widgets\ListView;

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
      <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_vistaVentas',
        ]) ?>
    </table>


</div>
