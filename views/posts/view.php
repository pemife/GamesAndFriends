<?php

use yii\grid\GridView;
use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $model app\models\Posts */

$this->title = $model->titulo;
$this->params['breadcrumbs'][] = ['label' => 'Posts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$url = Url::to(['posts/votar']);
$uId = Yii::$app->user->isGuest ? false : Yii::$app->user->id;
$pId = $model->id;

$js = <<<SCRIPT
$("#botonVoto").click(votar);
function votar(e){
  e.preventDefault();
  $.ajax({
    method: 'POST',
    url: '$url',
    data: {pId: $pId, uId: $uId},
    success: function(result){
      if (result) {
        $("#numeroVotos").html(result);
        cambiarIcono();
      } else {
        alert('Ha ocurrido un error al votar');
      }
    }
  });
}

function cambiarIcono(){
    var claseBoton = $("#botonVoto").attr('class');
    if (claseBoton == 'fas fa-star') {
        $("#botonVoto").attr('class', 'fas fa-star-empty');
    } else {
        $("#botonVoto").attr('class', 'fas fa-star');
    }
}
SCRIPT;
$this->registerJs($js);
?>
<div class="posts-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (!empty($model->usuario)) : ?>
        <?php if ($uId === $model->usuario->id) : ?>
        <p>
            <?= Html::a('Modificar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary mr-2']) ?>
            <?= Html::a('Borrar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estas seguro de querer borrar este elemento?',
                'method' => 'post',
            ],
            ]) ?>
            </p>
        <?php endif ?>
    <?php endif ?>

    <h3>
        <?= Html::a('', 'javascript:void(0)', [
        'class' => $usuarioHaVotado ? 'fas fa-star' : 'fas fa-star-empty',
        'title' => 'Votar Post',
        'id' => 'botonVoto',
        ]) ?>
    </h3>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'desarrollo:ntext',
            'created_at:RelativeTime:Creado',
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
                'attribute' => 'usuario.nombre:text:Usuario',
                'label' => 'Usuario',
                'format' => 'raw',
                'value' => function ($model) {
                    if (empty($model->usuario_id)) {
                        return '<span class="text-danger">Eliminado</span>';
                    }
                    return Html::encode($model->usuario->nombre);
                }
            ],
            [
                'attribute' => 'votos',
                'contentOptions' => [
                    'id' => 'numeroVotos',
                ]
            ]
        ],
    ]) ?>

<h2>Comentarios</h2>

<?php if (!Yii::$app->user->isGuest) {
    echo Html::a('Comentar', ['comentarios/create', 'pId' => $model->id], ['class' => 'btn btn-success mb-2']);
}?>
<?= GridView::widget([
        'dataProvider' => $comentariosProvider,
        'columns' => [
            [
                'attribute' => 'usuario.nombre',
                'format' => 'raw',
                'value' => function ($model) {
                    if (empty($model->usuario_id)) {
                        return '<span class="text-danger">Eliminado</span>';
                    }
                    return Html::encode($model->usuario->nombre);
                }
            ],
            'texto:text:Comentario',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete} {reportar}',
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        if (empty($model->usuario)) {
                            return '';
                        }
                        if (Yii::$app->user->id != $model->usuario->id) {
                            return '';
                        }
                        return Html::a(
                            '<span class="fas fa-pencil"></span>',
                            [
                                '/comentarios/update',
                                'id' => $model->id,
                            ],
                            [
                                'title' => 'editar comentario',
                            ]
                        );
                    },
                    'delete' => function ($url, $model, $key) {
                        if (empty($model->usuario)) {
                            return '';
                        }
                        if (Yii::$app->user->id != $model->usuario->id) {
                            return '';
                        }
                        return Html::a(
                            '<span class="fas fa-trash"></span>',
                            [
                                'comentarios/delete',
                                'id' => $model->id,
                            ],
                            [
                                'data' => [
                                  'method' => 'post',
                                  'confirm' => '¿Estas seguro de borrar el comentario?(Esta accion no se puede deshacer)',
                                ],
                                'title' => 'borrar comentario',
                            ]
                        );
                    },
                    'reportar' => function ($url, $model, $action) {
                        if (Yii::$app->user->isGuest) {
                            return '';
                        };
                        
                        return Html::a('', ['comentarios/reportar', 'cId' => $model->id], [
                            'class' => 'fas fa-exclamation-sign',
                            'title' => 'Reportar comentario',
                            'style' => [
                                'color' => 'red',
                            ],
                            'data-confirm' => '¿Confirmas querer reportar el comentario?',
                        ]);
                    }
                ]
            ],
        ]
    ]); ?>

</div>
