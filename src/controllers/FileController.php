<?php
/**
 * Created by PhpStorm.
 * User: HZD
 * Date: 16/5/12
 * Time: 下午9:59
 */

namespace mmgg\controllers;

use Yii;
use mmgg\utils\HttpClient;
use mmgg\utils\OSS;

class FileController extends RestController
{

    // 文件信息存储一个星期
    const FILE_INFO_DURATION = 60 * 60 * 24 * 7;

    /**
     * @SWG\Post(path="/v1/file/file/upload",
     *     tags={"file"},
     *     summary="上传文件",
     *     description="上传文件",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "success"
     *     )
     * )
     */
    public function actionUpload()
    {
        if (Yii::$app->request->isOptions) {
            return $this->success();
        }
        $oss = new OSS();
        $result = $oss->upload();
        return $this->success($result);
    }

    /**
     * @SWG\Post(path="/v1/file/file/image-info",
     *     tags={"file"},
     *     summary="获取图片的信息",
     *     description="获取图片的信息",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "imageName",
     *        description = "图片名称，包括后缀名",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = " success"
     *     )
     * )
     */
    public function actionImageInfo()
    {
        if (!$this->getParam('imageName')) {
            return $this->failed('请上传图片名称!');
        }
        $key = 'imageInfo' . md5($this->getParam('imageName'));
        $result = Yii::$app->cache->get($key);
        if ($result !== false) {
            return $this->success($result);
        }

        $ossConfig = $this->getLocalParam('oss');
        $fileUrl = $ossConfig['host'] . $this->getParam('imageName');
        $url = $fileUrl . '@!imageInfo';
        $result = HttpClient::get($url);
        if (empty($result)) {
            return $this->failed('信息未查到！');
        }

        $data = [
            'size' => $result['FileSize']['value'],
            'format' => $result['Format']['value'],
            'height' => $result['ImageHeight']['value'],
            'width' => $result['ImageWidth']['value'],
        ];
        Yii::$app->cache->set($key, $data, self::FILE_INFO_DURATION);

        return $this->success($data);
    }

    /**
     * @SWG\Post(path="/v1/file/file/meta",
     *     tags={"file"},
     *     summary="获取文件的元信息",
     *     description="获取文件的元信息",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "filename",
     *        description = "文件名，包括后缀名",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = " success"
     *     )
     * )
     */
    public function actionMeta()
    {
        if (!$this->getParam('filename')) {
            return $this->failed('请上传文件名!');
        }

        $key = 'metaInfo' . md5($this->getParam('filename'));
        $result = Yii::$app->cache->get($key);
        if ($result !== false) {
            return $this->success($result);
        }

        $oss = new OSS();
        $result = $oss->getObjectMeta($this->getParam('filename'));
        $data = [
            'size' => $result['content-length'],
            'contentType' => $result['content-type'],
        ];
        Yii::$app->cache->set($key, $data, self::FILE_INFO_DURATION);

        return $this->success($data);
    }
}
