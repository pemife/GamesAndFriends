<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "copias".
 *
 * @property int $id
 * @property int $juego_id
 * @property int $propietario_id
 * @property string $clave
 * @property int $plataforma_id
 *
 * @property Juegos $juego
 * @property Plataformas $plataforma
 * @property Usuarios $propietario
 * @property Ventas[] $ventas
 */
class Copias extends \yii\db\ActiveRecord
{
    // public $en_venta;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'copias';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['juego_id', 'plataforma_id'], 'required'],
            [['juego_id', 'propietario_id', 'plataforma_id'], 'default', 'value' => null],
            [['juego_id', 'propietario_id', 'plataforma_id'], 'integer'],
            [['clave'], 'string', 'max' => 17],
            [['clave'], 'match', 'pattern' => '/^[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}$/'],
            [['juego_id'], 'exist', 'skipOnError' => true, 'targetClass' => Juegos::className(), 'targetAttribute' => ['juego_id' => 'id']],
            [['plataforma_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plataformas::className(), 'targetAttribute' => ['plataforma_id' => 'id']],
            [['propietario_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['propietario_id' => 'id']],
            // [['en_venta'], 'boolean'],
            // [['en_venta'], 'default', 'value' => false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'juego_id' => 'Juego',
            'propietario_id' => 'propietario',
            'clave' => 'Clave',
            'plataforma_id' => 'Plataforma',
            'en_venta' => 'En venta',
        ];
    }

    public static function listaQuery()
    {
        $query = self::find();

        if (!Yii::$app->user->isGuest) {
            $query->andWhere(['propietario_id' => Yii::$app->user->id]);
        }

        return $query;
    }

    public function getEnVenta()
    {
        return $this->en_venta;
    }

    public function setEnVenta($value)
    {
        $this->en_venta = $value;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJuego()
    {
        return $this->hasOne(Juegos::className(), ['id' => 'juego_id'])->inverseOf('copias');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlataforma()
    {
        return $this->hasOne(Plataformas::className(), ['id' => 'plataforma_id'])->inverseOf('copias');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPropietario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'propietario_id'])->inverseOf('copias');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVentas()
    {
        return $this->hasMany(Ventas::className(), ['copia_id' => 'id'])->inverseOf('copia');
    }

    public function cambiarPropietario($pId)
    {
        $this->unlink('propietario', $this->propietario);
        $this->link('propietario', Usuarios::findOne($pId));
    }
}
