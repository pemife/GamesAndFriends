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
            [['precio'], 'number', 'max' => '9999.99'],
            ['producto_id', 'required', 'when' => function ($model) {
                return empty($model->copia_id);
            }],
            ['copia_id', 'required', 'when' => function ($model) {
                return empty($model->producto_id);
            }],
            ['copia_id', 'unique', 'message' => 'Ya tienes esa copia en venta'],
            ['producto_id', 'unique', 'message' => 'Ya tienes ese producto en venta!'],
            [['copia_id', 'producto_id'], 'validarCopiaProducto'],
            [['vendedor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['vendedor_id' => 'id']],
            [['comprador_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['comprador_id' => 'id']],
        ];
    }

    public function validarCopiaProducto($atributo, $params)
    {
        if (empty($this->copia_id) && empty($this->producto_id)) {
            $this->addError('copia_id', 'Debes elegir el producto o copia que poner en venta.');
        } elseif (!empty($this->copia_id) && !empty($this->producto_id)) {
            $this->addError('copia_id', 'No puedes poner en venta una copia y un producto a la vez.');
        }
    }

    //  Funcion que realice como validacion (no es correcto)
    // function ($model) {
    //     var_dump($model);
    //     if (($model->copia_id != '0' and $model->producto_id == '0')
    //             or
    //         ($model->copia_id == '0' and $model->producto_id != '0')) {
    //         $this->addError($copia_id, 'No puedes poner en venta a la vez un producto y una copia.');
    //     }
    // }

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
}
