<?php

namespace app\models;

/**
 * Esta es la clase modelo para la tabla "ventas".
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

    /**
     * Valida que al crear una venta, el usuario sea el propietario del producto/copia.
     *
     * @param [type] $atributo
     * @param [type] $params
     * @return void
     */
    public function validarVendedorPropietario($atributo, $params)
    {
        if (!empty($this->producto) && ($this->vendedor_id != $this->producto->propietario_id)) {
            $this->addError('vendedor_id', '¡No eres el propietario de ese producto');
        }
        if (!empty($this->copia) && ($this->vendedor_id != $this->copia->propietario_id)) {
            $this->addError('vendedor_id', '¡No eres el propietario de esa copia!');
        }
    }

    /**
     * Valida que la venta sea o de un producto, o de una copia, pero no de los dos
     * y valida que no sea una venta vacía.
     *
     * @param [type] $atributo
     * @param [type] $params
     * @return void
     */
    public function validarCopiaProducto($atributo, $params)
    {
        if (empty($this->copia_id) && empty($this->producto_id)) {
            $this->addError('copia_id', 'Debes elegir el producto o copia que poner en venta.');
        } elseif (!empty($this->copia_id) && !empty($this->producto_id)) {
            $this->addError('copia_id', 'No puedes poner en venta una copia y un producto a la vez.');
        }
    }

    /**
     * Devuelve la copia en venta, o null si la venta es de un producto.
     *
     * @return \yii\db\ActiveQuery|null
     */
    public function getCopia()
    {
        return $this->hasOne(Copias::className(), ['id' => 'copia_id'])->inverseOf('ventas');
    }

    /**
     * Devuelve el producto en venta, o null si la venta es de una copia.
     *
     * @return \yii\db\ActiveQuery|null
     */
    public function getProducto()
    {
        return $this->hasOne(Productos::className(), ['id' => 'producto_id'])->inverseOf('ventas');
    }

    /**
     * Devuelve el usuario que ha creado la venta, o null si el usuario se ha borrado
     *
     * @return \yii\db\ActiveQuery|null
     */
    public function getVendedor()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'vendedor_id'])->inverseOf('ventas');
    }

    /**
     * Devuelve el usuario que ha comprado el producto/copia,
     * o null si la venta no ha finalizado aún.
     *
     * @return \yii\db\ActiveQuery|null
     */
    public function getComprador()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'comprador_id'])->inverseOf('ventas0');
    }

    /**
     * Devuelve si es la venta de un producto
     *
     * @return boolean
     */
    public function esProducto()
    {
        return isset($this->producto_id);
    }
}
