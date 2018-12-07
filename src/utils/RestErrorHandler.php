<?php
/**
 * Created by PhpStorm.
 * User: HZD
 * Date: 2017/10/11
 * Time: 上午11:28
 */

namespace mmgg\utils;

use mmgg\traits\Response;
use yii\base\ErrorHandler as BaseErrorHandler;
use Yii;
use yii\web\HttpException;

class RestErrorHandler extends BaseErrorHandler
{
    use Response;

    /**
     * Renders the exception.
     * @param \Exception $exception the exception to be rendered.
     */
    protected function renderException($exception)
    {
        $status = 500;
        if ($exception instanceof HttpException) {
            $status = $exception->statusCode;
        }

        $this->failed($this->getErrorMessage($exception, $status), $status, false);

        // 若为500错误，则发送邮件
        if ($status == 500) {
            Mailer::sendException($exception);
        }

        if ($status == 404) {
            Yii::warning($exception, 'warning');
        } else {
            Yii::error($exception, 'error');
        }
    }

    protected function handleFallbackExceptionMessage($exception, $previousException)
    {
        Yii::error($exception, 'error');
        Mailer::sendException($exception);

        parent::handleFallbackExceptionMessage($exception, $previousException);
    }

    public function handleFatalError()
    {
        $error = error_get_last();
        if (!$error) {
            parent::handleFatalError();
            return;
        }
        $exception = new \ErrorException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);

        Yii::error($exception, 'error');
        Mailer::sendException($exception);

        parent::handleFatalError();
    }

    public function getErrorMessage(\Throwable $exception, $status=500)
    {
        $message = '系统异常，请重试或联系管理员！';
        if ($status == 404) {
            $message = '您请求的地址不存在！';
        }
        if (YII_ENV_DEV) {
            $message = $this->getExceptionDetail($exception);
        }

        return $message;
    }

    public function getExceptionDetail(\Throwable $exception)
    {
        return  [
            'description' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'file' => $exception->getFile(),
            'trace' => $exception->getTraceAsString(),
            'previous' => $exception->getPrevious(),
        ];
    }
}
