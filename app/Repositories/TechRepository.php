<?

namespace app\Repositories;

use app\core\Locale;
use app\core\Mailer;
use app\libs\Db;
use ZipArchive;

class TechRepository
{
    public static function backup(string $table = 'all')
    {
        if ($table !== 'all')
            return Db::query("SELECT * FROM $table ORDER BY id", [], 'Assoc');

        $tables = Db::getTables();
        $result = [];
        $count = count($tables);
        for ($i=0; $i < $count; $i++) { 
            $result[$tables[$i]['tablename']] = self::backup($tables[$i]['tablename']);
        }
        return $result;
    }
    public static function archive(string $filename = 'backup', array $dataArray = []){
        if (empty($dataArray))
            return false;

        // $zip = new ZipArchive();

        $folder = $_SERVER['DOCUMENT_ROOT'] .'/public/backups';

        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $extension = 'json.gz';
        $fullpath = "$folder/$filename.$extension";

        file_put_contents($fullpath, gzencode(json_encode($dataArray, JSON_UNESCAPED_UNICODE), 9));

        // $zip->addFromString("$name.json", json_encode($data, JSON_UNESCAPED_UNICODE));
        // }

        // if ($zip->open($fullpath, ZipArchive::CREATE)!==TRUE) {
        //     exit("Невозможно открыть <$fullpath>\n");
        // }
        // foreach($dataArray as $name=>$data){
            // $zip->addFromString("$name.json", json_encode($data, JSON_UNESCAPED_UNICODE));
        // }
        // error_log(json_encode($zip, JSON_UNESCAPED_UNICODE));
        // $zip->close();

        return $fullpath;
    }
    public static function sendBackup(string $email){
        // error_reporting(0);
        $result = self::backup();
        $archiveName = 'backup '.date('d.m.Y', $_SERVER['REQUEST_TIME']);
        $archive = self::archive($archiveName, $result);

        $mailer = new Mailer();
        $mailer->prepMessage([
            'title' => Locale::phrase(['string' => '<no-reply> %s - %s', 'vars' => [ MAFCLUB_NAME, $archiveName ]]),
            'body' => '<p>Database backup.</p><p>Full DB in attached file.</p>',
        ]);
        $mailer->attach($archive, $archiveName.'.zip');
        return $mailer->send($email);
    }
}
