<?php

    use Phalcon\Mvc\Micro\Collection as MicroCollection;
    use App\Controllers\UserController;
    use App\Controllers\ProductController;

    $users = new MicroCollection();
    $users->setHandler(new UserController())
          ->setPrefix('/api/users')
          ->get('/', 'index')
          ->post('/register', 'register')
          ->put('/update', 'update')
          ->put('/update/{id}', 'update')
          ->put('/change-password', 'changePassword')
          ->put('/change-password/{id}', 'changePassword')
          ->delete('/delete', 'delete')
          ->delete('/delete/{id}', 'delete')
          ->post('/login', 'login')
          ->get('/logout', 'logout');

    $app->mount($users);

    $products = new MicroCollection();
    $products->setHandler(new ProductController())
             ->setPrefix('/api/products')
             ->get('/', 'index')
             ->post('/register', 'register')
             ->put('/update/{id}', 'update')
             ->delete('/delete/{id}', 'delete');

    $app->mount($products);

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