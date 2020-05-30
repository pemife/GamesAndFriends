<?php

use yii\grid\GridView;
use yii\bootstrap4\Html;

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

                        $botones['peticion-amistad-pendiente'] = Html::a(
                            '',
                            '#',
                            [
                                'class' =>'glyphicon glyphicon-time',
                                'title' => 'Peticion de amistad pendiente'
                            ]
                        );

                        $botones['mandar-peticion-amistad'] = Html::a(
                            '',
                            ['mandar-peticion', 'amigoId' => $model->id],
                            [
                                'class' => 'glyphicon glyphicon-plus',
                                'title' => 'Mandar peticion de amistad a ' . $model->nombre
                            ]
                        );

                        $botones['borrar-amigo'] = Html::a(
                            '',
                            ['borrar-amigo','amigoId' => $model->id],
                            [
                                'class' =>'glyphicon glyphicon-remove',
                                'title' => 'Borrar amigo',
                                'style' => [
                                    'color' => 'red',
                                ],
                                'data-confirm' => '¿Seguro que quieres borrar al usuario ' . $model->nombre . ' de tus amigos?'
                            ]
                        );

                        $botones['desbloquear-usuario'] = Html::a(
                            '',
                            ['desbloquear-usuario', 'usuarioId' => $model->id],
                            [
                                'class' =>'glyphicon glyphicon-remove-circle',
                                'title' => 'Desbloquear usuario',
                                'style' => [
                                    'color' => 'red',
                                ],
                                'data-confirm' => '¿Seguro que quieres desbloquear al usuario ' . $model->nombre . '?'
                            ]
                        );

                        $botones['seguir-critico'] = Html::a(
                            '',
                            ['seguir-critico', 'uId' => $model->id],
                            [
                                'class' => 'glyphicon glyphicon-star',
                                'title' => 'Seguir crítico',
                                'data-confirm' => '¿Confirmas querer seguir al crítico ' . $model->nombre . '? (Si el critico es un amigo, dejara de serlo)',
                            ]
                        );

                        $botones['abandonar-critico'] = Html::a(
                            '',
                            ['abandonar-critico', 'uId' => $model->id],
                            [
                                'class' => 'glyphicon glyphicon-star-empty',
                                'title' => 'Dejar de seguir crítico',
                                'data-confirm' => '¿Confirmas dejar de seguir al crítico ' . $model->nombre . '?',
                            ]
                        );

                        switch ($model->estadoRelacion(Yii::$app->user->id)) {
                            case 0:
                                return $botones['peticion-amistad-pendiente'];
                            break;
                            case 1:
                                return
                                ($model->es_critico ? $botones['seguir-critico'] : '') . ' ' .
                                $botones['borrar-amigo'];
                            break;
                            case 2:
                            break;
                            case 3:
                                return $botones['desbloquear-usuario'];
                            break;
                            case 4:
                                if ($model->es_critico) {
                                    return $botones['abandonar-critico'] . ' ' .
                                    $botones['mandar-peticion-amistad'];
                                }
                                return $botones['mandar-peticion-amistad'];
                            break;
                            case 5:
                                return
                                ($model->es_critico ? $botones['seguir-critico'] : '') . ' ' .
                                $botones['mandar-peticion-amistad'];
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