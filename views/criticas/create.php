<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Criticas */

$this->title = 'Create Criticas';
$this->params['breadcrumbs'][] = ['label' => 'Criticas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="criticas-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
