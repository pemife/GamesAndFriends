<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;
use app\models\Usuarios;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CopiasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::$app->user->id == $modelUsuario->id ? 'Mis juegos' : 'Juegos de ' . $modelUsuario->nombre;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="copias-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Crear Copias', ['create'], ['class' => 'btn btn-success']) ?>
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
