<?php

namespace app\models;

/**
 * This is the model class for table "ventas".
 *
 * @property int $id
 * @property string $created_at
 * @property string $finished_at
 * @property int $vendedor_id
 * @property int $comprador_id
 * @property int $producto_id
 *
 * @property Productos $producto
 * @property Usuarios $vendedor
 * @property Usuarios $comprador
 */
class Ventas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ventas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'finished_at'], 'safe'],
            [['vendedor_id', 'producto_id', 'precio'], 'required'],
            [['precio'], 'number', 'min' => 0.00, 'max' => 9999.99],
            [['vendedor_id', 'comprador_id', 'producto_id'], 'default', 'value' => null],
            [['vendedor_id', 'comprador_id', 'producto_id'], 'integer'],
            [['producto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Productos::className(), 'targetAttribute' => ['producto_id' => 'id']],
            [['vendedor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['vendedor_id' => 'id']],
            [['comprador_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['comprador_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Creacion',
            'finished_at' => 'Finalizacion',
            'vendedor_id' => 'Vendedor ID',
            'comprador_id' => 'Comprador ID',
            'producto_id' => 'Producto ID',
            'precio' => 'Precio',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducto()
    {
        return $this->hasOne(Productos::className(), ['id' => 'producto_id'])->inverseOf('ventas');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendedor()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'vendedor_id'])->inverseOf('ventas');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComprador()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'comprador_id'])->inverseOf('ventas0');
    }
}
