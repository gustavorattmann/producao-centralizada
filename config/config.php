<?php

    use Phalcon\Config;

    $config = new Config(
        [
            'database' => [
                'adapter'  => 'Mysql',
                'host'     => $_ENV['DB_HOST'],
                'port'     => $_ENV['DB_PORT'],
                'username' => $_ENV['DB_USERNAME'],
                'password' => $_ENV['DB_PASSWORD'],
                'dbname'   => $_ENV['DB_DATABASE'],
                'options'  => [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                ]
            ],
            'redis' => [
                'host' => $_ENV['REDIS_HOST'],
                'port' => $_ENV['REDIS_PORT'],
                'auth' => $_ENV['REDIS_PASSWORD']
            ],
            'application' => [
                'controllersDir' => CONTROLLERS_PATH,
                'modelsDir'      => MODELS_PATH,
                'baseUri'        => '/'
            ]
        ]
    );

    return $config;

?>