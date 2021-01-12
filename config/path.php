<?php

    DEFINE('DS', DIRECTORY_SEPARATOR);

    DEFINE('ROOT_PATH', realpath(dirname(__DIR__)) . DS);
    DEFINE('APP_PATH', ROOT_PATH . 'app' . DS);
    DEFINE('CONTROLLERS_PATH', APP_PATH . 'controllers' . DS);
    DEFINE('MODELS_PATH', APP_PATH . 'models' . DS);
    DEFINE('VIEWS_PATH', APP_PATH . 'views' . DS);
    DEFINE('CONFIG_PATH', ROOT_PATH . 'config' . DS);
    DEFINE('PUBLIC_PATH', ROOT_PATH . 'public' . DS);
    DEFINE('VENDOR_PATH', ROOT_PATH . 'vendor' . DS);

?>