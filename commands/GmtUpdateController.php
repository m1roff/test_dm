<?php
/**
 * Обновления GMT поля у таблицы codes
 * 
 * @author Mirkhamidov Jasur <mirkhamidov.jasur@gmail.com>
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use app\models\Codes;

class GmtUpdateController extends Controller
{
    /**
     * Запрос на обновление таблицы codes
     */
    public function actionIndex()
    {
        $sql = 'update codes join gmt_list g set codes.gmt=g.gmt'
            .' where lower(codes.region) like lower( concat("%", replace(g.city, "-", "%"), "%") )'
            .' or lower(codes.city) like lower( concat("%", replace(g.city, "-", "%"), "%") );';
        $res = Yii::$app->db->createCommand($sql)->execute();
        $this->stdout( "Обновлено записей: ".$res."\n", Console::FG_GREEN);
        return 1;
    }
}
