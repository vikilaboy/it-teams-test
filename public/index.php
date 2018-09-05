<?php

use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/../vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function ($class_name) {
    $class_name = mb_convert_case(mb_strtolower($class_name), MB_CASE_TITLE, 'UTF-8');

    if (!is_readable(__DIR__ . '/../app/classes/' . $class_name . '.php')) {
        die(sprintf('%s class does not exist', $class_name));
    }
    include __DIR__ . '/../app/classes/' . $class_name . '.php';
});

$arguments = [];
foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['task'] = $arg;
    } elseif ($k == 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        if (false !== strpos($arg, '--')) {
            $params = explode(':', $arg);
            $param = current($params);
            $key = ltrim($param, '--');
            $value = substr($arg, strlen($param) + 1);
            $arguments['params'][$key] = $value;
        } else {
            $arguments['params'][] = $arg;
        }
    }
}

(new Cli)->handle($arguments);

