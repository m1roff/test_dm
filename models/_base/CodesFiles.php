<?php

namespace app\models\_base;

use Yii;

/**
 * This is the model class for table "codes_files".
 *
 * @property integer $id_codes_files
 * @property string $fname
 * @property string $fname_hash
 * @property string $f_last_hash
 */
class CodesFiles extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'codes_files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fname'], 'string', 'max' => 150],
            [['fname_hash', 'f_last_hash'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_codes_files' => 'Id Codes Files',
            'fname' => 'Fname',
            'fname_hash' => 'Fname Hash',
            'f_last_hash' => 'F Last Hash',
        ];
    }
}
