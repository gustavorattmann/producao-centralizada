<?php

    namespace App\Controllers;

    use Phalcon\Mvc\Controller;
    use Phalcon\Http\Request;
    use Phalcon\Http\Response;
    use Firebase\JWT\JWT;
    use App\Models\Category;

    class CategoryController extends Controller
    {
        public function index()
        {
            $category = new Category();
            
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
                                        *
                                    FROM
                                        category
                                    ORDER BY id ASC;
                                ';

                                $result = $this->db->fetchAll($sql);

                                if ( !empty($result) ) {
                                    foreach ($result as $key => $category) {
                                        $contents[$key] = [
                                            'category' => [
                                                'id'   => $category['id'],
                                                'name' => $category['name']
                                            ]
                                        ];
                                    }

                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                        ->send();
                                } else {
                                    $contents = [
                                        'msg' => 'Nenhuma categoria encontrada!'
                                    ];
                    
                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                        ->send();
                                }
                            } else {
                                $contents = [
                                    'msg' => 'Você não possui autorização para visualizar as categorias!'
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
            $category = new Category();
            
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
                                if ( !empty($request->get('name')) ) {
                                    $sql_verify_category = '
                                        SELECT
                                            name
                                        FROM
                                            category
                                        WHERE
                                            name = :name
                                    ';

                                    $query = $this->db->query(
                                        $sql_verify_category,
                                        [
                                            'name' => $request->get('name')
                                        ]
                                    );

                                    $verify_category_exists = $query->numRows();

                                    if ( $verify_category_exists < 1 ) {
                                        $category->setName($request->get('name'));

                                        $sql = '
                                            INSERT INTO category
                                                (name)
                                            VALUES
                                                (:name);
                                        ';

                                        try {
                                            $this->db->begin();

                                            $success = $this->db->query(
                                                $sql,
                                                [
                                                    'name' => $category->getName()
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
                                            'msg' => 'Categoria já está cadastrada!'
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

        public function update($id)
        {
            $category = new Category();
            
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
                                if ( !empty($request->getPut('name')) ) {
                                    $category->setId($id);
                                    $category->setName($request->getPut('name'));

                                    $sql_verify_category = '
                                        SELECT
                                            name
                                        FROM
                                            category
                                        WHERE
                                            name = :name
                                    ';

                                    $query = $this->db->query(
                                        $sql_verify_category,
                                        [
                                            'name' => $category->getName()
                                        ]
                                    );

                                    $result = $query->fetch();

                                    if ( $category->getName() != $result['name'] ) {
                                        $sql = '
                                            UPDATE
                                                category
                                            SET
                                                name = :name
                                            WHERE
                                                id = :id
                                        ';

                                        try {
                                            $this->db->begin();

                                            $update = $this->db->execute(
                                                $sql,
                                                [
                                                    'id'   => $category->getId(),
                                                    'name' => $category->getName()
                                                ]
                                            );

                                            if ( $update ) {
                                                $contents = [
                                                    'msg' => 'Categoria alterada com sucesso!'
                                                ];
                                
                                                $response
                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 201)
                                                    ->send();
                                            } else {
                                                $contents = [
                                                    'msg' => 'Falha na alteração da categoria!'
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
                                            'msg' => 'Preencha um nome diferente do atual!'
                                        ];
                        
                                        $response
                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                            ->send();
                                    }
                                } else {
                                    $contents = [
                                        'msg' => 'Preencha um nome para a categoria!'
                                    ];
                    
                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                        ->send();
                                }
                            } else {
                                $contents = [
                                    'msg' => 'Você não possui autorização para visualizar as categorias!'
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
            $category = new Category();
            
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
                                $sql_verify_category = '
                                    SELECT
                                        *
                                    FROM
                                        category
                                    WHERE
                                        id = :id
                                ';

                                $query = $this->db->query(
                                    $sql_verify_category,
                                    [
                                        'id' => $id
                                    ]
                                );

                                $verify_category_exists = $query->numRows();

                                if ( $verify_category_exists == 1 ) {
                                    $category->setId($id);

                                    $sql = '
                                        DELETE FROM
                                            category
                                        WHERE
                                            id = :id
                                    ';

                                    $sql_product = '
                                        DELETE FROM
                                            products
                                        WHERE
                                            category = :id
                                    ';

                                    try {
                                        $this->db->begin();

                                        $del_product = $this->db->execute(
                                            $sql_product,
                                            [
                                                'id' => $category->getId()
                                            ]
                                        );

                                        $del = $this->db->execute(
                                            $sql,
                                            [
                                                'id' => $category->getId()
                                            ]
                                        );

                                        if ( $del ) {
                                            $contents = [
                                                'msg' => 'Categoria deletada com sucesso!'
                                            ];
                    
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                                ->send();
                                        } else {
                                            $contents = [
                                                'msg' => 'Não foi possível deletar categoria!'
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
                                        'msg' => 'Categoria não encontrada!'
                                    ];
                    
                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                        ->send();
                                }
                            } else {
                                $contents = [
                                    'msg' => 'Você não possui autorização para visualizar as categorias!'
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