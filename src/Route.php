<?php

namespace PhazRouter;

/**
 * Route class
 */
class Route {
    static private $routes = [];
    /**
     * get handle for get method
     * Params
     * $uri string
     * $callback callable
     * $name string
     */
    static public function get(string $uri,callable | array $handler, string $name = "" ) {
        // TODO make some verification for validate uri
        array_push(self::$routes, array('uri' => $uri, 'handler' => $handler, 'method' => 'GET'));
    }

    static public function execute() {
        $result_search = array_search($_SERVER['REQUEST_URI'], array_column(self::$routes, 'uri'));
        if (is_callable(self::$routes[$result_search]['handler'])) {
            return self::$routes[$result_search]['handler']();
        } else if (gettype(self::$routes[$result_search]['handler']) === 'array') {
            
            $uri = self::$routes[$result_search]['uri'];
            $handler = explode('@', self::$routes[$result_search]['handler'][0]);
            $controller = $handler[0];
            $method  = $handler[1];

            $params_required = self::required_params($uri);
            $params = self::find_params($uri, $params_required);

            if (is_array($params)) {
                self::init_class($controller, $method, $params);
            } else {
                echo "Key $params is required";
            }
        }
    }

    static private function required_params(string $uri) : array{
        if (str_contains($uri, ":")) {
            // get uri query params
            preg_match_all('/(?<!\w):\w+/', $uri, $result, PREG_PATTERN_ORDER);
            $params = array();
            foreach($result[0] as $param) {
                array_push($params, ltrim($param, ':'));
            }
            return $params;
        }
    }

    static private function find_params($uri, $required_params): array {
        $uri_all_values = preg_split('/\//', $_SERVER['REQUEST_URI'], -1, PREG_PATTERN_ORDER);
        preg_match_all('/(?<=\/)[^:\/]+/', $uri, $matches);

        $all_params = array();
        $params_values = array_diff($uri_all_values, $matches[0]);

        $query_parameters = array_values($params_values);
        for($i = 0; $i < count($required_params); $i++) {
            array_push($all_params, array($required_params[$i] => $query_parameters[$i]));
        }

        return $all_params;
    }

    static private function init_class($controller, $method, $params) {
        $controller_folder = __DIR__ . "/controllers";
        $class_file = $controller_folder . '/' . $controller . '.php';

        if (file_exists($class_file)){
            require_once $class_file;

            $class_namespace = __NAMESPACE__ . '\\Controller\\' . $controller;
            $class = new $class_namespace();
            
            $reflection = new \ReflectionMethod($class, $method);
            $reflector = new \ReflectionClass($class);

            $parameters = $reflector->getMethod($method)->getParameters();
            if ($reflection->getNumberOfRequiredParameters() > 0) {
                if (count($params) > 1) {
                    $args = array();
                    $url_params = array();
                    foreach($params as $param){
                        array_push($url_params, array_values($param)[0]);
                    }

                    foreach($parameters as $key => $parameter){
                        $args[$parameter->name] = $url_params[$key];
                    }
                    // print_r($args);
                    return $class->$method(...$args);
                }
                return $class->$method($params);
            }

            return $class->$method();
        } else {
            trigger_error("Controller class don't exist", E_USER_ERROR);
        }
    }

}
