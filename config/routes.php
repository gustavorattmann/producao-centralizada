<?php

    use Phalcon\Mvc\Micro\Collection as MicroCollection;
    use App\Controllers\UserController;

    $users = new MicroCollection();
    $users->setHandler(new UserController());
    $users->setPrefix('/api/users');
    $users->get('/', 'index');

    $app->mount($users);

?>