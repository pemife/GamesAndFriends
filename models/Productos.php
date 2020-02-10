<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "productos".
 *
 * @property int $id
 * @property string $nombre
 * @property string $descripcion
 * @property string $precio
 * @property string $stock
 * @property int $poseedor_id
 *
 * @property Criticas[] $criticas
 * @property Usuarios $poseedor
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
            [['precio', 'stock'], 'number'],
            [['poseedor_id'], 'default', 'value' => null],
            [['poseedor_id'], 'integer'],
            [['nombre'], 'string', 'max' => 255],
            [['nombre'], 'unique'],
            [['poseedor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['poseedor_id' => 'id']],
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
            'descripcion' => 'Descripcion',
            'precio' => 'Precio',
            'stock' => 'Stock',
            'poseedor_id' => 'Poseedor ID',
        ];
    }

    public static function lista()
    {
        return self::find()
            ->select('nombre, id')
            ->indexBy('id')
            ->where(['poseedor_id' => Yii::$app->user->isGuest ? '*' : Yii::$app->user->id])
            ->all();
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
    public function getPoseedor()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'poseedor_id'])->inverseOf('productos');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVentas()
    {
        return $this->hasMany(Ventas::className(), ['producto_id' => 'id'])->inverseOf('producto');
    }
}
