<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\bootstrap4\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    $usuarioNombre = '';
    $usuarioId = '';
    if(!Yii::$app->user->isGuest){
        $usuarioNombre = Yii::$app->user->identity->nombre;
        $usuarioId = Yii::$app->user->identity->id;
    }

    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-dark bg-dark navbar-expand-md fixed-top',
        ],
        'collapseOptions' => [
            'class' => 'justify-content-end',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'items' => [
            ['label' => '2ª Mano', 'url' => ['/ventas/index']],
            ['label' => 'Tienda', 'url' => ['/productos/index']],
            ['label' => 'Login', 'url' => ['/site/login'], 'visible' => Yii::$app->user->isGuest],
            ['label' => 'Registrar', 'url' => ['/usuarios/create'], 'visible' => Yii::$app->user->isGuest],
            ['label' => 'Mis Ventas', 'url' => ['/ventas/mis-ventas', 'u' => $usuarioId], 'visible' => !Yii::$app->user->isGuest],
            [
                'label' => $usuarioNombre,
                'items' => [
                 ['label' => 'Modificar perfil', 'url' => Url::to(['usuarios/update', 'id' => Yii::$app->user->id])],
                 Html::beginForm(['site/logout'], 'post')
                 . Html::submitButton(
                    '&nbsp;&nbsp;Logout (' . $usuarioNombre . ')',
                    ['class' => 'btn btn-link logout'])
                 . Html::endForm()],
                 'visible' => !Yii::$app->user->isGuest
            ]
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="float-left">&copy; My Company <?= date('Y') ?></p>

        <p class="float-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
