<?php

namespace  app\core;

use app\libs\Db;
use app\models\Settings;

class Pages
{
    public static function save($name, $data)
    {
        $html = "
        <h3 class='index-subtitle'>{$data['subtitle']}</h3>
        <div class='index-text'>{$data['html']}</div>";
        if (PAGES_AS_LOCAL_FILE) {
            $fullpath = "/app/views/pages/$name.php";
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . $fullpath, $html);
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $fullpath)) {
                $settings = [
                    'type' => 'pages',
                    'short_name' => $name,
                    'name' => $data['title'],
                    'value' => $fullpath
                ];
                Settings::save($settings);
            }
            return true;
        } else {
            $settings = [
                'type' => 'pages',
                'short_name' => $name,
                'name' => $data['title'],
                'value' => $html
            ];
            Settings::save($settings);
        }
        return false;
    }
    public static function remove($id)
    {
        $pageData = Settings::getPageById($id);
        if ($pageData && $pageData['short_name'] !== 'index') {
            if (PAGES_AS_LOCAL_FILE) {
                $filepath = $_SERVER['DOCUMENT_ROOT'] . "/app/views/pages/{$pageData['short_name']}.php";
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
            Settings::remove($id);
        }
    }
    public static function getList()
    {
        $list = Settings::load('pages');
        for ($i = 0; $i < count($list); $i++) {
            if (PAGES_AS_LOCAL_FILE) {
                if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $list[$i]['value'])) {
                    unset($list[$i]);
                }
            }
        }
        return array_values($list);
    }
}
