<?php

use yii\bootstrap4\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Juegos */

$this->title = 'Update Juegos: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Juegos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="juegos-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'generosArray' => $generosArray,
        'plataformasArray' => $plataformasArray,
        'edadesValidas' => $edadesValidas,
    ]) ?>

</div>
