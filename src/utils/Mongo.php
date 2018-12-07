<?php
/**
 * Created by PhpStorm.
 * User: hzd
 * Date: 2018/10/17
 * Time: 6:15 PM
 */

namespace mmgg\utils;

use Yii;
use yii\mongodb\Connection;

class Mongo
{

    /**
     * @var string
     */
    protected $dbname;

    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var \yii\mongodb\Command
     */
    protected $command;

    public function __construct($dbname='mongodb')
    {
        $this->dbname = $dbname;
        $this->db = Yii::$app->$dbname;
        $this->command = $this->db->createCommand();

        return $this;
    }

    /**
     * @param $collectionName
     * @return bool
     */
    public function createCollection($collectionName)
    {
        return $this->command->createCollection($collectionName);
    }

    /**
     * @param string $collectionName
     * @param array $condition
     * @param array $options | ['skip' => 1, 'limit' => 2, 'sort' => ['id' => SORT_ASC]]
     * @return \MongoDB\Driver\Cursor
     */
    public function all(string $collectionName, array $condition=[], array $options=[])
    {
        return $this->command->find($collectionName, $condition, $options)->toArray();
    }

    /**
     * @param string $collectionName
     * @param array|string $condition | 查询条件，如果是字符串，则key为_i
     * @return \MongoDB\Driver\Cursor
     */
    public function one(string $collectionName, $condition)
    {
        if (is_string($condition)) {
            $condition = ['_id' => $condition];
        }
        $result = $this->command->find($collectionName, $condition)->toArray();
        if (count($result) > 0) {
            return $result[0];
        }
        return null;
    }

    /**
     * 查询集合中数据条数
     * @param string $collectionName
     * @param array $condition
     * @param array $options
     * @return int
     */
    public function count(string $collectionName, array $condition=[], array $options=[])
    {
        return $this->command->count($collectionName, $condition, $options);
    }

    /**
     * @param string $collectionName
     * @param array $item
     * @return bool|\MongoDB\BSON\ObjectID
     */
    public function insert(string $collectionName, array $item)
    {
        return $this->command->insert($collectionName, $item);
    }

    /**
     * @param string $collectionName
     * @param array $list
     * @return array
     */
    public function insertList(string $collectionName, array $list)
    {
        $result = [];
        foreach ($list as $item) {
            $result[] = $this->command->insert($collectionName, $item);
        }
        return $result;
    }

    /**
     * @param string $collectionName
     * @param array $data
     * @param array $condition
     * @return \MongoDB\Driver\WriteResult
     */
    public function update(string $collectionName, array $data, array $condition)
    {
        return $this->command->update($collectionName, $condition, $data);
    }

    /**
     * @param string $collectionName
     * @param array $data
     * @param array $condition
     * @return \MongoDB\Driver\WriteResult
     */
    public function save(string $collectionName, array $data, array $condition=null)
    {
        if (!$condition) {
            return $this->insert($collectionName, $data);
        }

        $model = $this->one($collectionName, $condition);
        if (!$model) {
            return $this->insert($collectionName, $data);
        }
        return $this->update($collectionName, $data, $condition);
    }

    /**
     * @param string $collectionName
     * @param array $condition
     * @return \MongoDB\Driver\WriteResult
     */
    public function delete(string $collectionName, array $condition)
    {
        return $this->command->delete($collectionName, $condition);
    }
    
}
