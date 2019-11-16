<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CopiasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Copias';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="copias-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Copias', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'juego_id',
            'poseedor_id',
            'clave',
            'plataforma_id',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
