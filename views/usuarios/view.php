<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Usuarios */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);


$puedeModificar = (Yii::$app->user->id === 1 || Yii::$app->user->id === $model->id);
$enlaceMod = $puedeModificar ? Url::to(['usuarios/update', 'id' => $model->id]) : '#';
$enlaceBor = $puedeModificar ? Url::to(['usuarios/delete', 'id' => $model->id]) : '#';
$enlacePass = $puedeModificar ? Url::to(['usuarios/cambio-pass', 'id' => $model->id]) : '#';
?>

<style>
  .nombreOpciones{
    display: inline-flex;
    justify-content: space-between;
    width: 100%;
  }

  .opciones{
    margin-top: 30px;
  }

  .titulo{
    display: inline-flex;
    justify-content: space-between;
  }

  .flex-container{
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
  }

  .flex-container > div {
    flex-grow: 1;
    width: 32%;
    padding: 10px;
  }
</style>
<div class="usuarios-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <span class="dropdown">
      <button class="glyphicon glyphicon-cog" type="button" data-toggle="dropdown" style="height: 30px; width: 30px;"></button>
      <ul class="dropdown-menu pull-right">
          <li>
            <?= Html::a('Modificar perfil', $enlaceMod, [
                'class' => 'btn btn-link',
                'disabled' => !$puedeModificar,
              ]) ?>
          </li>
          <li>
            <?= Html::a('Borrar perfil', $enlaceBor, [
              'class' => 'btn btn-link',
              'disabled' => !$puedeModificar,
              'data' => $puedeModificar ?
                [
                  'confirm' => 'Seguro que quieres borrar el perfil?',
                  'method' => 'post',
                ] :
                [],
            ]) ?>
          </li>
          <li>
            <?= Html::a('Cambiar contraseña', $enlacePass, [
                'class' => 'btn btn-link',
                'disabled' => !$puedeModificar,
                'data-method' => 'POST',
                'data-params' => [
                  'tokenUsuario' => $model->token,
                ],
              ]) ?>
          </li>
        </ul>
      </span>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'nombre',
            'password',
            'created_at',
            'token',
            'email:email',
            'biografia:ntext',
            'fechanac',
        ],
    ]) ?>

</div>
