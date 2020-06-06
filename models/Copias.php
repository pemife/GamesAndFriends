<?php

namespace app\models;

use Yii;

/**
 * Esta es la clase modelo para la tabla copias
 *
 * @property int $id
 * @property int $juego_id
 * @property int $propietario_id
 * @property string $clave
 * @property int $plataforma_id
 *
 * @property Juegos $juego
 * @property Plataformas $plataforma
 * @property Usuarios $propietario
 * @property Ventas[] $ventas
 */
class Copias extends \yii\db\ActiveRecord
{
    const SCENARIO_ANADIR_INVENTARIO = 'create';

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
            [['clave'], 'required', 'on' => [self::SCENARIO_ANADIR_INVENTARIO]],
            [['juego_id', 'propietario_id', 'plataforma_id'], 'default', 'value' => null],
            [['juego_id', 'propietario_id', 'plataforma_id'], 'integer'],
            [['clave'], 'default', 'value' => $this->generaClave()],
            [['clave'], 'string', 'max' => 17],
            [['clave'], 'match', 'pattern' => '/^[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}$/'],
            [['juego_id'], 'exist', 'skipOnError' => true, 'targetClass' => Juegos::className(), 'targetAttribute' => ['juego_id' => 'id']],
            [['plataforma_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plataformas::className(), 'targetAttribute' => ['plataforma_id' => 'id']],
            [['propietario_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['propietario_id' => 'id']],
            // [['en_venta'], 'boolean'],
            // [['en_venta'], 'default', 'value' => false],
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
            'propietario_id' => 'propietario',
            'clave' => 'Clave',
            'plataforma_id' => 'Plataforma',
            'en_venta' => 'En venta',
        ];
    }

    /**
     * Devuelve una query con las copias del usuario logeado
     * o devuelve una query con todas las copias
     *
     * @return Query una query con copias
     */
    public static function listaQuery()
    {
        $query = self::find();

        if (!Yii::$app->user->isGuest) {
            $query->andWhere(['propietario_id' => Yii::$app->user->id]);
        }

        return $query;
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
    public function getPropietario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'propietario_id'])->inverseOf('copias');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVentas()
    {
        return $this->hasMany(Ventas::className(), ['copia_id' => 'id'])->inverseOf('copia');
    }

    /**
     * Devuelve claves validas de copias generadas automaticamente
     *
     * @return string la clave de copia generada
     */
    public function generaClave()
    {
        do {
            $clave = '';
            for ($i = 0; $i < 3; $i++) {
                $clave .= strtoupper(substr(uniqid(), -5));
                $clave .= $i != 2 ? '-' : '';
            }
        } while (!$this->claveValida($clave) && !$this->claveUnica($clave));
        
        return $clave;
    }

    /**
     * Valida que la clave generada tiene el formato adecuado
     *
     * @param [string] $clave
     * @return boolean si es valida o no
     */
    private function claveValida($clave)
    {
        return preg_match('/^[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}$/', $clave);
    }

    /**
     * Valida que la clave generada es única
     *
     * @param [string] $clave
     * @return boolean si es única o no
     */
    private function claveUnica($clave)
    {
        return !self::find()->where(['clave' => $clave])->exists();
    }

    /**
     * Devuelve el estado de la copia, si esta en venta o bloqueada
     *
     * @return string
     */
    public function getEstado()
    {
        if (Ventas::find()->where(['copia_id' => $this->id])->exists()) {
            if (Ventas::find()->where(['copia_id' => $this->id])->andWhere(['>', 'finished_at', date('Y-m-d')])->exists()) {
                return 'Bloqueada';
            }
            return 'En venta';
        }

        return '';
    }
}
