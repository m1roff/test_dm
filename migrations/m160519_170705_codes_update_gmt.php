<?php

use yii\db\Migration;

class m160519_170705_codes_update_gmt extends Migration
{
    public function up()
    {
        Yii::$app->controller->run('gmt-update/index');
    }

    public function down()
    {
        return true;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
