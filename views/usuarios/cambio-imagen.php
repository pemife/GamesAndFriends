<?php

use yii\bootstrap4\Html;

?>
<style>
  .contenedorImagen {
    display: inline-block;
    position: relative ;
  }

  .imagenPerfil {
    transition: 0.3s ;
  }

  .botonImagen {
    position: absolute ;
    top: 50% ;
    left: 50% ;
    transform: translate(-50%, -50%) ;
    opacity: 0 ;
    transition: 0.3s ;
  }

  .contenedorImagen:hover > .imagenPerfil {opacity: 0.6 }
  .contenedorImagen:hover > .botonImagen {opacity: 1}

</style>

<?php foreach ($model->arrayCarpetasImagenes as $carpeta => $datos) : ?>
    <h1 class="text-center bg-info rounded-pill"><?= $datos['nombre'] ?></h1>
    <?php for ($i = 1; $i <= $datos['total']; $i++) :
        $cmd = $s3->getCommand('GetObject', [
            'Bucket' => 'gamesandfriends',
            'Key' => 'Usuarios/default/' . $carpeta . '/' . $i . '.jpg',
        ]);

        $request = $s3->createPresignedRequest($cmd, '+20 minutes');

        ?>

        <span class="contenedorImagen mb-4 mt-2">
            <?= Html::img(
                (string)$request->getUri(),
                [
                    'class' => 'rounded-circle imagenPerfil',
                    'width' => 150,
                    'height' => 150,
                    'name' => $carpeta . '/' . $i . '.jpg',
                ]
            ) ?>
            <span class="botonImagen bg-light rounded-circle p-2">
                <?= Html::a(
                    '',
                    '#',
                    [
                    'class' => 'glyphicon glyphicon-edit rounded-circle',
                    'id' => 'botonEdit'
                    ]
                ) ?>
            </span>
        </span>
    <?php endfor; ?>
<?php endforeach; ?>
