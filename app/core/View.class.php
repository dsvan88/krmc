<?php

namespace app\core;

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
        $pageTitle = preg_replace('/<.*?>/', '', $title);
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
            <section class='section'>
                <header>
                    <h1 class='title'>$title $dashboard</h1>
                    <h2 class='subtitle'>{$texts['subtitle']}</h2>
                </header>
                <div class='content'>
                    {$texts['html']}
                </div>
            </section>";
        require $_SERVER['DOCUMENT_ROOT'] . '/app/views/layouts/' . self::$layout . '.php';
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
            exit(json_encode($response));
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
        if ($url[strlen($url)-1] !== '/')
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
    public static function location(string $url, int $error = 0): void
    {
        if ($url[strlen($url)-1] !== '/')
            $url .= '/';
        $message = ['location' => $url];
        if ($error > 0)
            $message['error'] = $error;
        exit(json_encode($message));
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
            $data['message'] = Locale::phrase($data['message']);
        }
        if (!isset($data['error'])) {
            $data['error'] = 0;
        }
        if ($data['error'] > 1) {
            http_response_code($data['error']);
        }
        exit(json_encode($data));
    }
    public static function response($data){
        if (is_array($data)){
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        exit($data);
    }
    public static function defaultVars()
    {
        $header = ViewHeader::get();
        $footer = ViewFooter::get();
        return array_merge($header, $footer);
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
        header('Content-Length: ' . strlen($file));
        echo $file;
        exit;
    }
}
