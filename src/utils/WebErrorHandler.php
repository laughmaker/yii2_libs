<?php
/**
 * Created by PhpStorm.
 * User: HZD
 * Date: 2017/10/17
 * Time: 下坈10:36
 */

namespace mmgg\utils;

use Yii;
use \yii\web\ErrorHandler;
use yii\web\HttpException;

class WebErrorHandler extends ErrorHandler
{
    protected function renderException($exception)
    {
        $status = 500;
        if ($exception instanceof HttpException) {
            $status = $exception->statusCode;
        }

        if ($status == 500) {
            Mailer::sendException($exception);
        }

        if ($status == 404) {
            Yii::warning($exception, 'warning');
        } else {
            Yii::error($exception, 'error');
        }

        parent::renderException($exception);
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
            return parent::handleFatalError();
        }
        $exception = new \ErrorException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);

        Yii::error($exception, 'error');
        Mailer::sendException($exception);

        return parent::handleFatalError();
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
