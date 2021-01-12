<?php

    use Phalcon\Di\FactoryDefault;
    use Phalcon\Http\Response;
    use Phalcon\Db\Adapter\Pdo\Mysql;

    $di = new FactoryDefault();

    $di->setShared(
        'response',
        function () {
            $response = new Response();
            $response->setContentType('application/json', 'utf-8');
      
            return $response;
        }
    );

    $di->setShared('config', $config);

    $di->set(
        'db',
        function () use ($config) {
            $db = new Mysql(
                [
                    'host'     => $config->database->host,
                    'username' => $config->database->username,
                    'password' => $config->database->password,
                    'dbname'   => $config->database->dbname,
                ]
            );

            return $db;
        }
    );

    $di->setShared(
        'redis',
        function () use ($config) {
            $redis = new \Redis;
            $redis->connect($config->redis->host, $config->redis->port);
            if($config->redis->auth) {
                $redis->auth($config->redis->auth);
            }

            return $redis;
        }
    );

    $di->set(
        'router',
        function () {
            require_once CONFIG_PATH . 'routes.php';
    
            return $router;
        }
    );

    return $di;

?>