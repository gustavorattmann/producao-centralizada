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
                                        orders o
                                    ON
                                        pr.ordered = o.id
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
                                    ORDER BY pr.date DESC;
                                ';

                                $productions = $this->db->fetchAll($sql);

                                if ( !empty($productions) ) {
                                    foreach ($productions as $key => $production) {
                                        $date = new \DateTime($production['date']);

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
                                                'date'                         => $date->format('d/m/Y H:i:s')
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
                                        orders o
                                    ON
                                        pr.ordered = o.id
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
                                        pr.id = :id
                                    ORDER BY pr.date DESC;
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
                                    $date = new \DateTime($result['date']);

                                    $contents = [
                                        'production' => [
                                            'id'                           => $result['id'],
                                            'solicitor'                    => $result['solicitor'],
                                            'product'                      => $result['product'],
                                            'category'                     => $result['category'],
                                            'raw_material'                 => $result['raw_material'],
                                            'stock'                        => $result['stock'],
                                            'quantity_product_requested'   => $result['quantity_product_requested'],
                                            'quantity_product_produced'    => $result['quantity_product_produced'],
                                            'quantity_product_losted'      => $result['quantity_product_losted'],
                                            'quantity_raw_material_limit'  => $result['quantity_raw_material_limit'],
                                            'quantity_raw_material_used'   => $result['quantity_raw_material_used'],
                                            'quantity_raw_material_losted' => $result['quantity_raw_material_losted'],
                                            'justification'                => $result['justification'],
                                            'date'                         => $date->format('d/m/Y H:i:s')
                                        ]
                                    ];

                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                        ->send();
                                } else {
                                    $contents = [
                                        'msg' => 'Produto solicitado ainda não foi produzido!'
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
            $production = New Production();
            
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
                                if ( !empty($request->get('ordered')) &&
                                     !empty($request->get('quantity_product_produced')) &&
                                     !empty($request->get('quantity_product_losted')) &&
                                     !empty($request->get('quantity_raw_material_used')) &&
                                     !empty($request->get('quantity_raw_material_losted')) ) {
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
                                            'id' => intval($request->get('ordered'))
                                        ]
                                    );

                                    $row_order = $query_verify_order->numRows();
                                    
                                    if ( $row_order == 1 ) {
                                        $date = new \DateTime();

                                        $production->setUser(intval($token_array['id']));
                                        $production->setOrdered(intval($request->get('ordered')));
                                        $production->setQuantityProductProduced(intval($request->get('quantity_product_produced')));
                                        $production->setQuantityProductLosted(intval($request->get('quantity_product_losted')));
                                        $production->setQuantityRawMaterialUsed(intval($request->get('quantity_raw_material_used')));
                                        $production->setQuantityRawMaterialLosted(intval($request->get('quantity_raw_material_losted')));

                                        if ( !empty($request->get('justification')) ) {
                                            $production->setJustification(intval($request->get('justification')));
                                        } else {
                                            $production->setJustification(NULL);
                                        }
                                        
                                        $production->setDate($date->format('Y-m-d H:i:s'));

                                        $sql = '
                                            INSERT INTO production
                                                (user, ordered, quantity_product_produced, quantity_product_losted, quantity_raw_material_used, quantity_raw_material_losted, justification, date)
                                            VALUES
                                                (:user, :ordered, :quantity_product_produced, :quantity_product_losted, :quantity_raw_material_used, :quantity_raw_material_losted, :justification, :date);
                                        ';

                                        try {
                                            $this->db->begin();

                                            $success = $this->db->query(
                                                $sql,
                                                [
                                                    'user'                         => $production->getUser(),
                                                    'ordered'                      => $production->getOrdered(),
                                                    'quantity_product_produced'    => $production->getQuantityProductProduced(),
                                                    'quantity_product_losted'      => $production->getQuantityProductLosted(),
                                                    'quantity_raw_material_used'   => $production->getQuantityRawMaterialUsed(),
                                                    'quantity_raw_material_losted' => $production->getQuantityRawMaterialLosted(),
                                                    'justification'                => $production->getJustification(),
                                                    'date'                         => $production->getDate()
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
                                            'msg' => 'Produção de produto não encontrada!'
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
                            $sql_verify_production = '
                                SELECT
                                    *
                                FROM
                                    production
                                WHERE
                                    id = :id
                            ';

                            $query_verify_production = $this->db->query(
                                $sql_verify_production,
                                [
                                    'id' => $id
                                ]
                            );

                            $row = $query_verify_production->numRows();
                            $result = $query_verify_production->fetch();

                            if ( $row == 1 ) {
                                if ( intval($token_array['level']) == 0 || $token_array['id'] == $result['user'] ) {
                                    if ( !empty($request->getPut('quantity_product_produced')) &&
                                         !empty($request->getPut('quantity_product_losted')) &&
                                         !empty($request->getPut('quantity_raw_material_used')) &&
                                         !empty($request->getPut('quantity_raw_material_losted')) ) {
                                        if ( intval($request->getPut('quantity_product_produced')) != $result['quantity_product_produced'] ||
                                                intval($request->getPut('quantity_product_losted')) != $result['quantity_product_losted'] ||
                                                intval($request->getPut('quantity_raw_material_used')) != $result['quantity_raw_material_used'] ||
                                                intval($request->getPut('quantity_raw_material_losted')) != $result['quantity_raw_material_losted'] ||
                                                $request->getPut('justification') != $result['justification'] ) {
                                            // $date = new \DateTime();
                                            $production->setId($id);
                                            // $production->setDate($result['date']);

                                            if ( $request->getPut('quantity_product_produced') != $result['quantity_product_produced'] ) {
                                                $production->setQuantityProductProduced(intval($request->getPut('quantity_product_produced')));
                                            } else {
                                                $production->setQuantityProductProduced(intval($result['quantity_product_produced']));
                                            }

                                            if ( $request->getPut('quantity_product_losted') != $result['quantity_product_losted'] ) {
                                                $production->setQuantityProductLosted(intval($request->getPut('quantity_product_losted')));
                                            } else {
                                                $production->setQuantityProductLosted(intval($result['quantity_product_produced']));
                                            }

                                            if ( $request->getPut('quantity_raw_material_used') != $result['quantity_raw_material_used'] ) {
                                                $production->setQuantityRawMaterialUsed(intval($request->getPut('quantity_raw_material_used')));
                                            } else {
                                                $production->setQuantityRawMaterialUsed(intval($result['quantity_raw_material_used']));
                                            }

                                            if ( $request->getPut('quantity_raw_material_losted') != $result['quantity_raw_material_losted'] ) {
                                                $production->setQuantityRawMaterialLosted(intval($request->getPut('quantity_raw_material_losted')));
                                            } else {
                                                $production->setQuantityRawMaterialLosted(intval($result['quantity_raw_material_losted']));
                                            }

                                            if ( $request->getPut('justification') != $result['justification'] ) {
                                                $production->setJustification($request->getPut('justification'));
                                            } else {
                                                $production->setJustification($result['justification']);
                                            }

                                            $sql = '
                                                UPDATE
                                                    production
                                                SET
                                                    quantity_product_produced    = :quantity_product_produced,
                                                    quantity_product_losted      = :quantity_product_losted,
                                                    quantity_raw_material_used   = :quantity_raw_material_used,
                                                    quantity_raw_material_losted = :quantity_raw_material_losted,
                                                    justification                = :justification
                                                WHERE
                                                    id = :id
                                            ';

                                            try {
                                                $this->db->begin();

                                                $success = $this->db->query(
                                                    $sql,
                                                    [
                                                        'id'                           => $production->getId(),
                                                        'quantity_product_produced'    => $production->getQuantityProductProduced(),
                                                        'quantity_product_losted'      => $production->getQuantityProductLosted(),
                                                        'quantity_raw_material_used'   => $production->getQuantityRawMaterialUsed(),
                                                        'quantity_raw_material_losted' => $production->getQuantityRawMaterialUsed(),
                                                        'justification'                => $production->getJustification()
                                                    ]
                                                );

                                                if ( $success ) {
                                                    $contents = [
                                                        'msg' => 'Produção de pedido alterada com sucesso!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 201)
                                                        ->send();
                                                } else {
                                                    $contents = [
                                                        'msg' => 'Não foi possível alterar produção de produto!'
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
                                    'msg' => 'Produção de produto não encontrada!'
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
                            if ( intval($token_array['level']) == 0 ) {
                                $sql_verify_production = '
                                    SELECT
                                        *
                                    FROM
                                        production
                                    WHERE
                                        id = :id
                                ';

                                $query = $this->db->query(
                                    $sql_verify_production,
                                    [
                                        'id' => $id
                                    ]
                                );

                                $verify_production_exists = $query->numRows();

                                if ( $verify_production_exists == 1 ) {
                                    $production->setId($id);

                                    $sql = '
                                        DELETE FROM
                                            production
                                        WHERE
                                            id = :id
                                    ';

                                    try {
                                        $this->db->begin();

                                        $del = $this->db->execute(
                                            $sql,
                                            [
                                                'id' => $production->getId()
                                            ]
                                        );

                                        if ( $del ) {
                                            $contents = [
                                                'msg' => 'Produção de produto deletada com sucesso!'
                                            ];
                    
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                                ->send();
                                        } else {
                                            $contents = [
                                                'msg' => 'Não foi possível deletar produção de produto!'
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
                                        'msg' => 'Produção de produto não encontrada!'
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

        public function report()
        {
            
        }
    }

?>