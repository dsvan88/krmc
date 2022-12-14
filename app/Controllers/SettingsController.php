<?php


namespace app\Controllers;

use app\core\Controller;
use app\core\View;
use app\models\Settings;
use app\core\Locale;
use app\core\TelegramBot;

class SettingsController extends Controller
{
    public function listAction()
    {
        $vars = [
            'title' => '{{ Settings_List_Page_Title }}',
            'settingsData' => Settings::getList(['tg-bot', 'point', 'tg-tech', 'tg-main']),
        ];
        View::render($vars);
    }
    public function addAction()
    {
        if (!empty($_POST)) {
            $array = $_POST;
            $array['short_name'] = Locale::translitization($array['name']);
            $array['by_default'] = $array['value'];
            Settings::save($array);
            View::message(['error' => 0, 'message' => 'Changes saved successfully!', 'location' => '/settings/list']);
        }
        $vars = [
            'title' => '{{ Settings_Add_Title }}',
            'texts' => [
                'BlockTitle' => '{{ Settings_Add_Title }}',
                'SubmitLabel' => 'Save',
            ]
        ];
        View::render($vars);
    }
    public function editAction()
    {
        extract(self::$route['vars']);
        if (!empty($_POST)) {
            $array = $_POST;
            if ($array['type'] === 'tg-bot' && $array['value'] !== '' && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
                $bot = new TelegramBot();
                $bot->webhookDelete();
                $bot->webhookSet($array['value']);
            }
            $array['short_name'] = Locale::translitization($array['name']);
            Settings::save($array);
        }

        $settingsData = Settings::getById($settingId);
        $vars = [
            'title' => '{{ Settings_Edit_Title }}',
            'texts' => [
                'SubmitLabel' => 'Save',
            ],
            'settingsData' => $settingsData,
        ];
        View::render($vars);
    }
    public function deleteAction()
    {
        extract(self::$route['vars']);
        if (isset($settingId)) {
            Settings::remove($settingId);
        }
        View::redirect('/settings/list');
    }
}
