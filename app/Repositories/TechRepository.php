<?

namespace app\Repositories;

use app\core\Locale;
use app\core\Mailer;
use app\libs\Db;
use app\models\Settings;
use PDO;
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
    public static function pack(array $dataArray = []){
        if (empty($dataArray)) return false;

        return gzencode(json_encode($dataArray, JSON_UNESCAPED_UNICODE), 9);
    }
    public static function archive(string $filename = 'backup', array $dataArray = []){
        
        $zip = new ZipArchive();

        $folder = sys_get_temp_dir();

        $extension = 'zip';
        $fullpath = "$folder/$filename.$extension";

        if ($zip->open($fullpath, ZipArchive::CREATE)!==TRUE) {
            exit("Невозможно открыть <$fullpath>\n");
        }

        $zip->setPassword(sha1(ROOT_PASS_DEFAULT.date('d.m.Y')));

        foreach($dataArray as $name=>$data){
            $filename = "$name.json";
            $zip->addFromString($filename, json_encode($data, JSON_UNESCAPED_UNICODE));
            $zip->setEncryptionName($filename, ZipArchive::EM_AES_256);
        }

        $zip->close();

        return $fullpath;
    }
    public static function unzipping(){
        $dotPlace = mb_strrpos($_FILES['data']['name'], '.', 0, 'UTF-8');
        $extension = mb_substr($_FILES['data']['name'], $dotPlace+1, null, 'UTF-8');
        $backupName = mb_substr($_FILES['data']['name'], 0, $dotPlace, 'UTF-8');

        $folder = sys_get_temp_dir();

        $folder .= "/$backupName";
        if (file_exists($folder)){
            self::truncateDirectory($folder, 'json');
        }

        preg_match("/\d{2}.\d{2}.\d{4}/", $backupName, $matched);
        $date = $matched[count($matched)-1];
        $zip = new ZipArchive();
        $zip->open($_FILES['data']['tmp_name']);
        $zip->setPassword(sha1(ROOT_PASS_DEFAULT.$date));
        $zip->extractTo($folder);
        return glob("$folder/*.json");
    }
    public static function truncateDirectory(string $folder = null, string $pattern = null): bool{
        
        if (empty($folder)) return false;

        if (!empty($pattern)){
            $pattern = "*.$pattern";
        }
        
        $files = glob("$folder/$pattern");
        foreach($files as $file){
            if (!is_file($file)) continue;
            unlink($file);
        }
        return true;
    }
    public static function rowsCount(array $array){
        $count = 0;
        foreach($array as $table => $rows){
            $count += count($rows);
        }
        return $rows;
    }

    public static function scheduleBackup():void{
        error_reporting(0);
        $settings = Settings::getGroup('backup');

        if (empty($settings['email']['value']) || $settings['last']['value'] > $_SERVER['REQUEST_TIME'] - BACKUP_FREQ) exit();

        if (self::sendBackup($settings['email']['value'])) {
            Settings::edit($settings['last']['id'], ['value' => $_SERVER['REQUEST_TIME']]);
        }
    }

    public static function sendBackup(string $email){
        
        $result = self::backup();
        $archiveName = 'backup '.date('d.m.Y', $_SERVER['REQUEST_TIME']);
        // $archive = base64_encode(self::pack($result));
        $archive = self::archive($archiveName, $result);

        $rowsCount = self::rowsCount($result);
        $mailer = new Mailer();
        $mailer->prepMessage([
            'title' => Locale::phrase(['string' => '<no-reply> %s - %s', 'vars' => [ CLUB_NAME, $archiveName ]]),
            'body' => "<p>Database backup.</p><p>Full DB in attached file.</p><p>Rows count: <b>$rowsCount</b></p>",
        ]);
        $mailer->attach($archive, $archiveName.'.zip');
/*         $mailer->prepMessage([
            'title' => Locale::phrase(['string' => '<no-reply> %s - %s', 'vars' => [ CLUB_NAME, $archiveName ]]),
            'body' => "<p>START_BASE64_STRING:$archive:END_BASE64_STRING</p>",
        ]); */
        return $mailer->send($email);
    }
    public static function migration(){

        $dotPlace = mb_strrpos($_FILES['data']['name'], '.', 0, 'UTF-8');
        $extension = mb_substr($_FILES['data']['name'], $dotPlace+1, null, 'UTF-8');
        $backupName = mb_substr($_FILES['data']['name'], 0, $dotPlace, 'UTF-8');

        if ($extension === 'zip'){
            return self::refillTables();
        }
        else {
            return self::refillTable($backupName, $_FILES['data']['tmp_name']);
        }
    }
    public static function refillTables(){

        $files = self::unzipping();

        if (empty($files)) return false;

        usort($files, function ($value){
            return strrpos($value, 'users') ? -1 : 1;
        });
        DB::dbDropTables();

        $folderLength = mb_strrpos(str_replace('\\', '/', $files[0]), '/', 0, 'UTF-8')+1;
        $dotPlace = $folderLength - mb_strrpos($files[0], '.', 0, 'UTF-8');

        foreach($files as $file){
            $table = mb_substr($file, $folderLength, $dotPlace, 'UTF-8');
            self::refillTable($table, $file);
        }
        return true;
    }
    public static function refillTable($table, $path){

        if (!in_array($table, [SQL_TBL_GAMES, SQL_TBL_USERS, SQL_TBL_WEEKS, SQL_TBL_SETTINGS, SQL_TBL_PAGES, SQL_TBL_CONTACTS, SQL_TBL_TG_CHATS]))
            return false;

        $data = json_decode(trim(file_get_contents($path)), true);

        if (!is_array($data))
            return false;

        DB::tableTruncate($table);
        DB::insert($data, $table);
        DB::query("SELECT setval(pg_get_serial_sequence('$table', 'id'), coalesce(max(id)+1, 1), false) FROM $table;");
    }
}
