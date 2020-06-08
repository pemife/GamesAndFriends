<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\bootstrap4\Html;

$this->title = $name;
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>

    <p>
        El error mostrado arriba ha ocurrido procesando tu solicitud.
    </p>
    <p>
        Por favor, <?= Html::a('contacta', ['site/contact']) ?> conmigo si piensas que es un error del servidor. Gracias.
    </p>

</div>
