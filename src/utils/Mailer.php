<?php
/**
 * Created by PhpStorm.
 * User: HZD
 * Date: 2017/12/25
 * Time: 下午9:34
 */

namespace mmgg\utils;

use Yii;

class Mailer
{
    /**
     * 发送邮件
     * @param $to
     * @param $subject
     * @param $body
     */
    public static function send($to, $subject, $body)
    {
        try {
            if (is_array($body)) {
                $body = json_encode($body);
            } elseif (is_object($body)) {
                $body = json_encode($body);
            }

            $mailer = Yii::$app->mailer->compose();
            $mailer->setTo($to);
            $mailer->setSubject($subject);
            $mailer->setHtmlBody($body);
            if (!$mailer->send()) {
                Yii::error(['to' => $to, 'subject' => $subject, 'body' => $body], 'error');
            }
        } catch (\Exception $e) {
            Yii::error($e, 'error');
        }
    }

    /**
     * 发送日志邮件
     * @param $subject
     * @param $body
     */
    public static function sendLog($subject, $body)
    {
        $to = Yii::$app->params['logEmail'];
        self::send($to, $subject, $body);
    }

    /**
     * 发送异常邮件
     * @param \Throwable $exception
     */
    public static function sendException(\Throwable $exception)
    {
        self::sendLog($exception->getMessage(), self::buildMessageBody($exception));
    }

    /**
     * @param \Throwable $exception
     * @return string
     */
    private static function buildMessageBody(\Throwable $exception)
    {
        $getStr = '';
        foreach ($_GET as $key => $value) {
            $getStr .= '<p style="font-family:Microsoft Yahei;font-size: 14px;color:#444444;margin-bottom:8px;margin-top: 0;">'. $key . ':    ' . $value .'</p>';
        }

        $postStr = '';
        foreach ($_POST as $key => $value) {
            $postStr .= '<p style="font-family:Microsoft Yahei;font-size: 14px;color:#444444;margin-bottom:8px;margin-top: 0;">'. $key . ':    ' . $value .'</p>';
        }

        $serverStr = '';
        foreach ($_SERVER as $key => $value) {
            $serverStr .= '<p style="font-family:Microsoft Yahei;font-size: 14px;color:#444444;margin-bottom:8px;margin-top: 0;">'. $key . ':    ' . $value .'</p>';
        }

        $traceStr = '';
        $trace = explode('#', $exception->getTraceAsString());
        foreach ($trace as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $traceStr .= '<p style="font-family:Microsoft Yahei;font-size: 14px;color:#444444;margin-bottom:8px;margin-top: 0;">#'. $value .'</p>';
        }

        $previousStr = '';
        $previous = explode('Stack trace:', $exception->getPrevious());
        if (count($previous) > 1) {
            $previousStr .= '<p style="font-family:Microsoft Yahei;font-size: 15px;color:#444444;margin-bottom:8px;margin-top: 0;">'. $previous[0] .'</p>';
            $previousStr .= '<p style="font-family:Microsoft Yahei;font-size: 16px;color:#444444;margin-bottom:8px;margin-top: 0;">'. 'Stack trace:' .'</p>';

            $subPrevious = explode('#', $previous[1]);
            foreach ($subPrevious as $key => $value) {
                if (empty($value) || $value == PHP_EOL) {
                    continue;
                }
                $previousStr .= '<p style="font-family:Microsoft Yahei;font-size: 14px;color:#444444;margin-bottom:8px;margin-top: 0;">#'. $value .'</p>';
            }
        }

        $message =
            '<p style="color: red; font-size: 1.2em;">line: ' . $exception->getLine() . '</p>' .
            '<p style="color: red; font-size: 1.2em;">file: ' . $exception->getFile(). '</p>' .
            '<p style="color: red; font-size: 1.2em;line-height: 1.6em;">description: ' . $exception->getMessage() . '</p>' .
            '<br><p style="color: #ed8108; font-size: 2em;">TRACE: </p>' . $traceStr .
            '<br><p style="color: #054013; font-size: 2em;">PREVIOUS: </p>' . $previousStr .
            '<br><p style="color: #076cff; font-size: 2em;">GET: </p>' . $getStr .
            '<br><p style="color: #076cff; font-size: 2em;">POST: </p>' . $postStr .
            '<br><p style="color: #0eff5c; font-size: 2em;">SERVER: </p>' . $serverStr;

        return $message;
    }
}
