<?php

use yii\bootstrap4\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Juegos */

$this->title = $model->titulo;
$this->params['breadcrumbs'][] = ['label' => 'Juegos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="juegos-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <span>
      <?= Html::img('urlDeImagen', ['height' => 200, 'width' => 300]) ?>
      <?php
        if($precioMinimo != null){
            ?>
                <h3>En venta desde <?= Html::encode($precioMinimo) ?>€</h3>
            <?php
        } else {
            ?>
                <h3>No hay ninguna copia en venta actualmente</h3>
            <?php
        }
      ?>
      <?= Html::a(
          'Ver en mercado',
          [
            'ventas/ventas-item',
            'id' => $model->id,
            'esProducto' => false
          ],
          ['class' => 'btn btn-success']
        ) ?>
    </span>

    </br></br>

    <?php if (Yii::$app->user->id === 1) : ?>
      <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary mr-2']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
          'class' => 'btn btn-danger',
          'data' => [
            'confirm' => '¿Estas seguro de querer borrar este elemento?',
            'method' => 'post',
          ],
          ]) ?>
        </p>
    <?php endif ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'descripcion:ntext',
            'fechalan',
            'dev',
            'publ',
        ],
    ]) ?>

    <h3>Críticas</h3>

    <p>
        <?php if($tieneJuego){
          echo Html::a('Opinar', ['criticas/critica-juego', 'juego_id' => $model->id], ['class' => 'btn btn-success']);
        } ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'usuario.nombre',
            'opinion',
            'valoracion',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model, $key){
                        return Html::a(
                          '<span class="glyphicon glyphicon-pencil"></span>',
                          [
                              '/criticas/update',
                              'id' => $model->id,
                          ],
                          [
                              'title' => 'editar crítica',
                          ]
                        );
                    },
                    'delete' => function ($url, $model, $key){
                        return Html::a(
                          '<span class="glyphicon glyphicon-trash"></span>',
                          [
                              'criticas/delete',
                              'id' => $model->id,
                          ],
                          [
                              'data' => [
                                'method' => 'post',
                                'confirm' => '¿Estas seguro de borrar la crítica?(Esta accion no se puede deshacer)',
                              ],
                              'title' => 'borrar crítica',
                          ]
                        );
                    }
                ]
            ],
        ]
    ]); ?>


</div>
