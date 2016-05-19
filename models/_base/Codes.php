<?php

namespace app\models\_base;

use Yii;

/**
 * This is the model class for table "codes".
 *
 * @property string $id_codes
 * @property integer $code
 * @property integer $start
 * @property integer $end
 * @property integer $capacity
 * @property string $operator
 * @property string $city
 * @property string $region
 * @property integer $gmt
 */
class Codes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'codes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'start', 'end', 'capacity'], 'required'],
            [['code', 'start', 'end', 'capacity', 'gmt'], 'integer'],
            [['operator', 'city', 'region'], 'string', 'max' => 255],
            [['code', 'start'], 'unique', 'targetAttribute' => ['code', 'start'], 'message' => 'The combination of Code and Start has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_codes' => 'Id Codes',
            'code' => 'Code',
            'start' => 'Start',
            'end' => 'End',
            'capacity' => 'Capacity',
            'operator' => 'Operator',
            'city' => 'City',
            'region' => 'Region',
            'gmt' => 'Gmt',
        ];
    }
}
