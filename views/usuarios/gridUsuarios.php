<?php

use yii\grid\GridView;
use yii\helpers\Html;

?>


<?= GridView::widget([
        'dataProvider' => $dataProvider,
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
                'template' => '{relaciones} {view} {bloquear} {update} {delete} {seguir}',
                'buttons' => [
                    'relaciones' => function ($url, $model, $key) {
                        if (Yii::$app->user->isGuest || Yii::$app->user->id == $model->id) {
                            return '';
                        }

                        switch ($model->estadoRelacion(Yii::$app->user->id)) {
                            case 0:
                                return Html::a('', '#', ['class' =>'glyphicon glyphicon-time', 'title' => 'Peticion de amistad pendiente']);
                            break;
                            case 1:
                                return Html::a('', ['borrar-amigo', 'amigoId' => $model->id], [
                                    'class' =>'glyphicon glyphicon-remove',
                                    'title' => 'Borrar amigo',
                                    'style' => [
                                        'color' => 'red',
                                    ],
                                    'data-confirm' => '¿Seguro que quieres borrar al usuario ' . $model->nombre . ' de tus amigos?'
                                ]);
                            break;
                            case 2:
                            break;
                            case 3:
                                return Html::a('', ['desbloquear-usuario', 'usuarioId' => $model->id], [
                                    'class' =>'glyphicon glyphicon-remove-circle',
                                    'title' => 'Desbloquear usuario',
                                    'style' => [
                                        'color' => 'red',
                                    ],
                                    'data-confirm' => '¿Seguro que quieres desbloquear al usuario ' . $model->nombre . '?'
                                ]);
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
                        
                        if (Yii::$app->user->id == 1 || $model->esAmigo(Yii::$app->user->id)) {
                            return Html::a('', ['view', 'id' => $model->id], ['class' => 'glyphicon glyphicon-eye-open']);
                        }
                    },

                    'update' => function ($url, $model, $key) {
                        if (Yii::$app->user->isGuest) {
                            return '';
                        }
                        
                        if (Yii::$app->user->id == 1 || Yii::$app->user->id == $model->id) {
                            return Html::a('', ['update', 'id' => $model->id], [
                                'class' => 'glyphicon glyphicon-pencil',
                                'title' => 'Editar perfil',
                                'style' => [
                                    'color' => 'red',
                                ],
                            ]);
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
                                'style' => [
                                    'color' => 'red',
                                ],
                                'data' => [
                                    'confirm' => 'Seguro que quieres borrar el perfil?',
                                    'method' => 'POST',
                                ],
                            ]);
                        }
                    },

                    'bloquear' => function ($url, $model, $action) {
                        if (Yii::$app->user->isGuest ||$model->estadoRelacion(Yii::$app->user->id) == 3 || $model->id == Yii::$app->user->id) {
                            return '';
                        };
                        
                        return Html::a('', ['bloquear-usuario', 'usuarioId' => $model->id], [
                            'class' => 'glyphicon glyphicon-ban-circle',
                            'title' => 'Bloquear usuario',
                            'style' => [
                                'color' => 'red',
                            ],
                            'data-confirm' => '¿Confirmas querer bloquear al usuario ' . $model->nombre . '?',
                        ]);
                    },

                    // 'seguir' => function ($url, $model, $action) {
                    //     if (Yii::$app->user->isGuest || !$model->es_critico) {
                    //         if ($model->esSeguidoPor(Yii::$app->user->id)) {
                    //             return Html::a('', ['anadir-quitar-critico', 'usuarioId' => $model->id], [
                    //                 'class' => 'glyphicon glyphicon-star-empty',
                    //                 'title' => 'Dejar de seguir crítico',
                    //                 'data-confirm' => '¿Confirmas dejar de seguir al crítico ' . $model->nombre . '?',
                    //             ]);
                    //         }
                    //         return '';
                    //     }

                    //     return Html::a('', ['anadir-quitar-critico', 'usuarioId' => $model->id], [
                    //         'class' => 'glyphicon glyphicon-star',
                    //         'title' => 'Seguir crítico',
                    //         'data-confirm' => '¿Confirmas querer seguir al crítico ' . $model->nombre . '?',
                    //     ]);
                    // }
                ]
            ],
        ],
    ]); ?>