<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "copias".
 *
 * @property int $id
 * @property int $juego_id
 * @property int $poseedor_id
 * @property string $clave
 * @property int $plataforma_id
 *
 * @property Juegos $juego
 * @property Plataformas $plataforma
 * @property Usuarios $poseedor
 * @property Ventas[] $ventas
 */
class Copias extends \yii\db\ActiveRecord
{
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
            [['juego_id', 'poseedor_id', 'plataforma_id'], 'default', 'value' => null],
            [['juego_id', 'poseedor_id', 'plataforma_id'], 'integer'],
            [['clave'], 'string', 'max' => 17],
            [['juego_id'], 'exist', 'skipOnError' => true, 'targetClass' => Juegos::className(), 'targetAttribute' => ['juego_id' => 'id']],
            [['plataforma_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plataformas::className(), 'targetAttribute' => ['plataforma_id' => 'id']],
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
            'juego_id' => 'Juego',
            'poseedor_id' => 'Poseedor',
            'clave' => 'Clave',
            'plataforma_id' => 'Plataforma',
        ];
    }

    public static function lista()
    {
        return self::find()
            ->where(['poseedor_id' => Yii::$app->user->id])
            ->indexBy('id')
            ->all();
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
    public function getPoseedor()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'poseedor_id'])->inverseOf('copias');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVentas()
    {
        return $this->hasMany(Ventas::className(), ['copia_id' => 'id'])->inverseOf('copia');
    }
}
