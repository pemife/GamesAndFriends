<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "precios".
 *
 * @property int $juego_id
 * @property int $plataforma_id
 * @property float $cifra
 *
 * @property Juegos $juego
 * @property Plataformas $plataforma
 */
class Precios extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'precios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['juego_id', 'plataforma_id'], 'required'],
            [['juego_id', 'plataforma_id'], 'default', 'value' => null],
            [['juego_id', 'plataforma_id'], 'integer'],
            [['cifra'], 'number'],
            [['juego_id', 'plataforma_id'], 'unique', 'targetAttribute' => ['juego_id', 'plataforma_id']],
            [['juego_id'], 'exist', 'skipOnError' => true, 'targetClass' => Juegos::className(), 'targetAttribute' => ['juego_id' => 'id']],
            [['plataforma_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plataformas::className(), 'targetAttribute' => ['plataforma_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'juego_id' => 'Juego ID',
            'plataforma_id' => 'Plataforma ID',
            'cifra' => 'Cifra',
        ];
    }

    /**
     * Gets query for [[Juego]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJuego()
    {
        return $this->hasOne(Juegos::className(), ['id' => 'juego_id'])->inverseOf('precios');
    }

    /**
     * Gets query for [[Plataforma]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlataforma()
    {
        return $this->hasOne(Plataformas::className(), ['id' => 'plataforma_id'])->inverseOf('precios');
    }

    public static function totalCarrito()
    {
        if (\Yii::$app->request->cookies->has('Carro-' . \Yii::$app->user->id)) {
            $cookieCarro = \Yii::$app->request->cookies->getValue('Carro-' . \Yii::$app->user->id);

            $arrayCarro = explode(' ', $cookieCarro);
    
            return sizeof($arrayCarro);
        }

        return 0;
    }

    public function getOferta()
    {
        // Si es navidad, oferta del 50%
        $hoy = date('d-m');
        if ($hoy == '25-12') {
            return 'Bieeeeen';
        }

        return 'Ohhhhh...';
    }
}
