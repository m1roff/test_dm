<?php
/**
 * Работа с базой реестра Российской системы 
 * Источник реестра: http://rossvyaz.ru/activity/num_resurs/registerNum/
 * 
 * @author Mirkhamidov Jasur <mirkhamidov.jasur@gmail.com>
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use app\models\Codes;
use app\models\CodesFiles;

class PhonesController extends Controller
{

    /**
     * @access private
     * @var Array Список файлов для скачивания
     */
    private $codeFiles = [
        'http://rossvyaz.ru/docs/articles/Kody_ABC-3kh.csv',
        'http://rossvyaz.ru/docs/articles/Kody_ABC-4kh.csv',
        'http://rossvyaz.ru/docs/articles/Kody_ABC-8kh.csv',
        'http://rossvyaz.ru/docs/articles/Kody_DEF-9kh.csv',
    ];


    public $change = false;


    /**
     * @access private
     * @var string Путь-алиас, куда загружать файлы
     */
    private $filesDir = '@app/files_rossvyaz';


    /**
     * Получить данные с сайта http://rossvyaz.ru/activity/num_resurs/registerNum/
     * Для изменения даты файла сегодняшним (эмуляция), примените атрибут <code>--change=true</code>
     * 
     * Список файлов для скачивания указывается параметром 
     */
    public function actionGetFiles()
    {
        if( empty($this->codeFiles) )
        {
            $this->consolePrint('Не указаны файлы для скачивания!', 'error');
            return 0;
        }
        elseif ( !$this->checkDownloadDir() )
        {
            $this->consolePrint('Ошибка с директорией для скачивания файлов', 'error');
            return 0;
        }

        $_codeFilesCount = count($this->codeFiles);
        $_pathFilesDir = Yii::getAlias($this->filesDir);

        for($i=0; $i<$_codeFilesCount; ++$i)
        {
            $_urlFile = $this->codeFiles[$i];

            $this->consolePrint('Загрузка файла "'.$_urlFile.'" ', 'info');

            // Проверка, нужно ли обновлять записи в БД
            if( !$this->needToImport($_urlFile) )
            {
                $this->consolePrint('    > записи в БД актуальны!', 'info');
                continue;
            }

            // Формирование временного файла
            $_tmpFileName = md5($_urlFile);
            $_pathFileTmp = $_pathFilesDir.DIRECTORY_SEPARATOR.$_tmpFileName.'.csv';

            // Загрузка файла
            if( !@copy($_urlFile, $_pathFileTmp) )
            {
                $err= error_get_last();
                $this->consolePrint('Ошибка при скачивании файла ('.$err['type'].':'.$err['message'].').', 'error');
                return 0;
            }
            else
            {
                $this->consolePrint('    > загружен!', 'success');
                $this->consolePrint('    > импортируем!', 'info');

                if( $this->importData($_pathFileTmp) )
                {
                    $this->saveInfo($_urlFile);
                    unlink($_pathFileTmp);
                }
                else
                {
                    unlink($_pathFileTmp);
                    $this->consolePrint('    > ошибка импорта', 'error');
                    return 0;
                }
            }
        }

        $this->consolePrint("\nИмпорт всех файлов завершен", 'info');

        return 1;
    }

    /**
     * Сохранить информацию о файле в БД
     * @access private
     * @param string $fileUrl URL к файлу для скачивания
     */
    private function saveInfo($fileUrl)
    {
        $f_nameHash = md5($fileUrl);
        $f_fileHash = md5_file($fileUrl);

        $sql = 'insert into '.CodesFiles::tableName()
            .' (fname, fname_hash, f_last_hash) VALUES(:fname, :fname_hash, :f_last_hash)'
            .'ON DUPLICATE KEY UPDATE f_last_hash=VALUES(f_last_hash)';
        $cmd = Yii::$app->db->createCommand($sql);
        $cmd->bindValue(':fname', basename($fileUrl));
        $cmd->bindValue(':fname_hash', $f_nameHash);
        $cmd->bindValue(':f_last_hash', $f_fileHash);
        $cmd->execute();
    }

    /**
     * Проверка на обновление файла
     * @access private
     * @param string $fileUrl URL к файлу для скачивания
     * @return bool true - если необходимо обновить данные
     */
    private function needToImport($fileUrl)
    {
        $f_nameHash = md5($fileUrl);

        $model = CodesFiles::findOne(['fname_hash'=>$f_nameHash]);
        if($model)
        {
            $f_fileHash = md5_file($fileUrl);
            if( $model->f_last_hash == $f_fileHash )
            {
                // Файлы одинаковые, менять ничего не нужно
                return false;
            }
            return true;
        }
        else
        {
            // Записи о файле в БД нет, значит надо загрузить
            return true;
        }
    }

    /**
     * @access private
     * @param string $filePath путь к файлу для чтения
     * @return bool
     */
    private function importData($filePath)
    {
        if( is_file($filePath) )
        {
            $h = fopen('php://temp', 'w+');
            fwrite($h, iconv('CP1251', 'UTF-8', file_get_contents($filePath)));
            rewind($h);

            $_row = 0;  // Для подсчета кол-ва импортированных строк
            $_fl = 0;  // Флаг, для игнорирования первой строки файла
            while( ($_csvData = fgetcsv($h, 0, ';')) !== false )
            {
                if( $_fl==0 ) 
                {
                    $_fl = 1;
                    continue;
                }
                $sql = 'insert into '.Codes::tableName()
                    .' (code, start, end, capacity, operator, region, city) '
                    .' VALUES(:code, :start, :end, :capacity, :operator, :region, :city) '
                    .' ON DUPLICATE KEY UPDATE end=VALUES(end), capacity=VALUES(capacity), operator=VALUES(operator), region=VALUES(region), city=VALUES(city)';
                $cmd = Yii::$app->db->createCommand($sql);
                $cmd->bindValue(':code', trim($_csvData[0]) );
                $cmd->bindValue(':start', trim($_csvData[1]) );
                $cmd->bindValue(':end', trim($_csvData[2]) );
                $cmd->bindValue(':capacity', trim($_csvData[3]) );
                $cmd->bindValue(':operator', trim($_csvData[4]) );

                $_region = explode('|', trim($_csvData[5]) );
                if( !empty($_region[1]) )
                {
                    $cmd->bindValue(':region', trim($_region[1]) );
                    $cmd->bindValue(':city', trim($_region[0]) );
                }
                else
                {
                    $cmd->bindValue(':region', trim($_region[0]) );
                    $cmd->bindValue(':city', null );
                }
                

                $_res = $cmd->execute();

                ++$_row;

                if( $_row !=0 && ($_row%1000) == 0 )
                {
                    $this->consolePrint('.', 'trace', false);
                }
            }

            $this->consolePrint("\n    > импортировано записей:".$_row, 'success');

            fclose($h);
            return true;
        }

        return false;
    }


    /**
     * Проверить, существует ли директория для скачивания файлов
     * Если не существует, то попробовать создать.
     * 
     * @access private
     * @return bool
     */
    private function checkDownloadDir()
    {
        $_dirPath = Yii::getAlias($this->filesDir);
        if( !is_dir( $_dirPath ))
        {
            return FileHelper::createDirectory($_dirPath, 509, true);
        }
        return true;
    }


    /**
     * Печать в консоль
     * 
     * Проверяется, запущен ли скрипт через консоль, иначе ничего не выводит
     * 
     * Возможные типы сообщений:
     *     error    Текст красным цветом
     *     success  Текст зеленым цветом
     *     info     Текст желтым цветом
     *     trace    Просто текст
     * 
     * @access private
     * @param string $message Сообщение для вывода в коносоли
     * @param string $type В каком виде (цвет) выводить текст, по умолчанию - 'trace'
     * @param bool $n Нужно ли вставлять перевод строки, true по умолчанию
     */
    private function consolePrint($message, $type='trace', $n=true)
    {
        $_opt = null;
        if( Yii::$app instanceof \yii\console\Application)
        {
            switch ($type) {
                case 'error':
                    $_opt = Console::FG_RED;
                break;
                case 'success':
                    $_opt = Console::FG_GREEN;
                break;
                case 'info':
                    $_opt = Console::FG_YELLOW;
                break;
            }
            if($n) $message .= "\n";
            $this->stdout($message, $_opt);
        }
    }
}
