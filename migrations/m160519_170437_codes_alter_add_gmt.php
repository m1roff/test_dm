<?php

use yii\db\Migration;

class m160519_170437_codes_alter_add_gmt extends Migration
{
    public function up()
    {
        $this->addColumn('codes', 'gmt', 'int default null comment "Обновляется/дополняется через консольную команду"');
        $this->createIndex('gmt', 'codes', 'gmt');
    }

    public function down()
    {
        $this->dropColumn('codes', 'gmt');
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
