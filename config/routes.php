<?php

    use Phalcon\Mvc\Micro\Collection as MicroCollection;
    use App\Controllers\RolesController;
    use App\Controllers\UsersController;
    use App\Controllers\ProductsController;
    use App\Controllers\CategoryController;
    use App\Controllers\RawMaterialsController;
    use App\Controllers\StatusOrdersController;
    use App\Controllers\OrdersController;
    use App\Controllers\ProductionController;

    $roles = new MicroCollection();
    $roles->setHandler(new RolesController())
          ->setPrefix('/api/roles')
          ->get('/', 'index')
          ->post('/register', 'register')
          ->put('/update/{id}', 'update')
          ->delete('/delete/{id}', 'delete');

    $app->mount($roles);

    $users = new MicroCollection();
    $users->setHandler(new UsersController())
          ->setPrefix('/api/users')
          ->get('/', 'index')
          ->get('/profile', 'profile')
          ->get('/profile/{id}', 'profile')
          ->post('/search', 'search')
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
    $products->setHandler(new ProductsController())
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
    $rawMaterials->setHandler(new RawMaterialsController())
                 ->setPrefix('/api/raw-materials')
                 ->get('/', 'index')
                 ->post('/register', 'register')
                 ->put('/update/{id}', 'update')
                 ->delete('/delete/{id}', 'delete');

    $app->mount($rawMaterials);

    $status_orders = new MicroCollection();
    $status_orders->setHandler(new StatusOrdersController())
           ->setPrefix('/api/status-orders')
           ->get('/', 'index')
           ->post('/register', 'register')
           ->put('/update/{id}', 'update')
           ->delete('/delete/{id}', 'delete');

    $app->mount($status_orders);

    $orders = new MicroCollection();
    $orders->setHandler(new OrdersController())
           ->setPrefix('/api/orders')
           ->get('/', 'index')
           ->get('/view/{id}', 'view')
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
               ->put('/update/{id}', 'update')
               ->delete('/delete/{id}', 'delete')
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