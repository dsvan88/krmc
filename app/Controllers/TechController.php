<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Entities\Coupon;
use app\core\Entities\Day;
use app\core\Entities\User;
use app\core\Entities\week;
use app\core\Tech;
use app\core\Validator;
use app\core\View;
use app\libs\Db;
use app\models\Coupons;
use app\models\Days;
use app\models\Settings;
use app\models\SocialPoints;
use app\models\TelegramChats;
use app\models\Users;
use app\models\Weeks;
use app\Services\CouponService;
use app\Services\DayService;
use app\Services\SocialPointsService;
use app\Services\TechService;
use app\Services\TelegramBotService;

class TechController extends Controller
{
    public static function localeGetAction()
    {
        $lang = 'uk';
        extract(self::$route['vars']);

        $module = Validator::validate('localeModule', $_GET['module'] ?? '');
        if ($module) $lang .= '-' . $module;

        $file = "{$_SERVER['DOCUMENT_ROOT']}/app/locale/js-{$lang}.php";

        if (!file_exists($file)) return View::response([]);

        $dict = require "{$_SERVER['DOCUMENT_ROOT']}/app/locale/js-{$lang}.php";

        return View::response(['dict' => $dict]);
    }
    public static function sqlAction()
    {
        if (!empty($_POST)) {
            if (isset($_POST['sql_query'])) {
                $result = Db::query($_POST['sql_query'], [], 'Assoc');
                return View::message(['error' => 0, 'sql-result' => $result]);
            }
        }
        $vars = [
            'title' => 'Execute SQL-query',
            'texts' => [
                'SubmitLabel' => 'Execute',
            ],
            'scripts' => [
                'plugins/ckeditor.js',
                'forms-admin-funcs.js',
                'images-pad.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public static function backupAction()
    {
        if (!empty($_POST)) {
            if (empty($_POST['table']))
                return View::message(['error' => 1, 'message' => 'Something wrong with your query!']);

            $table = trim($_POST['table']);
            $result = TechService::backup($table);
            $archiveName = CLUB_SNAME . ' backup ' . date('d.m.Y', $_SERVER['REQUEST_TIME']);
            if ($table !== 'all') {
                $result = [$table => array_values($result)];
                $archiveName = "$table $archiveName";
            }
            $archive = TechService::archive($archiveName, $result);
            return View::file($archive, basename($archive));
        }
        $vars = [
            'title' => 'DB Backup form',
            'texts' => [
                'SubmitLabel' => 'Execute',
            ],
            'scripts' => [
                'plugins/ckeditor.js',
                'forms-admin-funcs.js',
                'images-pad.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public static function backupSaveAction()
    {
        ignore_user_abort(true);
        error_reporting(0);
        set_time_limit(90);

        if (Days::current() < 3) {
            SocialPointsService::applyBookingPoints();
        }

        $settings = Settings::findBy('type', 'backup')[0];

        foreach ($settings['setting'] as $index => $setting) {
            $backup[$setting['slug']] = [
                'index' => $index,
                'value' => $setting['value']
            ];
        }
        if (empty($backup['email']['value']) || $backup['last']['value'] > $_SERVER['REQUEST_TIME'] - BACKUP_FREQ) exit();

        $settings['setting'][$backup['last']['index']]['value'] = $_SERVER['REQUEST_TIME'];
        Settings::edit($settings['id'], ['setting' => $settings['setting']]);

        header("Connection: close", true);
        header("Content-Encoding: none" . PHP_EOL);
        header("Content-Length: 0", true);
        flush();

        TechService::sendBackup($backup['email']['value']);

        exit();
    }
    public static function scheduleFinishAction()
    {
        ignore_user_abort(true);
        error_reporting(0);
        set_time_limit(90);

        $settings = Settings::load('finish');
        if (($settings['last']['value'] ?? 0) > $_SERVER['REQUEST_TIME'] - (TIMESTAMP_DAY / 3)) exit();

        Settings::save('finish', 'last', $_SERVER['REQUEST_TIME']);

        header("Connection: close", true);
        header("Content-Encoding: none" . PHP_EOL);
        header("Content-Length: 0", true);
        flush();

        DayService::finishExpiredDays();

        exit();
    }
    public static function restoreAction()
    {
        // View::redirect('/');
        ini_set('max_execution_time', 300);
        if (!empty($_POST)) {
            if (TechService::restore())
                return View::message('Done!');
            return View::message(['error' => true, 'message' => 'Something wrong with your datafile!']);
        }
        $vars = [
            'title' => 'DB Restore Form',
            'texts' => [
                'SubmitLabel' => 'Execute',
            ],
            'scripts' => [
                'plugins/ckeditor.js',
                'forms-admin-funcs.js',
                'images-pad.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public static function dbrebuildAction()
    {
        return View::redirect('/');
        set_time_limit(720);
        // $users = Users::getAll();
        // foreach ($users as $user) {
        //     SocialPoints::set(0, $user['id']);
        // }
        $weeks = Weeks::getAll();
        $time = strtotime('01.01.2025');
        foreach ($weeks as $week) {
            if ($week['finish'] > $time) {
                SocialPointsService::applyBookingPoints($week['id']);
            }
        }
        echo 'Weeks rebuilded.<br>';

        echo 'Done!';
    }
    public static function selfTestTelegramAction()
    {
        // View::redirect('/');
        $params = [
            'message' => [
                'message_id' => 189,
                'from' => [
                    'id' => 900669168,
                    // 'id' => 412223734,
                    // 'is_bot' => false,
                    'first_name' => 'Dmytro',
                    'last_name' => 'Vankevych',
                    // 'username' => 'dsvan88',
                    'language_code' => 'uk',
                ],
                'chat' => [
                    'id' => 900669168,
                    // 'id' => -1001871083872,
                    'first_name' => 'Dmytro',
                    'last_name' => 'Vankevych',
                    // 'username' => 'dsvan88',
                    'type' => 'private',
                    // 'type' => 'group',
                ],
                'date' => 1652025484,
                // 'text' => '+ на 18',
                'text' => '/day',
                // 'text' => '/dice',
                // 'text' => '/chat',
                // 'text' => '/spshop',
                // 'text' => '/week',
                // 'text' => '/chat main',
                // 'text' => 'Checker',
                // 'text' => '/unreg',
                // 'text' => '/reg +сг,Джокер,18:40,(тест)',
                // 'text' => '+ на четвер, десь на 18:45, звісно, що підстрахую, але поки що (під ?)',
                // 'text' => '/?',
            ]
        ];

        $url = strtolower(Tech::getRequestProtocol()) . '://' . $_SERVER['HTTP_HOST'] . '/api/telegram/webhook';
        $options = [
            CURLOPT_RETURNTRANSFER => false,
            // CURLOPT_RETURNTRANSFER => true,
            // CURLOPT_FRESH_CONNECT => true,
            CURLOPT_POST => true,       // отправка данных методом POST
            CURLOPT_TIMEOUT => 10,      // максимальное время выполнения запроса
            CURLOPT_FAILONERROR => true,
            CURLOPT_HTTPHEADER => ['Content-Type:application/json'],
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => json_encode($params, JSON_UNESCAPED_UNICODE),
        ];
        $curl = curl_init();

        curl_setopt_array($curl, $options);
        $result = json_decode(curl_exec($curl), true);
        var_dump($result);
        return $result;
    }
    public static function testAction()
    {
        error_reporting(E_ALL);
        $id = '9fbea223facb5808';
        Tech::dump($id);
        $day = Day::create();
        $coupon = Coupon::create($id);
        $coupon->expire($day)->save();
    }
}
