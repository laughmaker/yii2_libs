<?php

namespace mmgg\controllers;

use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use mmgg\traits\Param;
use mmgg\traits\RedisCache;
use mmgg\traits\Response;

/**
 * Created by HZD.
 * User: HZD
 * Date: 2017/6/6
 * Time: 下坈4:42
 */
class RestController extends Controller
{
    use Response, Param, RedisCache;

    public $enableCsrfValidation = false;

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function behaviors()
    {
        if (YII_ENV_DEV) {
            $allows = ['*'];
        } else {
            $allows = $this->getLocalParam('allowsCros');
        }
        $behaviors = [
            [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => $allows,
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Headers' => ['Origin', 'X-Requested-With', 'Content-Type', 'Accept', 'Sign'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                ],
            ],
        ];

        return ArrayHelper::merge($behaviors, parent::behaviors());
    }
}
