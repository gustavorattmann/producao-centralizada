<?php

    namespace App\Controllers;

    use Phalcon\Mvc\Controller;
    use Phalcon\Http\Request;
    use Phalcon\Http\Response;
    use Phalcon\Security;
    use Firebase\JWT\JWT;
    use App\Models\User;

    class UserController extends Controller
    {
        public function index()
        {
            $request = new Request();

            $response = new Response();
            
            session_start();

            $bearerToken = $request->getHeaders()['Authorization'];

            $bearerToken = str_replace('Bearer ', '', $bearerToken);

            if ( !empty($_SESSION['user']) && !empty($bearerToken) ) {
                $key = base64_encode($_ENV['SECRET_KEY'] . $_SESSION['user']);

                JWT::$leeway = 60;
                $token = JWT::decode($bearerToken, $key, array('HS512'));
            }

            $token_array = (array) $token;
            $nbf_array = (array) $token_array['nbf'];

            if ( date(\DateTime::ISO8601) <= $nbf_array && $token_array['situation'] == 1 ) {
                if ( $token_array['level'] == 0 ) {
                    $sql = '
                        SELECT
                            *
                        FROM
                            users
                    ';

                    $users = $this->db->fetchAll($sql);

                    foreach ($users as $key => $user) {
                        $contents[$key] = [
                            'user' => [
                                'id'        => $user['id'],
                                'name'      => $user['name'],
                                'email'     => $user['email'],
                                'level'     => $user['level'],
                                'situation' => $user['situation']
                            ]
                        ];
                    }
                } else {
                    $sql = '
                        SELECT
                            *
                        FROM
                            users
                        WHERE
                            email = :email
                    ';

                    $result = $this->db->query(
                        $sql,
                        [
                            'email' => $_SESSION['user']
                        ]
                    );

                    $user = $result->fetch();

                    $contents = [
                        'user' => [
                            'id'        => $user['id'],
                            'name'      => $user['name'],
                            'email'     => $user['email'],
                            'level'     => $user['level'],
                            'situation' => $user['situation']
                        ]
                    ];
                }

                $response
                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                    ->send();
            } else {
                $contents = [
                    'msg' => 'Token inválido!'
                ];

                $response
                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                    ->send();
            }
        }

        public function register()
        {
            $user = new User();

            $request = new Request();

            $response = new Response();

            $security = new Security();

            session_start();

            $bearerToken = $request->getHeaders()['Authorization'];

            $bearerToken = str_replace('Bearer ', '', $bearerToken);

            if ( !empty($_SESSION['user']) && !empty($bearerToken) ) {
                $key = base64_encode($_ENV['SECRET_KEY'] . $_SESSION['user']);

                JWT::$leeway = 60;
                $token = JWT::decode($bearerToken, $key, array('HS512'));
            }

            $token_array = (array) $token;
            $nbf_array = (array) $token_array['nbf'];

            if ( date(\DateTime::ISO8601) <= $nbf_array && $token_array['situation'] == 1 ) {
                $contents = [
                    'msg' => 'Seu usuário já está autenticado!'
                ];

                $response
                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                    ->send();
            } else {
                if ( empty($request->get('name')) && empty($request->get('email')) && empty($request->get('password'))
                  && empty($request->get('level')) && empty($request->get('situation')) ) {
                    $contents = [
                        'msg' => 'Dados incompletos!'
                    ];
    
                    $response
                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                        ->send();
                } else {
                    $sql_verify_email = '
                        SELECT
                            email
                        FROM
                            users
                        WHERE
                            email = :email
                    ';

                    try {
                        $this->db->begin();

                        $email_verified = $this->db->query(
                            $sql_verify_email,
                            [
                                'email' => $request->get('email')
                            ]
                        );

                        if ( $email_verified->numRows() > 0 ) {
                            $response
                                ->setStatusCode(400)
                                ->setContent('Usuário já está cadastrado!')
                                ->send();
                        } else {
                            $password_hashed = $security->hash($request->get('password'));

                            $user->setName($request->get('name'));
                            $user->setEmail($request->get('email'));
                            $user->setPassword($password_hashed);
                            $user->setLevel(2);
                            $user->setSituation(1);

                            $sql = '
                                INSERT INTO users
                                    (name, email, password, level, situation)
                                VALUES
                                    (:name, :email, :password, :level, :situation);
                            ';

                            $success = $this->db->query(
                                $sql,
                                [
                                    'name'      => $user->getName(),
                                    'email'     => $user->getEmail(),
                                    'password'  => $user->getPassword(),
                                    'level'     => $user->getLevel(),
                                    'situation' => $user->getSituation(),
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
            }
        }

        public function update($id = NULL)
        {
            $user = new User();

            $request = new Request();

            $response = new Response();

            $security = new Security();

            session_start();

            $bearerToken = $request->getHeaders()['Authorization'];

            $bearerToken = str_replace('Bearer ', '', $bearerToken);

            if ( !empty($_SESSION['user']) && !empty($bearerToken) ) {
                $key = base64_encode($_ENV['SECRET_KEY'] . $_SESSION['user']);

                JWT::$leeway = 60;
                $token = JWT::decode($bearerToken, $key, array('HS512'));
            }

            $token_array = (array) $token;
            $nbf_array = (array) $token_array['nbf'];

            if ( date(\DateTime::ISO8601) <= $nbf_array && $token_array['situation'] == 1 ) {
                if ( empty($id) ) {
                    $id = $token_array['id'];
                }

                $sql_verify = '
                    SELECT
                        *
                    FROM
                        users
                    WHERE
                        id = :id
                ';

                $query = $this->db->query(
                    $sql_verify,
                    [
                        'id' => $id
                    ]
                );

                $result = $query->fetch();

                if ( ( !empty($request->get('name')) || !empty($request->get('email')) || !empty($request->get('level')) || !empty($request->get('situation')) ) &&
                     ( ($request->get('name') != $result['name']) || ($request->get('email') != $result['email']) ||
                     ($request->get('level') != $result['level']) || ($request->get('situation') != $result['situation']) ) ) {
                    if ( $request->get('name') != $result['name'] ) {
                        $user->setName($request->get('name'));
                    } else {
                        $user->setName($result['name']);
                    }

                    if ( $request->get('email') != $result['email'] ) {
                        $user->setEmail($request->get('email'));
                    } else {
                        $user->setEmail($result['email']);
                    }

                    if ( $request->get('situation') != $result['situation'] ) {
                        $user->setSituation($request->get('situation'));
                    } else {
                        $user->setSituation($result['situation']);
                    }

                    if ( $token_array['level'] == 0 ) {
                        if ( $request->get('level') != $result['level'] ) {
                            $user->setLevel($request->get('level'));
                        } else {
                            $user->setLevel($result['level']);
                        }
                    } else {
                        $user->setLevel(2);
                    }

                    $sql = '
                        UPDATE
                            users
                        SET
                            name      =: name,
                            email     =: email,
                            level     =: level,
                            situation =: situation
                        WHERE
                            id        = :id
                    ';

                    try {
                        $this->db->begin();

                        $update = $this->db->execute(
                            $sql,
                            [
                                'name'      => $user->getName(),
                                'email'     => $user->getEmail(),
                                'level'     => $user->getLevel(),
                                'situation' => $user->getSituation(),
                                'id'        => $id
                            ]
                        );

                        if ( $update ) {
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
                        'msg' => 'Preencha pelo menos um campo com valor diferente do atual!'
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
        }

        public function changePassword($id = NULL) {
            
        }

        public function delete($id = NULL)
        {
            $request = new Request();

            $response = new Response();

            session_start();

            $bearerToken = $request->getHeaders()['Authorization'];

            $bearerToken = str_replace('Bearer ', '', $bearerToken);

            if ( !empty($_SESSION['user']) && !empty($bearerToken) ) {
                $key = base64_encode($_ENV['SECRET_KEY'] . $_SESSION['user']);

                JWT::$leeway = 60;
                $token = JWT::decode($bearerToken, $key, array('HS512'));
            }

            $token_array = (array) $token;
            $nbf_array = (array) $token_array['nbf'];

            if ( date(\DateTime::ISO8601) <= $nbf_array && $token_array['situation'] == 1 ) {
                if ( empty($id) ) {
                    $id = $token_array['id'];
                }

                $sql = '
                    DELETE
                        users
                    WHERE
                        id = :id
                ';

                try {
                    $this->db->begin();

                    $del = $this->db->execute(
                        $sql,
                        [
                            'id' => $id
                        ]
                    );

                    if ( $del ) {
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
                    'msg' => 'Token inválido!'
                ];

                $response
                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                    ->send();
            }
        }

        public function login()
        {
            $user = new User();

            $request = new Request();

            $response = new Response();

            $security = new Security();

            session_start();

            $bearerToken = $request->getHeaders()['Authorization'];

            $bearerToken = str_replace('Bearer ', '', $bearerToken);

            if ( !empty($_SESSION['user']) && !empty($bearerToken) ) {
                $key = base64_encode($_ENV['SECRET_KEY'] . $_SESSION['user']);

                JWT::$leeway = 60;
                $token = JWT::decode($bearerToken, $key, array('HS512'));
            }

            $token_array = (array) $token;
            $nbf_array = (array) $token_array['nbf'];

            if ( date(\DateTime::ISO8601) <= $nbf_array && $token_array['situation'] == 1 ) {
                $contents = [
                    'msg' => 'Seu usuário já está autenticado!'
                ];

                $response
                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                    ->send();
            } else {
                if ( !empty($request->get('email')) && !empty($request->get('password')) ) {
                    $sql_verify_email = '
                        SELECT
                            email
                        FROM
                            users
                        WHERE
                            email = :email
                    ';
    
                    $email_verified = $this->db->query(
                        $sql_verify_email,
                        [
                            'email' => $request->get('email')
                        ]
                    );
    
                    if ( $email_verified ) {
                        $user->setEmail($request->get('email'));

                        $sql_verify_password = '
                            SELECT
                                password
                            FROM
                                users
                            WHERE
                                email = :email
                        ';
    
                        $password_verify = $this->db->query(
                            $sql_verify_password,
                            [
                                'email' => $user->getEmail()
                            ]
                        );
    
                        $result = $password_verify->fetch();

                        $user->setPassword($result['password']);
    
                        if ( $security->checkHash($request->get('password'), $user->getPassword()) ) {
                            $sql_verify_situation = '
                                SELECT
                                    id, level, situation
                                FROM
                                    users
                                WHERE
                                    email = :email
                            ';
    
                            $verify_situation = $this->db->query(
                                $sql_verify_situation,
                                [
                                    'email' => $user->getEmail()
                                ]
                            );
    
                            $result = $verify_situation->fetch();

                            $user->setId($result['id']);
                            $user->setLevel($result['level']);
                            $user->setSituation($result['situation']);
    
                            if ( $user->getSituation() == 1 ) {
                                $key = base64_encode($_ENV['SECRET_KEY'] . $user->getEmail());

                                $payload = array(
                                    "iss"       => $_SERVER['HOST_NAME'],
                                    "aud"       => $_ENV['APP_URL'],
                                    "iat"       => date(\DateTime::ISO8601),
                                    "nbf"       => date(\DateTime::ISO8601, strtotime('+3 minute')),
                                    'id'        => $user->getId(),
                                    'level'     => $user->getLevel(),
                                    'situation' => $user->getSituation()
                                );
                                
                                try {
                                    $token = JWT::encode($payload, $key, 'HS512');
    
                                    if ( $this->redis->exists($user->getEmail()) ) {
                                        $this->redis->del($user->getEmail());
                                    }

                                    if ( empty($_SESSION['user']) ) {
                                        unset($_SESSION['user']);
                                    }
        
                                    if ( $this->redis->set($user->getEmail(), $token) ) {
                                        $_SESSION['user'] = $user->getEmail();

                                        $contents = [
                                            'token' => $token
                                        ];
                        
                                        $response
                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 200)
                                            ->send();
                                    } else {
                                        $contents = [
                                            'msg' => 'Não foi possível registrar token!'
                                        ];
                        
                                        $response
                                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 500)
                                            ->send();
                                    }
                                } catch (UnexpectedValueException $error) {
                                    $contents = [
                                        'msg' => $error->getMessage()
                                    ];
                    
                                    $response
                                        ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
                                        ->send();
                                }
                            } else {
                                $contents = [
                                    'msg' => 'Usuário desativado, favor contatar departamento de RH!'
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
                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
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
                        'msg' => 'Dados não preenchidos!'
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

            $bearerToken = $request->getHeaders()['Authorization'];

            $bearerToken = str_replace('Bearer ', '', $bearerToken);

            if ( !empty($_SESSION['user']) && !empty($bearerToken) ) {
                $key = base64_encode($_ENV['SECRET_KEY'] . $_SESSION['user']);

                JWT::$leeway = 60;
                $token = JWT::decode($bearerToken, $key, array('HS512'));
            }

            $token_array = (array) $token;
            $nbf_array = (array) $token_array['nbf'];

            if ( date(\DateTime::ISO8601) <= $nbf_array && $token_array['situation'] == 1 ) {
                if ( $this->redis->exists($_SESSION['user']) ) {
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
                }
            } else {
                $contents = [
                    'msg' => 'Token inválido!'
                ];

                $response
                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                    ->send();
            }
        }
    }

?>