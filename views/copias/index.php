<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CopiasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Mis Copias';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="copias-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Copias', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            'juego.titulo',
            'plataforma.nombre:text:Plataforma',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
