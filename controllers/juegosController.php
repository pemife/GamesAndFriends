<?php

namespace app\controllers;

class juegosController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
