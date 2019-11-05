<?php

use yii\helpers\Html;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ventas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ventas-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Crear venta', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <table>
      <tr>
        <th>Producto</th>
        <th>Usuario</th>
      </tr>
      <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_vistaVentas',
        ]) ?>
    </table>


</div>
