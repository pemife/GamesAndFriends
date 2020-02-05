<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'En venta';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="ventas-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Ventas', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= Gridview::widget([
        'dataProvider' => $copiasProvider,
        'columns' => [
            'copia.juego.titulo',
            // [
            //     'label' => 'Generos',
            //     'value' => function($model){
            //         var_dump($model);
            //         $generos = "";
            //         // foreach ($model->copia->juego->etiquetas as $genero) {
            //         //     $generos += $genero->nombre;
            //         // }
            //         return $model->copia->id;
            //     }
            // ],

        ],
    ]); ?>

</div>
