<?php

    namespace App\Controllers;

    use Phalcon\Mvc\Controller;
    use Phalcon\Http\Request;
    use Phalcon\Http\Response;
    use Phalcon\Security;
    use Firebase\JWT\JWT;
    use App\Models\Users;

    class UsersController extends Controller
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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 2 ) {
                                $sql = '
                                    SELECT
                                        u.id AS id, u.name AS name, u.email AS email, r.name AS role, u.situation AS situation
                                    FROM
                                        users u
                                    LEFT JOIN
                                        roles r
                                    ON
                                        u.level = r.id
                                    ORDER BY u.id ASC;
                                ';
            
                                $users = $this->db->fetchAll($sql);

                                if ( !empty($users) ) {
                                    foreach ($users as $key => $user) {
                                        if ( $user['role'] == NULL ) {
                                            $user['role'] = '';
                                        }

                                        $contents[$key] = [
                                            'user' => [
                                                'id'        => $user['id'],
                                                'name'      => $user['name'],
                                                'email'     => $user['email'],
                                                'role'      => $user['role'],
                                                'situation' => $user['situation']
                                            ]
                                        ];
                                    }

                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                        ->send();
                                } else {
                                    $contents = [
                                        'msg' => 'Nenhum usuário encontrado!'
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

        public function profile($id = NULL)
        {
            $users = new Users();

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
                            if ( empty($id) ) {
                                $users->setId(intval($token_array['id']));
                            } else {
                                $users->setId($id);
                            }

                            if ( intval($token_array['id']) == $users->getId() || (intval($token_array['level']) == 1 || intval($token_array['level']) == 2) ) {
                                $sql = '
                                    SELECT
                                        u.id AS id, u.name AS name, u.email AS email, r.name AS role, u.situation AS situation
                                    FROM
                                        users u
                                    LEFT JOIN
                                        roles r
                                    ON
                                        u.level = r.id
                                    WHERE
                                        u.id = :id;
                                ';
            
                                $query = $this->db->query(
                                    $sql,
                                    [
                                        'id' => $users->getId()
                                    ]
                                );

                                $row = $query->numRows();
                                $user = $query->fetch();

                                if ( $row == 1 ) {
                                    if ( $user['role'] == NULL ) {
                                        $user['role'] = '';
                                    }

                                    $contents = [
                                        'user' => [
                                            'id'        => $user['id'],
                                            'name'      => $user['name'],
                                            'email'     => $user['email'],
                                            'role'      => $user['role'],
                                            'situation' => $user['situation']
                                        ]
                                    ];

                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                        ->send();
                                } else {
                                    $contents = [
                                        'msg' => 'Nenhum usuário encontrado!'
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

        public function search()
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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 2 ) {
                                if ( !empty($request->get('value')) ) {
                                    $sql = '
                                        SELECT
                                            u.id AS id, u.name AS name, u.email AS email, r.name AS role, u.situation AS situation
                                        FROM
                                            users u
                                        LEFT JOIN
                                            roles r
                                        ON
                                            u.level = r.id
                                        WHERE
                                            u.name LIKE :pesquisa OR u.email LIKE :pesquisa;
                                    ';
                
                                    $query = $this->db->query(
                                        $sql,
                                        [
                                            'pesquisa' => '%' . $request->get('value') . '%'
                                        ]
                                    );

                                    $row = $query->numRows();
                                    $users = $query->fetchAll();

                                    if ( $row > 0 ) {
                                        foreach ($users as $key => $user) {
                                            if ( $user['role'] == NULL ) {
                                                $user['role'] = '';
                                            }
    
                                            $contents[$key] = [
                                                'user' => [
                                                    'id'        => $user['id'],
                                                    'name'      => $user['name'],
                                                    'email'     => $user['email'],
                                                    'role'      => $user['role'],
                                                    'situation' => $user['situation']
                                                ]
                                            ];
                                        }

                                        $response
                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                            ->send();
                                    } else {
                                        $contents = [
                                            'msg' => 'Nenhum usuário encontrado!'
                                        ];
                        
                                        $response
                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                            ->send();
                                    }
                                } else {
                                    $contents = [
                                        'msg' => 'Insira um valor para pesquisa!'
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
            $users = new Users();

            $request = new Request();

            $response = new Response();

            $security = new Security();

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
                            if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 2 ) {
                                if ( !empty($request->get('name')) && !empty($request->get('email')) && !empty($request->get('password')) && is_numeric($request->get('level')) ) {
                                    if ( $request->get('level') < 1 ) {
                                        $contents = [
                                            'msg' => 'Valor informado para cargo está diferente do permitido!'
                                        ];
                        
                                        $response
                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                            ->send();
                                    } else {
                                        $sql_verify_email = '
                                            SELECT
                                                email
                                            FROM
                                                users
                                            WHERE
                                                email = :email;
                                        ';

                                        try {
                                            $this->db->begin();

                                            $email_verified = $this->db->query(
                                                $sql_verify_email,
                                                [
                                                    'email' => $request->get('email')
                                                ]
                                            );

                                            if ( $email_verified->numRows() == 1 ) {
                                                $contents = [
                                                    'msg' => 'Usuário já está cadastrado!'
                                                ];
                                
                                                $response
                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                    ->send();
                                            } else {
                                                $sql_verify_role = '
                                                    SELECT
                                                        situation
                                                    FROM
                                                        roles
                                                    WHERE
                                                        id = :id;
                                                ';

                                                $role_verified = $this->db->query(
                                                    $sql_verify_role,
                                                    [
                                                        'id' => intval($request->get('level'))
                                                    ]
                                                );

                                                $row_role = $role_verified->numRows();
                                                $role = $role_verified->fetch();

                                                if ( $row_role == 1 ) {
                                                    if ( $role['situation'] == 1 ) {
                                                        $password_hashed = $security->hash($request->get('password'));

                                                        $users->setName($request->get('name'));
                                                        $users->setEmail($request->get('email'));
                                                        $users->setPassword($password_hashed);
                                                        $users->setLevel(intval($request->get('level')));
                                                        $users->setSituation(1);
            
                                                        $sql = '
                                                            INSERT INTO users
                                                                (name, email, password, level, situation)
                                                            VALUES
                                                                (:name, :email, :password, :level, :situation);
                                                        ';
            
                                                        $success = $this->db->query(
                                                            $sql,
                                                            [
                                                                'name'      => $users->getName(),
                                                                'email'     => $users->getEmail(),
                                                                'password'  => $users->getPassword(),
                                                                'level'     => $users->getLevel(),
                                                                'situation' => $users->getSituation(),
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
                                                    } else {
                                                        $contents = [
                                                            'msg' => 'Cargo não está ativo, faça a ativação ou escolha outro!'
                                                        ];
                
                                                        $response
                                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                            ->send();
                                                    }
                                                } else {
                                                    $contents = [
                                                        'msg' => 'Cargo não encontrado!'
                                                    ];
                                    
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                }
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

        public function update($id = NULL)
        {
            $users = new Users();

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
                            if ( empty($id) ) {
                                $users->setId(intval($token_array['id']));
                            } else {
                                $users->setId($id);
                            }

                            if ( intval($token_array['id']) == $users->getId() || (intval($token_array['level']) == 1 || intval($token_array['level']) == 2) ) {
                                if ( !empty($request->getPut('name')) && !empty($request->getPut('email')) ) {
                                    if ( ( intval($token_array['level']) != 1 && intval($token_array['level']) != 2 ) ||
                                         ( (intval($token_array['level']) == 1 || intval($token_array['level']) == 2) &&
                                           (intval($request->getPut('level')) > 0 && ((is_numeric($request->getPut('situation'))) &&
                                           (intval($request->getPut('situation')) == 0 || intval($request->getPut('situation')) == 1))) ) ) {
                                        $sql_verify_user = '
                                            SELECT
                                                *
                                            FROM
                                                users
                                            WHERE
                                                id = :id;
                                        ';
                        
                                        $query = $this->db->query(
                                            $sql_verify_user,
                                            [
                                                'id' => $users->getId()
                                            ]
                                        );
                        
                                        $result = $query->fetch();

                                        if ( ($request->getPut('name') != $result['name']) || ($request->getPut('email') != $result['email']) ||
                                             ( (intval($token_array['level']) == 1 || intval($token_array['level']) == 2) &&
                                               (($request->getPut('level') != $result['level']) || ($request->getPut('situation') != $result['situation'])) ) ) {
                                            if ( ( intval($token_array['id']) == $users->getId() && (intval($token_array['level']) != 1 || intval($token_array['level']) != 2) ) ) {
                                                $level = $token_array['level'];
                                            } else {
                                                $level = intval($request->getPut('level'));
                                            }
        
                                            $sql_verify_role = '
                                                SELECT
                                                    situation
                                                FROM
                                                    roles
                                                WHERE
                                                    id = :id;
                                            ';
                            
                                            $role_verified = $this->db->query(
                                                $sql_verify_role,
                                                [
                                                    'id' => $level
                                                ]
                                            );
            
                                            $row_role = $role_verified->numRows();
                                            $role = $role_verified->fetch();
                            
                                            if ( $row_role == 1 ) {
                                                if ( $role['situation'] == 1 ) {
                                                    $sql_verify_email = '
                                                        SELECT
                                                            email
                                                        FROM
                                                            users
                                                        WHERE
                                                            id != :id AND email = :email;
                                                    ';
        
                                                    $query_email = $this->db->query(
                                                        $sql_verify_email,
                                                        [
                                                            'id'    => $users->getId(),
                                                            'email' => $request->getPut('email')
                                                        ]
                                                    );
        
                                                    $row_email = $query_email->numRows();
                                
                                                    if ( $row_email == 0 ) {
                                                        if ( $request->getPut('name') != $result['name'] ) {
                                                            $users->setName($request->getPut('name'));
                                                        } else {
                                                            $users->setName($result['name']);
                                                        }
                                    
                                                        if ( $request->getPut('email') != $result['email'] ) {
                                                            $users->setEmail($request->getPut('email'));
                                                        } else {
                                                            $users->setEmail($result['email']);
                                                        }
    
                                                        if ( intval($token_array['level']) == 1 || intval($token_array['level']) == 2 ) {
                                                            if ( $request->getPut('level') != $result['level'] ) {
                                                                $users->setLevel($request->getPut('level'));
                                                            } else {
                                                                $users->setLevel($result['level']);
                                                            }
            
                                                            if ( $request->getPut('situation') != $result['situation'] ) {
                                                                $users->setSituation($request->getPut('situation'));
                                                            } else {
                                                                $users->setSituation($result['situation']);
                                                            }
                                                        } else {
                                                            $users->setLevel(5);
                                                            $users->setSituation(1);
                                                        }
                                    
                                                        $sql = '
                                                            UPDATE
                                                                users
                                                            SET
                                                                name      = :name,
                                                                email     = :email,
                                                                level     = :level,
                                                                situation = :situation
                                                            WHERE
                                                                id        = :id
                                                        ';
                                    
                                                        try {
                                                            $this->db->begin();
                                    
                                                            $update = $this->db->execute(
                                                                $sql,
                                                                [
                                                                    'name'      => $users->getName(),
                                                                    'email'     => $users->getEmail(),
                                                                    'level'     => $users->getLevel(),
                                                                    'situation' => $users->getSituation(),
                                                                    'id'        => $users->getId()
                                                                ]
                                                            );
                                    
                                                            if ( $update ) {
                                                                if ( $result['email'] != $users->getEmail() || $result['level'] != $users->getLevel() ||
                                                                    $result['situation'] != $users->getSituation() ) {
                                                                    if ( $this->redis->exists($users->getEmail()) ) {
                                                                        $this->redis->del($users->getEmail());
                                                                    }
                                                                }
                                    
                                                                $contents = [
                                                                    'msg' => 'Usuário atualizado com sucesso!'
                                                                ];
                                        
                                                                $response
                                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                                                    ->send();
                                                            } else {
                                                                $contents = [
                                                                    'msg' => 'Não foi possível atualizar usuário!'
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
                                                            'msg' => 'Já existe um usuário cadastrado com esse e-mail!'
                                                        ];
                
                                                        $response
                                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                            ->send();
                                                    }
                                                } else {
                                                    $contents = [
                                                        'msg' => 'Cargo não está ativo, faça a ativação ou escolha outro!'
                                                    ];
            
                                                    $response
                                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                        ->send();
                                                }
                                            } else {
                                                $contents = [
                                                    'msg' => 'Cargo não encontrado!'
                                                ];
                                
                                                $response
                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
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
                                        if ( ( intval($token_array['level']) == 1 || intval($token_array['level']) == 2 ) &&
                                             ( !is_numeric($request->getPut('level')) || intval($request->getPut('level')) < 1 ) ) {
                                            $contents = [
                                                'msg' => 'Valor informado para cargo está diferente do permitido!'
                                            ];
                            
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                ->send();
                                        } else {
                                            $contents = [
                                                'msg' => 'Valor informado para situação está diferente do permitido!'
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

        public function changePassword($id = NULL) {
            $users = new Users();

            $request = new Request();

            $response = new Response();

            $security = new Security();

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
                            if ( empty($id) ) {
                                $users->setId(intval($token_array['id']));
                            } else {
                                $users->setId($id);
                            }

                            if ( intval($token_array['id']) == $users->getId() || (intval($token_array['level']) == 1 || intval($token_array['level']) == 2) ) {
                                if ( !empty($request->getPut('password')) ) {
                                    $sql_verify_password = '
                                        SELECT
                                            password
                                        FROM
                                            users
                                        WHERE
                                            id = :id;
                                    ';
    
                                    $query_password = $this->db->query(
                                        $sql_verify_password,
                                        [
                                            'id' => $users->getId()
                                        ]
                                    );
    
                                    $user = $query_password->fetch();
                                    
                                    if ( $security->checkHash($request->getPut('password'), $user['password']) ) {
                                        $contents = [
                                            'msg' => 'Senhas iguais!'
                                        ];
                        
                                        $response
                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                            ->send();
                                    } else {
                                        $password_hash = $security->hash($request->getPut('password'));

                                        $users->setPassword($password_hash);
                
                                        $sql_verify_email = '
                                            SELECT
                                                email
                                            FROM
                                                users
                                            WHERE
                                                id = :id;
                                        ';
                    
                                        $query = $this->db->query(
                                            $sql_verify_email,
                                            [
                                                'id' => $users->getId()
                                            ]
                                        );
                    
                                        $result = $query->fetch();
                    
                                        $sql = '
                                            UPDATE
                                                users
                                            SET
                                                password = :password
                                            WHERE
                                                id = :id;
                                        ';
                    
                                        try {
                                            $this->db->begin();
                    
                                            $change = $this->db->execute(
                                                $sql,
                                                [
                                                    'id'       => $users->getId(),
                                                    'password' => $users->getPassword()
                                                ]
                                            );
                    
                                            if ( $change ) {
                                                if ( $this->redis->exists($result['email']) ) {
                                                    $this->redis->del($result['email']);
                                                }
                    
                                                $contents = [
                                                    'msg' => 'Senha alterada com sucesso!'
                                                ];
                        
                                                $response
                                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                                    ->send();
                                            } else {
                                                $contents = [
                                                    'msg' => 'Não foi possível alterar senha!'
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
                                        'msg' => 'Forneça uma senha!'
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

        public function delete($id = NULL)
        {
            $users = new Users();
            
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
                            if ( empty($id) ) {
                                $users->setId(intval($token_array['id']));
                            } else {
                                $users->setId($id);
                            }

                            if ( intval($token_array['id']) == $users->getId() || (intval($token_array['level']) == 1 || intval($token_array['level']) == 2) ) {
                                $sql_verify_email = '
                                    SELECT
                                        email
                                    FROM
                                        users
                                    WHERE
                                        id = :id;
                                ';
                
                                $query = $this->db->query(
                                    $sql_verify_email,
                                    [
                                        'id' => $users->getId()
                                    ]
                                );
                                
                                $row = $query->numRows();
                                $result = $query->fetch();

                                if ( $row == 1 ) {
                                    $sql = '
                                        DELETE FROM
                                            users
                                        WHERE
                                            id = :id;
                                    ';
                    
                                    try {
                                        $this->db->begin();
                    
                                        $del = $this->db->execute(
                                            $sql,
                                            [
                                                'id' => $users->getId()
                                            ]
                                        );
                    
                                        if ( $del ) {
                                            if ( $this->redis->exists($result['email']) ) {
                                                $this->redis->del($result['email']);
                                            }
                    
                                            $contents = [
                                                'msg' => 'Usuário deletado com sucesso!'
                                            ];
                    
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                                ->send();
                                        } else {
                                            $contents = [
                                                'msg' => 'Não foi possível deletar usuário!'
                                            ];
                    
                                            $response
                                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                                ->send();
                                        }
                    
                                        $this->db->commit();
                                    } catch(Exception $error) {
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
                                        'msg' => 'Usuário não encontrado!'
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

        public function login()
        {
            $users = new Users();

            $request = new Request();

            $response = new Response();

            $security = new Security();

            session_start();

            // $this->redis->del($_SESSION['user']);
            // session_destroy();
            // exit();

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
                            $contents = [
                                'msg' => 'Seu usuário já está autenticado!'
                            ];
            
                            $response
                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                ->send();
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
                if ( !empty($request->get('email')) && !empty($request->get('password')) ) {
                    $sql_verify_email = '
                        SELECT
                            email
                        FROM
                            users
                        WHERE
                            email = :email;
                    ';
    
                    $query_email = $this->db->query(
                        $sql_verify_email,
                        [
                            'email' => $request->get('email')
                        ]
                    );

                    $row = $query_email->numRows();
    
                    if ( $row == 1 ) {
                        $users->setEmail($request->get('email'));

                        $sql_verify_password = '
                            SELECT
                                password
                            FROM
                                users
                            WHERE
                                email = :email;
                        ';
    
                        $password_verify = $this->db->query(
                            $sql_verify_password,
                            [
                                'email' => $users->getEmail()
                            ]
                        );
    
                        $result = $password_verify->fetch();

                        $users->setPassword($result['password']);
    
                        if ( (!empty($request->get('password')) && !empty($users->getPassword())) &&
                              $security->checkHash($request->get('password'), $users->getPassword()) ) {
                            $sql_verify_situation = '
                                SELECT
                                    id, level, situation
                                FROM
                                    users
                                WHERE
                                    email = :email;
                            ';
    
                            $verify_situation = $this->db->query(
                                $sql_verify_situation,
                                [
                                    'email' => $users->getEmail()
                                ]
                            );
    
                            $result = $verify_situation->fetch();

                            $users->setId($result['id']);
                            $users->setLevel($result['level']);
                            $users->setSituation($result['situation']);
    
                            if ( $users->getSituation() == 1 ) {
                                $key = base64_encode($_ENV['SECRET_KEY'] . $users->getEmail());

                                $payload = array(
                                    "iss"       => $_SERVER['HOST_NAME'],
                                    "aud"       => $_ENV['APP_URL'],
                                    "iat"       => date(\DateTime::ISO8601),
                                    "nbf"       => date(\DateTime::ISO8601, strtotime('+3 minute')),
                                    'id'        => $users->getId(),
                                    'level'     => $users->getLevel(),
                                    'situation' => $users->getSituation()
                                );
                                
                                try {
                                    $token = JWT::encode($payload, $key, 'HS512');
    
                                    if ( $this->redis->exists($users->getEmail()) ) {
                                        $this->redis->del($users->getEmail());
                                    }

                                    if ( empty($_SESSION['user']) ) {
                                        unset($_SESSION['user']);
                                    }
        
                                    if ( $this->redis->set($users->getEmail(), $token) ) {
                                        $_SESSION['user'] = $users->getEmail();

                                        $contents = [
                                            'token' => $token,
                                            'level' => $users->getLevel()
                                        ];
                        
                                        $response
                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                            ->send();
                                    } else {
                                        $contents = [
                                            'msg' => 'Não foi possível registrar token!'
                                        ];
                        
                                        $response
                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                            ->send();
                                    }
                                } catch (UnexpectedValueException $error) {
                                    $contents = [
                                        'msg' => $error->getMessage()
                                    ];
                    
                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 500)
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
                                'msg' => 'E-mail e/ou senha incorreto(s)!'
                            ];
            
                            $response
                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                                ->send();
                        }
                    } else {
                        $contents = [
                            'msg' => 'Usuário não encontrado no sistema!'
                        ];
        
                        $response
                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                            ->send();
                    }
                } else {
                    $contents = [
                        'msg' => 'E-mail e/ou senha não preenchido(s)!'
                    ];
    
                    $response
                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                        ->send();
                }
            }
        }

        public function logout()
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
                            try {
                                if ( $this->redis->del($_SESSION['user']) ) {
                                    session_destroy();
                
                                    $contents = [
                                        'msg' => 'Sessão encerrada com sucesso!'
                                    ];
                    
                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                        ->send();
                                } else {
                                    $contents = [
                                        'msg' => 'Não foi possível encerrar sessão!'
                                    ];
                    
                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                        ->send();
                                }
                            } catch (Exception $error) {
                                $contents = [
                                    'msg' => 'Ocorreu um erro em nosso servidor, tente mais tarde!'
                                ];
                
                                $response
                                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 500)
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