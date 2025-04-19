<?php
// app/Core/Router.php
namespace App\Core;

/**
 * Router.php
 *
 * Main router class for handling URL requests in an MVC structure.
 */
class Router {
    private $controller = 'HomeController';
    private $method = 'index';
    private $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        $namespacePrefix = 'App\\Controllers\\';
        $controllerName = $this->controller;

        if (isset($url[0]) && strtolower($url[0]) === 'admin') {
            $namespacePrefix .= 'Admin\\';
            array_shift($url);
        }

        if (isset($url[0]) && !empty($url[0])) {
            $controllerName = ucfirst($url[0]) . 'Controller';
            array_shift($url);
        }

        $controllerClass = $namespacePrefix . $controllerName;

        if (!class_exists($controllerClass)) {
            require_once APP_ROOT . '/core/helpers.php';
            setFlashMessage('danger', 'La ressource demandée n\'a pas été trouvée.');
            redirect('');
            exit;
        }
        
        $this->controller = new $controllerClass();

        if (isset($url[0]) && method_exists($this->controller, $url[0])) {
            $this->method = $url[0];
            array_shift($url);
        } elseif (isset($url[0])) {
            require_once APP_ROOT . '/core/helpers.php';
            setFlashMessage('danger', 'La ressource demandée n\'a pas été trouvée.');
            redirect('');
            exit;
        }        

        $this->params = $url ?? [];

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl(): array {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}