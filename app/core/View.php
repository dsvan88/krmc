<?php

namespace app\core;

use app\models\Settings;
use app\Repositories\ViewRepository;

class View
{
    public static $path;
    public static $route;
    public static $scriptsPath = '';
    public static $layout = 'default';
    public static $viewsFolder = '/app/views';
    public static $defaultScripts = [
        'request.js',
        'action-handler.js',
        'popups.js',
        'noticer.js',
        'common-funcs.js',
        'modals.js',
        'common.js',
    ];
    public static $refresh = false;

    public function __construct($route)
    {
        self::$route = $route;
        self::$path = $route['controller'] . '/' . $route['action'];
    }
    public static function set($route)
    {
        if (!empty($_SESSION['TelegramApp'])) self::$layout = 'telegram';

        self::$route = $route;
        if (strpos($route['action'], 'Form') === false) {
            self::$path = $route['controller'] . '/' . $route['action'];
            return true;
        }
        $decamelized = Locale::decamelize($route['action']);
        $viewPath = str_replace(' ', '-', mb_substr($decamelized, 0, mb_strrpos($decamelized, ' ', 0, 'UTF-8'), 'UTF-8'));
        $viewPath = Locale::camelize($viewPath);
        self::$path = "{$route['controller']}/forms/$viewPath";
    }
    public static function render()
    {
        self::$route['vars'] = Locale::apply(self::$route['vars']);
        extract(self::$route['vars']);

        extract(ViewRepository::defaultVars());

        if (empty($styles)) $styles = [];
        if (empty($scripts)) $scripts = [];
        if (!empty($css)) $styles = array_merge($styles, $css);
        if (empty($mainClass)) $mainClass = 'index';

        $scripts = ViewRepository::compressScripts($scripts);

        $pageTitle = preg_replace('/<.*?>/', '', $title);


        $filename = self::$path;
        $path = $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder . "/$filename.php";
        if (file_exists($path)) {
            ob_start();
            require $path;
            $content = ob_get_clean();
        }

        if (empty($content)) {
            self::errorCode('404', ['message' => "View $filename isn’t found!"]);
        }

        $notices = Noticer::get();
        Noticer::clear();

        $lang = Locale::$langCode;
        $locales = Locale::getLocaledLinks();
        $backdroundImages = array_slice(Settings::get('img')['background']['value'], 0, 8, true);

        require $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder . '/layouts/' . self::$layout . '.php';

        return true;
    }
    public static function modal()
    {
        self::$route['vars'] = Locale::apply(self::$route['vars']);
        extract(self::$route['vars']);

        $response = [
            'modal' => true,
            'html' => '',
            'title' => $title,
        ];

        if (isset($scripts))
            $response['jsFile'] = $scripts;
        if (isset($css))
            $response['cssFile'] = $css;

        $path = $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder . '/' . self::$path . '.php';
        if (file_exists($path)) {
            ob_start();
            require $path;
            $response['html'] = ob_get_clean();

            echo json_encode($response);
            return true;
        }
        self::errorCode('404', ['message' => 'View ' . self::$path . ' isn’t found!']);
    }
    /**
     * Use for hard redirect from server
     * 
     * @param string $url - where to redirect;
     * 
     * @return void
     */
    public static function redirect(string $url = '/'): void
    {
        if (headers_sent()) {
            var_dump(headers_list());
        }
        if ($url[strlen($url) - 1] !== '/')
            $url .= '/';
        header('Location: ' . $url);
        exit;
    }
    /**
     * Use for soft redirect for js handler
     * 
     * @param string $url - where to redirect;
     * 
     * @return json string with
     */
    public static function location(string $url = '/', int $error = 0): void
    {
        if ($url[strlen($url) - 1] !== '/')
            $url .= '/';
        $message = ['location' => $url];
        if ($error > 0)
            $message['error'] = $error;

        echo json_encode($message);
    }
    public static function errorCode($code, $data = [])
    {
        extract($data);
        $path = $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder . "/errors/$code.php";
        http_response_code($code);
        if (file_exists($path))
            require $path;
    }
    public static function html()
    {
        extract(self::$route['vars']);
        $path = $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder . "/$path.php";
        if (file_exists($path)) {
            ob_start();
            require $path;
            $content = ob_get_clean();
        }
        self::message(['html' => $content]);
    }
    public static function message($data = '')
    {
        if (!is_array($data)) {
            $data = ['message' => $data];
        }
        if (isset($data['message'])) {
            $data['message'] = Locale::phrase($data['message']);
        }
        if (!empty($data['error']) && $data['error'] > 1) {
            http_response_code($data['error']);
        }
        echo json_encode($data);
    }
    public static function notice($data = '')
    {
        if (!is_array($data)) {
            $data = [
                'type' => '',
                'message' => $data,
            ];
        }

        $data['message'] = empty($data['message']) ? '' : Locale::phrase($data['message']);

        if (!empty($data['error'])) {
            $data['type'] = 'error';
        }
        echo json_encode(['notice' => $data]);
    }
    public static function response($data)
    {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return $data;
    }
    public static function file($file, $name = 'backup.txt')
    {
        if (empty($file) || !file_exists($file)) {
            exit('File is not found');
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename='$name'");
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit();
    }
    public static function component(string $filename, array $vars = [])
    {
        extract($vars);

        $texts = empty(self::$route['vars']['texts']) ? [] : self::$route['vars']['texts'];

        return require $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder . "/components/$filename.php";
    }
    public static function icon(string $filename, string $filetype = 'svg')
    {
        return require $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder . "/components/icons/$filename.$filetype";
    }
}
