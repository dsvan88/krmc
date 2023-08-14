<?php


namespace app\Controllers;

use app\models\Settings;
use app\core\Controller;
use app\core\View;
use app\core\Locale;

class SettingsController extends Controller
{
    public function indexAction()
    {
        extract(self::$route['vars']);

        if (empty($section)){
            $section = 'email';
        }
        
        $settings = Settings::getGroup($section);
        
        $vars = [
            'title' => '{{ Settings_List_Page_Title }}',
            'section' => $section,
            'settings' => $settings,
        ];
        View::render($vars);
    }
    public function addAction()
    {
        if (!empty($_POST)) {
            $array = $_POST;
            $array['slug'] = Locale::translitization($array['name']);
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
    public function editAction(){

        extract(self::$route['vars']);
        
        $setting = Settings::find($settingId);

        Settings::edit($settingId, ['value' => trim($_POST['value'])]);

        View::message(['message'=>'Success!', 'location' => '/settings/section/index/'.$setting['type']]);
    }
    public function editFormAction()
    {
        $settingId = (int) trim($_POST['settingId']);
        $setting = Settings::find($settingId);

        $vars = [
            'title' => '{{ Settings_Edit_Title }}',
            'texts' => [
                'SubmitLabel' => 'Save',
            ],
            'setting' => $setting,
        ];
        View::modal($vars);
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
