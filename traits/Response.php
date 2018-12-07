<?php
/**
 * Created by PhpStorm.
 * User: HZD
 * Date: 2017/6/24
 * Time: 下午2:19
 */

namespace mmgg\traits;

use mmgg\tools\AliyunLog;
use Yii;

define('SUCCESS', 2000);

define('TOKEN_EXPIRED', 2001);
define('USER_EXIST', 2002);
define('USERNAME_OR_PASSWORD_ERROR', 2003);
define('SMS_CODE_EXPIRED', 2004);
define('SMS_CODE_ERROR', 2005);
define('USER_NOT_EXIST', 2006);
define('PUBLIC_KEY_ERROR', 2007);
define('TEL_NOT_BIND', 2008);

define('FAILED', 4000);

// 签名过期
define('SIGN_TIME_EXPIRED', 4001);

// 客户端需要升级
define('CLIENT_NEED_UPDATE', 4002);

// 服务器升级维护中
define('SERVICES_MAINTAIN', 5001);

define('AUTH_ERROR', 401);
define('RATE_LIMITER_ERROR', 429);

define('TEL_CARD_HAS_TAKEN', -10078);
define('SERVER_ERROR', 500);


trait Response
{
    /**
     * @param null $data
     * @param int $status
     * @param string|array|object $message
     * @param null $page
     * @param bool $end
     * @return bool|mixed
     */
    static public function send($data=null, $status=SUCCESS, $message=null, $page=null, $end=false) {
        if (!$message) {
            if ($status == SUCCESS) {
                $message = '请求成功!';
            } elseif ($status == TOKEN_EXPIRED) {
                $message = 'token已过期,请重新登录!';
            } elseif ($status == FAILED) {
                $message = '请求失败!';
            }
        }
        $responseData = [
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'page' => $page
        ];
        Yii::$app->response->format = 'json';
        Yii::$app->response->data = $responseData;
        Yii::$app->response->send();

        // 上传日志到阿里云
        (new AliyunLog())->putLog(Yii::$app->response->content, $status);

        if ($end) {
            self::end();
        }

        return Yii::$app->response->isSent;
    }

    /**
     * @param null $data
     * @param int $status
     * @param string|array|object $message
     * @param null $page
     * @param bool $end
     * @return bool|mixed
     */
    public function response($data=null, $status=SUCCESS, $message=null, $page=null, $end=false) {
        return self::send($data, $status, $message, $page, $end);
    }

    /**
     * @param null $data
     * @param null $page
     * @param bool $end
     * @return bool|mixed
     */
    public function success($data=null, $page=null, $end=false) {
        return $this->response($data, SUCCESS, '请求成功！', $page, $end);
    }

    /**
     * 成功的一个提示信息
     * @param string $message
     * @param bool $end
     * @return bool|mixed
     */
    public function info(string $message, $end=false) {
        $this->response(null, SUCCESS, $message, null, $end);
        return true;
    }

    /**
     * @param string|array|object $message
     * @param int $status
     * @param bool $end
     * @return bool|mixed
     */
    public function failed($message, $status=FAILED, $end=true) {
        $this->response(null, $status, $message, null, $end);
        return true;
    }

    /**
     * 结束app运行，完成本次生命周期
     */
    static public function end() {
        Yii::$app->end();
    }

}