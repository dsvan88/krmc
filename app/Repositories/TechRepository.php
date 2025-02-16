<?

namespace app\Repositories;

use app\core\Locale;
use app\core\Mailer;
use app\core\Tech;
use app\libs\Db;
use app\models\Settings;
use Google\Client as Google_Client;
use Google\Service\Drive  as Google_Drive;
use Google\Service\Drive\DriveFile as Google_Drive_File;
use Google\Service\Drive\Permission as Google_Drive_Permission;

class TechRepository
{
    public static function backup(string $table = 'all')
    {
        if ($table !== 'all')
            return Db::query("SELECT * FROM $table ORDER BY id", [], 'Assoc');

        $tables = Db::getTables();
        $result = [];
        $count = count($tables);
        for ($i = 0; $i < $count; $i++) {
            $result[$tables[$i]['tablename']] = self::backup($tables[$i]['tablename']);
        }
        return $result;
    }
    public static function pack(array $dataArray = [])
    {
        if (empty($dataArray)) return false;

        return gzencode(json_encode($dataArray, JSON_UNESCAPED_UNICODE), 9);
    }
    public static function archive(string $filename = 'backup', array $dataArray = [])
    {

        $zip = new \ZipArchive();

        $folder = sys_get_temp_dir();

        $extension = 'zip';
        $fullpath = "$folder/$filename.$extension";

        if ($zip->open($fullpath, \ZipArchive::CREATE) !== TRUE) {
            exit("Невозможно открыть <$fullpath>\n");
        }

        $zip->setPassword(sha1(ROOT_PASS_DEFAULT . date('d.m.Y')));

        foreach ($dataArray as $name => $data) {
            $filename = "$name.json";
            $zip->addFromString($filename, json_encode($data, JSON_UNESCAPED_UNICODE));
            $zip->setEncryptionName($filename, \ZipArchive::EM_AES_256);
        }

        $zip->close();

        return $fullpath;
    }
    public static function unzipping()
    {
        $dotPlace = mb_strrpos($_FILES['data']['name'], '.', 0, 'UTF-8');
        $extension = mb_substr($_FILES['data']['name'], $dotPlace + 1, null, 'UTF-8');
        $backupName = mb_substr($_FILES['data']['name'], 0, $dotPlace, 'UTF-8');

        $folder = sys_get_temp_dir();

        $folder .= "/$backupName";
        if (file_exists($folder)) {
            self::truncateDirectory($folder, 'json');
        }

        preg_match("/\d{2}.\d{2}.\d{4}/", $backupName, $matched);
        $date = $matched[count($matched) - 1];
        $zip = new \ZipArchive();
        $zip->open($_FILES['data']['tmp_name']);
        $zip->setPassword(sha1(ROOT_PASS_DEFAULT . $date));
        $zip->extractTo($folder);

        return glob("$folder/*.json");
    }
    public static function truncateDirectory(string $folder = null, string $pattern = null): bool
    {

        if (empty($folder)) return false;

        if (!empty($pattern)) {
            $pattern = "*.$pattern";
        }

        $files = glob("$folder/$pattern");
        foreach ($files as $file) {
            if (!is_file($file)) continue;
            unlink($file);
        }
        return true;
    }
    public static function rowsCount(array $array)
    {
        $count = 0;
        foreach ($array as $table => $rows) {
            $count += count($rows);
        }
        return $count;
    }

    public static function scheduleBackup(): void
    {
        $settings = Settings::getGroup('backup');

        if (empty($settings['email']['value']) || $settings['last']['value'] > $_SERVER['REQUEST_TIME'] - BACKUP_FREQ) exit();

        $url = "{$_SERVER['HTTP_X_FORWARDED_PROTO']}://{$_SERVER['SERVER_NAME']}/tech/backup/save";
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => 100,      // максимальное время выполнения запроса
            CURLOPT_FAILONERROR => true,
            CURLOPT_URL => $url,
        ];
        $curl = curl_init();

