<?

namespace app\Controllers;

use app\core\Controller;
use app\core\Pages;
use app\core\View;
use app\libs\Db;
use app\models\Settings;
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
                'SubmitLabel' => '{{ Submit_Label }}',
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
                'SubmitLabel' => '{{ Submit_Label }}',
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

            if (!in_array($table, ['news', 'settings', 'tgchats', 'users', 'weeks']))
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
                'SubmitLabel' => '{{ Submit_Label }}',
            ],
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::render($vars);
    }
}
