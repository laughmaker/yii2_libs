<?php
/**
 * Created by pzq.
 * User: mac
 * Date: 2018/12/6
 * Time: 11:07 AM
 */

namespace mmgg\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class MysqlActiveRecord extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
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


}