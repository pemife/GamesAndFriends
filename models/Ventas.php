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
 * @property int $copia_id
 * @property string $precio
 *
 * @property Copias $copia
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
            [['vendedor_id', 'precio'], 'required'],
            [['vendedor_id', 'comprador_id', 'producto_id', 'copia_id'], 'default', 'value' => null],
            [['vendedor_id', 'comprador_id', 'producto_id', 'copia_id'], 'integer'],
            [['copia_id', 'producto_id'], 'validarCopiaProducto'],
            [['precio'], 'number', 'max' => '9999.99'],
            ['copia_id', 'unique', 'message' => '¡Ya tienes esa copia en venta!'],
            ['producto_id', 'unique', 'message' => '¡Ya tienes ese producto en venta!'],
            [['vendedor_id'], 'validarVendedorPropietario'],
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
            'created_at' => 'Fecha de creación',
            'finished_at' => 'Fecha de finalización',
            'vendedor_id' => 'Id de vendedor',
            'comprador_id' => 'Id de comprador',
            'producto_id' => 'Id de producto',
            'copia_id' => 'Id de copia',
            'precio' => 'Precio',
        ];
    }

    public function validarVendedorPropietario($atributo, $params)
    {
        if (!empty($this->producto) && ($this->vendedor_id != $this->producto->propietario_id)) {
            $this->addError('vendedor_id', '¡No eres el propietario de ese producto');
        }
        if (!empty($this->copia) && ($this->vendedor_id != $this->copia->propietario_id)) {
            $this->addError('vendedor_id', '¡No eres el propietario de esa copia!');
        }
    }

    public function validarCopiaProducto($atributo, $params)
    {
        if (empty($this->copia_id) && empty($this->producto_id)) {
            $this->addError('copia_id', 'Debes elegir el producto o copia que poner en venta.');
        } elseif (!empty($this->copia_id) && !empty($this->producto_id)) {
            $this->addError('copia_id', 'No puedes poner en venta una copia y un producto a la vez.');
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCopia()
    {
        return $this->hasOne(Copias::className(), ['id' => 'copia_id'])->inverseOf('ventas');
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

    public function esProducto()
    {
        return isset($this->producto_id);
    }
}
