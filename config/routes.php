<?php

    use Phalcon\Mvc\Micro\Collection as MicroCollection;
    use App\Controllers\UserController;
    use App\Controllers\ProductController;
    use App\Controllers\CategoryController;
    use App\Controllers\RawMaterialController;
    use App\Controllers\OrdersController;
    use App\Controllers\ProductionController;

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

    $category = new MicroCollection();
    $category->setHandler(new CategoryController())
             ->setPrefix('/api/category')
             ->get('/', 'index')
             ->post('/register', 'register')
             ->put('/update/{id}', 'update')
             ->delete('/delete/{id}', 'delete');

    $app->mount($category);

    $rawMaterials = new MicroCollection();
    $rawMaterials->setHandler(new RawMaterialController())
                 ->setPrefix('/api/raw-materials')
                 ->get('/', 'index')
                 ->post('/register', 'register')
                 ->put('/update/{id}', 'update')
                 ->delete('/delete/{id}', 'delete');

    $app->mount($rawMaterials);

    $orders = new MicroCollection();
    $orders->setHandler(new OrdersController())
           ->setPrefix('/api/orders')
           ->get('/', 'index')
           ->post('/register', 'register')
           ->put('/update/{id}', 'update')
           ->delete('/delete/{id}', 'delete');

    $app->mount($orders);

    $production = new MicroCollection();
    $production->setHandler(new ProductionController())
               ->setPrefix('/api/production')
               ->get('/', 'index')
               ->get('/product/{id}', 'product')
               ->post('/produced', 'produced')
               ->post('/report', 'report');

    $app->mount($production);

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