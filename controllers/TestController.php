<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\CheckPhone;
use DateTimeZone;
use DateTime;

class TestController extends Controller
{

    /**
     * @var string Регулярка для проверки корректности ввода номера телефона
     */
    private $checkPhone = '/^7([0-9]{3})([0-9]{7})$/';

    private $dateTimeFormat = 'd.m.Y  H:i:s \G\M\TP';

    /**
     * Выполнение проверки
     * @param string $phone default=null
     * @return string JSON formatted
     */
    public function actionIndex($phone=null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // get UTC timestamp
        $ts = new DateTime(date('d.m.Y H:i:s'), new DateTimeZone('UTC'));

        $_status =false;
        $data = [
            'timezone'       => null, 
            'localtime'      => null, 
            'localdate'      => null, 
            'moscowdatetime' => null,
            'userdatetime'   => null,
            'utcdatetime'    => null,
            'region'         => null, 
            'city'           => null, 
            'operator'       => null, 
        ];

        if( $phone != null && preg_match($this->checkPhone, $phone, $m) )
        {
            $_code = $m[1];
            $_num = $m[2];

            $sql = 'SELECT `operator`, `city`, `region`, `gmt` FROM codes WHERE `code`=:code AND :num BETWEEN `start` AND `end`;';
            $cmd = Yii::$app->db->createCommand($sql);
            $cmd->bindValue(':code', $_code);
            $cmd->bindValue(':num', $_num);

            // Поиск номера по БД
            $in = $cmd->queryOne();

            if($in)
            {
                $_status = true;
                $data['operator'] = $in['operator'];
                $data['city']     = $in['city'];
                $data['region']   = $in['region'];

                $data['utcdatetime']    = $ts->format($this->dateTimeFormat);

                $timezoneMoscow = timezone_name_from_abbr("", 3*3600, false);
                $ts->setTimezone(new DateTimezone($timezoneMoscow));
                $data['moscowdatetime'] = $ts->format($this->dateTimeFormat);

                if( !empty($in['gmt']) )
                {
                    $timezoneTest = timezone_name_from_abbr("", $in['gmt']*3600, false);
                    $ts->setTimezone(new DateTimezone($timezoneTest));
                    $data['userdatetime'] = $ts->format($this->dateTimeFormat);
                    $data['timezone']     = $ts->format('\G\M\TP');
                    $data['localtime']    = $ts->format('H:i');
                    $data['localdate']    = $ts->format('Y-m-d');
                }

            }
        }

        return ['phone'=>$phone, 'status'=>$_status, 'data'=>$data];
    }

    private function getUTC()
    {
        return date('U');
    }
}
