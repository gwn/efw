<?php

namespace EFW;
use \Exception;

class Tpl {
    public static $conf;
    private static $engine;


    public static function init(&$conf) {
        self::$conf = $conf;
        $callback = 'init' . ucfirst($conf['engine']);
        self::$callback();
    }

    public static function __callStatic($method, $args) {
        return call_user_func_array(array(self::$engine, $method), $args);
    }

    private static function initNative() { self::$engine = new NativeTpl(); }

    private static function initMustache() {
        $partials_path = isset(self::$conf['options']['partials_path'])
          ? '/' . self::$conf['options']['partials_path'] : '';

        self::$engine = new \Mustache_Engine(array(
            'loader' => new \Mustache_Loader_FilesystemLoader(__DIR__ .
              '/../../../app/view'),
            'partials_loader' => new \Mustache_Loader_FilesystemLoader(__DIR__ .
              '/../../../app/view' . $partials_path),
            'helpers' => array(
                '_url' => function($qs, \Mustache_LambdaHelper $h) {
                    return _url($h->render($qs)); 
                }
            )
        ));
    }
}

class NativeTpl {
    public function render($tpl, $params) {
        $filename = __DIR__ . '/../../../app/view/' . $tpl . '.php';
        if (!file_exists($filename)) {
            throw new Exception("Template '{$tpl}' does not exist.");
        }

        if (!empty(Tpl::$conf['options']['auto_escape'])) {
            $callback = function ($val) {
                if (is_object($val)) { return $val; }
                return htmlentities($val, ENT_QUOTES, EFW::$conf['charset']);
            };
            $params = self::array_map_recursive($callback, $params);
        }

        extract($params);
        include $filename;
    }
    
    private static function array_map_recursive($fn, $arr) {
        $rarr = array();
        foreach ($arr as $k => $v) {
            $rarr[$k] = is_array($v)
                ? call_user_func(__METHOD__, $fn, $v)
                : $fn($v);
        }
        return $rarr;
    }
}

?>
