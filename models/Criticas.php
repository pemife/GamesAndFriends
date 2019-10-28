<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "criticas".
 *
 * @property int $id
 * @property string $opinion
 * @property string $created_at
 * @property string $valoracion
 * @property int $usuario_id
 * @property int $producto_id
 *
 * @property Productos $producto
 * @property Usuarios $usuario
 */
class Criticas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'criticas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['opinion', 'valoracion', 'usuario_id', 'producto_id'], 'required'],
            [['opinion'], 'string'],
            [['created_at'], 'safe'],
            [['valoracion'], 'number'],
            [['usuario_id', 'producto_id'], 'default', 'value' => null],
            [['usuario_id', 'producto_id'], 'integer'],
            [['producto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Productos::className(), 'targetAttribute' => ['producto_id' => 'id']],
            [['usuario_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['usuario_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'opinion' => 'Opinion',
            'created_at' => 'Created At',
            'valoracion' => 'Valoracion',
            'usuario_id' => 'Usuario ID',
            'producto_id' => 'Producto ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducto()
    {
        return $this->hasOne(Productos::className(), ['id' => 'producto_id'])->inverseOf('criticas');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'usuario_id'])->inverseOf('criticas');
    }
}
