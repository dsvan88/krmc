<?

namespace app\Controllers;

use app\core\Controller;
use app\core\PHPMailer\PHPMailer;
use app\core\View;
use app\libs\Db;
use app\models\Contacts;
use app\models\TelegramChats;
use app\models\Users;
use app\models\Weeks;

class TechController extends Controller
{
    public static function sqlAction()
    {
        if (!empty($_POST)) {
            if (isset($_POST['sql_query'])) {
                $result = Db::query($_POST['sql_query'], [], 'Assoc');
                View::message(['error' => 0, 'sql-result' => $result]);
            }
        }
        $vars = [
            'title' => '{{ SQL_Action_Title }}',
            'texts' => [
                'SubmitLabel' => 'Execute',
            ],
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::render($vars);
    }
    public static function backupAction()
    {
        if (!empty($_POST)) {
            $query = !empty($_POST['table']) ? "SELECT * FROM {$_POST['table']} ORDER BY id" : $_POST['sql_query'];
            if (!empty($query)) {
                $result = Db::query($query, [], 'Assoc');
                View::file(json_encode($result, JSON_UNESCAPED_UNICODE), empty($_POST['table']) ? 'backup-query.txt' : "backup-{$_POST['table']}.txt");
            }
            View::message(['error' => true, 'message' => 'Something wrong with sql-query!' . PHP_EOL . $query]);
        }
        $vars = [
            'title' => '{{ SQL_Action_Title }}',
            'texts' => [
                'SubmitLabel' => 'Execute',
            ],
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::render($vars);
    }
    public static function migrationAction()
    {
        // View::redirect('/');
        if (!empty($_POST)) {
            $table = substr($_FILES['data']['name'], strpos($_FILES['data']['name'], '-') + 1);
            $table = substr($table, 0, strrpos($table, '.'));

            if (!in_array($table, ['settings', 'tgchats', 'users', 'weeks', 'pages', 'games', 'contacts']))
                View::message(['error' => true, 'message' => 'Something wrong with your datafile!']);

            $data = json_decode(trim(file_get_contents($_FILES['data']['tmp_name'])), true);

            if (!is_array($data))
                View::message(['error' => true, 'message' => 'Something wrong with your datafile!']);

            DB::tableTruncate($table);
            DB::insert($data, $table);
            DB::query("SELECT setval(pg_get_serial_sequence('$table', 'id'), coalesce(max(id)+1, 1), false) FROM $table;");

            View::message('Done!');
        }
        $vars = [
            'title' => '{{ SQL_Action_Title }}',
            'texts' => [
                'SubmitLabel' => 'Execute',
            ],
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::render($vars);
    }
    public static function dbrebuildAction()
    {
        // View::redirect('/');db
        $chatsData = TelegramChats::getChatsList();
        foreach ($chatsData as $index => $chat) {
            if (empty($chat['personal']['nickname'])) continue;
            $userData = Users::getDataByName($chat['personal']['nickname']);
            if (empty($userData['id'])) continue;
            TelegramChats::edit(['user_id' => $userData['id']], $chat['id']);
        }
        // $weekId = 0;
        // while ($weekData = Weeks::weekDataById(++$weekId)) {
        //     // echo '$weekData id - '.$weekId.'</br>';
        //     foreach ($weekData['data'] as $dayNum => $dayData) {
        //         if (!in_array($dayData['game'], ['poker', 'cash'])) continue;
        //         $weekData['data'][$dayNum]['game'] = 'nlh';
        //     }
        //     Weeks::update(['data' => json_encode($weekData['data'], JSON_UNESCAPED_UNICODE)], ['id' => $weekData['id']], Weeks::$table);
        // }
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
                    'is_bot' => false,
                    'first_name' => 'Dmytro',
                    'last_name' => 'Vankevych',
                    'username' => 'dsvan88',
                    'language_code' => 'uk',
                ],
                'chat' => [
                    'id' => -684025311,
                    'first_name' => 'Dmytro',
                    'last_name' => 'Vankevych',
                    'username' => 'dsvan88',
                    'type' => 'group',
                ],
                'date' => 1652025484,
                // 'text' => '- на сегодня',
                // 'text' => '/nick Думатель',
                // 'text' => '/day',
                // 'text' => '+tod',
                // 'text' => '/users',
                'text' => '/?',
            ]
        ];

        $url = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) . '://' . $_SERVER['HTTP_HOST'] . '/api/telegram/webhook';
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
    // public static function testAction(){
    //     error_log(json_encode($_POST));
    //     return View::redirect('/game/mafia/start/');
    // }
    public static function sendMailAction()
    {
        $mailer = new PHPMailer();

        $mailer->isSMTP();
        $mailer->CharSet = "UTF-8";
        $mailer->SMTPAuth   = true;
        // $mailer->SMTPDebug = 4;
        // $mailer->Debugoutput = function ($str, $level) {
        //     $GLOBALS['status'][] = $str;
        // };

        $mailer->Host       = 'smtp.gmail.com';
        $mailer->Username   = '';
        $mailer->Password   = '';
        $mailer->SMTPSecure = 'ssl';
        $mailer->Port       = 465;

        $mailer->Subject    = 'Test mail';
        $mailer->Body       = "That's my second try to send simple email";

        // if (isset($this->senderData['email']))
        //     $mailer->setFrom($this->senderData['email'], $this->senderData['name']);
        // else
        //     $mailer->setFrom($authData['email'], $authData['name']);
        // // $mailer->addAddress('yourMainEmail@gmail.com'); // If you need send message from your main tech email to your main email - you can change it here
        $mailer->isHTML(true);

        $mailer->addAddress('');
        $mailer->send();
    }
}
