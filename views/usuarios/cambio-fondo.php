<?php

use yii\bootstrap4\Html;
use yii\helpers\Url;

$url = Url::to(['cambio-fondo', 'id' => $model->id], true);

$js = <<<SCRIPT

$('[name="botonSeleccionar"]').click(function () {
  $('.imagenPerfil').removeClass('imagenSeleccionada');
  $(this).parent().siblings('.imagenPerfil').toggleClass('imagenSeleccionada');
  // $(this).toogleClass('imagenSeleccionada');
  $('#botonGuardar').show();
  var imagenSrc = $('.imagenSeleccionada').attr('src');
  $(window.opener.document).find('body').attr('background-image', imagenSrc);
});

$('#botonGuardar').click(function () {
  // console.log($('.imagenSeleccionada').attr('name'));
  var imagenSrc = $('.imagenSeleccionada').attr('src');
  var imagenName = $('.imagenSeleccionada').attr('name');
  $(window.opener.document).find('body').attr('background-image', imagenSrc);
  $.post(
    '$url',
    { fondo_key: imagenName },
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

<h1 class="text-center bg-info rounded-pill">Imagenes de fondo</h1>
<?php for ($i = 1; $i <= 6; $i++) :
    $cmd = $s3->getCommand('GetObject', [
        'Bucket' => 'gamesandfriends',
        'Key' => 'Usuarios/fondos/personalizacion' . $i . '.jpg',
    ]);

    if (getenv('MEDIA')) {
        $request = $s3->createPresignedRequest($cmd, '+20 minutes');
    }
    ?>

    <span class="contenedorImagen mb-4 mt-2">
        <?= Html::img(
            getenv('MEDIA') ? (string)$request->getUri() : '',
            [
                'class' => 'imagenPerfil',
                'width' => 600,
                'height' => 400,
                'name' => 'personalizacion' . $i . '.jpg',
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

<?= Html::a('Guardar', 'javascript:void(0)', ['class' => 'btn btn-success', 'id' => 'botonGuardar']) ?>
