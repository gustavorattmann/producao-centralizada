<?php

    use Phalcon\Mvc\Micro\Collection as MicroCollection;
    use App\Controllers\UserController;

    $users = new MicroCollection();
    $users->setHandler(new UserController())
          ->setPrefix('/api/users')
          ->get('/', 'index')
        //->get('/', 'index')
          ->post('/register', 'register')
          ->post('/login', 'login')
          ->get('/logout', 'logout');

    $app->mount($users);

    $app->notFound(
        function () use ($app) {
            $app
                ->response
                ->setStatusCode(404)
                ->sendHeaders()
                ->setContent('Página não encontrada...')
                ->send();
        }
    );

?>