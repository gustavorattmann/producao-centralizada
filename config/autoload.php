<?php

    use Phalcon\Loader;

    $loader = new Loader();

    $loader->registerNamespaces(
        [
            'App\Controllers' => CONTROLLERS_PATH,
            'App\Models'      => MODELS_PATH
        ]
    );
    
    $loader->register();

?>