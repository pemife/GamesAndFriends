<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Copias */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Copias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="copias-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if(Yii::$app->user->id === 1) : ?>
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
            'id',
            'juego_id',
            'propietario_id',
            'clave',
            'plataforma_id',
        ],
    ]) ?>

</div>
