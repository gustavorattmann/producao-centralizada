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
            $order = New Orders();
            
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
                                $order->setId($id);

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
                                    WHERE
                                        o.id = :id
                                    ORDER BY o.date DESC;
                                ';

                                $query = $this->db->query(
                                    $sql,
                                    [
                                        'id' => $order->getId()
                                    ]
                                );

                                $row = $query->numRows();
                                $result = $query->fetch();

                                if ( $row == 1 ) {
                                    $contents = [
                                        'order' => [
                                            'id'                          => $result['id'],
                                            'solicitor'                   => $result['solicitor'],
                                            'product'                     => $result['product'],
                                            'category'                    => $result['category'],
                                            'raw_material'                => $result['raw_material'],
                                            'stock'                       => $result['stock'],
                                            'quantity_product_requested'  => $result['quantity_product_requested'],
                                            'quantity_raw_material_limit' => $result['quantity_raw_material_limit'],
                                            'date'                        => $result['date']
                                        ]
                                    ];

                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                        ->send();
                                } else {
                                    $contents = [
                                        'msg' => 'Pedido não encontrado!'
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

        public function register()
        {
            $order = New Orders();
            
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
                                if ( !empty($request->get('product')) && !empty($request->get('raw_material')) &&
                                     !empty($request->get('quantity_product_requested')) &&
                                     !empty($request->get('quantity_raw_material_limit')) ) {
                                    $sql_verify_product = '
                                        SELECT
                                            *
                                        FROM
                                            products
                                        WHERE
                                            id = :id;
                                    ';

                                    $query_verify_product = $this->db->query(
                                        $sql_verify_product,
                                        [
                                            'id' => intval($request->get('product'))
                                        ]
                                    );

                                    $row_product = $query_verify_product->numRows();
                                    
                                    if ( $row_product == 1 ) {
                                        $sql_verify_raw_material = '
                                            SELECT
                                                stock
                                            FROM
                                                raw_materials
                                            WHERE
                                                id = :id;
                                        ';

                                        $query_verify_raw_material = $this->db->query(
                                            $sql_verify_raw_material,
                                            [
                                                'id' => intval($request->get('raw_material'))
                                            ]
                                        );

                                        $result = $query_verify_raw_material->fetch();

                                        if ( !empty($result) ) {
                                            if ( intval($request->get('quantity_raw_material_limit')) <= $result['stock'] ) {
                                                $date = new \DateTime();

                                                $order->setUser(intval($token_array['id']));
                                                $order->setProduct(intval($request->get('product')));
                                                $order->setRawMaterial(intval($request->get('raw_material')));
                                                $order->setQuantityProductRequested(intval($request->get('quantity_product_requested')));
                                                $order->setQuantityRawMaterialLimit(intval($request->get('quantity_raw_material_limit')));
                                                $order->setDate($date->format('Y-m-d H:i:s'));

                                                $sql = '
                                                    INSERT INTO orders
                                                        (user, product, raw_material, quantity_product_requested, quantity_raw_material_limit, date)
                                                    VALUES
                                                        (:user, :product, :raw_material, :quantity_product_requested, :quantity_raw_material_limit, :date);
                                                ';

                                                try {
                                                    $this->db->begin();

                                                    $success = $this->db->query(
                                                        $sql,
                                                        [
                                                            'user'                        => $order->getUser(),
                                                            'product'                     => $order->getProduct(),
                                                            'raw_material'                => $order->getRawMaterial(),
                                                            'quantity_product_requested'  => $order->getQuantityProductRequested(),
                                                            'quantity_raw_material_limit' => $order->getQuantityRawMaterialLimit(),
                                                            'date'                        => $order->getDate()
                                                        ]
                                                    );

                                                    if ( $success ) {
                                                        $contents = [
                                                            'msg' => 'Cadastro realizado com sucesso!'
                                                        ];
                                        
                                                        $response
                                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 201)
                                                            ->send();
                                                    } else {
                                                        $contents = [
                                                            'msg' => 'Falha no cadastro!'
                                                        ];
                                        
                                                        $response
                                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                            ->send();
                                                    }

                                                    $this->db->commit();
                                                } catch (Exception $error) {
                                                    $this->db->rollback();

                                                    $contents = [
                                                        'msg' => 'Ocorreu um erro em nosso servidor, tente mais tarde!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 500)
                                                        ->send();
                                                }
                                            } else {
                                                $contents = [
                                                    'msg' => 'A quantidade de matéria-prima requisitada é maior que a quantidade disponível em estoque!'
                                                ];
                                
                                                $response
                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                    ->send();
                                            }
                                        } else {
                                            $contents = [
                                                'msg' => 'Matéria-prima não encontrada!'
                                            ];
                            
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                ->send();
                                        }
                                    } else {
                                        $contents = [
                                            'msg' => 'Produto não encontrado!'
                                        ];
                        
                                        $response
                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                            ->send();
                                    }
                                } else {
                                    $contents = [
                                        'msg' => 'Dados incompletos!'
                                    ];
                    
                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
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

        public function update($id)
        {
            $order = New Orders();
            
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
                                $sql_verify_order = '
                                    SELECT
                                        *
                                    FROM
                                        orders
                                    WHERE
                                        id = :id
                                ';

                                $query_verify_order = $this->db->query(
                                    $sql_verify_order,
                                    [
                                        'id' => $id
                                    ]
                                );

                                $row = $query_verify_order->numRows();
                                $result = $query_verify_order->fetch();

                                if ( $row == 1 ) {
                                    if ( !empty($request->getPut('product')) && !empty($request->getPut('raw_material')) &&
                                         !empty($request->getPut('quantity_product_requested')) &&
                                         !empty($request->getPut('quantity_raw_material_limit')) ) {
                                        $sql_verify_product = '
                                            SELECT
                                                *
                                            FROM
                                                products
                                            WHERE
                                                id = :id;
                                        ';

                                        $query_verify_product = $this->db->query(
                                            $sql_verify_product,
                                            [
                                                'id' => intval($request->getPut('product'))
                                            ]
                                        );

                                        $row_product = $query_verify_product->numRows();
                                        
                                        if ( $row_product == 1 ) {
                                            $sql_verify_raw_material = '
                                                SELECT
                                                    stock
                                                FROM
                                                    raw_materials
                                                WHERE
                                                    id = :id;
                                            ';

                                            $query_verify_raw_material = $this->db->query(
                                                $sql_verify_raw_material,
                                                [
                                                    'id' => intval($request->getPut('raw_material'))
                                                ]
                                            );

                                            $result_raw_material = $query_verify_raw_material->fetch();

                                            if ( !empty($result_raw_material) ) {
                                                if ( intval($request->getPut('quantity_raw_material_limit')) <= $result_raw_material['stock'] ) {
                                                    if ( intval($request->getPut('product')) != $result['product'] ||
                                                         intval($request->getPut('raw_material')) != $result['raw_material'] ||
                                                         intval($request->getPut('quantity_product_requested')) != $result['quantity_product_requested'] ||
                                                         intval($request->getPut('quantity_raw_material_limit')) != $result['quantity_raw_material_limit'] ) {
                                                        $date = new \DateTime();

                                                        $order->setId($id);
                                                        $order->setUser(intval($token_array['id']));
                                                        $order->setDate($date->format('Y-m-d H:i:s'));
    
                                                        if ( intval($request->getPut('product')) != $result['product'] ) {
                                                            $order->setProduct(intval($request->getPut('product')));
                                                        } else {
                                                            $order->setProduct($result['product']);
                                                        }
    
                                                        if ( intval($request->getPut('raw_material')) != $result['raw_material'] ) {
                                                            $order->setRawMaterial(intval($request->getPut('raw_material')));
                                                        } else {
                                                            $order->setRawMaterial($result['raw_material']);
                                                        }
    
                                                        if ( intval($request->getPut('quantity_product_requested')) != $result['quantity_product_requested'] ) {
                                                            $order->setQuantityProductRequested(intval($request->getPut('quantity_product_requested')));
                                                        } else {
                                                            $order->setQuantityProductRequested($result['quantity_product_requested']);
                                                        }
    
                                                        if ( intval($request->getPut('quantity_raw_material_limit')) != $result['quantity_raw_material_limit'] ) {
                                                            $order->setQuantityRawMaterialLimit(intval($request->getPut('quantity_raw_material_limit')));
                                                        } else {
                                                            $order->setQuantityRawMaterialLimit($result['quantity_raw_material_limit']);
                                                        }
    
                                                        $sql = '
                                                            UPDATE
                                                                orders
                                                            SET
                                                                user = :user,
                                                                product = :product,
                                                                raw_material = :raw_material,
                                                                quantity_product_requested = :quantity_product_requested,
                                                                quantity_raw_material_limit = :quantity_raw_material_limit,
                                                                date = :date
                                                            WHERE
                                                                id = :id;
                                                        ';
    
                                                        try {
                                                            $this->db->begin();
    
                                                            $success = $this->db->query(
                                                                $sql,
                                                                [
                                                                    'id'                          => $order->getId(),
                                                                    'user'                        => $order->getUser(),
                                                                    'product'                     => $order->getProduct(),
                                                                    'raw_material'                => $order->getRawMaterial(),
                                                                    'quantity_product_requested'  => $order->getQuantityProductRequested(),
                                                                    'quantity_raw_material_limit' => $order->getQuantityRawMaterialLimit(),
                                                                    'date'                        => $order->getDate()
                                                                ]
                                                            );
    
                                                            if ( $success ) {
                                                                $contents = [
                                                                    'msg' => 'Pedido alterado com sucesso!'
                                                                ];
                                                
                                                                $response
                                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 201)
                                                                    ->send();
                                                            } else {
                                                                $contents = [
                                                                    'msg' => 'Não foi possível alterar pedido!'
                                                                ];
                                                
                                                                $response
                                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                                    ->send();
                                                            }
    
                                                            $this->db->commit();
                                                        } catch (Exception $error) {
                                                            $this->db->rollback();
    
                                                            $contents = [
                                                                'msg' => 'Ocorreu um erro em nosso servidor, tente mais tarde!'
                                                            ];
                                            
                                                            $response
                                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 500)
                                                                ->send();
                                                        }
                                                    } else {
                                                        $contents = [
                                                            'msg' => 'Digite pelo menos um campo com valor diferente do atual!'
                                                        ];
                                        
                                                        $response
                                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                            ->send();
                                                    }
                                                } else {
                                                    $contents = [
                                                        'msg' => 'A quantidade de matéria-prima requisitada é maior que a quantidade disponível em estoque!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                }
                                            } else {
                                                $contents = [
                                                    'msg' => 'Matéria-prima não encontrada!'
                                                ];
                                
                                                $response
                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                    ->send();
                                            }
                                        } else {
                                            $contents = [
                                                'msg' => 'Produto não encontrado!'
                                            ];
                            
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                ->send();
                                        }
                                    } else {
                                        $contents = [
                                            'msg' => 'Dados incompletos!'
                                        ];
                        
                                        $response
                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                                            ->send();
                                    }
                                } else {
                                    $contents = [
                                        'msg' => 'Pedido não encontrado!'
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

        public function delete($id)
        {
            $order = new Orders();

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
                                $sql_verify_order = '
                                    SELECT
                                        *
                                    FROM
                                        orders
                                    WHERE
                                        id = :id
                                ';

                                $query = $this->db->query(
                                    $sql_verify_order,
                                    [
                                        'id' => $id
                                    ]
                                );

                                $verify_order_exists = $query->numRows();

                                if ( $verify_order_exists == 1 ) {
                                    $order->setId($id);

                                    $sql = '
                                        DELETE FROM
                                            orders
                                        WHERE
                                            id = :id
                                    ';

                                    try {
                                        $this->db->begin();

                                        $del = $this->db->execute(
                                            $sql,
                                            [
                                                'id' => $order->getId()
                                            ]
                                        );

                                        if ( $del ) {
                                            $contents = [
                                                'msg' => 'Pedido deletado com sucesso!'
                                            ];
                    
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                                ->send();
                                        } else {
                                            $contents = [
                                                'msg' => 'Não foi possível deletar pedido!'
                                            ];
                    
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                ->send();
                                        }

                                        $this->db->commit();
                                    } catch (Exception $error) {
                                        $this->db->rollback();
                
                                        $contents = [
                                            'msg' => 'Ocorreu um erro em nosso servidor, tente mais tarde!'
                                        ];
                        
                                        $response
                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 500)
                                            ->send();
                                    }
                                } else {
                                    $contents = [
                                        'msg' => 'Pedido não encontrado!'
                                    ];
                    
                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                        ->send();
                                }
                            } else {
                                $contents = [
                                    'msg' => 'Você não possui autorização para fazer alterações nesse usuário!'
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
    }

?>