<?php

namespace app\models;

/**
 * This is the model class for table "juegos".
 *
 * @property int $id
 * @property string $titulo
 * @property string $descripcion
 * @property string $fechalan
 * @property string|null $descripcion
 * @property string|null $fechalan
 * @property string $dev
 * @property string $publ
 * @property bool $cont_adul
 * @property float $edad_minima
 *
 * @property Etiquetas[] $etiquetas
 * @property Posts[] $posts
 * @property Productos[] $productos
 */
class Juegos extends \yii\db\ActiveRecord
{
    //TODO:
    //public $imagen;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'juegos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['edad_minima'], 'default', 'value' => 3],
            [['titulo', 'dev', 'publ', 'edad_minima'], 'required'],
            [['descripcion'], 'string'],
            [['fechalan'], 'safe'],
            [['titulo', 'dev', 'publ'], 'string', 'max' => 255],
            [['titulo'], 'unique'],
            [['cont_adul'], 'default', 'value' => function ($model, $attribute) {
                return $this->edad_minima == 18;
            }],
            [['cont_adul'], 'boolean', 'trueValue' => true, 'falseValue' => false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'titulo' => 'Título',
            'descripcion' => 'Descripción',
            'fechalan' => 'Fecha de lanzamiento',
            'dev' => 'Desarrolladora',
            'publ' => 'Editora',
            'cont_adul' => 'Contenido adulto',
            'edad_minima' => 'Edad Minima',
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
        foreach (self::lista() as $juego) {
            $listaAsociativa[$juego->id] = $juego->titulo;
        }

        return $listaAsociativa;
    }

    /**
     * Gets query for [[Etiquetas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEtiquetas()
    {
        return $this->hasMany(Etiquetas::className(), ['id' => 'etiqueta_id'])->viaTable('juegos_etiquetas', ['juego_id' => 'id']);
    }

    /**
     * Gets query for [[Posts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Posts::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }

    /**
     * Gets query for [[Copias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCopias()
    {
        return $this->hasMany(Copias::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }

    /**
     * Gets query for [[Criticas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCriticas()
    {
        return $this->hasMany(Criticas::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }
    
    /**
     * Gets query for [[Deseados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeseados()
    {
        return $this->hasMany(Deseados::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }

    public function getIgnorados()
    {
        return $this->hasMany(Ignorados::className(), ['id' => 'usuario_id'])->viaTable('juegos_ignorados', ['juego_id' => 'id']);
    }
}
