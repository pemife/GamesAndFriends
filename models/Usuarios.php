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

    public function getAmigos()
    {
        return $this->hasMany(self::className(), ['id' => 'amigo_id'])->viaTable('amigos', ['usuario_id' => 'id'])->inverseOf('amigos0');
    }

    public function getAmigos0()
    {
        return $this->hasMany(self::className(), ['id' => 'usuario_id'])->viaTable('amigos', ['amigo_id' => 'id']);
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
    
    public function esAmigo($amigoId)
    {
        $usuario = $this;
        $amigo = self::findOne($amigoId);

        var_dump($usuario->amigos);
        var_dump($amigo->amigos);
        exit;

        if (in_array($usuario, $amigo->amigos)) {
            return true;
        }

        if (in_array($amigo, $usuario->amigos)) {
            return true;
        }

        return false;
    }
    
    public function anadirAmigo($usuarioId, $amigoId)
    {
        if (!$this->esAmigo($amigoId)) {
            $sql = 'insert into amigos(usuario_id, amigo_id) values(' . $usuarioId . ', ' . $amigoId . '), (' . $amigoId . ', ' . $usuarioId . ')';
            if (Yii::$app->db->createCommand($sql)->execute()) {
                Yii::$app->session->setFlash('info', 'Te has añadido satisfactoriamente como amigo');
                return true;
            }
            Yii::$app->session->setFlash('error', 'Ha habido un error al añadirte como amigo');
            return false;
        }
        Yii::$app->session->setFlash('error', 'Ya sois amigos!');
        return false;
    }
    
    public function borrarAmigo($usuarioId, $amigoId)
    {
        if ($this->esAmigo($amigoId)) {
            $sql = 'delete from amigos where (usuario_id = ' . $usuarioId . ' and amigo_id =' . $amigoId . ') or (amigo_id =' . $usuarioId . ' and usuario_id = ' . $amigoId . ')';
            if (Yii::$app->db->createCommand($sql)->execute()) {
                Yii::$app->session->setFlash('info', 'Te has borrado satisfactoriamente como amigo');
                return true;
            }
            Yii::$app->session->setFlash('error', 'Ha habido un error al borrarte como amigo');
            return false;
        }
        Yii::$app->session->setFlash('error', 'No sois amigos!');
        return false;
    }
}
