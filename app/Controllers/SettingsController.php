<?php


namespace app\Controllers;

use app\models\Settings;
use app\core\Controller;
use app\core\View;
use app\core\Locale;
use app\core\Tech;

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

        $setting = Settings::findBy('type', $type, 1)[0];

        foreach ($setting['setting'] as $index => $set) {
            if ($set['slug'] !== $slug) continue;
            $setting['setting'][$index]['value'] = str_replace('\\n', "\n", trim($_POST['value']));
            break;
        }

        $result = Settings::edit($setting['id'], ['setting' => $setting['setting']]);

        return $result ?
            View::notice(['message' => 'Success!', 'location' => '/settings/section/index/' . $type]) :
            View::notice(['type' => 'error', 'message' => 'Fail!']);
    }
    // public function editFormAction()
    // {
    //     $type = trim($_POST['type']);
    //     $slug = trim($_POST['slug']);

    //     $settings = Settings::get($type);

    //     $setting = [];
    //     foreach ($settings as $_slug => $set) {
    //         if ($_slug !== $slug) continue;
    //         $setting = $set;
    //         break;
    //     }

    //     if (empty($setting))
    //         return View::notice(['type' => 'error', 'message' => 'Fail!']);

    //     $vars = [
    //         'title' => '{{ Settings_Edit_Title }}',
    //         'texts' => [
    //             'SubmitLabel' => 'Save',
    //         ],
    //         'type' => $type,
    //         'slug' => $slug,
    //         'setting' => $setting,
    //     ];
    //     View::$route['vars'] = array_merge(View::$route['vars'], $vars);
    //     return View::modal();
    // }
    public function deleteAction()
    {
        extract(self::$route['vars']);
        if (isset($settingId)) {
            Settings::remove($settingId);
        }
        return View::redirect('/settings/list');
    }
}
