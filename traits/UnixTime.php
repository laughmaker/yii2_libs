<?php
/**
 * Created by PhpStorm.
 * User: HZD
 * Date: 2017/8/12
 * Time: 下午4:02
 */

namespace mmgg\traits;

const SECOND_OF_DAY = 60 * 60 * 24;
const SECOND_OF_HALF_DAY = 60 * 60 * 12;
const SECOND_OF_TWO_HOUR = 60 * 60 * 2;

trait UnixTime
{
    /**
     * 获取时间的零点时间，如不传，则获取当前时间
     * @param int|null $timestamp
     * @return false|int
     */
    static public function zeroTime(int $timestamp=null) {
        if (!$timestamp) {
            $timestamp = time();
        }
        return strtotime(date("Y-m-d", $timestamp));
    }

    public function getDateTime(int $timestamp=null) {
        if (!$timestamp) {
            $timestamp = time();
        }
        return date("Y-m-d H:i", $timestamp);
    }

    /**
     * @param int|null $timestamp
     * @return false|int
     */
    public function getZeroTime(int $timestamp=null) {
        return self::zeroTime($timestamp);
    }

    /**
 * 获取24点的时间
 * @param int|null $timestamp
 * @return false|int
 */
    public function get24Time(int $timestamp=null) {
        return $this->getZeroTime($timestamp) + SECOND_OF_DAY;
    }

    /**
     * 获取前一天24点的时间
     * @param int|null $timestamp
     * @return false|int
     */
    public function getBefore24Time(int $timestamp=null) {
        return $this->getZeroTime($timestamp) - SECOND_OF_DAY;
    }

    /**
     * 获取22点的时间
     * @param int|null $timestamp
     * @return false|int
     */
    public function get22Time(int $timestamp=null) {
        return $this->getZeroTime($timestamp) + SECOND_OF_DAY - SECOND_OF_TWO_HOUR;
    }

    /**
     * 获取前一天22点的时间
     * @param int|null $timestamp
     * @return false|int
     */
    public function getBefore22Time(int $timestamp=null) {
        return $this->getZeroTime($timestamp) - SECOND_OF_TWO_HOUR;
    }

    /**
     * 获取后一天22点的时间
     * @param int|null $timestamp
     * @return false|int
     */
    public function getAfter22Time(int $timestamp=null) {
        return $this->get24Time($timestamp) + SECOND_OF_DAY - SECOND_OF_TWO_HOUR;
    }

    /**
     * 获取中午12点的时间
     * @param int|null $timestamp
     * @return false|int
     */
    public function get12Time(int $timestamp=null) {
        return $this->getZeroTime($timestamp) + SECOND_OF_HALF_DAY;
    }


    /**
     * 获取星期编号从星期日开始，从0到6
     * @param int $timestamp
     * @return false|string
     */
    static public function weekNumber(int $timestamp=null) {
        if ($timestamp) {
            return date('w', $timestamp);
        }

        return date('w');
    }

    /**
     * @param int $timestamp
     * @return false|string
     */
    public function getWeekNumber(int $timestamp=null) {
        return self::weekNumber($timestamp);
    }

    /**
     * @return false|string
     */
    public function getMonday() {
        return time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600;
    }

    /**
     * 获取时间对应的星期
     * @param int|null $timestamp
     * @return mixed
     */
    public function weekString(int $timestamp=null) {
        $weekNumber = $this->getWeekNumber($timestamp);
        $weeks = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
        return $weeks[$weekNumber];
    }

    /**
     * @param int|null $timestamp
     * @return mixed
     */
    public function getWeekString(int $timestamp=null) {
        return self::weekString($timestamp);
    }


    /**
     * 日期转unixtime
     * @param $str
     * @return false|int
     */
    public function str2Time($str) {
        if ($str == '0000-00-00 00:00:00' || $str == '0000-00-00') {
            return 0;
        }

        return intval(strtotime($str));
    }

}
