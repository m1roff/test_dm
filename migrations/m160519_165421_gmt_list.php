<?php

use yii\db\Migration;

class m160519_165421_gmt_list extends Migration
{
    public function up()
    {
        $this->createTable('gmt_list', [
                'id_gmt_list' => 'pk',
                'gmt'         => 'int not null comment "Разница от +0000"',
                'city'        => 'varchar(150) comment "Название города"',
                'key `gmt` (`gmt`)',
            ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');
    }

    public function down()
    {
        $this->dropTable('gmt_list');
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
