<?php
/**
 * Created by PhpStorm.
 * User: HZD
 * Date: 2017/8/12
 * Time: 下午7:39
 */

namespace mmgg\traits;

use Yii;

define('METHOD_POST', 'post');
define('METHOD_GET', 'get');
define('METHOD_HEADER', 'header');


trait Param
{
    /**
     * @param string $method 参数类型
     * @return array 返回用户上传参数
     */
    public function getParams($method=METHOD_POST)
    {
        if ($method == 'post') {
            return Yii::$app->request->post();
        } elseif ($method == 'get') {
            return Yii::$app->request->get();
        } elseif ($method == 'header') {
            $header = Yii::$app->request->getHeaders();
            return $header->getIterator()->getArrayCopy();
        }

        return [];
    }

    /**
     * @param $key
     * @param string $method 参数类型
     * @return string 返回对应的key对应的参数值
     */
    public function getParam(string $key, $method=METHOD_POST)
    {
        if ($method == 'post') {
            return Yii::$app->request->post($key);
        } elseif ($method == 'get') {
            return Yii::$app->request->get($key);
        } elseif ($method == 'header') {
            return Yii::$app->request->getHeaders()->get($key);
        }
        return null;
    }

    /**
     * @param $key
     * @return mixed 返回本地配置文件中的参数
     */
    public function getLocalParam(string $key)
    {
        try {
            return Yii::$app->params[$key];
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param string $method
     * @return string token 字符串
     */
    public function getToken($method=METHOD_POST)
    {
        return $this->getParam('token', $method);
    }

    /**
     * 获取输入原始数据
     * @param bool $asArray
     * @return bool|string
     */
    public function getInput(bool $asArray=true)
    {
        $input = @file_get_contents('php://input');
        if ($asArray) {
            return json_decode($input, true);
        }

        return $input;
    }
}
