<?php

namespace app\models;

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

    public static function lista()
    {
        return self::find()
        ->indexBy('id')
        ->all();
    }

    public static function listaAsociativa()
    {
        foreach (self::lista() as $plataforma) {
            $listaAsociativa[$plataforma->id] = $plataforma->nombre;
        }

        return $listaAsociativa;
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
