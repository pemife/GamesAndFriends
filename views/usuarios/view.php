<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Usuarios */

$this->title = $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$enlaceFoto = null; // Cambiar con enlace de AWS

$puedeModificar = (Yii::$app->user->id === 1 || Yii::$app->user->id === $model->id);
$enlaceMod = $puedeModificar ? Url::to(['usuarios/update', 'id' => $model->id]) : '#';
$enlaceBor = $puedeModificar ? Url::to(['usuarios/delete', 'id' => $model->id]) : '#';
$enlacePass = $puedeModificar ? Url::to(['usuarios/cambio-pass', 'id' => $model->id]) : '#';
// $enlaceFoto = $enlaceFoto ? 'enlace' : 'https://www.library.caltech.edu/sites/default/files/styles/headshot/public/default_images/user.png?itok=1HlTtL2d';
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
    <div class="nombreOpciones">
        <div class="titulo">
            <h1><?= Html::encode($model->nombre) ?></h1>
        </div>
        <div class="opciones">
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
        </div>
    </div>

    <img src="<?= $enlaceFoto ?>" width="150" height="150">
    <br><br>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'nombre',
            'created_at:Date',
            'email:email',
            'biografia:ntext',
            'fechanac',
        ],
    ]) ?>

</div>
