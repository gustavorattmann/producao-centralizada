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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 3 || intval($token_array['level']) == 5 ) {
                                if ( intval($token_array['level']) == 5 ) {
                                    $sql = '
                                        SELECT
                                            pr.id AS id,
                                            us.name AS solicitor,
                                            ud.name AS designated,
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
                                            o.status_order AS status,
                                            pr.situation AS situation,
                                            pr.date_initial AS date_created,
                                            pr.date_final AS date_updated
                                        FROM
                                            production pr
                                        INNER JOIN
                                            orders o
                                        ON
                                            pr.ordered = o.id
                                        INNER JOIN
                                            users us
                                        ON
                                            o.solicitor = us.id
                                        LEFT JOIN
                                            users ud
                                        ON
                                            o.designated = ud.id
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
                                            ud.id = :id
                                        ORDER BY pr.date_initial DESC;
                                    ';

                                    $query = $this->db->query(
                                        $sql,
                                        [
                                            'id' => intval($token_array['id'])
                                        ]
                                    );

                                    $productions = $query->fetchAll();
                                } else if ( intval($token_array['level']) == 3 ) {
                                    $sql = '
                                        SELECT
                                            pr.id AS id,
                                            us.name AS solicitor,
                                            ud.name AS designated,
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
                                            o.status_order AS status,
                                            pr.situation AS situation,
                                            pr.date_initial AS date_created,
                                            pr.date_final AS date_updated
                                        FROM
                                            production pr
                                        INNER JOIN
                                            orders o
                                        ON
                                            pr.ordered = o.id
                                        INNER JOIN
                                            users us
                                        ON
                                            o.solicitor = us.id
                                        LEFT JOIN
                                            users ud
                                        ON
                                            o.designated = ud.id
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
                                            us.id = :id
                                        ORDER BY pr.date_initial DESC;
                                    ';

                                    $query = $this->db->query(
                                        $sql,
                                        [
                                            'id' => intval($token_array['id'])
                                        ]
                                    );

                                    $productions = $query->fetchAll();
                                } else {
                                    $sql = '
                                        SELECT
                                            pr.id AS id,
                                            us.name AS solicitor,
                                            ud.name AS designated,
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
                                            o.status_order AS status,
                                            pr.situation AS situation,
                                            pr.date_initial AS date_created,
                                            pr.date_final AS date_updated
                                        FROM
                                            production pr
                                        INNER JOIN
                                            orders o
                                        ON
                                            pr.ordered = o.id
                                        INNER JOIN
                                            users us
                                        ON
                                            o.solicitor = us.id
                                        LEFT JOIN
                                            users ud
                                        ON
                                            o.designated = ud.id
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
                                        ORDER BY pr.date_initial DESC;
                                    ';

                                    $productions = $this->db->fetchAll($sql);
                                }

                                if ( !empty($productions) ) {
                                    foreach ($productions as $key => $production) {
                                        $date_created = new \DateTime($production['date_created']);

                                        if ( $production['date_updated'] != NULL ) {
                                            $date = new \DateTime($production['date_updated']);
                                            $date_updated = $date->format('d/m/Y H:i:s');
                                        } else {
                                            $date_updated = NULL;
                                        }

                                        $contents[$key] = [
                                            'production' => [
                                                'id'                           => $production['id'],
                                                'solicitor'                    => $production['solicitor'],
                                                'designated'                   => $production['designated'],
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
                                                'status'                       => $production['status'],
                                                'situation'                    => $production['situation'],
                                                'date_created'                 => $date_created->format('d/m/Y H: i: s'),
                                                'date_updated'                 => $date_updated
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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 3 || intval($token_array['level']) == 5 ) {
                                $production->setId($id);

                                $sql = '
                                    SELECT
                                        pr.id AS id,
                                        us.id AS id_solicitor,
                                        us.name AS solicitor,
                                        ud.id AS id_designated,
                                        ud.name AS designated,
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
                                        o.status_order AS status,
                                        pr.situation AS situation,
                                        pr.date_initial AS date_created,
                                        pr.date_final AS date_updated
                                    FROM
                                        production pr
                                    INNER JOIN
                                        orders o
                                    ON
                                        pr.ordered = o.id
                                    INNER JOIN
                                        users us
                                    ON
                                        o.solicitor = us.id
                                    LEFT JOIN
                                        users ud
                                    ON
                                        o.designated = ud.id
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
                                    ORDER BY pr.date_initial DESC;
                                ';

                                $query = $this->db->query(
                                    $sql,
                                    [
                                        'id' => $production->getId()
                                    ]
                                );

                                $row = $query->numRows();
                                $result = $query->fetch();

                                if ( intval($token_array['level']) == 1 || ( intval($token_array['id']) == intval($result['id_solicitor']) ||
                                     intval($token_array['id']) == intval($result['id_designated']) ) ) {
                                    if ( $row == 1 ) {
                                        $date_created = new \DateTime($result['date_created']);

                                        if ( $result['date_updated'] != NULL ) {
                                            $date = new \DateTime($result['date_updated']);
                                            $date_updated = $date->format('d/m/Y H:i:s');
                                        } else {
                                            $date_updated = NULL;
                                        }
    
                                        $contents = [
                                            'production' => [
                                                'id'                           => $result['id'],
                                                'solicitor'                    => $result['solicitor'],
                                                'designated'                   => $result['designated'],
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
                                                'status'                       => $result['status'],
                                                'situation'                    => $result['situation'],
                                                'date_created'                 => $date_created->format('d/m/Y H: i: s'),
                                                'date_updated'                 => $date_updated
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
                                        'msg' => 'Você não possui autorização para visualizar a produção desse produto!'
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

        public function produced($id)
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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 5 ) {
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
                                        'id' => intval($id)
                                    ]
                                );

                                $row_order = $query_verify_order->numRows();
                                $result_order = $query_verify_order->fetch();
                                
                                if ( $row_order == 1 ) {
                                    if ( $result_order['status_order'] == 1 ) {
                                        if ( intval($token_array['level']) == 1 || $result_order['designated'] == intval($token_array['id']) ) {
                                            if ( ( !empty($request->get('quantity_product_produced')) || is_numeric($request->get('quantity_product_produced')) ) &&
                                                ( !empty($request->get('quantity_product_losted')) || is_numeric($request->get('quantity_product_losted')) ) &&
                                                ( !empty($request->get('quantity_raw_material_used')) || is_numeric($request->get('quantity_raw_material_used')) ) &&
                                                ( !empty($request->get('quantity_raw_material_losted')) || is_numeric($request->get('quantity_raw_material_losted')) ) ) {
                                                if ( $request->get('quantity_product_produced') < 1 ) {
                                                    $contents = [
                                                        'msg' => 'Valor informado para quantidade de produto produzido está diferente do permitido!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                } else if ( $request->get('quantity_product_losted') < 1 ) {
                                                    $contents = [
                                                        'msg' => 'Valor informado para quantidade de produto perdido está diferente do permitido!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                } else if ( $request->get('quantity_raw_material_used') < 1 ) {
                                                    $contents = [
                                                        'msg' => 'Valor informado para quantidade de matéria-prima utilizada está diferente do permitido!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                } else if ( $request->get('quantity_raw_material_losted') < 1 ) {
                                                    $contents = [
                                                        'msg' => 'Valor informado para quantidade de matéria-prima perdida está diferente do permitido!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                } else if ( intval($request->get('quantity_product_produced')) > $result_order['quantity_product_requested'] ) {
                                                    $contents = [
                                                        'msg' => 'Quantidade de produto(s) produzido(s) acima da quantidade solicitada!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                } else {
                                                    $date = new \DateTime();
        
                                                    $production->setOrdered(intval($id));
                                                    $production->setQuantityProductProduced(intval($request->get('quantity_product_produced')));
                                                    $production->setQuantityProductLosted(intval($request->get('quantity_product_losted')));
                                                    $production->setQuantityRawMaterialUsed(intval($request->get('quantity_raw_material_used')));
                                                    $production->setQuantityRawMaterialLosted(intval($request->get('quantity_raw_material_losted')));
                                                    $production->setSituation(1);
                                                    $production->setDateInitial($date->format('Y-m-d H:i:s'));
                                                    $production->setDateFinal(NULL);
            
                                                    if ( !empty($request->get('justification')) ) {
                                                        $production->setJustification($request->get('justification'));
                                                    } else {
                                                        $production->setJustification(NULL);
                                                    }
            
                                                    $sql = '
                                                        INSERT INTO production
                                                            (ordered, quantity_product_produced, quantity_product_losted, quantity_raw_material_used,
                                                            quantity_raw_material_losted, justification, situation, date_initial, date_final)
                                                        VALUES
                                                            (:ordered, :quantity_product_produced, :quantity_product_losted, :quantity_raw_material_used,
                                                            :quantity_raw_material_losted, :justification, :situation, :date_initial, :date_final);
                                                    ';
            
                                                    try {
                                                        $this->db->begin();
            
                                                        $success = $this->db->query(
                                                            $sql,
                                                            [
                                                                'ordered'                      => $production->getOrdered(),
                                                                'quantity_product_produced'    => $production->getQuantityProductProduced(),
                                                                'quantity_product_losted'      => $production->getQuantityProductLosted(),
                                                                'quantity_raw_material_used'   => $production->getQuantityRawMaterialUsed(),
                                                                'quantity_raw_material_losted' => $production->getQuantityRawMaterialLosted(),
                                                                'justification'                => $production->getJustification(),
                                                                'situation'                    => $production->getSituation(),
                                                                'date_initial'                 => $production->getDateInitial(),
                                                                'date_final'                   => $production->getDateFinal()
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
                                                'msg' => 'Você não possui autorização para produzir esse produto!'
                                            ];
                            
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                                                ->send();
                                        }
                                    } else {
                                        $contents = [
                                            'msg' => 'Pedido não está disponível para produção!'
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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 5 ) {
                                $sql_verify_production = '
                                    SELECT
                                        ud.id AS id_designated,
                                        pr.ordered AS ordered,
                                        o.quantity_product_requested AS quantity_product_requested,
                                        pr.quantity_product_produced AS quantity_product_produced,
                                        pr.quantity_product_losted AS quantity_product_losted,
                                        pr.quantity_raw_material_used AS quantity_raw_material_used,
                                        pr.quantity_raw_material_losted AS quantity_raw_material_losted,
                                        pr.justification AS justification,
                                        o.status_order AS status,
                                        pr.situation AS situation,
                                        pr.date_initial AS date_created
                                        pr.date_final AS date_updated
                                    FROM
                                        production pr
                                    INNER JOIN
                                        orders o
                                    ON
                                        pr.ordered = o.id
                                    LEFT JOIN
                                        users ud
                                    ON
                                        o.designated = ud.id
                                    WHERE
                                        pr.id = :id
                                ';

                                $query_verify_production = $this->db->query(
                                    $sql_verify_production,
                                    [
                                        'id' => intval($id)
                                    ]
                                );

                                $row = $query_verify_production->numRows();
                                $result = $query_verify_production->fetch();

                                if ( $row == 1 ) {
                                    if ( $result['status'] == 3 ) {
                                        if ( intval($token_array['level']) == 1 || $result['id_designated'] == intval($token_array['id']) ) {
                                            if ( ( !empty($request->getPut('quantity_product_produced')) || is_numeric($request->getPut('quantity_product_produced')) ) &&
                                                ( !empty($request->getPut('quantity_product_losted')) || is_numeric($request->getPut('quantity_product_losted')) ) &&
                                                ( !empty($request->getPut('quantity_raw_material_used')) || is_numeric($request->getPut('quantity_raw_material_used')) ) &&
                                                ( !empty($request->getPut('quantity_raw_material_losted')) || is_numeric($request->getPut('quantity_raw_material_losted')) ) &&
                                                ( !empty($request->getPut('situation')) || is_numeric($request->getPut('situation')) ) ) {
                                                if ( $request->getPut('quantity_product_produced') < 1 ) {
                                                    $contents = [
                                                        'msg' => 'Valor informado para quantidade de produto produzido está diferente do permitido!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                } else if ( $request->getPut('quantity_product_losted') < 1 ) {
                                                    $contents = [
                                                        'msg' => 'Valor informado para quantidade de produto perdido está diferente do permitido!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                } else if ( $request->getPut('quantity_raw_material_used') < 1 ) {
                                                    $contents = [
                                                        'msg' => 'Valor informado para quantidade de matéria-prima utilizada está diferente do permitido!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                } else if ( $request->getPut('quantity_raw_material_losted') < 1 ) {
                                                    $contents = [
                                                        'msg' => 'Valor informado para quantidade de matéria-prima perdida está diferente do permitido!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                } else if ( intval($request->getPut('quantity_product_produced')) > $result['quantity_product_requested'] ) {
                                                    $contents = [
                                                        'msg' => 'Quantidade de produto(s) produzido(s) acima da quantidade solicitada!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                } else if ( $request->getPut('situation') != 0 && $request->getPut('situation') != 1 ) {
                                                    $contents = [
                                                        'msg' => 'Valor informado para situação está diferente do permitido!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                } else {
                                                    if ( intval($request->getPut('quantity_product_produced')) != $result['quantity_product_produced'] ||
                                                        intval($request->getPut('quantity_product_losted')) != $result['quantity_product_losted'] ||
                                                        intval($request->getPut('quantity_raw_material_used')) != $result['quantity_raw_material_used'] ||
                                                        intval($request->getPut('quantity_raw_material_losted')) != $result['quantity_raw_material_losted'] ||
                                                        $request->getPut('justification') != $result['justification'] ||
                                                        intval($request->getPut('situation')) != $result['situation']  ) {
                                                        if ( !empty($result['date_updated']) ) {
                                                            $date = new \DateTime($result['date_updated']);
                                                        } else {
                                                            $date = new \DateTime();
                                                        }

                                                        $production->setId(intval($id));
                                                        $production->setDateFinal($date->format('Y-m-d H:i:s'));

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

                                                        if ( $request->getPut('situation') != $result['situation'] ) {
                                                            $production->setSituation($request->getPut('situation'));
                                                        } else {
                                                            $production->setSituation($result['situation']);
                                                        }
                
                                                        $sql = '
                                                            UPDATE
                                                                production
                                                            SET
                                                                quantity_product_produced    = :quantity_product_produced,
                                                                quantity_product_losted      = :quantity_product_losted,
                                                                quantity_raw_material_used   = :quantity_raw_material_used,
                                                                quantity_raw_material_losted = :quantity_raw_material_losted,
                                                                justification                = :justification,
                                                                situation                    = :situation,
                                                                date_final                   = :date_final
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
                                                                    'justification'                => $production->getJustification(),
                                                                    'situation'                    => $production->getSituation(),
                                                                    'date_final'                   => $production->getDateFinal()
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
                                                'msg' => 'Você não possui autorização para alterar a produção desse produto!'
                                            ];
                            
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                                                ->send();
                                        }
                                    } else {
                                        $contents = [
                                            'msg' => 'Pedido não está disponível para alteração de produção!'
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
                            if ( intval($token_array['level']) == 1 ) {
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

                                $verify_production_exist = $query->numRows();

                                if ( $verify_production_exist == 1 ) {
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

        public function report()
        {
            
        }
    }

?>