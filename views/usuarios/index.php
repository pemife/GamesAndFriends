<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UsuariosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Usuarios';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="usuarios-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'nombre',
            // 'password',
            'created_at',
            // 'token',
            // 'email:email',
            // 'biografia:ntext',
            // 'fechanac',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{relaciones} {view} {bloquear} {update} {delete}',
                'buttons' => [
                    'relaciones' => function ($url, $model, $key) {
                        if (Yii::$app->user->isGuest || Yii::$app->user->id == $model->id) {
                            return '';
                        }

                        switch ($model->estadoRelacion(Yii::$app->user->id)) {
                            case 0:
                                return Html::a('', '', ['class' =>'glyphicon glyphicon-time', 'title' => 'Peticion de amistad pendiente']);
                            break;
                            case 1:
                                return Html::a('', ['borrar-amigo', 'amigoId' => $model->id], [
                                    'class' =>'glyphicon glyphicon-remove',
                                    'title' => 'Borrar amigo',
                                    'data-confirm' => 'Seguro que quieres borrar al usuario ' . $model->nombre . ' de tus amigos?'
                                ]);
                            break;
                            case 2:
                            break;
                            case 3:
                                return Html::a('', '', ['class' =>'glyphicon glyphicon-remove-circle', 'title' => 'Has bloqueado a este usuario']);
                            break;
                            case 5:
                                return Html::a('', ['mandar-peticion', 'amigoId' => $model->id], ['class' => 'glyphicon glyphicon-plus', 'title' => 'Mandar peticion de amistad a ' . $model->nombre]);
                            break;
                        }
                    },

                    'view' => function ($url, $model, $key) {
                        if (Yii::$app->user->isGuest) {
                            return '';
                        }
                        
                        if (Yii::$app->user->id == 1) {
                            return Html::a('', ['view', 'id' => $model->id], ['class' => 'glyphicon glyphicon-eye-open']);
                        }
                        
                        if (!$model->esAmigo(Yii::$app->user->id)) {
                            return '';
                        }
                    },

                    'update' => function ($url, $model, $key) {
                        if (Yii::$app->user->isGuest) {
                            return '';
                        }
                        
                        if (Yii::$app->user->id == 1 || Yii::$app->user->id == $model->id) {
                            return Html::a('', ['update', 'id' => $model->id], ['class' => 'glyphicon glyphicon-pencil']);
                        }
                    },

                    'delete' => function ($url, $model, $key) {
                        if (Yii::$app->user->isGuest) {
                            return '';
                        }
                        
                        if (Yii::$app->user->id == 1 || Yii::$app->user->id == $model->id) {
                            return Html::a('', ['delete', 'id' => $model->id], [
                                'class' => 'glyphicon glyphicon-trash',
                                'title' => 'Borrar perfil',
                                'data' => [
                                    'confirm' => 'Seguro que quieres borrar el perfil?',
                                    'method' => 'POST',
                                ],
                            ]);
                        }
                    },

                    'bloquear' => function ($url, $model, $action) {
                        if ($model->estadoRelacion(Yii::$app->user->id) == 3) {
                            return '';
                        }
                        
                        return Html::a('', ['bloquear-usuario', 'usuarioId' => $model->id], [
                            'class' => 'glyphicon glyphicon-ban-circle',
                            'data-confirm' => '¿Confirmas querer bloquear al usuario ' . $model->nombre . '?',
                        ]);
                    }
                ]
            ],
        ],
    ]); ?>


</div>
