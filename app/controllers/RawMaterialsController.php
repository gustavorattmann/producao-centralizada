<?php

    namespace App\Controllers;

    use Phalcon\Mvc\Controller;
    use Phalcon\Http\Request;
    use Phalcon\Http\Response;
    use Firebase\JWT\JWT;
    use App\Models\RawMaterials;

    class RawMaterialsController extends Controller
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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 4 ) {
                                $sql = '
                                    SELECT
                                        id, name, stock, situation
                                    FROM
                                        raw_materials;
                                ';

                                $raw_materials = $this->db->fetchAll($sql);

                                if ( !empty($raw_materials) ) {
                                    foreach ($raw_materials as $key => $raw_material) {
                                        $contents[$key] = [
                                            'raw_material' => [
                                                'id'        => $raw_material['id'],
                                                'name'      => $raw_material['name'],
                                                'stock'     => $raw_material['stock'],
                                                'situation' => $raw_material['situation']
                                            ]
                                        ];
                                    }

                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                        ->send();
                                } else {
                                    $contents = [
                                        'msg' => 'Nenhuma matéria-prima encontrada!'
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
            $raw_materials = new RawMaterials();
            
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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 4 ) {
                                if ( !empty($request->get('name')) && !empty(intval($request->get('stock'))) ) {
                                    $sql_verify_raw_material = '
                                        SELECT
                                            *
                                        FROM
                                            raw_materials
                                        WHERE
                                            name = :name;
                                    ';

                                    $query_verify_raw_material = $this->db->query(
                                        $sql_verify_raw_material,
                                        [
                                            'name' => $request->get('name')
                                        ]
                                    );

                                    $verify_raw_material_exist = $query_verify_raw_material->numRows();

                                    if ( $verify_raw_material_exist < 1 ) {
                                        $raw_materials->setName($request->get('name'));
                                        $raw_materials->setStock(intval($request->get('stock')));
                                        $raw_materials->setSituation(1);

                                        $sql = '
                                            INSERT INTO raw_materials
                                                (name, stock, situation)
                                            VALUES
                                                (:name, :stock, :situation);
                                        ';

                                        try {
                                            $this->db->begin();

                                            $success = $this->db->query(
                                                $sql,
                                                [
                                                    'name'      => $raw_materials->getName(),
                                                    'stock'     => $raw_materials->getStock(),
                                                    'situation' => $raw_materials->getSituation()
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
                                            'msg' => 'Matéria-prima já está cadastrada!'
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
            $raw_materials = new RawMaterials();
            
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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 4 ) {
                                if ( !empty($request->getPut('name')) && !empty($request->getPut('stock')) && is_numeric($request->getPut('situation')) ) {
                                    if ( $request->getPut('situation') == 0 || $request->getPut('situation') == 1 ) {
                                        $sql_verify_raw_material = '
                                            SELECT
                                                *
                                            FROM
                                                raw_materials
                                            WHERE
                                                id = :id;
                                        ';

                                        $query_verify_raw_material = $this->db->query(
                                            $sql_verify_raw_material,
                                            [
                                                'id' => $id
                                            ]
                                        );

                                        $row = $query_verify_raw_material->numRows();
                                        $result = $query_verify_raw_material->fetch();

                                        if ( $row == 1 ) {
                                            $sql_verify_raw_material_name = '
                                                SELECT
                                                    name
                                                FROM
                                                    raw_materials
                                                WHERE
                                                    id != :id AND name = :name;
                                            ';

                                            $query_verify_raw_material_name = $this->db->query(
                                                $sql_verify_raw_material_name,
                                                [
                                                    'id'   => $id,
                                                    'name' => $request->getPut('name')
                                                ]
                                            );

                                            $row_raw_material_name = $query_verify_raw_material_name->numRows();

                                            if ( $row_raw_material_name == 0 ) {
                                                if ( $request->getPut('name') != $result['name'] || $request->getPut('stock') != $result['stock'] ||
                                                     $request->getPut('situation') != $result['situation'] ) {
                                                    $raw_materials->setId($id);

                                                    if ( $request->getPut('name') != $result['name'] ) {
                                                        $raw_materials->setName($request->getPut('name'));
                                                    } else {
                                                        $raw_materials->setName($result['name']);
                                                    }
                
                                                    if ( $request->getPut('stock') != $result['stock'] ) {
                                                        $raw_materials->setStock(intval($request->getPut('stock')));
                                                    } else {
                                                        $raw_materials->setStock($result['stock']);
                                                    }

                                                    if ( $request->getPut('situation') != $result['situation'] ) {
                                                        $raw_materials->setSituation(intval($request->getPut('situation')));
                                                    } else {
                                                        $raw_materials->setSituation($result['situation']);
                                                    }

                                                    $sql = '
                                                        UPDATE
                                                            raw_materials
                                                        SET
                                                            name = :name, stock = :stock, situation = :situation
                                                        WHERE
                                                            id = :id;
                                                    ';
                
                                                    try {
                                                        $this->db->begin();
                
                                                        $update = $this->db->execute(
                                                            $sql,
                                                            [
                                                                'id'        => $raw_materials->getId(),
                                                                'name'      => $raw_materials->getName(),
                                                                'stock'     => $raw_materials->getStock(),
                                                                'situation' => $raw_materials->getSituation()
                                                            ]
                                                        );
                
                                                        if ( $update ) {
                                                            $contents = [
                                                                'msg' => 'Matéria-prima alterada com sucesso!'
                                                            ];
                                    
                                                            $response
                                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                                                ->send();
                                                        } else {
                                                            $contents = [
                                                                'msg' => 'Falha na alteração da matéria-prima!'
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
                                                    'msg' => 'Já existe uma matéria-prima cadastrada com esse nome!'
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
            $raw_materials = new RawMaterials();
            
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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 4 ) {
                                $sql_verify_raw_material = '
                                    SELECT
                                        *
                                    FROM
                                        raw_materials
                                    WHERE
                                        id = :id;
                                ';

                                $query = $this->db->query(
                                    $sql_verify_raw_material,
                                    [
                                        'id' => $id
                                    ]
                                );

                                $verify_raw_material_exists = $query->numRows();

                                if ( $verify_raw_material_exists == 1 ) {
                                    $raw_materials->setId($id);

                                    $sql = '
                                        DELETE FROM
                                            raw_materials
                                        WHERE
                                            id = :id
                                    ';

                                    try {
                                        $this->db->begin();

                                        $del = $this->db->execute(
                                            $sql,
                                            [
                                                'id' => $raw_materials->getId()
                                            ]
                                        );

                                        if ( $del ) {
                                            $contents = [
                                                'msg' => 'Matéria-prima deletada com sucesso!'
                                            ];
                    
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                                ->send();
                                        } else {
                                            $contents = [
                                                'msg' => 'Não foi possível deletar matéria-prima'
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
                                        'msg' => 'Matéria-prima não encontrada!'
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