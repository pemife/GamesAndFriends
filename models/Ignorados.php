<?php

namespace app\models;

use Yii;

/**
 * Esta es la clase modelo para la tabla "juegos_ignorados".
 *
 * @property int $usuario_id
 * @property int $juego_id
 *
 * @property Juegos $juego
 * @property Usuarios $usuario
 */
class Ignorados extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'juegos_ignorados';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['usuario_id', 'juego_id'], 'required'],
            [['usuario_id', 'juego_id'], 'default', 'value' => null],
            [['usuario_id', 'juego_id'], 'integer'],
            [['usuario_id', 'juego_id'], 'unique', 'targetAttribute' => ['usuario_id', 'juego_id']],
            [['juego_id'], 'exist', 'skipOnError' => true, 'targetClass' => Juegos::className(), 'targetAttribute' => ['juego_id' => 'id']],
            [['usuario_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['usuario_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'usuario_id' => 'Usuario ID',
            'juego_id' => 'Juego ID',
        ];
    }

    /**
     * Devuelve la query para [[Juego]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJuego()
    {
        return $this->hasOne(Juegos::className(), ['id' => 'juego_id'])->inverseOf('ignorados');
    }

    /**
     * Devuelve la query para [[Usuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'usuario_id'])->inverseOf('ignorados');
    }
}
