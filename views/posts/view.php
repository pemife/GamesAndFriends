<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

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
        <?= Html::a('', '#', [
        'class' => $usuarioHaVotado ? 'glyphicon glyphicon-star' : 'glyphicon glyphicon-star-empty',
        'title' => 'Votar Post',
        'id' => 'botonVoto',
        ]) ?>
    </h3>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'desarrollo:ntext',
            'created_at:RelativeTime',
            'juego.titulo:text:Juego',
            'usuario.nombre:text:Usuario',
            [
                'attribute' => 'votos',
                'contentOptions' => [
                    'id' => 'numeroVotos',
                ]
            ]
        ],
    ]) ?>

</div>
