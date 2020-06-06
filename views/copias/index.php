<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CopiasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Lista de copias';
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
            [
                'attribute' => 'propietario.nombre',
                'format' => 'raw',
                'label' => 'Propietario',
                'value' => function ($model) {
                    if (empty($model->propietario_id)) {
                        return '<span class="text-danger">Eliminado</span>';
                    }
                    return Html::encode($model->propietario->nombre);
                }
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
