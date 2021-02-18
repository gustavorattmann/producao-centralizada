<?php

    use Phalcon\Loader;
    use Phalcon\Mvc\Micro;
    use Phalcon\Mvc\View\Simple;
    use Phalcon\Http\Request;

    $path_dir = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'path.php');

    require_once $path_dir;

    require_once VENDOR_PATH . 'autoload.php';

    $app = new Micro();

    require_once CONFIG_PATH . 'autoload.php';
    require_once CONFIG_PATH . 'env.php';
    require_once CONFIG_PATH . 'config.php';
    require_once CONFIG_PATH . 'di.php';
    require_once CONFIG_PATH . 'routes.php';

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
    
    $app->handle(
        $_SERVER["REQUEST_URI"]
    );

?>