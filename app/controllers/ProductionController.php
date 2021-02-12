<?php

    namespace App\Controllers;

    use Phalcon\Mvc\Controller;
    use Phalcon\Http\Request;
    use Phalcon\Http\Response;
    use Firebase\JWT\JWT;
    use App\Models\Production;

    class ProductionController extends Controller
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
                                        pr.id AS id,
                                        u.name AS solicitor,
                                        p.name AS product,
                                        c.name AS category,
                                        r.name AS raw_material,
                                        r.stock AS stock,
                                        o.quantity_product_requested AS quantity_product_requested,
                                        pr.quantity_product_produced AS quantity_product_produced,
                                        pr.quantity_product_losted AS quantity_product_losted,
                                        o.quantity_raw_material_limit AS quantity_raw_material_limit,
                                        pr.quantity_raw_material_used AS quantity_raw_material_used,
                                        pr.quantity_raw_material_losted AS quantity_raw_material_losted,
                                        pr.justification AS justification,
                                        pr.date AS date
                                    FROM
                                        production pr
                                    INNER JOIN
                                        productions o
                                    ON
                                        pr.productions = o.id
                                    INNER JOIN
                                        users u
                                    ON
                                        pr.user = u.id
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
                                    ORDER BY p.date DESC;
                                ';

                                $productions = $this->db->fetchAll($sql);

                                if ( !empty($productions) ) {
                                    foreach ($productions as $key => $production) {
                                        $contents[$key] = [
                                            'production' => [
                                                'id'                           => $production['id'],
                                                'solicitor'                    => $production['solicitor'],
                                                'product'                      => $production['product'],
                                                'category'                     => $production['category'],
                                                'raw_material'                 => $production['raw_material'],
                                                'stock'                        => $production['stock'],
                                                'quantity_product_requested'   => $production['quantity_product_requested'],
                                                'quantity_product_produced'    => $production['quantity_product_produced'],
                                                'quantity_product_losted'      => $production['quantity_product_losted'],
                                                'quantity_raw_material_limit'  => $production['quantity_raw_material_limit'],
                                                'quantity_raw_material_used'   => $production['quantity_raw_material_used'],
                                                'quantity_raw_material_losted' => $production['quantity_raw_material_losted'],
                                                'justification'                => $production['justification'],
                                                'date'                         => $production['date']
                                            ]
                                        ];
                                    }

                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                        ->send();
                                } else {
                                    $contents = [
                                        'msg' => 'Nenhum produto foi produzido!'
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

        public function product($id)
        {
            $production = new Production();
            
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
                                $production->setId($id);

                                $sql = '
                                    SELECT
                                        pr.id AS id,
                                        u.name AS solicitor,
                                        p.name AS product,
                                        c.name AS category,
                                        r.name AS raw_material,
                                        r.stock AS stock,
                                        o.quantity_product_requested AS quantity_product_requested,
                                        pr.quantity_product_produced AS quantity_product_produced,
                                        pr.quantity_product_losted AS quantity_product_losted,
                                        o.quantity_raw_material_limit AS quantity_raw_material_limit,
                                        pr.quantity_raw_material_used AS quantity_raw_material_used,
                                        pr.quantity_raw_material_losted AS quantity_raw_material_losted,
                                        pr.justification AS justification,
                                        pr.date AS date
                                    FROM
                                        production pr
                                    INNER JOIN
                                        productions o
                                    ON
                                        pr.productions = o.id
                                    INNER JOIN
                                        users u
                                    ON
                                        pr.user = u.id
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
                                    WHERE
                                        p.id = :id
                                    ORDER BY p.date DESC;
                                ';

                                $query = $this->db->query(
                                    $sql,
                                    [
                                        'id' => $production->getId()
                                    ]
                                );

                                $row = $query->numRows();
                                $result = $query->fetch();

                                if ( $row == 1 ) {
                                    $contents = [
                                        'production' => [
                                            'id'                           => $production['id'],
                                            'solicitor'                    => $production['solicitor'],
                                            'product'                      => $production['product'],
                                            'category'                     => $production['category'],
                                            'raw_material'                 => $production['raw_material'],
                                            'stock'                        => $production['stock'],
                                            'quantity_product_requested'   => $production['quantity_product_requested'],
                                            'quantity_product_produced'    => $production['quantity_product_produced'],
                                            'quantity_product_losted'      => $production['quantity_product_losted'],
                                            'quantity_raw_material_limit'  => $production['quantity_raw_material_limit'],
                                            'quantity_raw_material_used'   => $production['quantity_raw_material_used'],
                                            'quantity_raw_material_losted' => $production['quantity_raw_material_losted'],
                                            'justification'                => $production['justification'],
                                            'date'                         => $production['date']
                                        ]
                                    ];

                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                        ->send();
                                } else {
                                    $contents = [
                                        'msg' => 'Esse produto ainda não foi produzido!'
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

        public function produced()
        {
            
        }

        public function update($id)
        {

        }

        public function delete($id)
        {

        }

        public function report()
        {

        }
    }

?>