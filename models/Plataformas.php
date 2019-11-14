<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "plataformas".
 *
 * @property int $id
 * @property string $nombre
 *
 * @property Copias[] $copias
 */
class Plataformas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plataformas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre'], 'string', 'max' => 50],
            [['nombre'], 'unique'],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCopias()
    {
        return $this->hasMany(Copias::className(), ['plataforma_id' => 'id'])->inverseOf('plataforma');
    }
}
