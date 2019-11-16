<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ventas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ventas-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Ventas', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'label' => 'Desde',
                'value' => 'created_at',
                'format' => 'relativeTime',
            ],
            [
                'label' => 'Vendedor',
                'value' => 'vendedor.nombre',
            ],
            'producto.nombre',
            'copia.juego.titulo',
            'precio:currency',
        ],
    ]); ?>


</div>
