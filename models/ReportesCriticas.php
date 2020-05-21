<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "reportes_criticas".
 *
 * @property int $usuario_id
 * @property int $critica_id
 * @property string|null $razon
 * @property bool|null $voto_positivo
 *
 * @property Criticas $critica
 * @property Usuarios $usuario
 */
class ReportesCriticas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reportes_criticas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['usuario_id', 'critica_id'], 'required'],
            [['usuario_id', 'critica_id'], 'default', 'value' => null],
            [['usuario_id', 'critica_id'], 'integer'],
            [['razon'], 'string'],
            [['voto_positivo'], 'boolean'],
            [['usuario_id', 'critica_id'], 'unique', 'targetAttribute' => ['usuario_id', 'critica_id']],
            [['critica_id'], 'exist', 'skipOnError' => true, 'targetClass' => Criticas::className(), 'targetAttribute' => ['critica_id' => 'id']],
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
            'critica_id' => 'Critica ID',
            'razon' => 'Razon',
            'voto_positivo' => 'Voto Positivo',
        ];
    }

    /**
     * Gets query for [[Critica]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCritica()
    {
        return $this->hasOne(Criticas::className(), ['id' => 'critica_id'])->inverseOf('reportesCriticas');
    }

    /**
     * Gets query for [[Usuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'usuario_id'])->inverseOf('reportesCriticas');
    }
}
