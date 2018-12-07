<?php
/**
 * Created by PhpStorm.
 * User: HZD
 * Date: 2017/6/20
 * Time: 下午7:55
 */

namespace mmgg\utils;

use mmgg\traits\Response;
use OSS\OssClient;

class OSS
{
    use Response;

    private $_accessKeyId;
    private $_accessKeySecret;
    private $_endpoint;
    private $_bucket;
    private $_host;

    private $_ossClient;

    private $_file;

    public function __construct()
    {
        $ossConfig = \Yii::$app->params['oss'];
        $this->_accessKeyId = $ossConfig['accessKeyId'];
        $this->_accessKeySecret = $ossConfig['accessKeySecret'];
        $this->_endpoint = $ossConfig['endpoint'];
        $this->_bucket = $ossConfig['bucket'];
        $this->_host = $ossConfig['host'];

        $this->_ossClient = new OssClient($this->_accessKeyId, $this->_accessKeySecret, $this->_endpoint);
    }

    /***
     * 允许上传的文件类型
     * @param array $extensions
     * @return bool
     */
    public function allowedExtensions(array $extensions)
    {
        if (in_array($this->_getExtension(), $extensions)) {
            return true;
        }

        return false;
    }

    /**
     * 获取上传文件的扩展名
     * @return mixed
     */
    private function _getExtension()
    {
        return pathinfo($this->_file['name'], PATHINFO_EXTENSION);
    }

    /**
     * 通过$_FILES['file']上传文件
     * @return array
     */
    public function upload()
    {
        if (empty($_FILES) || empty($_FILES["file"])) {
            return $this->failed('没有上传文件或上传文件名不为file！');
        }

        $this->_file = $_FILES["file"];

        $filePath = $this->_file['tmp_name'];
        $extension = $this->_getExtension();
        $filename = time() . '_' . hash("md5", $this->_file['name']);
        $name = $this->_file['name'];

        // 如果有扩展名，去掉扩展名
        if (strlen($extension) > 0) {
            $name = substr($this->_file['name'], 0, strlen($this->_file['name']) - strlen($extension) - 1);
            $filename = $filename . '.' . $extension ;
        }

        $result = $this->_ossClient->uploadFile($this->_bucket, $filename, $filePath);
        if ($result) {
            if (isset($result['info'])) {
                return $this->_buildData($filename, $name, $extension, $this->_file['size']);
            }
        }

        return null;
    }

    /**
     * 上传内存中的文件
     * @param $content
     * @param $filename  / 上传的文件名，包括了后缀
     * @return array
     */
    public function uploadMemFile($content, $filename)
    {
        $size = strlen($content);
        if ($size == 0) {
            return $this->failed('文件大小不能为0');
        }

        $fileParts = explode('.', $filename);
        if (count($fileParts) < 2) {
            return $this->failed('文件名没有包含后缀');
        }
        $extension = $fileParts[(count($fileParts) - 1)];

        $result = $this->_ossClient->putObject($this->_bucket, $filename, $content);
        if ($result) {
            if (isset($result['info'])) {
                return $this->_buildData($filename, $filename, $extension, $size);
            }
        }

        return null;
    }

    /**
     * 组装返回数据
     * @param string $filename
     * @param string $originalName
     * @param string $extension
     * @param int $size
     * @return array
     */
    private function _buildData(string $filename, string $originalName, string $extension, int $size)
    {
        return [
            'url' => $this->_host . $filename,
            'name' => $filename,
            'original_name' => $originalName,
            'extension' => $extension,
            'size' => $size
        ];
    }

    /**
     * 获取对象的元信息
     * @param $filename
     * @return array
     */
    public function getObjectMeta($filename)
    {
        $meta = $this->_ossClient->getObjectMeta($this->_bucket, $filename);
        return $meta;
    }
}
