<?php

namespace app\models;

use Aws\S3\S3Client;

/**
 * This is the model class for table "juegos".
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

    public static function lista()
    {
        return self::find()
        ->indexBy('id')
        ->all();
    }

    public static function listaAsociativa()
    {
        foreach (self::lista() as $juego) {
            $listaAsociativa[$juego->id] = $juego->titulo;
        }

        return $listaAsociativa;
    }

    /**
     * Gets query for [[Etiquetas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEtiquetas()
    {
        return $this->hasMany(Etiquetas::className(), ['id' => 'etiqueta_id'])->viaTable('juegos_etiquetas', ['juego_id' => 'id']);
    }

    /**
     * Gets query for [[Posts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Posts::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }

    /**
     * Gets query for [[Copias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCopias()
    {
        return $this->hasMany(Copias::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }

    /**
     * Gets query for [[Criticas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCriticas()
    {
        return $this->hasMany(Criticas::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }
    
    /**
     * Gets query for [[Deseados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeseados()
    {
        return $this->hasMany(Deseados::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }

    public function getIgnorados()
    {
        return $this->hasMany(Ignorados::className(), ['id' => 'usuario_id'])->viaTable('juegos_ignorados', ['juego_id' => 'id']);
    }

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

    public function similares()
    {
        return $this->find()
        ->joinWith('etiquetas')
        ->where(['in', 'etiqueta_id', $this->generosId()])
        ->andWhere(['!=', 'juego_id', $this->id])
        ->limit(4);
    }

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

        $cmd = $s3->getCommand('GetObject', [
            'Bucket' => 'gamesandfriends',
            'Key' => 'Juegos/' . $carpeta . $this->img_key,
        ]);

        $request = $s3->createPresignedRequest($cmd, '+20 minutes');

        return (string)$request->getUri();
    }

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

        for ($i = 1; $i <= $numeroTrailers; $i++) {
            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => 'gamesandfriends',
                'Key' => 'Juegos/' . $carpeta . '/trailer' . $i . '.mp4',
            ]);
    
            $urlTrailers[] = (string)$s3->createPresignedRequest($cmd, '+20 minutes')->getUri();
        }

        return $urlTrailers;
    }
}