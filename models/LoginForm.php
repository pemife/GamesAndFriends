<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Loginform es el modelo tras el formulario de login
 *
 * @property User|null $user Esta propiedad es de solo lectura
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array Las reglas de validación
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Nombre de usuario',
            'password' => 'Contraseña'
        ];
    }

    /**
     * Valida la contraseña
     * Este metodo sirve como validacion en una linea para la contraseña
     *
     * @param string $attribute el atributo siendo validado en el momento
     * @param array $params las paregas adicionales dadas en la regla
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Inicia sesion un usuario usando el usuario y contraseña provistos.
     * @return bool si el usuario se ha logueado exitosamente o no
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }

    /**
     * Encuentra usuario por [[username]].
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = Usuarios::findOne(['nombre' => $this->username]);
        }

        return $this->_user;
    }
}
