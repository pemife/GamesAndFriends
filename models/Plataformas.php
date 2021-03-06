<?php

namespace app\models;

use Aws\S3\S3Client;

/**
 * Esta es la clase modelo para la tabla "plataformas".
 *
 * @property int $id
 * @property string $nombre
 * @property Copias[] $copias
 */
class Plataformas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plataformas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre'], 'string', 'max' => 50],
            [['nombre'], 'unique'],
            [['img_key'], 'string', 'max' => 255],
            [['img_key'], 'unique'],
        ];
    }

    /**
     * Devuelve la lista de modelos de Plataformas
     *
     * @return ActiveRecord[]
     */
    public static function lista()
    {
        return self::find()
        ->indexBy('id')
        ->all();
    }

    /**
     * Devuelve una lista asociativa de clave => valor, para plataformas
     *
     * @return array
     */
    public static function listaAsociativa()
    {
        foreach (self::lista() as $plataforma) {
            $listaAsociativa[$plataforma->id] = $plataforma->nombre;
        }

        return $listaAsociativa;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
        ];
    }

    /**
     * Devuelve query para [[Copias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCopias()
    {
        return $this->hasMany(Copias::className(), ['plataforma_id' => 'id'])->inverseOf('plataforma');
    }

    /**
     * Devuelve query para [[Precios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrecios()
    {
        return $this->hasMany(Precios::className(), ['plataforma_id' => 'id'])->inverseOf('plataforma');
    }

    /**
     * Devuelve una url de la imagen del logo de la plataforma
     *
     * @return string
     */
    public function getUrlLogo()
    {
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => 'eu-west-3',
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
                'Key' => 'Plataformas/' . $this->img_key,
            ]);

            $request = $s3->createPresignedRequest($cmd, '+20 minutes');

            return (string) $request->getUri();
        }
        return '';
    }

    /**
     * Devuelve un string representativo con el color caracteristico de la plataforma
     *
     * @return string
     */
    public function getColor()
    {
        switch ($this->id) {
            case 1:
                return '#00a4ef';
            break;
            case 2:
                return '#003087';
            break;
            case 3:
                return '#0e7a0d';
            break;
            case 4:
                return '#e60012';
            break;
            default:
                return '#000000';
        }
    }
}
