<?php

/**
 * Created by PhpStorm.
 * User: hzd
 * Date: 2018/11/15
 * Time: 9:35 PM
 */

namespace mmgg\models;


use yii\behaviors\TimestampBehavior;
use yii\mongodb\ActiveRecord;

/**
 * This is the model class for collection "malfunction_category".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property integer $id
 * @property integer $deleted
 * @property integer $created_at
 * @property integer $updated_at
 *
 */
class MongoActiveRecord extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'deleted'], 'integer'],
            ['deleted', 'default', 'value' => 0],
        ];
    }

    /**
     * 保存数据前，对数据做一些处理
     *
     * @param bool $insert
     * @return void
     */
    public function beforeSave($insert)
    {
        $result = parent::beforeSave($insert);

        $this->createAutoIncrementId($insert);
        $this->convert2Int($this->integerFields());
        
        return $result;
    }

    /**
     * 如果是插入数据，则添加自增id
     *
     * @param boolean $insert
     * @return void
     */
    public function createAutoIncrementId(bool $insert)
    {
        if ($insert) {
            $this->id = self::find()->count() + 1;
        }
    }

    /**
     * 配置需要强制为整型的字段
     *
     * @return array
     */
    public function integerFields()
    {
        return ['id', 'created_at', 'updated_at', 'deleted'];
    }

    /**
     * 将fields中的字段值强转为整型
     * @param array $fields
     */
    public function convert2Int(array $fields)
    {
        foreach ($fields as $field) {
            $this->$field = (int)$this->$field;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return [
            '_id',
            'id',
            'deleted',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * 配置返回给客户端数据字段
     *
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'deleted',
            'created_at',
        ];
    }


}