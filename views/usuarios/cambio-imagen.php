<?php

use yii\bootstrap4\Html;
use yii\helpers\Url;

$url = Url::to(['cambio-imagen', 'id' => $model->id], true);

$js = <<<SCRIPT

$('[name="botonSeleccionar"]').click(function () {
  $('.imagenPerfil').removeClass('imagenSeleccionada');
  $(this).parent().siblings('.imagenPerfil').toggleClass('imagenSeleccionada');
  // $(this).toogleClass('imagenSeleccionada');
  $('#botonGuardar').show();
});

$('#botonGuardar').click(function () {
  // console.log($('.imagenSeleccionada').attr('name'));
  var imagenSrc = $('.imagenSeleccionada').attr('src');
  var imagenName = $('.imagenSeleccionada').attr('name');
  $(window.opener.document).find('.imagenPerfil').attr('src', imagenSrc);
  $.post(
    '$url',
    { img_key: imagenName },
    function (data) {
      if (data) {
        document.html = data;
      } else {
        window.opener.location.reload();
        window.close();
      }
    }
  );
});

SCRIPT;

$this->registerJS($js);
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

  .imagenSeleccionada {
    border-style: solid;
    border-color: green;
    border-width: 4px;
  }

  .contenedorImagen:hover > .imagenPerfil {opacity: 0.6 }
  .contenedorImagen:hover > .botonImagen {opacity: 1}

  #botonGuardar {
    position: fixed;
    bottom: 20px;
    left: 20px;
    display: none;
  }

  .navbar {
    display: none;
  }

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
                    'javascript:void(0)',
                    [
                    'class' => 'glyphicon glyphicon-ok',
                    'name' => 'botonSeleccionar'
                    ]
                ) ?>
            </span>
        </span>
    <?php endfor; ?>
<?php endforeach; ?>

<?= Html::a('Guardar', 'javascript:void(0)', ['class' => 'btn btn-success', 'id' => 'botonGuardar']) ?>
