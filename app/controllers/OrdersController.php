<?php

    namespace App\Controllers;

    use Phalcon\Mvc\Controller;
    use Phalcon\Http\Request;
    use Phalcon\Http\Response;
    use Firebase\JWT\JWT;
    use App\Models\Orders;

    class OrdersController extends Controller
    {
        public function index()
        {
            $request = new Request();

            $response = new Response();
            
            session_start();

            if ( !empty($_SESSION['user']) && $this->redis->exists($_SESSION['user']) ) {
                $bearerToken = $request->getHeaders()['Authorization'];

                $bearerToken = str_replace('Bearer ', '', $bearerToken);

                if ( !empty($bearerToken) && $bearerToken == $this->redis->get($_SESSION['user']) ) {
                    $key = base64_encode($_ENV['SECRET_KEY'] . $_SESSION['user']);

                    JWT::$leeway = 60;
                    $token = JWT::decode($bearerToken, $key, array('HS512'));

                    $token_array = (array) $token;
                    $nbf_array = (array) $token_array['nbf'];

                    if ( date(\DateTime::ISO8601) <= $nbf_array ) {
                        if ( intval($token_array['situation']) == 1 ) {
                            if ( intval($token_array['level']) == 0 || intval($token_array['level']) == 1 ) {
                                $sql = '
                                    SELECT
                                        o.id AS id,
                                        u.name AS solicitor,
                                        p.name AS product,
                                        c.name AS category,
                                        r.name AS raw_material,
                                        r.stock AS stock,
                                        o.quantity_product_requested AS quantity_product_requested,
                                        o.quantity_raw_material_limit AS quantity_raw_material_limit,
                                        o.date AS date
                                    FROM
                                        orders o
                                    INNER JOIN
                                        users u
                                    ON
                                        o.user = u.id
                                    INNER JOIN
                                        products p
                                    ON
                                        o.product = p.id
                                    INNER JOIN
                                        raw_materials r
                                    ON
                                        o.raw_material = r.id
                                    INNER JOIN
                                        category c
                                    ON
                                        p.category = c.id
                                    ORDER BY o.date DESC;
                                ';

                                $orders = $this->db->fetchAll($sql);

                                if ( !empty($orders) ) {
                                    foreach ($orders as $key => $order) {
                                        $contents[$key] = [
                                            'order' => [
                                                'id'                          => $order['id'],
                                                'solicitor'                   => $order['solicitor'],
                                                'product'                     => $order['product'],
                                                'category'                    => $order['category'],
                                                'raw_material'                => $order['raw_material'],
                                                'stock'                       => $order['stock'],
                                                'quantity_product_requested'  => $order['quantity_product_requested'],
                                                'quantity_raw_material_limit' => $order['quantity_raw_material_limit'],
                                                'date'                        => $order['date']
                                            ]
                                        ];
                                    }

                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                        ->send();
                                } else {
                                    $contents = [
                                        'msg' => 'Nenhum pedido encontrado!'
                                    ];
                    
                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                        ->send();
                                }
                            } else {
                                $contents = [
                                    'msg' => 'Você não possui autorização para acessar essa página!'
                                ];
                
                                $response
                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                    ->send();
                            }
                        } else {
                            $contents = [
                                'msg' => 'Seu usuário não está ativo, contate um administrador ou RH!'
                            ];
            
                            $response
                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                                ->send();
                        }
                    } else {
                        $contents = [
                            'msg' => 'Não é possível acessar essa página, faça login!'
                        ];
        
                        $response
                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                            ->send();
                    }
                } else {
                    $contents = [
                        'msg' => 'Token inválido!'
                    ];
    
                    $response
                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                        ->send();
                }
            } else {
                $contents = [
                    'msg' => 'Seu usuário não está logado. Por favor, faça login!'
                ];

                $response
                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                    ->send();
            }
        }

        public function view($id)
        {

        }

        public function register()
        {

        }

        public function update($id)
        {

        }

        public function delete($id)
        {
            
        }
    }

?>