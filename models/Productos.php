<?php

namespace app\models;

use Aws\S3\S3Client;
use Yii;

/**
 * Esta es la clase modelo para la tabla "productos".
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
            [['img_key'], 'default', 'value' => 'sin-imagen.jpg'],
            [['nombre', 'img_key'], 'string', 'max' => 255],
            [['nombre'], 'unique'],
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
     *
     * @return array los modelos de productos
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
     *  Lista de Productos que devuelve un activeQuery.
     *
     *  @return \yii\db\ActiveQuery
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
     * Devuelve query de [[Criticas]]
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCriticas()
    {
        return $this->hasMany(Criticas::className(), ['producto_id' => 'id'])->inverseOf('producto');
    }

    /**
     * Devuelve el propietario del producto, o null en su defecto
     *
     * @return Usuarios|null
     */
    public function getPropietario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'propietario_id'])->inverseOf('productos');
    }

    /**
     * Devuelve query para [[Ventas]]
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentas()
    {
        return $this->hasMany(Ventas::className(), ['producto_id' => 'id'])->inverseOf('producto');
    }

    /**
     * Devuelve la url de la imagen de Amazon S3 asociada al producto
     *
     * @return string la url de la imagen
     */
    public function getUrlImagen()
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
                'Key' => 'Productos/' . $this->img_key,
            ]);

            $request = $s3->createPresignedRequest($cmd, '+20 minutes');

            return (string)$request->getUri();
        }
        return '';
    }

    /**
     * Devuelve el estado del producto, si esta en venta o no
     *
     * @return string
     */
    public function getEstado()
    {
        if (Ventas::find()->where(['copia_id' => $this->id])->exists()) {
            return 'En venta';
        }

        return '';
    }
}
