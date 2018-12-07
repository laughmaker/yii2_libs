<?php
/**
 * Created by PhpStorm.
 * User: HZD
 * Date: 2017/8/27
 * Time: 下午7:43
 */

namespace common\traits;

use Yii;

// 默认缓存时间
define('DEFAULT_CACHE_DURATION', 0);

trait Cache
{
    /**
     * @return \yii\caching\CacheInterface
     */
    public function getCache() {
        return Yii::$app->cache;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getCacheValue(string $key) {
        return $this->getCache()->get($key);
    }

    /**
     * @param string $key
     * @param $value
     * @param $duration
     * @return bool
     */
    public function setCacheValue(string $key, $value, $duration=DEFAULT_CACHE_DURATION) {
        return $this->getCache()->set($key, $value, $duration);
    }

    /**
     * 删除缓存
     * @param $key
     * @return bool
     */
    public function deleteCacheValue($key) {
        return $this->getCache()->delete($key);
    }

    /**
     * @param array $keys
     */
    public function deleteCacheByKeys(array $keys=[]) {
        foreach ($keys as $key) {
            $this->getCache()->delete($key);
        }
    }


    /**
     * @return yii\redis\Connection;
     */
    public function getRedis() {
        return Yii::$app->cache->redis;
    }

    /**
     * 获取key的剩余时间
     * @param string $key
     * @return int 剩余的ms数，key不存在，返回-2，key存在，但没有设置剩余时间返回-1
     */
    public function getRedisItemRemainingTime(string $key) {
        return $this->getRedis()->executeCommand('PTTL', [$key]);
    }

    /**
     * 模糊查询匹配的keys数组
     * @param string $keyPattern
     * @return array|bool|null|string
     */
    public function getRedisKeys(string $keyPattern) {
        $keys = $this->getRedis()->executeCommand('KEYS', [$keyPattern]);
        return $keys;
    }

}