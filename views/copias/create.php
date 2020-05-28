<?php

use yii\bootstrap4\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Copias */

$this->title = 'Añadir juego a inventario';
$this->params['breadcrumbs'][] = ['label' => 'Copias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="copias-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('creaCopia', [
        'model' => $model,
        'listaJuegos' => $listaJuegos,
        'listaPlataformas' => $listaPlataformas,
    ]) ?>

</div>
