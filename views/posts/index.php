<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PostsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Posts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="posts-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Crear Posts', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'titulo',
            [
                'attribute' => 'juego.titulo',
                'format' => 'text',
                'label' => 'Juego',
                'contentOptions' => [
                    'itemscope' => true,
                    'itemtype' => 'https://schema.org/VideoGame',
                    'itemprop' => 'name'
                ]
            ],
            [
                'attribute' => 'usuario.nombre',
                'format' => 'raw',
                'label' => 'Usuario',
                'value' => function ($model) {
                    if (empty($model->usuario_id)) {
                        return '<span class="text-danger">Eliminado</span>';
                    }
                    return Html::encode($model->usuario->nombre);
                }
            ],
            'created_at:RelativeTime',
            // 'desarrollo:ntext',
            // 'media',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        if (Yii::$app->user->isGuest || Yii::$app->user->id != $model->usuario->id) {
                            return '';
                        }
                        return Html::a('', ['update', 'id' => $model->id], [
                            'class' => 'glyphicon glyphicon-pencil',
                            'title' => 'Editar post',
                            'style' => [
                                'color' => 'red',
                            ],
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        if (Yii::$app->user->isGuest || Yii::$app->user->id != $model->usuario->id) {
                            return '';
                        }
                        return Html::a('', ['delete', 'id' => $model->id], [
                            'class' => 'glyphicon glyphicon-trash',
                            'title' => 'Borrar post',
                            'style' => [
                                'color' => 'red',
                            ],
                            'data' => [
                                'confirm' => 'Seguro que quieres borrar el post?',
                                'method' => 'POST',
                            ],
                        ]);
                    }
                ]
            ],
        ],
    ]); ?>


</div>
