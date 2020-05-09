<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "usuarios".
 *
 * @property int $id
 * @property string $nombre
 * @property string $password
 * @property string $created_at
 * @property string $token
 * @property string $email
 * @property string $biografia
 * @property string $fechanac
 * @property string $requested_at
 *
 * @property Comentarios[] $comentarios
 * @property Criticas[] $criticas
 * @property Posts[] $posts
 * @property UsuariosEtiquetas[] $usuariosEtiquetas
 */
class Usuarios extends \yii\db\ActiveRecord implements IdentityInterface
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_CAMBIOPASS = 'cambioPass';
    const SCENARIO_VERIFICACION = 'verificar';

    public $password_repeat;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'usuarios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'email'], 'required'],
            [['password'], 'required', 'on' => [self::SCENARIO_CREATE]],
            [['fechanac'], 'date', 'format' => 'yyyy-mm-dd', 'on' => [self::SCENARIO_UPDATE, self::SCENARIO_CREATE]],
            [['fechanac'], 'validaFecha', 'on' => [self::SCENARIO_UPDATE, self::SCENARIO_CREATE]],
            [['created_at'], 'safe'],
            [['biografia'], 'string'],
            [['nombre'], 'string', 'max' => 32],
            [['token'], 'string', 'max' => 32, 'on' => [self::SCENARIO_CREATE]],
            [['token'], 'required', 'on' => [self::SCENARIO_CREATE]],
            [['password'], 'string', 'max' => 60],
            [['password', 'password_repeat', 'email'], 'required', 'on' => [self::SCENARIO_CREATE]],
            [['password', 'password_repeat'], 'required', 'on' => [self::SCENARIO_CAMBIOPASS]],
            [['password'], 'compare', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_CAMBIOPASS]],
            [['email'], 'email'],
            [['email'], 'unique'],
            [['nombre'], 'unique'],
            [['requested_at'], 'datetime', 'format' => 'yyyy-mm-dd HH:mm:ss'],
            [['requested_at'], 'safe', 'on' => [self::SCENARIO_VERIFICACION]],
            [['token'], 'safe', 'on' => [self::SCENARIO_VERIFICACION]],
            [['venta_solicitada'], 'safe'],
            // [['venta_solicitada'], 'validarVentaTerminada'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'nombre' => 'Nombre',
            'password' => 'Contraseña',
            'password_repeat' => 'Repite Contraseña',
            'created_at' => 'Miembro desde',
            'token' => 'Token',
            'email' => 'Email',
            'biografia' => 'Biografia',
            'fechanac' => 'Fecha de Nacimiento',
            'requested_at' => 'Miembro desde',
            'venta_solicitada' => 'Id de venta solicitada',
        ];
    }

    // public function validarVentaTerminada($atributo, $params)
    // {
    //     if (isset($this->solicitud->finished_at)) {
    //         $this->addError('venta_solicitada', 'Esa venta ya esta terminada');
    //     }
    // }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComentarios()
    {
        return $this->hasMany(Comentarios::className(), ['usuario_id' => 'id'])->inverseOf('usuario');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCriticas()
    {
        return $this->hasMany(Criticas::className(), ['usuario_id' => 'id'])->inverseOf('usuario');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Posts::className(), ['usuario_id' => 'id'])->inverseOf('usuario');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEtiquetas()
    {
        return $this->hasMany(Etiquetas::className(), ['id' => 'etiqueta_id'])->viaTable('usuarios_etiquetas', ['usuario_id' => 'id']);
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Finds an identity by the given token.
     *
     * @param string $token the token to be looked for
     * @param null|mixed $type
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
    }

    /**
     * @return int|string current user ID
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @return string current user auth key
     */
    public function getAuthKey()
    {
    }
    /**
     * @param string $authKey
     * @return bool if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
    }
    /**
     * Validates password.
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            if ($this->scenario === self::SCENARIO_CREATE) {
                goto salto;
            }
        } elseif ($this->scenario === self::SCENARIO_UPDATE || $this->scenario === self::SCENARIO_CAMBIOPASS) {
            if ($this->password === '') {
                $this->password = $this->getOldAttribute('password');
            } else {
                salto:
                $this->password = Yii::$app->security
                    ->generatePasswordHash($this->password);
            }
        }
        return true;
    }

    public function getVentas()
    {
        return $this->hasMany(Ventas::className(), ['vendedor_id' => 'id'])->inverseOf('vendedor');
    }

    public function getCompras()
    {
        return $this->hasMany(Ventas::className(), ['comprador_id' => 'id'])->inverseOf('comprador');
    }

    /**
     * Gets query for [[Copias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCopias()
    {
        return $this->hasMany(Copias::className(), ['propietario_id' => 'id'])->inverseOf('propietario');
    }

    public function getProductos()
    {
        return $this->hasMany(Productos::className(), ['propietario_id' => 'id'])->inverseOf('propietario');
    }

    public function getSolicitud()
    {
        return $this->hasOne(Ventas::className(), ['id' => 'venta_solicitada']);
    }

    public function getRelaciones()
    {
        return $this->hasMany(Relaciones::className(), ['usuario1_id' => 'id']);
    }

    /**
     * Gets query for [[Juegos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJuegosDeseados()
    {
        return $this->hasMany(Juegos::className(), ['id' => 'juego_id'])->viaTable('deseados', ['usuario_id' => 'id']);
    }

    /**
     * Gets query for [[Deseados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeseados()
    {
        return $this->hasMany(Deseados::className(), ['usuario_id' => 'id'])->inverseOf('usuario');
    }

    /**
     * Gets query for [[JuegosIgnorados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIgnorados()
    {
        return $this->hasMany(Ignorados::className(), ['usuario_id' => 'id'])->inverseOf('usuario');
    }

    public function creaToken()
    {
        return Yii::$app->security->generateRandomString(32);
    }

    public function validaFecha($fecha)
    {
        if (strtotime($this->fechanac) > strtotime(date('Y-m-d'))) {
            $this->addError($fecha, 'No puede ser mayor que hoy');
        }
    }

    public function tieneProducto($pId)
    {
        $arrayProductos = $this->productos;

        foreach ($arrayProductos as $producto) {
            if ($producto->id == $pId) {
                return true;
            }
        }

        return false;
    }

    public function tieneJuego($jId)
    {
        $arrayJuegos = $this->copias;

        foreach ($arrayJuegos as $copia) {
            if ($copia->juego->id == $jId) {
                return true;
            }
        }
        
        return false;
    }
    
    public function esMayorDeEdad()
    {
        return $prueba = $this->fechanac < (date('Y-m-d', strtotime('- 18 years')));
    }
    
    public function esVerificado()
    {
        return !isset($this->token);
    }

    // Devuelve un array con los usuarios relacionados, con un estado concreto
    // si estado==1, devuelve los amigos, y si estado==3 devuelve los usuarios bloqueados
    public function arrayRelacionados($estado)
    {
        $relaciones = Relaciones::find()
        ->where(['estado' => $estado, 'usuario1_id' => $this->id])
        ->orWhere(['estado' => $estado, 'usuario2_id' => $this->id])
        ->all();

        // var_dump($relaciones);
        // exit;
        
        foreach ($relaciones as $relacion) {
            $usuario1 = self::findOne($relacion->usuario1_id);
            $usuario2 = self::findOne($relacion->usuario2_id);
            if ($usuario1 == $this) {
                $arrayRelacionados[] = $usuario2;
                continue;
            }
            $arrayRelacionados[] = $usuario1;
        }
        
        if (empty($arrayRelacionados)) {
            return [];
        }

        return $arrayRelacionados;
    }
    
    public function esAmigo($usuario2Id)
    {
        $usuario1 = $this;
        $usuario2 = self::findOne($usuario2Id);

        return in_array($usuario1, $usuario2->arrayRelacionados(1));
    }

    public function estadoRelacion($usuario2Id)
    {
        $usuario2 = $this->findOne($usuario2Id);
        
        if ($this->estaBloqueadoPor($usuario2Id) || $usuario2->estaBloqueadoPor($this->id)) {
            return 3;
        }

        $relacion = Relaciones::find()
        ->where(['usuario1_id' => $this->id, 'usuario2_id' => $usuario2Id])
        ->orWhere(['usuario1_id' => $usuario2Id, 'usuario2_id' => $this->id])
        ->one();

        if (empty($relacion)) {
            // Si no tiene valor que devolver, devuelve un estado inventado
            return 5;
        }

        return $relacion->estado;
    }

    public function relacionesCon($usuarioId)
    {
        $relaciones = Relaciones::find()
        ->where(['usuario1_id' => $this->id, 'usuario2_id' => $usuarioId])
        ->orWhere(['usuario1_id' => $usuarioId, 'usuario2_id' => $this->id])
        ->all();

        return $relaciones;
    }

    public function relacionCon($usuarioId)
    {
        return Relaciones::find()
        ->where(['usuario1_id' => $this->id, 'usuario2_id' => $usuarioId])
        ->one();
    }

    public function estaBloqueadoPor($usuarioId)
    {
        return Relaciones::find()
        ->where(['usuario1_id' => $usuarioId, 'usuario2_id' => $this->id, 'estado' => 3])
        ->exists();
    }

    public function arrayUsuariosBloqueados($devolverIds)
    {
        $relacionesBloqueo = Relaciones::find()
        ->where(['estado' => 3, 'usuario1_id' => $this->id])
        ->orWhere(['estado' => 3, 'usuario2_id' => $this->id])
        ->all();

        if (!empty($relacionesBloqueo)) {
            foreach ($relacionesBloqueo as $relacion) {
                if ($relacion->usuario1_id == $this->id) {
                    $idsUsuariosBloqueados[] = $relacion->usuario2_id;
                } else {
                    $idsUsuariosBloqueados[] = $relacion->usuario1_id;
                }
            }

            if ($devolverIds) {
                return $idsUsuariosBloqueados;
            }

            return self::find()
            ->where(['not in', 'id', $idsUsuariosBloqueados])
            ->all();
        }

        return [];
    }

    public function arrayIdJuegosIgnorados()
    {
        foreach ($this->ignorados as $ignorado) {
            $idsJuegosBloqueados[] = $ignorado->juego->id;
        }

        if (!empty($idsJuegosBloqueados)) {
            return $idsJuegosBloqueados;
        }
        
        return [];
    }
}
