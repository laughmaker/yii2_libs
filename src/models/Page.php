<?php
/**
 * Created by PhpStorm.
 * User: HZD
 * Date: 16/12/18
 * Time: 下午2:54
 */

namespace mmgg\models;

use Yii;
use yii\base\Model;

class Page extends Model
{
    // 关联类型
    const JOIN_TYPE_LEFT = 'LEFT JOIN';
    const JOIN_TYPE_INNER = 'INNER JOIN';
    const JOIN_TYPE_RIGHT = 'RIGHT JOIN';

    // 默认分页大小
    const DEFAULT_PAGE_SIZE = 20;

    // 每一页大小
    public $limit;

    // 分页偏移量
    public $offset;

    // 总共有多少条数据
    public $total_rows;

    // 分页索引
    public $page_index;

    // 总共有多少页数据，根据totalRows和pageSize计算
    public $total_pages;

    /***
     * Pagination constructor.
     * @param string $class
     * @param string $joinWith
     * @param string $joinType
     * @param bool $eagerLoading
     * @param array $where
     * @param array $groupBy
     */
    function __construct($class, $where = [], $groupBy=[], $joinWith='', $joinType=self::JOIN_TYPE_LEFT, $eagerLoading=false) {
        parent::__construct();

        $query = $class::find();
        if (!empty($joinWith)) {
            $query->joinWith($joinWith, false, $joinType);
        }

        if ($query instanceof yii\mongodb\ActiveQuery) {
            $this->total_rows = $query->where($where)->count();
        } else {
            $this->total_rows = $query->where($where)->groupBy($groupBy)->count();
        }
        $this->limit = $this->getLimit();
        $this->offset = $this->getOffset();
        $this->total_pages = $this->getTotalPages();
        $this->page_index = $this->getPageIndex();
    }

    public function getLimit() {
        $pageSize = Yii::$app->request->post('page_size');
        if (empty($pageSize) || $pageSize > 100 || $pageSize < 0) {
            return self::DEFAULT_PAGE_SIZE;
        }

        return $pageSize;
    }

    public function getOffset() {
        return $this->getPageIndex() * $this->getLimit();
    }

    // 分页索引，默认从0开始，如果小于0，则默认为0
    public function getPageIndex() {
        if (Yii::$app->request->post('page_index') < 0) {
            return 0;
        }
        return (Yii::$app->request->post('page_index')) ? (Yii::$app->request->post('page_index')) : 0;
    }

    public function getTotalPages() {
        return ceil($this->total_rows / $this->getLimit());
    }

    public function fields() {
        $fields = parent::fields();

        $fields['page_size'] = $fields['limit'];
        unset($fields['limit'], $fields['offset']);

        return $fields;
    }
    
}