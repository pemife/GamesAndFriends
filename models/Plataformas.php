<?php

namespace app\models;

use Aws\S3\S3Client;

/**
 * This is the model class for table "plataformas".
 *
 * @property int $id
 * @property string $nombre
 *
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
            [['img_key'], 'unique']
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
     * @return \yii\db\ActiveQuery
     */
    public function getCopias()
    {
        return $this->hasMany(Copias::className(), ['plataforma_id' => 'id'])->inverseOf('plataforma');
    }

    /**
     * Gets query for [[Precios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrecios()
    {
        return $this->hasMany(Precios::className(), ['plataforma_id' => 'id'])->inverseOf('plataforma');
    }

    public function getUrlLogo()
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
                'Key' => 'Plataformas/' . $this->img_key,
            ]);
    
            $request = $s3->createPresignedRequest($cmd, '+20 minutes');

            return (string)$request->getUri();
        }
        return '';
    }

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
