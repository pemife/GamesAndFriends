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
 * @property int $juego_id
 *
 * @property Criticas[] $criticas
 * @property Juegos $juego
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
            [['nombre', 'descripcion'], 'required'],
            [['descripcion'], 'string'],
            [['precio'], 'number'],
            [['juego_id'], 'default', 'value' => null],
            [['juego_id'], 'integer'],
            [['nombre'], 'string', 'max' => 255],
            [['nombre'], 'unique'],
            [['juego_id'], 'exist', 'skipOnError' => true, 'targetClass' => Juegos::className(), 'targetAttribute' => ['juego_id' => 'id']],
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
            'juego_id' => 'Juego ID',
        ];
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
    public function getJuego()
    {
        return $this->hasOne(Juegos::className(), ['id' => 'juego_id'])->inverseOf('productos');
    }
}
