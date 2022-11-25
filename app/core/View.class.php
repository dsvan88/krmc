<?php

namespace app\core;

use app\models\Games;
use app\models\Settings;

class View
{
    public static $path;
    public static $route;
    public static $layout = 'default';

    public function __construct($route)
    {
        self::$route = $route;
        self::$path = $route['controller'] . '/' . $route['action'];
    }
    public static function set($route)
    {
        self::$route = $route;
        self::$path = $route['controller'] . '/' . $route['action'];
    }
    public static function render($vars = [])
    {
        $styles = $scripts = '';
        $vars = Locale::apply($vars);
        extract($vars);
        extract(self::defaultVars());
        $content = '';
        $path = $_SERVER['DOCUMENT_ROOT'] . '/app/views/' . self::$path . '.php';
        if (file_exists($path)) {
            ob_start();
            require $path;
            $content = ob_get_clean();
            require $_SERVER['DOCUMENT_ROOT'] . '/app/views/layouts/' . self::$layout . '.php';
        } else {
            self::errorCode('404', ['message' => 'View ' . self::$path . ' isn’t found!']);
        }
    }
    public static function renderPage($vars = [])
    {
        $styles = $scripts = '';
        $vars = Locale::apply($vars);
        extract($vars);
        extract(self::defaultVars());
        $content = "
            <section class='section index'>
                <h2 class='index-title'>$title $dashboard</h2>
                $html
            </section>";
        require $_SERVER['DOCUMENT_ROOT'] . '/app/views/layouts/' . self::$layout . '.php';
    }
    public static function modal($vars = [])
    {
        $response = [
            'error' => 0,
            'modal' => true,
            'html' => ''
        ];
        $vars = Locale::apply($vars);
        extract($vars);
        // Extract vars from array self::$route['vars'] (Arrays of vars in URL)
        if (isset(self::$route['vars']))
            extract(self::$route['vars']);
        if (isset($scripts))
            $response['jsFile'] = $scripts;
        if (isset($css))
            $response['cssFile'] = $css;

        $path = $_SERVER['DOCUMENT_ROOT'] . '/app/views/' . self::$path . '.php';
        if (file_exists($path)) {
            ob_start();
            require $path;
            $response['html'] = ob_get_clean();
            exit(json_encode($response));
        } else {
            self::errorCode('404', ['message' => 'View ' . self::$path . ' isn’t found!']);
        }
    }
    public static function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }
    public static function location($url, $error = 0)
    {
        exit(json_encode(['error' => $error, 'location' => $url]));
    }
    public static function errorCode($code, $data = [])
    {
        extract($data);
        $path = "{$_SERVER['DOCUMENT_ROOT']}/app/views/errors/$code.php";
        http_response_code($code);
        if (file_exists($path))
            require $path;
        exit;
    }
    public static function message($data)
    {
        if (!is_array($data)) {
            $data = ['message' => $data];
        }
        if (isset($data['message'])) {
            $data['message'] = Locale::applySingle($data['message']);
        }
        if (!isset($data['error'])) {
            $daya['error'] = 0;
        }
        exit(json_encode($data));
    }
    public static function defaultVars()
    {
        $defaultVars = [
            'headerLogo' => "<a href='/'>" . ImageProcessing::inputImage('/public/images/club_logo.png', ['title' => 'Main logo']) . '</a>',
            'headerProfileButton' => '<a class="header__profile-button" data-action-click="account/login/form">Вхід</a>',
            'footerContent' => '',
            'headerMenu' => [
                [
                    'path' => 'news',
                    'label' => Locale::applySingle('{{ HEADER_MENU_NEWS }}')
                ], [
                    'path' => 'weeks',
                    'label' => Locale::applySingle('{{ HEADER_MENU_WEEKS }}')
                ],
                [
                    'path' => '',
                    'label' => Locale::applySingle('{{ HEADER_MENU_INFORMATION }}'),
                    'drop-down-menu' => Pages::getList(),
                    'type' => 'page'
                ],
                /*                 [
                    'path' => '',
                    'label' => Locale::applySingle('{{ Header_Menu_Games_Label }}'),
                    'drop-down-menu' => Games::menu(),
                    'type' => 'game',
                ], */
            ]
        ];

        if (isset($_SESSION['id'])) {
            if ($_SESSION['avatar'] == '') {
                $profileImage = $_SESSION['gender'] === '' ? Settings::getImage('profile')['value'] : Settings::getImage($_SESSION['gender'])['value'];
            } else {
                $profileImage = FILE_USRGALL . "{$_SESSION['id']}/{$_SESSION['avatar']}";
            }

            $profileImage = ImageProcessing::inputImage($profileImage, ['title' => $_SESSION['name']]);

            $texts = [
                'headerMenuProfileLink' => '{{ HEADER_ASIDE_MENU_PROFILE }}',
                'headerMenuAddNewsLink' => '{{ HEADER_ASIDE_MENU_ADD_NEWS }}',
                'headerMenuChangePromoLink' => '{{ HEADER_ASIDE_MENU_CHANGE_PROMO }}',
                'headerMenuAddPageLink' => '{{ HEADER_ASIDE_MENU_ADD_PAGE }}',
                'headerMenuUsersListLink' => '{{ HEADER_ASIDE_MENU_USERS_LISTS }}',
                'headerMenuUsersChatsLink' => '{{ HEADER_ASIDE_MENU_USERS_CHATS }}',
                'headerMenuChatSendLink' => '{{ HEADER_ASIDE_MENU_CHAT_SEND }}',
                'headerMenuSettingsListLink' => '{{ HEADER_ASIDE_MENU_SETTINGS_LIST }}',
                'headerMenuLogoutLink' => '{{ HEADER_ASIDE_MENU_LOGOUT }}',
            ];

            $texts = Locale::apply($texts);

            ob_start();
            require $_SERVER['DOCUMENT_ROOT'] . '/app/views/main/header-menu.php';
            $defaultVars['headerProfileButton'] = ob_get_clean();
        }

        return $defaultVars;
    }

    public static function file($file, $name = 'backup.txt')
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename='$name'");
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . mb_strlen($file, 'UTF-8'));
        echo $file;
        exit;
    }
}
