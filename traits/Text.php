<?php
/**
 * Created by PhpStorm.
 * User: HZD
 * Date: 2017/6/24
 * Time: 下午5:27
 */

namespace common\traits;


trait Text
{
    /**
     * 获取文本的中文长度
     * @param $str
     * @param string $encoding
     * @return int
     */
    public function length($str, $encoding='utf-8') {
        return mb_strlen($str, $encoding);
    }

    /**
     * 截取文本的子字符串
     * @param $str
     * @param $start
     * @param $length
     * @param string $encoding
     * @return string
     */
    public function subStr($str, $start, $length=null, $encoding='utf-8') {
        return mb_substr($str, $start, $length, $encoding);
    }

    /**
     * 提取html中的图片数组
     * @param $html
     * @return array|null|string
     */
    public function getImageUrls($html) {
        preg_match_all("<img.*?src=\"(.*?.*?)\".*?>", $html, $match1);
        preg_match_all("<img.*?src=\"(.*?.*?)\".*?/>", $html, $match2);
        preg_match_all("<img.*?src='(.*?.*?)'.*?>", $html, $match3);
        preg_match_all("<img.*?src='(.*?.*?)'.*?/>", $html, $match4);

        $match = array_merge($match1, $match2, $match3, $match4);
        $imageUrls = [];
        foreach($match as $val) {
            if (count($val) == 0) {
                continue;
            }
            
            foreach ($val as $url) {
                if (substr($url, 0, 4) == 'http') {
                    $imageUrls[] = $url;
                }
            }
        }

        if (count($imageUrls) == 0) {
            return null;
        }

        return array_values(array_unique($imageUrls));
    }

    /***
     * 判断两个字符串是否存在于另一个字符串之中
     * @param string $str
     * @param string $subStr
     * @return bool
     */
    public function contains(string $str, string $subStr) {
        if (mb_strpos($str, $subStr) !== false) {
            return true;
        }

        return false;
    }

    /**
     * 产生一个随机长度的字符串，由数字和纯字母组成
     * @param int $length
     * @return string
     */
    public function generateRandString(int $length=8) {
        $str = '';
        $seeds = "0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($seeds) - 1;

        for($i=0; $i < $length; $i++) {
            $str .= $seeds[rand(0, $max)];
        }

        return $str;
    }

    /**
     * 判断一个字符串是否为json格式
     * @param $string
     * @return bool
     */
    static public function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * 返回json字符串中有多少个数组，如果转json失败，则返回-1
     * @param $string
     * @return int
     */
    static public function jsonArrayCount($string) {
        $obj = json_decode($string, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            if (self::isIndexArray($obj)) {
                return count($obj);
            }
        }

        return -1;
    }

    /**
     * 判断一个数组是否为key为自增的索引数组
     * @param $arr
     * @return bool
     */
    static public function isIndexArray(array $arr) {
        $index = 0;
        foreach (array_keys($arr) as $key) {
            if ($index++ != $key) {
                return false;
            }
        }
        return true;
    }

    /**
     * 产生随机昵称
     * @param $length | 昵称长度
     * @return string
     */
    static public function generateNickname($length=2) {
        $seeds = \Yii::$app->params['nicknameSeeds'];
        $nickname = '';
        for ($i = 0; $i < $length; $i++) {
            $char = $seeds[rand(0, count($seeds) - 1)];
            $nickname = $nickname . $char;
        }
        return $nickname;
    }

}