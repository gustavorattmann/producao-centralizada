<?php

    namespace App\Controllers;

    use Phalcon\Mvc\Controller;
    use Phalcon\Http\Response;
    use App\Models\User;

    class UserController extends Controller
    {
        public function index()
        {
            //$user = new User();

            //$user->setName('Gustavo');

            //echo $user->getName();

            //$this->redis->set('nome', 'Gustavo');

            echo $this->redis->get('nome');

            //$redis->get('nome');
        }
    }

?>