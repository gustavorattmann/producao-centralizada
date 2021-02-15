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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 3 ) {
                                $sql = '
                                    SELECT
                                        o.id AS id,
                                        u.solicitor AS solicitor,
                                        u.designated AS designated,
                                        p.name AS product,
                                        c.name AS category,
                                        r.name AS raw_material,
                                        r.stock AS stock,
                                        o.quantity_product_requested AS quantity_product_requested,
                                        o.quantity_raw_material_limit AS quantity_raw_material_limit,
                                        o.status_order AS status,
                                        o.date_initial AS date_created,
                                        o.date_final AS date_updated
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
                                        status_orders so
                                    ON
                                        o.status_order = so.id
                                    INNER JOIN
                                        category c
                                    ON
                                        p.category = c.id
                                    ORDER BY o.date_inital DESC;
                                ';

                                $orders = $this->db->fetchAll($sql);

                                if ( !empty($orders) ) {
                                    foreach ($orders as $key => $order) {
                                        $date_created = new \DateTime($order['date_created']);
                                        $date_updated = new \DateTime($order['date_updated']);

                                        $contents[$key] = [
                                            'order' => [
                                                'id'                          => $order['id'],
                                                'solicitor'                   => $order['solicitor'],
                                                'designated'                  => $order['designated'],
                                                'product'                     => $order['product'],
                                                'category'                    => $order['category'],
                                                'raw_material'                => $order['raw_material'],
                                                'stock'                       => $order['stock'],
                                                'quantity_product_requested'  => $order['quantity_product_requested'],
                                                'quantity_raw_material_limit' => $order['quantity_raw_material_limit'],
                                                'status'                      => $order['status'],
                                                'date_created'                => $date_created->format('d/m/Y H:i:s'),
                                                'date_updated'                => $date_updated->format('d/m/Y H:i:s')
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
                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
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
                            'msg' => 'Sua sessão expirou. Por favor, faça login novamente!'
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
            $orders = new Orders();
            
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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 3 ) {
                                $orders->setId($id);

                                $sql = '
                                    SELECT
                                        o.id AS id,
                                        u.solicitor AS solicitor,
                                        u.designated AS designated,
                                        p.name AS product,
                                        c.name AS category,
                                        r.name AS raw_material,
                                        r.stock AS stock,
                                        o.quantity_product_requested AS quantity_product_requested,
                                        o.quantity_raw_material_limit AS quantity_raw_material_limit,
                                        o.status_order AS status,
                                        o.date_initial AS date_created,
                                        o.date_final AS date_updated
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
                                        status_orders so
                                    ON
                                        o.status_order = so.id
                                    INNER JOIN
                                        category c
                                    ON
                                        p.category = c.id
                                    WHERE
                                        o.id = :id
                                    ORDER BY o.date_initial DESC;
                                ';

                                $query = $this->db->query(
                                    $sql,
                                    [
                                        'id' => $orders->getId()
                                    ]
                                );

                                $row = $query->numRows();
                                $result = $query->fetch();

                                if ( $row == 1 ) {
                                    $date_created = new \DateTime($result['date_created']);
                                    $date_updated = new \DateTime($result['date_updated']);

                                    $contents = [
                                        'order' => [
                                            'id'                          => $result['id'],
                                            'solicitor'                   => $result['solicitor'],
                                            'designated'                  => $result['designated'],
                                            'product'                     => $result['product'],
                                            'category'                    => $result['category'],
                                            'raw_material'                => $result['raw_material'],
                                            'stock'                       => $result['stock'],
                                            'quantity_product_requested'  => $result['quantity_product_requested'],
                                            'quantity_raw_material_limit' => $result['quantity_raw_material_limit'],
                                            'status'                      => $result['status'],
                                            'date_created'                => $date_created->format('d/m/Y H:i:s'),
                                            'date_updated'                => $date_updated->format('d/m/Y H:i:s')
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
                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
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
                            'msg' => 'Sua sessão expirou. Por favor, faça login novamente!'
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
            $orders = new Orders();
            
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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 3 ) {
                                if ( !empty($request->get('designated')) && !empty($request->get('product')) &&
                                     !empty($request->get('raw_material')) &&
                                     !empty($request->get('quantity_product_requested')) &&
                                     !empty($request->get('quantity_raw_material_limit')) &&
                                     !empty($request->get('status')) ) {
                                    $sql_verify_designated = '
                                        SELECT
                                            *
                                        FROM
                                            users
                                        WHERE
                                            id = :id;
                                    ';

                                    $query_verify_designated = $this->db->query(
                                        $sql_verify_designated,
                                        [
                                            'id' => intval($request->get('designated'))
                                        ]
                                    );

                                    $row_designated = $query_verify_designated->numRows();
                                    
                                    if ( $row_designated == 1 ) {
                                        $sql_verify_status_order = '
                                            SELECT
                                                *
                                            FROM
                                                status_orders
                                            WHERE
                                                id = :id;
                                        ';

                                        $query_verify_status_order = $this->db->query(
                                            $sql_verify_status_order,
                                            [
                                                'id' => intval($request->get('status'))
                                            ]
                                        );

                                        $row_status_order = $query_verify_status_order->numRows();
                                        
                                        if ( $row_status_order == 1 ) {
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
                                                    if ( intval($request->get('quantity_raw_material_limit')) > 0 &&
                                                         intval($request->get('quantity_raw_material_limit')) <= $result['stock'] ) {
                                                        $date = new \DateTime();

                                                        $orders->setSolicitor(intval($token_array['id']));
                                                        $orders->setDesignated(intval($request->get('designated')));
                                                        $orders->setProduct(intval($request->get('product')));
                                                        $orders->setRawMaterial(intval($request->get('raw_material')));
                                                        $orders->setQuantityProductRequested(intval($request->get('quantity_product_requested')));
                                                        $orders->setQuantityRawMaterialLimit(intval($request->get('quantity_raw_material_limit')));
                                                        $orders->setStatusOrder(intval($request->get('status')));
                                                        $orders->setDateInitial($date->format('Y-m-d H:i:s'));
                                                        $orders->setDateFinal(NULL);

                                                        $sql = '
                                                            INSERT INTO orders
                                                                (solicitor, designated, product, raw_material, quantity_product_requested, quantity_raw_material_limit, status_order, date_initial, date_final)
                                                            VALUES
                                                                (:solicitor, :designated, :product, :raw_material, :quantity_product_requested, :quantity_raw_material_limit, :status_order, :date_initial, :date_final);
                                                        ';

                                                        try {
                                                            $this->db->begin();

                                                            $success = $this->db->query(
                                                                $sql,
                                                                [
                                                                    'solicitor'                   => $orders->getSolicitor(),
                                                                    'designated'                  => $orders->getDesignated(),
                                                                    'product'                     => $orders->getProduct(),
                                                                    'raw_material'                => $orders->getRawMaterial(),
                                                                    'quantity_product_requested'  => $orders->getQuantityProductRequested(),
                                                                    'quantity_raw_material_limit' => $orders->getQuantityRawMaterialLimit(),
                                                                    'status_order'                => $orders->getStatusOrder(),
                                                                    'date_initial'                => $orders->getDateInitial(),
                                                                    'date_final'                  => $orders->getDateFinal()
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
                                                    } else if ( intval($request->get('quantity_raw_material_limit')) < $result['stock'] ) {
                                                        $contents = [
                                                            'msg' => 'A quantidade de matéria-prima requisitada precisa ser maior que 0!'
                                                        ];
                                        
                                                        $response
                                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                            ->send();
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
                                                'msg' => 'Status de pedido não encontrado!'
                                            ];
                            
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                ->send();
                                        }
                                    } else {
                                        $contents = [
                                            'msg' => 'Funcionário não encontrado!'
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
                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
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
                            'msg' => 'Sua sessão expirou. Por favor, faça login novamente!'
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
            $orders = new Orders();
            
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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 3 ) {
                                $sql_verify_order = '
                                    SELECT
                                        *
                                    FROM
                                        orders
                                    WHERE
                                        id = :id;
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
                                    if ( !empty($request->getPut('designated')) && !empty($request->getPut('product')) &&
                                         !empty($request->getPut('raw_material')) &&
                                         !empty($request->getPut('quantity_product_requested')) &&
                                         !empty($request->getPut('quantity_raw_material_limit')) &&
                                         !empty($request->getPut('status')) ) {
                                        $sql_verify_designated = '
                                            SELECT
                                                *
                                            FROM
                                                users
                                            WHERE
                                                id = :id;
                                        ';
    
                                        $query_verify_designated = $this->db->query(
                                            $sql_verify_designated,
                                            [
                                                'id' => intval($request->getPut('designated'))
                                            ]
                                        );
    
                                        $row_designated = $query_verify_designated->numRows();
                                        
                                        if ( $row_designated == 1 ) {
                                            $sql_verify_status_order = '
                                                SELECT
                                                    *
                                                FROM
                                                    status_orders
                                                WHERE
                                                    id = :id;
                                            ';
    
                                            $query_verify_status_order = $this->db->query(
                                                $sql_verify_status_order,
                                                [
                                                    'id' => intval($request->getPut('status'))
                                                ]
                                            );
    
                                            $row_status_order = $query_verify_status_order->numRows();
                                            
                                            if ( $row_status_order == 1 ) {
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
                                                        if ( intval($request->getPut('quantity_raw_material_limit')) > 0 &&
                                                             intval($request->getPut('quantity_raw_material_limit')) <= $result_raw_material['stock'] ) {
                                                            if ( intval($request->getPut('designated')) != $result['designated'] ||
                                                                 intval($request->getPut('product')) != $result['product'] ||
                                                                 intval($request->getPut('raw_material')) != $result['raw_material'] ||
                                                                 intval($request->getPut('quantity_product_requested')) != $result['quantity_product_requested'] ||
                                                                 intval($request->getPut('quantity_raw_material_limit')) != $result['quantity_raw_material_limit'] ||
                                                                 intval($request->getPut('status')) != $result['status_order'] ) {
                                                                $date = new \DateTime();

                                                                $orders->setId($id);
                                                                $orders->setSolicitor($result['solicitor']);
                                                                $orders->setDateInitial($result['date_initial']);
                                                                $orders->setDateFinal($date->format('Y-m-d H:i:s'));
                                                                
                                                                if ( intval($request->getPut('designated')) != $result['designated'] ) {
                                                                    $orders->setDesignated(intval($request->getPut('designated')));
                                                                } else {
                                                                    $orders->setDesignated($result['designated']);
                                                                }

                                                                if ( intval($request->getPut('product')) != $result['product'] ) {
                                                                    $orders->setProduct(intval($request->getPut('product')));
                                                                } else {
                                                                    $orders->setProduct($result['product']);
                                                                }
            
                                                                if ( intval($request->getPut('raw_material')) != $result['raw_material'] ) {
                                                                    $orders->setRawMaterial(intval($request->getPut('raw_material')));
                                                                } else {
                                                                    $orders->setRawMaterial($result['raw_material']);
                                                                }
            
                                                                if ( intval($request->getPut('quantity_product_requested')) != $result['quantity_product_requested'] ) {
                                                                    $orders->setQuantityProductRequested(intval($request->getPut('quantity_product_requested')));
                                                                } else {
                                                                    $orders->setQuantityProductRequested($result['quantity_product_requested']);
                                                                }
            
                                                                if ( intval($request->getPut('quantity_raw_material_limit')) != $result['quantity_raw_material_limit'] ) {
                                                                    $orders->setQuantityRawMaterialLimit(intval($request->getPut('quantity_raw_material_limit')));
                                                                } else {
                                                                    $orders->setQuantityRawMaterialLimit($result['quantity_raw_material_limit']);
                                                                }

                                                                if ( intval($request->getPut('status')) != $result['status_order'] ) {
                                                                    $orders->setStatusOrder(intval($request->getPut('status')));
                                                                } else {
                                                                    $orders->setStatusOrder($result['status_order']);
                                                                }
            
                                                                $sql = '
                                                                    UPDATE
                                                                        orders
                                                                    SET
                                                                        solicitor                   = :solicitor,
                                                                        designated                  = :designated,
                                                                        product                     = :product,
                                                                        raw_material                = :raw_material,
                                                                        quantity_product_requested  = :quantity_product_requested,
                                                                        quantity_raw_material_limit = :quantity_raw_material_limit,
                                                                        status_order                = :status,
                                                                        date_initial                = :date_initial,
                                                                        date_final                  = :date_final
                                                                    WHERE
                                                                        id = :id;
                                                                ';
            
                                                                try {
                                                                    $this->db->begin();
            
                                                                    $success = $this->db->query(
                                                                        $sql,
                                                                        [
                                                                            'id'                          => $orders->getId(),
                                                                            'solicitor'                   => $orders->getSolicitor(),
                                                                            'designated'                  => $orders->getDesignated(),
                                                                            'product'                     => $orders->getProduct(),
                                                                            'raw_material'                => $orders->getRawMaterial(),
                                                                            'quantity_product_requested'  => $orders->getQuantityProductRequested(),
                                                                            'quantity_raw_material_limit' => $orders->getQuantityRawMaterialLimit(),
                                                                            'status'                      => $orders->getStatusOrder(),
                                                                            'date_initial'                => $orders->getDateInitial(),
                                                                            'date_final'                  => $orders->getDateFinal()
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
                                                        } else if ( intval($request->get('quantity_raw_material_limit')) < $result['stock'] ) {
                                                            $contents = [
                                                                'msg' => 'A quantidade de matéria-prima requisitada precisa ser maior que 0!'
                                                            ];
                                            
                                                            $response
                                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                                ->send();
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
                                                    'msg' => 'Status de pedido não encontrado!'
                                                ];
                                
                                                $response
                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                    ->send();
                                            }
                                        } else {
                                            $contents = [
                                                'msg' => 'Funcionário não encontrado!'
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
                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
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
                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
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
                            'msg' => 'Sua sessão expirou. Por favor, faça login novamente!'
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
            $orders = new Orders();

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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 3 ) {
                                $sql_verify_order = '
                                    SELECT
                                        *
                                    FROM
                                        orders
                                    WHERE
                                        id = :id;
                                ';

                                $query = $this->db->query(
                                    $sql_verify_order,
                                    [
                                        'id' => $id
                                    ]
                                );

                                $verify_order_exist = $query->numRows();

                                if ( $verify_order_exist == 1 ) {
                                    $orders->setId($id);

                                    $sql = '
                                        DELETE FROM
                                            orders
                                        WHERE
                                            id = :id;
                                    ';

                                    try {
                                        $this->db->begin();

                                        $del = $this->db->execute(
                                            $sql,
                                            [
                                                'id' => $orders->getId()
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
                                    'msg' => 'Você não possui autorização para acessar essa página!'
                                ];
                
                                $response
                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
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
                            'msg' => 'Sua sessão expirou. Por favor, faça login novamente!'
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