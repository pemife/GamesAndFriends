<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\bootstrap4\Breadcrumbs;
use app\assets\AppAsset;
use app\models\Precios;
use Aws\S3\S3Client;

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
<?php

$s3 = new S3Client([
    'version' => 'latest',
    'region' => 'eu-west-2',
    'credentials' => [
        'key' => getenv('KEY'),
        'secret' => getenv('SECRET'),
        'token' => null,
        'expires' => null,
    ],
]);

$urlFondo = '';
$urlLogo = '';

if (getenv('MEDIA')) {
    $cmd = $s3->getCommand('GetObject', [
        'Bucket' => 'gamesandfriends',
        'Key' => 'logov2.png',
    ]);

    $request = $s3->createPresignedRequest($cmd, '+20 minutes');

    $urlLogo = (string)$request->getUri();

    $cmd = $s3->getCommand('GetObject', [
        'Bucket' => 'gamesandfriends',
        'Key' => 'fondo.png',
    ]);

    $request = $s3->createPresignedRequest($cmd, '+20 minutes');

    $urlFondo = (string)$request->getUri();
}
?>
<style>
body {
    background-image: url(<?= $urlFondo ?>);
}

.table {
    background-color: white;
}
</style>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    $usuarioNombre = '';
    $usuarioId = '';
    if (!Yii::$app->user->isGuest) {
        $usuarioNombre = Yii::$app->user->identity->nombre;
        $usuarioId = Yii::$app->user->identity->id;
    }

    NavBar::begin([
        'brandLabel' => Html::img($urlLogo, ['height' => 30, 'width' => 100]),
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-dark bg-dark navbar-expand-md fixed-top container-fluid',
        ],
        'collapseOptions' => [
            'class' => 'justify-content-end',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'items' => [
            ['label' => 'Novedades', 'url' => ['/juegos/novedades']],
            [
                'label' => 'Mercado',
                'items' => [
                    ['label' => '2ª Mano', 'url' => ['/ventas/index']],
                    ['label' => 'Juegos', 'url' => ['/juegos/index']],
                    ['label' => 'Productos', 'url' => ['/productos/index']],
                ],
            ],
            [
                'label' => 'Comunidad',
                'items' => [
                    ['label' => 'Posts', 'url' => ['posts/index']],
                    ['label' => 'Opiniones de Críticos', 'url' => ['criticas/index']]
                ]
            ],
            [
                'label' => 'Usuarios',
                'visible' => !Yii::$app->user->isGuest,
                'items' => [
                    ['label' => 'Indice', 'url' => ['usuarios/index']],
                    ['label' => 'Bloqueados', 'url' => ['usuarios/index-filtrado', 'texto' => false, 'tipoLista' => 'bloqueados']],
                    ['label' => 'Críticos', 'url' => ['usuarios/index-filtrado', 'texto' => false, 'tipoLista' => 'criticos']]
                ]
            ],
            ['label' => 'Login', 'url' => ['/site/login'], 'visible' => Yii::$app->user->isGuest],
            ['label' => 'Registrar', 'url' => ['/usuarios/create'], 'visible' => Yii::$app->user->isGuest],
            [
                'label' => $usuarioNombre,
                'items' => [
                 ['label' => 'Puesto en Venta', 'url' => ['/ventas/mis-ventas', 'u' => $usuarioId]],
                 ['label' => 'Ver perfil', 'url' => ['usuarios/view', 'id' => Yii::$app->user->id]],
                 ['label' => 'Modificar perfil', 'url' => ['usuarios/update', 'id' => Yii::$app->user->id]],
                 ['label' => 'Añadir a inventario', 'url' => ['usuarios/anadir-inventario']],
                 Html::beginForm(['site/logout'], 'post')
                 . Html::submitButton(
                     '&nbsp;&nbsp;Logout (' . Html::encode($usuarioNombre) . ')',
                     ['class' => 'btn btn-link logout']
                 )
                 . Html::endForm()],
                 'visible' => !Yii::$app->user->isGuest
            ],
            [
                'label' => 'Carrito (' . Precios::totalCarrito() . ')', 'url' => ['juegos/carrito-compra'], 'visible' => !Yii::$app->user->isGuest, 'class' => 'carrito'
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

        <p class="float-left ml-2">| <?= Html::a('Contacto', ['site/contact']) ?></p>

        <p class="float-left ml-2">|  <?= Html::a('Acerca de', ['site/about']) ?></p>

        <p class="float-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