        curl_setopt_array($curl, $options);
        curl_exec($curl);
        exit();
    }

    public static function sendBackup(string $email)
    {

        $result = self::backup();
        $archiveName = 'backup ' . date('d.m.Y', $_SERVER['REQUEST_TIME']);
        $archive = self::archive($archiveName, $result);

        $rowsCount = self::rowsCount($result);
        $mailer = new Mailer();
        $mailer->prepMessage([
            'title' => Locale::phrase(['string' => '<no-reply> %s - %s', 'vars' => [CLUB_NAME, $archiveName]]),
            'body' => "<p>Database backup.</p><p>Full DB in attached file.</p><p>Rows count: <b>$rowsCount</b></p>",
        ]);
        $mailer->attach($archive, $archiveName . '.zip');

        return $mailer->send($email);
    }
    public static function restore()
    {

        $dotPlace = mb_strrpos($_FILES['data']['name'], '.', 0, 'UTF-8');
        $extension = mb_substr($_FILES['data']['name'], $dotPlace + 1, null, 'UTF-8');
        $backupName = mb_substr($_FILES['data']['name'], 0, $dotPlace, 'UTF-8');

        if ($extension === 'zip') {
            return self::refillTables();
        } else {
            return self::refillTable($backupName, $_FILES['data']['tmp_name']);
        }
    }
    public static function refillTables()
    {

        $files = self::unzipping();

        if (empty($files)) return false;

        usort($files, function ($value) {
            return strrpos($value, 'users') ? -1 : 1;
        });

        $folderLength = mb_strrpos(str_replace('\\', '/', $files[0]), '/', 0, 'UTF-8') + 1;
        $dotPlace = $folderLength - mb_strrpos($files[0], '.', 0, 'UTF-8');

        foreach ($files as $file) {
            $table = mb_substr($file, $folderLength, $dotPlace, 'UTF-8');
            self::refillTable($table, $file);
        }
        return true;
    }
    public static function refillTable($table, $path)
    {

        if (!in_array($table, [SQL_TBL_GAMES, SQL_TBL_USERS, SQL_TBL_WEEKS, SQL_TBL_SETTINGS, SQL_TBL_PAGES, SQL_TBL_CONTACTS, SQL_TBL_TG_CHATS]))
            return false;

        $content = str_replace('1970-01-01 00:00:00', '1970-01-02 00:00:00', trim(file_get_contents($path)));
        $data = json_decode($content, true);

        if (!is_array($data))
            return false;

        DB::tableTruncate($table);
        DB::insert($data, $table);
        DB::resetIncrement($table);
    }
    public static function GoogleFS(){
        // $setting = Settings::load('gdrive');
        $jsonKey = json_decode(Settings::load('gdrive')['credentials']['value'], true);
        try {
            $client = new Google_Client();
            $client->setAuthConfig($jsonKey);
            $client->addScope(Google_Drive::DRIVE);
 
            $service = new Google_Drive($client);

            // Создание объекта файла
            // $file = new Google_Drive_File();
            // $file->setName('background.jfif');

            // $filePath = 'D:\OSPanel\domains\krmc\public\images\background.jfif';
            // Загрузка файла
            // $content = file_get_contents($filePath);

            // try {
            //     // Загружаем файл в корневую папку Google Drive
            //     $uploadedFile = $service->files->create($file, [
            //         'data' => $content,
            //         'mimeType' =>  mime_content_type($filePath),
            //         'uploadType' => 'multipart'
            //     ]);
            //     echo 'File uploaded successfully: ' . $uploadedFile->getId();
            // } catch (\Exception $e) {
            //     echo 'Error uploading file: ' . $e->getMessage();
            // }
           
            try {
                $results = $service->files->listFiles([
                    'pageSize' => 10,
                    'fields' => 'nextPageToken, files(id, name)',
                ]);
        
                if (count($results->files) == 0) {
                    echo "No files found.<br>";
                } else {
                    echo "Files:<br>";
                    foreach ($results->files as $file) {
                        // Tech::dump($file);
                        echo $file->name . " (" . $file->id . ")<br>";
                        $permission = new Google_Drive_Permission();
                        $permission->setType('anyone');
                        $permission->setRole('reader');

                        // Применяем разрешение к файлу
                        $service->permissions->create($file->id, $permission);

                        echo "<br>File is now public and accessible.";
                        echo '<img src="https://lh3.googleusercontent.com/d/' . $file->id .'" alt="Описание изображения">';
                    }
                }
                
                // echo '<img src="https://drive.google.com/uc?id=1mDmeJffenU_fnxQDFkWUfarDJ8DLdeHE" loading="lazy" alt="...">';
            } catch (\Exception $e) {
                echo 'Error fetching files: ' . $e->getMessage();
            }

            // $results = $service->files->listFiles();

            // if (count($results->files) == 0) {
            //     echo "Нет файлов в Google Drive.";
            // } else {
            //     echo "Файлы:<br>";
            //     foreach ($results->files as $file) {
            //         printf("%s (%s)<br>", $file->name, $file->id);
            //     }
            // }
        } catch(\Throwable $error){
            var_dump($error);
        }
        
        
        // return View::errorCode(404, ['message' => 'Result is Ok']);
    }
}
