<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "productos".
 *
 * @property int $id
 * @property string $nombre
 * @property string $descripcion
 * @property string $stock
 * @property int $propietario_id
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
            [['nombre'], 'string', 'max' => 255],
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
            'descripcion' => 'Descripcion',
            'stock' => 'Cantidad',
            'propietario_id' => 'Propietario',
        ];
    }

    public static function lista()
    {
        return self::find()
            ->select('nombre, id')
            ->indexBy('id')
            ->where(['propietario_id' => Yii::$app->user->isGuest ? '*' : Yii::$app->user->id])
            ->all();
    }

    /**
     *  @return \yii\db\ActiveQuery
     *  Lista de Productos que devuelve un activeQuery
     */
    public static function listaQuery()
    {
        return self::find()
            ->indexBy('id')
            ->where(['propietario_id' => Yii::$app->user->isGuest ? '*' : Yii::$app->user->id]);
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
}
