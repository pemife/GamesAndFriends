<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;
use yii\captcha\Captcha;

$this->title = 'Contacto';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('contactFormSubmitted')) : ?>

        <div class="alert alert-success">
            Gracias por el feedback. Responderé lo antes posible.
        </div>

    <?php else : ?>

        <p>
            Si tienes intereses de negocios, o otras preguntas, por favor, rellena el formulario para contactar conmigo.
            Gracias.
        </p>

        <div class="row">
            <div class="col-xl-8">

                <?php $form = ActiveForm::begin([
                    'id' => 'contact-form',
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'horizontalCssClasses' => ['label' => 'col-sm-2'],
                    ],
                ]); ?>

                    <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>

                    <?= $form->field($model, 'email') ?>

                    <?= $form->field($model, 'subject') ?>

                    <?= $form->field($model, 'body')->textarea(['rows' => 6]) ?>

                    <?= $form->field($model, 'verifyCode')->widget(Captcha::className(), [
                        'imageOptions' => ['class' => 'col-sm-3', 'style' => 'padding: 0'],
                        'options' => ['class' => 'form-control col-sm-7', 'style' => 'display: inline'],
                    ]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Enviar', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
                    </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>

    <?php endif; ?>
</div>
