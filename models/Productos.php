<?php

namespace app\models;

use Aws\S3\S3Client;
use Yii;

/**
 * This is the model class for table "productos".
 *
 * @property int $id
 * @property string $nombre
 * @property string $descripcion
 * @property string $stock
 * @property int $propietario_id
 * @property string|null $img_key
 *
 * @property Criticas[] $criticas
 * @property Usuarios $propietario
 * @property Ventas[] $ventas
 */
class Productos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'productos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'descripcion', 'stock'], 'required'],
            [['descripcion'], 'string'],
            [['propietario_id'], 'default', 'value' => null],
            [['propietario_id'], 'integer'],
            [['nombre', 'img_key'], 'string', 'max' => 255],
            [['nombre'], 'unique'],
            [['img_key'], 'unique'],
            [['propietario_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['propietario_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripción',
            'stock' => 'Cantidad',
            'propietario_id' => 'Id de Propietario',
        ];
    }

    /**
     * Devuelve una lista con los nombres e ids.
     * @return [type] [description]
     */
    public static function lista()
    {
        $query = self::find()
        ->select('nombre, id');

        if (!Yii::$app->user->isGuest) {
            $query->andWhere(['propietario_id' => Yii::$app->user->id]);
        }

        return $query->all();
    }

    /**
     *  @return \yii\db\ActiveQuery
     *  Lista de Productos que devuelve un activeQuery
     */
    public static function listaQuery()
    {
        $query = self::find()
        ->indexBy('id');

        if (!Yii::$app->user->isGuest) {
            $query->andWhere(['propietario_id' => Yii::$app->user->id]);
        }
        return $query;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCriticas()
    {
        return $this->hasMany(Criticas::className(), ['producto_id' => 'id'])->inverseOf('producto');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPropietario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'propietario_id'])->inverseOf('productos');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVentas()
    {
        return $this->hasMany(Ventas::className(), ['producto_id' => 'id'])->inverseOf('producto');
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

        $cmd = $s3->getCommand('GetObject', [
            'Bucket' => 'gamesandfriends',
            'Key' => 'Productos/' . $this->img_key,
        ]);

        $request = $s3->createPresignedRequest($cmd, '+20 minutes');

        return (string)$request->getUri();
    }
}
