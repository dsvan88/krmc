<?php

namespace app\core;

use app\Repositories\TechRepository;

class View
{
    public static $path;
    public static $route;
    public static $layout = 'default';
    public static $viewsFolder = '/app/views';

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
    public static function render()
    {
        $styles = $scripts = '';

        self::$route['vars'] = Locale::apply(self::$route['vars']);
        extract(self::$route['vars']);
        
        extract(self::defaultVars());
        
        if (empty($mainClass)) $mainClass = 'index';

        $pageTitle = preg_replace('/<.*?>/', '', $title);

        $filename = self::$path;
        $path = $_SERVER['DOCUMENT_ROOT']. self::$viewsFolder ."/$filename.php";
        if (file_exists($path)){
            ob_start();
            require $path;
            $content = ob_get_clean();
        }

        if (empty($content)) {
            self::errorCode('404', ['message' => "View $filename isn’t found!"]);
        }

        $notices = Noticer::get();
        Noticer::clear();

        $locales = Locale::getLocaledLinks();

        require $_SERVER['DOCUMENT_ROOT']. self::$viewsFolder . '/layouts/' . self::$layout . '.php';

        self::exit();
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
        $path = $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder."/errors/$code.php";
        http_response_code($code);
        if (file_exists($path))
            require $path;
        self::exit();
    }
    public static function html(){
        
        extract(self::$route['vars']);
        // extract($vars);

        $path = $_SERVER['DOCUMENT_ROOT']. self::$viewsFolder ."/$path.php";
        if (file_exists($path)){
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
        
        $data['message'] = empty($data['message']) ? '' : Locale::phrase($data['message']);

        if (!empty($data['error'])) {
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
        TechRepository::scheduleBackup();
        exit();
    }
    public static function component(string $filename, array $vars = []){
        extract($vars);
        $texts = [];
        if (!empty(self::$route['vars']['texts'])) $texts = self::$route['vars']['texts'];
        return require $_SERVER['DOCUMENT_ROOT'] . self::$viewsFolder."/components/$filename.php";
    }
}
