<?php
// use yii\helpers\Html;

use yii\helpers\Html;
use yii\widgets\ListView;
$this->title = 'Lista de deseos';
?>

<h1>Lista de Deseos de <?= $usuario->nombre ?></h1>

<?= ListView::widget([
    'dataProvider' => $deseadosProvider,
    'itemOptions' => ['class' => 'item'],
    'viewParams' => ['usuario' => $usuario],
    'itemView' => 'juegoDeseado',
]) ?>