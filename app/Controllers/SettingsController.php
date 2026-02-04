<?php


namespace app\Controllers;

use app\models\Settings;
use app\core\Controller;
use app\core\TelegramBot;
use app\core\TelegramInfoBot;
use app\core\View;

class SettingsController extends Controller
{
    public static function before(): bool
    {
        View::$route['vars']['styles'][] = 'settings';
        return true;
    }
    public function indexAction()
    {
        extract(self::$route['vars']);

        if (empty($section)) {
            $section = 'email';
        }

        $settings = Settings::get($section);

        $vars = [
            'title' => '{{ Settings_List_Page_Title }}',
            'section' => $section,
            'settings' => $settings,
            'texts' => [
                'edit' => 'Edit',
            ],
            'scripts' => [
                'forms-admin-funcs.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public function editAction()
    {
        $type = trim($_POST['type']);
        $slug = trim($_POST['slug']);
        $value = str_replace('\\n', "\n", trim($_POST['value']));

        $setting = Settings::findBy('type', $type, 1)[0];
        $prev = '';
        foreach ($setting['setting'] as $index => $set) {
            if ($set['slug'] !== $slug) continue;

            if ($setting['setting'][$index]['value'] == $value)
                return View::notice(['message' => 'Success']);

            $prev = $setting['setting'][$index]['value'];
            $setting['setting'][$index]['value'] = $value;
            break;
        }

        if (APP_LOC !== 'local' && strpos($slug, 'bot_token') !== false) {
            $tgBot = $slug === 'bot_token' ? new TelegramBot($prev) : new TelegramInfoBot($prev);
            $tgBot->webhookDelete();
            $tgBot->webhookSet($value);
        }

        $result = Settings::edit($setting['id'], ['setting' => $setting['setting']]);

        return $result ?
            View::notice(['message' => 'Success', 'location' => '/settings/section/index/' . $type]) :
            View::notice(['type' => 'error', 'message' => 'Fail!']);
    }
    public function deleteAction()
    {
        extract(self::$route['vars']);
        if (isset($settingId)) {
            Settings::remove($settingId);
        }
        return View::redirect('/settings/list');
    }
}
