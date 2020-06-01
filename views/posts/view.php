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
    if (claseBoton == 'glyphicon glyphicon-star') {
        $("#botonVoto").attr('class', 'glyphicon glyphicon-star-empty');
    } else {
        $("#botonVoto").attr('class', 'glyphicon glyphicon-star');
    }
}
SCRIPT;
$this->registerJs($js);
?>
<div class="posts-view">

    <h1><?= Html::encode($this->title) ?></h1>

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

    <h3>
        <?= Html::a('', 'javascript:void(0)', [
        'class' => $usuarioHaVotado ? 'glyphicon glyphicon-star' : 'glyphicon glyphicon-star-empty',
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
            'usuario.nombre:text:Usuario',
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
            'usuario.nombre',
            'texto:text:Comentario',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete} {reportar}',
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        if (Yii::$app->user->id != $model->usuario->id) {
                            return '';
                        }
                        return Html::a(
                            '<span class="glyphicon glyphicon-pencil"></span>',
                            [
                                '/comentarios/update',
                                'id' => $model->id,
                            ],
                            [
                                'title' => 'editar comentario',
                            ]
                        );
                    },
                    'delete' => function ($url, $model, $key){
                        if (Yii::$app->user->id != $model->usuario->id) {
                            return '';
                        }
                        return Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
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
                            'class' => 'glyphicon glyphicon-fire',
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
