<?php

namespace app\models;

use Yii;

/**
 * Esta es la clase modelo para la tabla "deseados".
 *
 * @property int $usuario_id
 * @property int $juego_id
 * @property int $orden
 *
 * @property Juegos $juego
 * @property Usuarios $usuario
 */
class Deseados extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'deseados';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['orden'], 'default', 'value' => $this->asignarOrden()],
            [['usuario_id', 'juego_id', 'orden'], 'required'],
            [['usuario_id', 'juego_id'], 'default', 'value' => null],
            [['usuario_id', 'juego_id', 'orden'], 'integer'],
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
            'orden' => 'Orden',
        ];
    }

    /**
     * Devuelve la query para [[Juego]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJuego()
    {
        return $this->hasOne(Juegos::className(), ['id' => 'juego_id'])->inverseOf('deseados');
    }

    /**
     * Devuelve la query para [[Usuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'usuario_id'])->inverseOf('deseados');
    }

    /**
     * Esta funcion asigna el orden del ultimo en la tabla
     * cuando se agregan a la lista de deseos
     *
     * @return int el numero de orden
     */
    public function asignarOrden()
    {
        $arrayDeseados = Deseados::find()
        ->where(['usuario_id' => $this->usuario_id])
        ->orderBy('orden')
        ->all();

        $orden = sizeof($arrayDeseados)+1;

        return $orden;
    }
}
