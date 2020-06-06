<?php

namespace app\models;

use Yii;

/**
 * Esta es la clase modelo para la tabla "relaciones".
 *
 * @property int $usuario1_id
 * @property int $usuario2_id
 * @property int $estado
 * @property int|null $old_estado
 *
 * @property Usuarios $usuario1
 * @property Usuarios $usuario2
 */
class Relaciones extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'relaciones';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['usuario1_id', 'usuario2_id'], 'required'],
            [['usuario1_id', 'usuario2_id', 'estado'], 'default', 'value' => null],
            [['usuario1_id', 'usuario2_id', 'estado', 'old_estado'], 'integer'],
            [['old_estado'], 'default', 'value' => 2],
            [['usuario1_id', 'usuario2_id'], 'unique', 'targetAttribute' => ['usuario1_id', 'usuario2_id']],
            [['usuario1_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['usuario1_id' => 'id']],
            [['usuario2_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['usuario2_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'usuario1_id' => 'Usuario1 ID',
            'usuario2_id' => 'Usuario2 ID',
            'estado' => 'Estado',
            'old_estado' => 'Old Estado',
        ];
    }

    /**
     * Devuelve query para [[Usuario1]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario1()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'usuario1_id'])->inverseOf('relaciones');
    }

    /**
     * Devuelve query para [[Usuario2]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario2()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'usuario2_id'])->inverseOf('relaciones0');
    }
}
