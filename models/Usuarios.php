<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;
use Aws\S3\S3Client;

/**
 * Esta es la clase modelo para la tabla "usuarios".
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
 * @property string|null $img_key
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
            [['nombre'], 'validaNombre', 'on' => [self::SCENARIO_UPDATE, self::SCENARIO_CREATE]],
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
            [['es_critico'], 'boolean'],
            // [['requested_at'], 'default', 'value' => date('Y-m-d h:i:s'), 'on' => [self::SCENARIO_CREATE]],
            [['requested_at'], 'datetime', 'format' => 'yyyy-mm-dd HH:mm:ss', 'on' => [self::SCENARIO_VERIFICACION]],
            [['requested_at'], 'safe', 'on' => [self::SCENARIO_VERIFICACION]],
            [['token'], 'safe', 'on' => [self::SCENARIO_VERIFICACION]],
            [['venta_solicitada'], 'default', 'value' => null],
            [['venta_solicitada'], 'integer'],
            [['fondo_key'], 'default', 'value' => ''],
            [['fondo_key'], 'string', 'max'=> 255],
            [['img_key'], 'default', 'value' => 'sin-imagen.jpg'],
            [['img_key'], 'string', 'max'=> 255],
            [['pay_token'], 'unique']
            // [['venta_solicitada'], 'validarVentaTerminada'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'nombre' => 'Nombre de usuario',
            'password' => 'Contraseña',
            'password_repeat' => 'Repite Contraseña',
            'created_at' => 'Miembro desde',
            'token' => 'Token',
            'email' => 'Email',
            'biografia' => 'Información del usuario',
            'fechanac' => 'Fecha de Nacimiento',
            'requested_at' => 'Pedido el',
            'es_critico' => 'Es Critico',
            'venta_solicitada' => 'Id de venta solicitada',
            'img_key' => 'Imagen de usuario',
            'pay_token' => 'Cliente ID de cuenta de PayPal Sandbox',
        ];
    }

    /**
     * Devuelve query para [[Comentarios]]
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComentarios()
    {
        return $this->hasMany(Comentarios::className(), ['usuario_id' => 'id'])->inverseOf('usuario');
    }

    /**
     * Devuelve query para [[Criticas]]
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCriticas()
    {
        return $this->hasMany(Criticas::className(), ['usuario_id' => 'id'])->inverseOf('usuario');
    }

    /**
     * Devuelve query para [[Posts]]
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Posts::className(), ['usuario_id' => 'id'])->inverseOf('usuario');
    }

    /**
     * Devuelve query para [[Etiquetas]]
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEtiquetas()
    {
        return $this->hasMany(Etiquetas::className(), ['id' => 'etiqueta_id'])->viaTable('usuarios_etiquetas', ['usuario_id' => 'id']);
    }

    /**
     * Encuentra identidad por el ID dado.
     *
     * @param string|int $id el ID buscado
     * @return IdentityInterface|null el objeto identidad que coincide con el ID dado
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Encuentra una identidad por el token dado.
     *
     * @param string $token la token a buscar
     * @param null|mixed $type
     * @return IdentityInterface|null el objeto identidad que coincide con la token dada
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
    }

    /**
     * El ID el usuario
     *
     * @return int|string current user ID
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * La clave de autenticacion del usuario
     *
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
     * Valida la contraseña
     *
     * @param string $password la contraseña a validar
     * @return bool si la contraseña provista es valida para el usuario actual
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * Funcion que se ejecuta antes de que se guarde el modelo del usuario
     *
     * @param [type] $insert
     * @return void
     */
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

    /**
     * Devuelve query para las ventas en las que participó como vendedor.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentas()
    {
        return $this->hasMany(Ventas::className(), ['vendedor_id' => 'id'])->inverseOf('vendedor');
    }

    /**
     * Devuelve query para las ventas en las que participó como comprador.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompras()
    {
        return $this->hasMany(Ventas::className(), ['comprador_id' => 'id'])->inverseOf('comprador');
    }

    /**
     * Devuelve las copias de las que el usuario es propietario.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCopias()
    {
        return $this->hasMany(Copias::className(), ['propietario_id' => 'id'])->inverseOf('propietario');
    }

    /**
     * Devuelve los productos de los que el usuario es propietario.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductos()
    {
        return $this->hasMany(Productos::className(), ['propietario_id' => 'id'])->inverseOf('propietario');
    }

    /**
     * Devuelve la venta del mercado de segunda mano que ha solicitado el usuario.
     *
     * @return Ventas
     */
    public function getSolicitud()
    {
        return $this->hasOne(Ventas::className(), ['id' => 'venta_solicitada']);
    }

    /**
     * Devuelve query de [[Relaciones]] en las que participa el usuario.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRelaciones()
    {
        return $this->hasMany(Relaciones::className(), ['usuario1_id' => 'id']);
    }

    /**
     * Devuelve query para los [[Juegos]] que desea el usuario.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJuegosDeseados()
    {
        return $this->hasMany(Juegos::className(), ['id' => 'juego_id'])->viaTable('deseados', ['usuario_id' => 'id']);
    }

    /**
     * Devuelve query para [[Deseados]] los deseos del usuario.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeseados()
    {
        return $this->hasMany(Deseados::className(), ['usuario_id' => 'id'])->inverseOf('usuario');
    }

    /**
     * Devuelve query para [[Ignorados]] los juegos ignorados.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIgnorados()
    {
        return $this->hasMany(Ignorados::className(), ['usuario_id' => 'id'])->inverseOf('usuario');
    }

    /**
     * Crea la token del usuario
     *
     * @return string
     */
    public function creaToken()
    {
        return Yii::$app->security->generateRandomString(32);
    }

    /**
     * Validador de fecha de nacimiento.
     *
     * @param string $fecha
     * @return void
     */
    public function validaFecha($fecha)
    {
        if (strtotime($this->fechanac) > strtotime(date('Y-m-d'))) {
            $this->addError($fecha, 'No puede ser mayor que hoy');
        }
    }

    /**
     * Validador de nombre sin espacios
     *
     * @param string $nombre
     * @return void
     */
    public function validaNombre($nombre)
    {
        if (sizeof(explode(' ', $this->$nombre)) > 1) {
            $this->addError($nombre, '¡El nombre de usuario no puede tener espacios!');
        }
    }

    /**
     * Devuelve si el usuario tiene el producto pasado por parametros como ID
     *
     * @param integer $pId el producto en cuestión
     * @return boolean
     */
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

    /**
     * Devuelve si el usuario tiene el juego pasado por parametros como ID
     *
     * @param integer $jId el juego en cuestión
     * @return boolean
     */
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
    
    /**
     * Devuelve si el usuario es mayor de edad o no
     *
     * @return boolean
     */
    public function esMayorDeEdad()
    {
        return ($this->fechanac < (date('Y-m-d', strtotime('- 18 years'))));
    }
    
    /**
     * Devuelve si el usuario ha verificado su correo electronico
     *
     * @return boolean
     */
    public function esVerificado()
    {
        return !isset($this->token);
    }

    /**
     * Devuelve un array con los usuarios relacionados, con un estado concreto
     * si estado==1, devuelve los amigos, y si estado==3 devuelve los usuarios bloqueados
     * si estado==2, devuelve las amistades rechazadas, y estado==4 el seguimiento de usuarios (críticos)
     *
     * @param integer $estado
     * @return Usuarios[]
     */
    public function arrayRelacionados($estado)
    {
        $relaciones = Relaciones::find()
        ->where(['estado' => $estado, 'usuario1_id' => $this->id])
        ->orWhere(['estado' => $estado, 'usuario2_id' => $this->id])
        ->all();
        
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
    
    /**
     * Devuelve si el usuario es amigo del usuario pasado por parametros como ID
     *
     * @param integer $usuario2Id el usuario comprobado
     * @return boolean
     */
    public function esAmigo($usuario2Id)
    {
        $usuario2 = self::findOne($usuario2Id);

        return in_array($this, $usuario2->arrayRelacionados(1));
    }

    /**
     * Devuelve el estado de una relacion entre dos usuarios
     *
     * @param integer $usuario2Id
     * @return integer el estado de su relacion (1,2,3,4,5) (amistad, rechazado, bloqueado, seguido, sin relacion)
     */
    public function estadoRelacion($usuario2Id)
    {
        $usuario2 = $this->findOne($usuario2Id);
        
        if ($this->estaBloqueadoPor($usuario2Id) || $usuario2->estaBloqueadoPor($this->id)) {
            return 3;
        }

        if ($this->estaSeguidoPor($usuario2Id)) {
            return 4;
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

    /**
     * Devuelve las relaciones que tiene un usuario con otro
     *
     * @param integer $usuarioId el otro usuario
     * @return integer[]
     */
    public function relacionesCon($usuarioId)
    {
        $relaciones = Relaciones::find()
        ->where(['usuario1_id' => $this->id, 'usuario2_id' => $usuarioId])
        ->orWhere(['usuario1_id' => $usuarioId, 'usuario2_id' => $this->id])
        ->all();

        return $relaciones;
    }

    /**
     * Devuelve la relacion que tiene un usuario con otro
     *
     * @param integer $usuarioId
     * @return Relaciones
     */
    public function relacionCon($usuarioId)
    {
        return Relaciones::find()
        ->where(['usuario1_id' => $this->id, 'usuario2_id' => $usuarioId])
        ->one();
    }

    /**
     * Devuelve si el usuario del modelo esta bloqueado o a bloqueado al
     * usuario pasado por parametros como ID
     *
     * @param integer $usuarioId
     * @return boolean
     */
    public function estaBloqueadoPor($usuarioId)
    {
        return Relaciones::find()
        ->where(['usuario1_id' => $usuarioId, 'usuario2_id' => $this->id, 'estado' => 3])
        ->exists();
    }

    /**
     * Devuelve si el usuario del modelo esta seguido por el usuario
     * pasado por parametros como ID
     *
     * @param integer $usuarioId
     * @return boolean
     */
    public function estaSeguidoPor($usuarioId)
    {
        return Relaciones::find()
        ->where(['usuario1_id' => $usuarioId, 'usuario2_id' => $this->id, 'estado' => 4])
        ->exists();
    }

    /**
     * Devuelve un array de usuarios que estan bloqueados por el usuario del modelo
     *
     * @param boolean $devolverIds si devuelve los IDs de los usuarios o los modelos
     * @return integer[]|Usuarios[]
     */
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
            ->where(['in', 'id', $idsUsuariosBloqueados])
            ->all();
        }

        return [];
    }

    /**
     * Devuelve un array de IDs de juegos ignorados por el usuario del modelo
     *
     * @return integer[]
     */
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

    /**
     * Devuelve los generos de preferencia del usuario
     *
     * @param boolean $devuelveIds si devuelve los IDs o los nombres de los modelos
     * @return integer[]|string[]
     */
    public function generosPreferencia($devuelveIds)
    {
        foreach ($this->etiquetas as $genero) {
            $generos[] = $devuelveIds ? $genero->id : $genero->nombre;
        }

        if (!empty($generos)) {
            return $generos;
        }

        return [];
    }

    /**
     * Devuelve si el usuario cumple los requisitos para ser crítico de juegos.
     * Un usuario se considerará Crítico de juegos/productos cuando la suma
     * de votos positivos de su conjunto de criticas supere una cifra concreta.
     * (para probar que funciona, lo limitaré a 5 votos positivos)
     *
     * @return boolean
     */
    public function cumpleRequisitoDeCritico()
    {
        $votosCriticas = Criticas::find()
        ->joinWith('usuario')
        ->joinWith('reportesCriticas')
        ->where(['usuarios.id' => $this->id, 'reportes_criticas.voto_positivo' => true])
        ->count();

        return $votosCriticas > 5;
    }

    /**
     * Devuelve los votos positivos que tiene el usuario en sus criticas de juegos.
     *
     * @return integer
     */
    public function puntuacionCritico()
    {
        $votosCriticas = Criticas::find()
        ->joinWith('usuario')
        ->joinWith('reportesCriticas')
        ->where(['usuarios.id' => $this->id, 'reportes_criticas.voto_positivo' => true])
        ->count();

        return $votosCriticas;
    }

    /**
     * Devuelve una lista de relaciones del usuario donde muestra
     * los criticos que son seguidos por el usuario del modelo.
     *
     * @return string[]
     */
    public function listaCriticosSeguidosId()
    {
        return Relaciones::find()
        ->where(['estado' => 4, 'usuario1_id' => $this->id])
        ->select('usuario2_id as id')
        ->column();
    }

    /**
     * Devuelve una lista de usuarios seguidores del usuario del modelo
     * para que le sigan, el usuario necesita ser critico.
     *
     * @return string[]
     */
    public function listaSeguidoresId()
    {
        return Relaciones::find()
        ->where(['estado' => 4, 'usuario2_id' => $this->id])
        ->select('usuario1_id as id')
        ->column();
    }

    /**
     * Devuelve un array con los IDs de los usuarios que el usuario del
     * modelo tiene bloqueados.
     *
     * @return string[]
     */
    public function listaIdsBloqueados()
    {
        return Relaciones::find()
        ->where(['usuario1_id' => $this->id, 'estado' => 3])
        ->select('usuario2_id as id')
        ->column();
    }

    /**
     * Devuelve un array con los modelos de los usuarios que el usuario
     * del modelo tiene bloqueados.
     *
     * @return Usuarios[]
     */
    public function listaBloqueados()
    {
        return self::find()
        ->where(['in', 'id', $this->listaIdsBloqueados()])
        ->all();
    }

    /**
     * Devuelve una URL de la imagen del Amazon S3 que tiene el usuario asignada
     *
     * @return string
     */
    public function getUrlImagen()
    {
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => 'eu-west-2',
            'credentials' => [
                'key' => getenv('KEY'),
                'secret' => getenv('SECRET'),
                'token' => null,
                'expires' => null,
            ],
        ]);

        if (getenv('MEDIA')) {
            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => 'gamesandfriends',
                'Key' => 'Usuarios/default/' . $this->img_key,
            ]);

            $request = $s3->createPresignedRequest($cmd, '+20 minutes');

            return (string)$request->getUri();
        }
        return '';
    }

    /**
     * Devuelve una URL de imagen de Amazon S3 que tiene el
     * usuario asignado al fondo de su perfil
     *
     * @return string
     */
    public function getUrlFondo()
    {
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => 'eu-west-2',
            'credentials' => [
                'key' => getenv('KEY'),
                'secret' => getenv('SECRET'),
                'token' => null,
                'expires' => null,
            ],
        ]);

        if (getenv('MEDIA')) {
            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => 'gamesandfriends',
                'Key' => 'Usuarios/fondos/' . $this->fondo_key,
            ]);

            $request = $s3->createPresignedRequest($cmd, '+20 minutes');

            return (string)$request->getUri();
        }
        return '';
    }

    /**
     * Devuelve un array de como estan dispuestas las fotos
     * por defecto que pueden elegir los usuarios para ponerse de fotos de perfil.
     *
     * @return array
     */
    public function getArrayCarpetasImagenes()
    {
        return [
            'animalCrossing' => [
                'nombre' => 'Animal Crossing',
                'total' => 9,
            ],
            'anime' =>[
                'nombre' => 'Anime',
                'total' => 15
            ],
            'colossus' => [
                'nombre' => 'Shadow of the Colossus',
                'total' => 2
            ],
            'csgo' => [
                'nombre' => 'Counter Strike: Global Offensive',
                'total' => 8
            ],
            'isaac' => [
                'nombre' => 'The binding of Isaac',
                'total' => 4
            ],
            'kirby' => [
                'nombre' => 'Kirby',
                'total' => 6
            ],
            'locoroco' => [
                'nombre' => 'LocoRoco',
                'total' => 3
            ],
            'marioBros' => [
                'nombre' => 'Super Mario Bros.',
                'total' => 13
            ],
            'minecraft' => [
                'nombre' => 'Minecraft',
                'total' => 6
            ],
            'pokemon' => [
                'nombre' => 'Pokemon',
                'total' => 3
            ],
            'retro' => [
                'nombre' => 'Retro',
                'total' => 11,
            ],
            'rocketLeague' => [
                'nombre' => 'Rocket League',
                'total' => 3
            ],
            'terraria' => [
                'nombre' => 'Terraria',
                'total' => 3
            ],
            'zelda' => [
                'nombre' => 'The legend of Zelda',
                'total' => 27
            ]
        ];
    }

    public function getPreferencias()
    {
        return implode(', ', $this->generosPreferencia(false));
    }
}
