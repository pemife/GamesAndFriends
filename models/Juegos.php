<?php

namespace app\models;

use Aws\S3\S3Client;

/**
 * Esta es la clase modelo para la tabla "juegos".
 *
 * @property int $id
 * @property string $titulo
 * @property string $descripcion
 * @property string $fechalan
 * @property string|null $descripcion
 * @property string|null $fechalan
 * @property string $dev
 * @property string $publ
 * @property bool $cont_adul
 * @property float $edad_minima
 * @property string|null $img_key
 *
 * @property Etiquetas[] $etiquetas
 * @property Posts[] $posts
 * @property Productos[] $productos
 */
class Juegos extends \yii\db\ActiveRecord
{
    private $_oferta;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'juegos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['edad_minima'], 'default', 'value' => 3],
            [['titulo', 'dev', 'publ', 'edad_minima'], 'required'],
            [['descripcion'], 'string'],
            [['fechalan'], 'safe'],
            [['titulo', 'dev', 'publ', 'img_key'], 'string', 'max' => 255],
            [['titulo'], 'unique'],
            [['img_key'], 'default', 'value' => 'sin-imagen.jpg'],
            [['img_key'], 'unique'],
            [['cont_adul'], 'default', 'value' => function ($model, $attribute) {
                return $this->edad_minima == 18;
            }],
            [['cont_adul'], 'boolean', 'trueValue' => true, 'falseValue' => false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'titulo' => 'Título',
            'descripcion' => 'Descripción',
            'fechalan' => 'Fecha de lanzamiento',
            'dev' => 'Desarrolladora',
            'publ' => 'Editora',
            'cont_adul' => 'Contenido adulto',
            'edad_minima' => 'Edad Minima',
        ];
    }

    /**
     * Devuelve la lista de todos los juegos.
     *
     * @return array Lista de juegos
     */
    public static function lista()
    {
        return self::find()
        ->indexBy('id')
        ->all();
    }

    /**
     * Devuelve la lista de todos los juegos con formato id => titulo.
     *
     * @return array Lista asociativa (id => titulo) de juegos
     */
    public static function listaAsociativa()
    {
        foreach (self::lista() as $juego) {
            $listaAsociativa[$juego->id] = $juego->titulo;
        }

        return $listaAsociativa;
    }

    /**
     * Devuelve query para [[Etiquetas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEtiquetas()
    {
        return $this->hasMany(Etiquetas::className(), ['id' => 'etiqueta_id'])->viaTable('juegos_etiquetas', ['juego_id' => 'id']);
    }

    /**
     * Devuelve query para [[Posts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Posts::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }

    /**
     * Devuelve query para [[Copias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCopias()
    {
        return $this->hasMany(Copias::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }

    /**
     * Devuelve query para [[Criticas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCriticas()
    {
        return $this->hasMany(Criticas::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }

    /**
     * Devuelve query para [[Deseados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeseados()
    {
        return $this->hasMany(Deseados::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }

    /**
     * Devuelve query para [[Ignorados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIgnorados()
    {
        return $this->hasMany(Ignorados::className(), ['id' => 'usuario_id'])->viaTable('juegos_ignorados', ['juego_id' => 'id']);
    }

    /**
     * Devuelve query para [[Plataformas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlataformas()
    {
        return $this->hasMany(Plataformas::className(), ['id' => 'plataforma_id'])->viaTable('precios', ['juego_id' => 'id']);
    }

    /**
     * Devuelve query para [[Precios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrecios()
    {
        return $this->hasMany(Precios::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }

    /**
     * Devuelve un array de Ids de generos.
     *
     * @return array
     */
    public function generosId()
    {
        $etiquetas = $this->etiquetas;
        if (!$etiquetas) {
            return [];
        }

        foreach ($etiquetas as $genero) {
            $generosIds[] = $genero->id;
        }

        return $generosIds;
    }

    /**
     * Devuelve un array de Ids de Plataformas
     *
     * @return array
     */
    public function plataformasId()
    {
        $plataformas = $this->plataformas;
        if (!$plataformas) {
            return [];
        }

        foreach ($plataformas as $plataforma) {
            $plataformasIds[] = $plataforma->id;
        }

        return $plataformasIds;
    }

    /**
     * Devuelve un array de nombres de géneros
     *
     * @return array
     */
    public function generosNombres()
    {
        $etiquetas = $this->etiquetas;
        if (!$etiquetas) {
            return [];
        }

        foreach ($etiquetas as $genero) {
            $generosNombres[] = $genero->nombre;
        }

        return $generosNombres;
    }

    /**
     * Devuelve un array de 4 juegos que coincidan en generos.
     *
     * @return array
     */
    public function similares()
    {
        return $this->find()
        ->joinWith('etiquetas')
        ->where(['in', 'etiqueta_id', $this->generosId()])
        ->andWhere(['!=', 'juego_id', $this->id])
        ->limit(4);
    }

    /**
     * Devuelve la url de la imagen en amazon S3 asignada al juego.
     *
     * @return string
     */
    public function getUrlImagen()
    {
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

        $carpeta = '';

        if ($this->img_key != 'sin-imagen.jpg') {
            $carpeta = str_replace(' ', '_', $this->titulo) . '/';
        }

        if (getenv('MEDIA')) {
            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => 'gamesandfriends',
                'Key' => 'Juegos/' . $carpeta . $this->img_key,
            ]);

            $request = $s3->createPresignedRequest($cmd, '+20 minutes');

            return (string) $request->getUri();
        }
        return '';
    }

    /**
     * Devuelve un array con las url de Amazon S3 asignadas a los trailers del juego
     *
     * @return array
     */
    public function getTrailers()
    {
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

        // El numero de trailers que tiene cada juego en AmazonS3
        switch ($this->id) {
            case 1:
            case 2:
                $numeroTrailers = 2;
            break;
            case 3:
                $numeroTrailers = 3;
            break;
            default:
                $numeroTrailers = 0;
        }

        $carpeta = str_replace(' ', '_', $this->titulo) . '/Trailers';

        $urlTrailers = [];

        if (getenv('MEDIA')) {
            for ($i = 1; $i <= $numeroTrailers; $i++) {
                $cmd = $s3->getCommand('GetObject', [
                    'Bucket' => 'gamesandfriends',
                    'Key' => 'Juegos/' . $carpeta . '/trailer' . $i . '.mp4',
                ]);

                $urlTrailers[] = (string) $s3->createPresignedRequest($cmd, '+20 minutes')->getUri();
            }
        }

        return $urlTrailers;
    }

    /**
     * Devuelve una url de una imagen que indica que no hay trailers del juego.
     *
     * @return string
     */
    public function sinTrailers()
    {
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

        if (getenv('MEDIA')) {
            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => 'gamesandfriends',
                'Key' => 'Juegos/sin-trailers.jpg',
            ]);

            return (string) $s3->createPresignedRequest($cmd, '+20 minutes')->getUri();
        }

        return '';
    }

    /**
     * Getter de _oferta
     *
     * @return float
     */
    public function getOferta()
    {
        return $this->_oferta;
    }

    /**
     * Setter de _oferta
     *
     * @param [float] $porcentaje
     * @return boolean si se ha modificado o no la variable
     */
    public function setOferta($porcentaje)
    {
        if ($porcentaje >= 0.1 && $porcentaje <= 1) {
            $this->_oferta = $porcentaje;
            return true;
        }

        return false;
    }
}
