<?php

    namespace App\Controllers;

    use Phalcon\Mvc\Controller;
    use Phalcon\Http\Request;
    use Phalcon\Http\Response;
    use Firebase\JWT\JWT;
    use App\Models\Products;

    class ProductsController extends Controller
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
                                        p.id AS id, p.name AS name, c.name AS category, p.situation AS situation
                                    FROM
                                        products p
                                    LEFT JOIN
                                        category c
                                    ON
                                        p.category = c.id;
                                ';

                                $products = $this->db->fetchAll($sql);

                                if ( !empty($products) ) {
                                    foreach ($products as $key => $product) {
                                        $contents[$key] = [
                                            'product' => [
                                                'id'        => $product['id'],
                                                'name'      => $product['name'],
                                                'category'  => $product['category'],
                                                'situation' => $product['situation']
                                            ]
                                        ];
                                    }

                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                        ->send();
                                } else {
                                    $contents = [
                                        'msg' => 'Nenhum produto encontrado!'
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
            $products = new Products();
            
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
                                if ( !empty($request->get('name')) && !empty(intval($request->get('category'))) ) {
                                    $sql_verify_product = '
                                        SELECT
                                            *
                                        FROM
                                            products
                                        WHERE
                                            name = :name;
                                    ';

                                    $sql_verify_category = '
                                        SELECT
                                            *
                                        FROM
                                            category
                                        WHERE
                                            id = :id;
                                    ';

                                    $query_verify_product = $this->db->query(
                                        $sql_verify_product,
                                        [
                                            'name' => $request->get('name')
                                        ]
                                    );

                                    $query_verify_category = $this->db->query(
                                        $sql_verify_category,
                                        [
                                            'id' => $request->get('category')
                                        ]
                                    );

                                    $verify_product_exist = $query_verify_product->numRows();
                                    $verify_category_exist = $query_verify_category->numRows();
                                    $category = $query_verify_category->fetch();

                                    if ( $verify_product_exist < 1 ) {
                                        if ( $verify_category_exist == 1 ) {
                                            if ( $category['situation'] == 1 ) {
                                                $products->setName($request->get('name'));
                                                $products->setCategory(intval($request->get('category')));
                                                $products->setSituation(1);
        
                                                $sql = '
                                                    INSERT INTO products
                                                        (name, category, situation)
                                                    VALUES
                                                        (:name, :category, :situation);
                                                ';
        
                                                try {
                                                    $this->db->begin();
        
                                                    $success = $this->db->query(
                                                        $sql,
                                                        [
                                                            'name'      => $products->getName(),
                                                            'category'  => $products->getCategory(),
                                                            'situation' => $products->getSituation()
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
                                                    'msg' => 'Categoria não está ativa, contate um responsável pelo setor de almoxarifado!'
                                                ];
        
                                                $response
                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                    ->send();
                                            }
                                        } else {
                                            $contents = [
                                                'msg' => 'Categoria não encontrada!'
                                            ];
    
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                ->send();
                                        }
                                    } else {
                                        $contents = [
                                            'msg' => 'Produto já está cadastrado!'
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
            $products = new Products();
            
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
                                if ( !empty($request->getPut('name')) && !empty($request->getPut('category')) && !empty($request->getPut('situation')) ) {
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
                                            'id' => $id
                                        ]
                                    );

                                    $row = $query_verify_product->numRows();
                                    $result = $query_verify_product->fetch();

                                    if ( $row == 1 ) {
                                        $sql_verify_category = '
                                            SELECT
                                                *
                                            FROM
                                                category
                                            WHERE
                                                id = :id;
                                        ';

                                        $query_verify_category = $this->db->query(
                                            $sql_verify_category,
                                            [
                                                'id' => $request->getPut('category')
                                            ]
                                        );

                                        $row_category = $query_verify_category->numRows();
                                        $category = $query_verify_category->fetch();

                                        if ( $row_category == 1 ) {
                                            if ( $category['situation'] == 1 ) {
                                                if ( $request->getPut('name') != $result['name'] || $request->getPut('category') != $result['category'] ||
                                                     $request->getPut('situation') != $result['situation'] ) {
                                                    $products->setId($id);

                                                    if ( $request->getPut('name') != $result['name'] ) {
                                                        $products->setName($request->getPut('name'));
                                                    } else {
                                                        $products->setName($result['name']);
                                                    }
                
                                                    if ( $request->getPut('category') != $result['category'] ) {
                                                        $products->setCategory(intval($request->getPut('category')));
                                                    } else {
                                                        $products->setCategory($result['category']);
                                                    }

                                                    if ( $request->getPut('situation') != $result['situation'] ) {
                                                        $products->setSituation(intval($request->getPut('situation')));
                                                    } else {
                                                        $products->setSituation($result['situation']);
                                                    }
        
                                                    $sql = '
                                                        UPDATE
                                                            products
                                                        SET
                                                            name = :name, category = :category, situation = :situation
                                                        WHERE
                                                            id = :id;
                                                    ';
                
                                                    try {
                                                        $this->db->begin();
                
                                                        $update = $this->db->execute(
                                                            $sql,
                                                            [
                                                                'id'        => $products->getId(),
                                                                'name'      => $products->getName(),
                                                                'category'  => $products->getCategory(),
                                                                'situation' => $products->getSituation()
                                                            ]
                                                        );
                
                                                        if ( $update ) {
                                                            $contents = [
                                                                'msg' => 'Produto alterado com sucesso!'
                                                            ];
                                    
                                                            $response
                                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                                                ->send();
                                                        } else {
                                                            $contents = [
                                                                'msg' => 'Falha na alteração do produto!'
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
                                                    'msg' => 'Categoria não está ativa, contate um responsável pelo setor de almoxarifado!'
                                                ];
        
                                                $response
                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                    ->send();
                                            }
                                        } else {
                                            $contents = [
                                                'msg' => 'Categoria não encontrada!'
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
            $products = new Products();
            
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
                                $sql_verify_product = '
                                    SELECT
                                        *
                                    FROM
                                        products
                                    WHERE
                                        id = :id;
                                ';

                                $query = $this->db->query(
                                    $sql_verify_product,
                                    [
                                        'id' => $id
                                    ]
                                );

                                $verify_product_exists = $query->numRows();

                                if ( $verify_product_exists == 1 ) {
                                    $products->setId($id);

                                    $sql = '
                                        DELETE FROM
                                            products
                                        WHERE
                                            id = :id;
                                    ';

                                    try {
                                        $this->db->begin();

                                        $del = $this->db->execute(
                                            $sql,
                                            [
                                                'id' => $products->getId()
                                            ]
                                        );

                                        if ( $del ) {
                                            $contents = [
                                                'msg' => 'Produto deletado com sucesso!'
                                            ];
                    
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                                ->send();
                                        } else {
                                            $contents = [
                                                'msg' => 'Não foi possível deletar produto!'
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
                                        'msg' => 'Produto não encontrado!'
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