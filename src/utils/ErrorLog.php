<?php
/**
 * Created by PhpStorm.
 * User: HZD
 * Date: 2017/11/20
 * Time: 下坈4:26
 */

namespace mmgg\utils;

use Yii;

class ErrorLog
{
    /**
     * @param $error
     */
    public static function error($error)
    {
        Yii::error($error, 'error');

        try {
            if (is_string($error)) {
                $message = $error;
            } elseif (is_array($error)) {
                $message = '';
                foreach ($error as $key => $value) {
                    $message = $message . $key . ':' . $value[0] . '</br>';
                }
            } else {
                $message = json_encode($error);
            }

            $e = new \Exception($message, 500);

            Mailer::sendException($e);

            (new AliyunLog())->putLog($message, AliyunLog::TOPIC_ERROR);
        } catch (\Exception $exception) {
            Yii::error($exception, 'error');

            Mailer::sendException($exception);
        }
    }
}
