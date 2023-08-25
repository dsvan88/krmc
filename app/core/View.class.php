<?php

namespace app\core;

use app\models\Settings;
use app\Repositories\TechRepository;

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
        if (strpos($route['action'], 'Form') === false) {
            self::$path = $route['controller'] . '/' . $route['action'];
            return true;
        }
        $decamelized = Locale::decamelize($route['action']);
        $viewPath = str_replace(' ', '-', mb_substr($decamelized, 0, mb_strrpos($decamelized, ' ', 0, 'UTF-8'), 'UTF-8'));
        $viewPath = Locale::camelize($viewPath);
        self::$path = "{$route['controller']}/forms/$viewPath";
    }
    public static function render($vars = [])
    {
        $styles = $scripts = '';
        $vars = Locale::apply($vars);
        extract($vars);
        extract(self::defaultVars());
        $content = '';
        $pageTitle = preg_replace('/<.*?>/', '', $title);
        $path = $_SERVER['DOCUMENT_ROOT'] . '/app/views/' . self::$path . '.php';

        $notices = Noticer::get();

        if (file_exists($path)) {
            ob_start();
            require $path;
            $content = ob_get_clean();
            require $_SERVER['DOCUMENT_ROOT'] . '/app/views/layouts/' . self::$layout . '.php';

            Noticer::clear();
        } else {
            self::errorCode('404', ['message' => 'View ' . self::$path . ' isn’t found!']);
        }
        self::exit();
    }
    public static function renderPage($vars = [])
    {
        $styles = $scripts = '';
        $vars = Locale::apply($vars);
        extract($vars);
        extract(self::defaultVars());

        $notices = Noticer::get();

        $content = "
            <section class='section page'>
                <header>
                    <h1 class='title'>$title $dashboard</h1>
                    <h2 class='subtitle'>{$texts['subtitle']}</h2>
                </header>
                <div class='content'>
                    {$texts['html']}
                </div>
            </section>";
        require $_SERVER['DOCUMENT_ROOT'] . '/app/views/layouts/' . self::$layout . '.php';

        Noticer::clear();
    }
    public static function modal($vars = [])
    {
        $vars = Locale::apply($vars);
        extract($vars);

        $response = [
            'error' => 0,
            'modal' => true,
            'html' => '',
            'title' => $title,
        ];

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

            self::exit(json_encode($response));
        } else {
            self::errorCode('404', ['message' => 'View ' . self::$path . ' isn’t found!']);
        }
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
        if ($url[strlen($url) - 1] !== '/')
            $url .= '/';
        header('Location: ' . $url);
        self::exit();
    }
    /**
     * Use for soft redirect for js handler
     * 
     * @param string $url - where to redirect;
     * 
     * @return json string with
     */
    public static function location(string $url, int $error = 0): void
    {
        if ($url[strlen($url) - 1] !== '/')
            $url .= '/';
        $message = ['location' => $url];
        if ($error > 0)
            $message['error'] = $error;
        self::exit(json_encode($message));
    }
    public static function errorCode($code, $data = [])
    {
        extract($data);
        $path = "{$_SERVER['DOCUMENT_ROOT']}/app/views/errors/$code.php";
        http_response_code($code);
        if (file_exists($path))
            require $path;
        self::exit();
    }
    public static function message($data = '')
    {
        if (!is_array($data)) {
            $data = ['message' => $data];
        }
        if (isset($data['message'])) {
            $data['message'] = Locale::phrase($data['message']);
        }
        if (empty($data['error'])) {
            $data['error'] = 0;
        }
        if ($data['error'] > 1) {
            http_response_code($data['error']);
        }
        self::exit(json_encode($data));
    }
    public static function notice($data = '')
    {
        if (!is_array($data)) {
            $data = [
                'type' => '',
                'message' => $data
            ];
        }
        if (isset($data['message'])) {
            $data['message'] = Locale::phrase($data['message']);
        } else {
            $data['message'] = '';
        }

        if (empty($data['error'])) {
            $data['error'] = 0;
        } else {
            $data['type'] = 'error';
        }
        self::exit(json_encode(['notice' => $data]));
    }
    public static function response($data)
    {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        self::exit($data);
    }
    public static function defaultVars()
    {
        $header = ViewHeader::get();
        $footer = ViewFooter::get();
        return array_merge($header, $footer);
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
    public static function exit(string $string = null): void
    {
        if (!empty($string)) {
            echo $string;
        }

        $settings = Settings::getGroup('backup');

        if (empty($settings['email']['value']) || $settings['last']['value'] > $_SERVER['REQUEST_TIME'] - BACKUP_FREQ) exit();

        if (TechRepository::sendBackup($settings['email']['value'])) {
            Settings::edit($settings['last']['id'], ['value' => $_SERVER['REQUEST_TIME']]);
        }
        exit();
    }
}
