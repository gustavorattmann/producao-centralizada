<?php

    namespace App\Controllers;

    use Phalcon\Mvc\Controller;
    use Phalcon\Http\Request;
    use Phalcon\Http\Response;
    use Firebase\JWT\JWT;
    use App\Models\StatusOrders;

    class StatusOrdersController extends Controller
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
                                        *
                                    FROM
                                        status_orders
                                    ORDER BY id ASC;
                                ';

                                $result = $this->db->fetchAll($sql);

                                if ( !empty($result) ) {
                                    foreach ($result as $key => $status_order) {
                                        $contents[$key] = [
                                            'status_order' => [
                                                'id'        => $status_order['id'],
                                                'name'      => $status_order['name'],
                                                'situation' => $status_order['situation']
                                            ]
                                        ];
                                    }

                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                        ->send();
                                } else {
                                    $contents = [
                                        'msg' => 'Nenhum status de pedido encontrado!'
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
            $status_orders = new StatusOrders();
            
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
                                if ( !empty($request->get('name')) ) {
                                    $sql_verify_status_order = '
                                        SELECT
                                            *
                                        FROM
                                            status_orders
                                        WHERE
                                            name = :name;
                                    ';

                                    $query = $this->db->query(
                                        $sql_verify_status_order,
                                        [
                                            'name' => $request->get('name')
                                        ]
                                    );

                                    $verify_status_order_exist = $query->numRows();

                                    if ( $verify_status_order_exist < 1 ) {
                                        $status_orders->setName($request->get('name'));
                                        $status_orders->setSituation(1);

                                        $sql = '
                                            INSERT INTO status_orders
                                                (name, situation)
                                            VALUES
                                                (:name, :situation);
                                        ';

                                        try {
                                            $this->db->begin();

                                            $success = $this->db->query(
                                                $sql,
                                                [
                                                    'name'      => $status_orders->getName(),
                                                    'situation' => $status_orders->getSituation()
                                                ]
                                            );

                                            if ( $success ) {
                                                $contents = [
                                                    'msg' => 'Status de pedidos cadastrado com sucesso!'
                                                ];
                                
                                                $response
                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 201)
                                                    ->send();
                                            } else {
                                                $contents = [
                                                    'msg' => 'Falha ao cadastrar status de pedidos!'
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
                                            'msg' => 'Esse status de pedidos já está cadastrado!'
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
            $status_orders = new StatusOrders();
            
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
                                if ( !empty($request->getPut('name')) && is_numeric($request->getPut('situation')) ) {
                                    if ( $request->getPut('situation') == 0 || $request->getPut('situation') == 1 ) {
                                        $sql_verify_status_order = '
                                            SELECT
                                                name, situation
                                            FROM
                                                status_orders
                                            WHERE
                                                id = :id;
                                        ';

                                        $query = $this->db->query(
                                            $sql_verify_status_order,
                                            [
                                                'id' => $id
                                            ]
                                        );

                                        $row = $query->numRows();
                                        $result = $query->fetch();

                                        if ( $row == 1 ) {
                                            $sql_verify_status_order_name = '
                                                SELECT
                                                    name
                                                FROM
                                                    status_orders
                                                WHERE
                                                    id != :id AND name = :name;
                                            ';

                                            $query_verify_status_order_name = $this->db->query(
                                                $sql_verify_status_order_name,
                                                [
                                                    'id'   => $id,
                                                    'name' => $request->getPut('name')
                                                ]
                                            );

                                            $row_status_order_name = $query_verify_status_order_name->numRows();

                                            if ( $row_status_order_name == 0 ) {
                                                if ( $request->getPut('name') != $result['name'] || intval($request->getPut('situation')) != $result['situation'] ) {
                                                    $status_orders->setId($id);
                                                
                                                    if ( $request->getPut('name') != $result['name'] ) {
                                                        $status_orders->setName($request->getPut('name'));
                                                    } else {
                                                        $status_orders->setName($result['name']);
                                                    }
    
                                                    if ( intval($request->getPut('situation')) != $result['situation'] ) {
                                                        $status_orders->setSituation(intval($request->getPut('situation')));
                                                    } else {
                                                        $status_orders->setSituation($result['situation']);
                                                    }
    
                                                    $sql = '
                                                        UPDATE
                                                            status_orders
                                                        SET
                                                            name = :name,
                                                            situation = :situation
                                                        WHERE
                                                            id = :id;
                                                    ';
    
                                                    try {
                                                        $this->db->begin();
    
                                                        $update = $this->db->execute(
                                                            $sql,
                                                            [
                                                                'id'        => $status_orders->getId(),
                                                                'name'      => $status_orders->getName(),
                                                                'situation' => $status_orders->getSituation()
                                                            ]
                                                        );
    
                                                        if ( $update ) {
                                                            $contents = [
                                                                'msg' => 'Status de pedidos alterado com sucesso!'
                                                            ];
                                            
                                                            $response
                                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 201)
                                                                ->send();
                                                        } else {
                                                            $contents = [
                                                                'msg' => 'Falha na alteração de status de pedidos!'
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
                                                        'msg' => 'Preencha pelo menos um campo com valor diferente do atual!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                }
                                            } else {
                                                $contents = [
                                                    'msg' => 'Já existe uma categoria cadastrada com esse nome!'
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
                                            'msg' => 'Valor informado para situação está diferente do permitido!'
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
            $status_orders = new StatusOrders();
            
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
                                $sql_verify_status_order = '
                                    SELECT
                                        *
                                    FROM
                                        status_orders
                                    WHERE
                                        id = :id;
                                ';

                                $query = $this->db->query(
                                    $sql_verify_status_order,
                                    [
                                        'id' => $id
                                    ]
                                );

                                $verify_status_order_exist = $query->numRows();

                                if ( $verify_status_order_exist == 1 ) {
                                    $status_orders->setId($id);

                                    $sql = '
                                        DELETE FROM
                                            status_orders
                                        WHERE
                                            id = :id;
                                    ';

                                    $sql_order = '
                                        UPDATE
                                            orders
                                        SET
                                            status_order = :status_order
                                        WHERE
                                            status_order = :status;
                                    ';

                                    try {
                                        $this->db->begin();

                                        
                                        $del = $this->db->execute(
                                            $sql,
                                            [
                                                'id' => $status_orders->getId()
                                            ]
                                        );

                                        if ( $del ) {
                                            $del_status = $this->db->execute(
                                                $sql_order,
                                                [
                                                    'status'       => $status_orders->getId(),
                                                    'status_order' => ''
                                                ]
                                            );

                                            if ( $del_status ) {
                                                $contents = [
                                                    'msg' => 'Status de pedidos deletado com sucesso!'
                                                ];
                        
                                                $response
                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                                    ->send();
                                            } else {
                                                $contents = [
                                                    'msg' => 'Não foi possível remover status dos pedidos!'
                                                ];
                        
                                                $response
                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                    ->send();
                                            }                                            
                                        } else {
                                            $contents = [
                                                'msg' => 'Não foi possível deletar status de pedidos!'
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
                                        'msg' => 'Status de pedidos não encontrado!'
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