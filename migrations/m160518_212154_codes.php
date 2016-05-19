<?php

use yii\db\Migration;

class m160518_212154_codes extends Migration
{
    public function up()
    {
        $this->createTable('codes', [
                'id_codes' => 'bigint unsigned not null auto_increment',
                'code'     => 'int unsigned not null',
                'start'    => 'int unsigned not null',
                'end'      => 'int unsigned not null',
                'capacity' => 'int unsigned not null',
                'operator' => 'varchar(255)',
                'city'     => 'varchar(255)',
                'region'   => 'varchar(255)',
                'primary key (`id_codes`)',
                'unique key `touniq` (`code`, `start`)',
                'key `code` (`code`)',
                'key `start` (`start`)',
                'key `end` (`end`)',
            ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');
    }

    public function down()
    {
        $this->dropTable('codes');
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
