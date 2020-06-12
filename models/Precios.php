<?php

namespace app\models;

use Yii;

/**
 * Esta es la clase modelo para la tabla "precios".
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
            [['oferta'], 'default', 'value' => 1.00],
            [['oferta'], 'required'],
            [['cifra'], 'number'],
            [['oferta'], 'number', 'min' => 0.10, 'max' => 1.00],
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
     * Devuelve el juego asociado al precio.
     *
     * @return Juegos
     */
    public function getJuego()
    {
        return $this->hasOne(Juegos::className(), ['id' => 'juego_id'])->inverseOf('precios');
    }

    /**
     * Devuelve la plataforma asociada al precio.
     *
     * @return Plataformas
     */
    public function getPlataforma()
    {
        return $this->hasOne(Plataformas::className(), ['id' => 'plataforma_id'])->inverseOf('precios');
    }

    /**
     * Devuelve el total de items que hay en el carrito, si no existe
     * la cookie que lo contabiliza, devuelve un 0.
     *
     * @return integer
     */
    public static function totalCarrito()
    {
        if (\Yii::$app->request->cookies->has('carro-' . \Yii::$app->user->id)) {
            $cookieCarro = \Yii::$app->request->cookies->getValue('carro-' . \Yii::$app->user->id);

            $arrayCarro = explode(' ', $cookieCarro);
    
            return sizeof($arrayCarro);
        }

        return 0;
    }
}
