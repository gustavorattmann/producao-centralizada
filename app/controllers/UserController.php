<?php

    namespace App\Controllers;

    use Phalcon\Mvc\Controller;
    use Phalcon\Http\Request;
    use Phalcon\Http\Response;
    use Phalcon\Security;
    use Lcobucci\JWT\Configuration;
    use Lcobucci\JWT\Signer\Hmac\Sha512;
    use Lcobucci\JWT\Signer\Key\InMemory;
    use App\Models\User;

    class UserController extends Controller
    {
        public function index()
        {
            $user = new User();

            $response = new Response();
            
            session_start();
            
            if ( $_SESSION['user'] ) {
                if ( $this->redis->exists($_SESSION['user']) ) {
                    $bearerToken = $this->redis->get($_SESSION['user']);
    
                    $jwtConfig = Configuration::forUnsecuredSigner();
                    $tokenId = $jwtConfig->parser()->parse($bearerToken)->claims()->get('jti');

                    var_dump($tokenId);
                    exit();
    
                    if ( $tokenId == '4f1g23a12aa' ) {
                        $sql = '
                            SELECT
                                *
                            FROM
                                users
                            WHERE
                                email = :email
                        ';

                        $query = $this->db->query(
                            $sql,
                            [
                                'email' => $_SESSION['user']
                            ]
                        );

                        $result = $query->fetch();

                        $contents = [
                            'user' => [
                                'id'        => $result['id'],
                                'name'      => $result['name'],
                                'email'     => $result['email'],
                                'level'     => $result['level'],
                                'situation' => $result['situation']
                            ]
                        ];

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
            } else {
                $contents = [
                    'msg' => 'Seu usuário não está logado!'
                ];

                $response
                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                    ->send();
            }
            
            //$user = new User();

            //$user->setName('Gustavo');

            //echo $user->getName();

            //$this->redis->set('nome', 'Gustavo');

            echo $this->redis->get('nome');

            //$redis->get('nome');
        }

        public function register()
        {
            $user = new User();

            $request = new Request();

            $response = new Response();

            $security = new Security();

            /*if ( empty($request->get('name')) && empty($request->get('email')) && empty($request->get('password'))
                 && empty($request->get('level')) && empty($request->get('situation')) ) {
                echo "Vazio";
            } else {*/
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
            //}           

        }

        public function login()
        {
            $user = new User();

            $request = new Request();

            $response = new Response();

            $security = new Security();

            session_start();

            if ( $_SESSION['user'] ) {
                if ( $this->redis->exists($_SESSION['user']) ) {
                    $bearerToken = $this->redis->get($_SESSION['user']);
    
                    $jwtConfig = Configuration::forUnsecuredSigner();
                    $tokenId = $jwtConfig->parser()->parse($bearerToken)->claims()->get('jti');

                    if ( $tokenId == '4f1g23a12aa' ) {
                        $response->redirect('api/users');
                    } else {
                        $contents = [
                            'msg' => 'Token inválido!'
                        ];
        
                        $response
                            ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                            ->send();
                    }
                }
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
                                'email' => $request->get('email')
                            ]
                        );
    
                        $result = $password_verify->fetch();
    
                        if ( $security->checkHash($request->get('password'), $result['password']) ) {
                            $sql_verify_situation = '
                                SELECT
                                    situation
                                FROM
                                    users
                            ';
    
                            $verify_situation = $this->db->query($sql_verify_situation);
    
                            $result = $verify_situation->fetch();
    
                            if ( $result['situation'] == 1 ) {
                                $encrypt = base64_encode($_ENV['SECRET_KEY'] . $request->get('email'));
    
                                $config = Configuration::forSymmetricSigner(
                                    new Sha512(),
                                    InMemory::base64Encoded($encrypt)
                                );
                                
                                
                                $now = new \DateTimeImmutable();
                                $token = $config->builder()
                                                ->issuedBy($_ENV['APP_URL'])
                                                ->permittedFor('http://localhost:9000')
                                                ->identifiedBy('4f1g23a12aa')
                                                ->issuedAt($now)
                                                ->canOnlyBeUsedAfter($now->modify('+1 minute'))
                                                ->expiresAt($now->modify('+1 hour'))
                                                ->withClaim('uid', 1)
                                                ->withHeader('foo', 'bar')
                                                ->getToken($config->signer(), $config->signingKey());
    
                                $token->headers(); // Retrieves the token headers
                                $token->claims(); // Retrieves the token claims
    
                                if ( $this->redis->exists($request->get('email')) ) {
                                    $this->redis->del($request->get('email'));
                                }

                                if ( empty($_SESSION['user']) ) {
                                    unset($_SESSION['user']);
                                }
    
                                if ( $this->redis->set($request->get('email'), $token->toString()) ) {
                                    $_SESSION['user'] = $request->get('email');

                                    $contents = [
                                        'token' => $token->toString()
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
    
                                /*$response
                                    ->setStatusCode(200)
                                    ->setContent('Logado!')
                                    ->send();*/
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
            $response = new Response();

            session_start();

            if ( $_SESSION['user'] ) {
                if ( $this->redis->exists($_SESSION['user']) ) {
                    $bearerToken = $this->redis->get($_SESSION['user']);
    
                    $jwtConfig = Configuration::forUnsecuredSigner();
                    $tokenId = $jwtConfig->parser()->parse($bearerToken)->claims()->get('jti');

                    if ( $tokenId == '4f1g23a12aa' ) {
                        if ( $this->redis->del($_SESSION['user']) ) {
                            session_destroy();

                            $response->redirect('api/users/login');
                        } else {
                            $contents = [
                                'msg' => 'Não foi possível deslogar da sessão!'
                            ];
            
                            $response
                                ->setJsonContent($contents, JSON_PRETTY_PRINT, 400)
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
            } else {
                $contents = [
                    'msg' => 'Seu usuário não está logado!'
                ];

                $response
                    ->setJsonContent($contents, JSON_PRETTY_PRINT, 401)
                    ->send();
            }
        }
    }

?>