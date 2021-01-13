<?php

    namespace App\Controllers;

    use Phalcon\Mvc\Controller;
    use Phalcon\Http\Request;
    use Phalcon\Http\Response;
    use Phalcon\Security;
    use Phalcon\Session\Manager;
    use Phalcon\Session\Adapter\Stream;
    use Lcobucci\JWT\Configuration;
    use Lcobucci\JWT\Signer\Hmac\Sha512;
    use Lcobucci\JWT\Signer\Key\InMemory;
    use App\Models\User;

    class UserController extends Controller
    {
        public function index()
        {
            $session = new Manager();
            $files = new Stream(
                [
                    'savePath' => '/tmp',
                ]
            );
            
            $session
                ->setAdapter($files)
                ->start();

            if ( $session->get('user') ) {
                if ( $this->redis->exists($session->get('user')) ) {
                    $bearerToken = $this->redis->get($session->get('user'));
    
                    $jwtConfig = Configuration::forUnsecuredSigner();
                    $tokenId = $jwtConfig->parser()->parse($bearerToken)->claims()->get('jti');
    
                    if ( $tokenId == '4f1g23a12aa' ) {
                        echo "funcionou";
                    } else {
                        echo "não";
                    }
                }
            } else {
                echo "redireciona para o login";
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
                            $response
                                ->setStatusCode(201)
                                ->setContent('Cadastro realizado com sucesso!')
                                ->send();
                        } else {
                            $response
                                ->setStatusCode(400)
                                ->setContent('Falha no cadastro!')
                                ->send();
                        }
                    }

                    $this->db->commit();
                } catch (Exception $error) {
                    $this->db->rollback();

                    $response
                        ->setStatusCode(500)
                        ->setContent('Ocorreu um erro em nosso servidor, tente mais tarde!')
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

            $session = new Manager();
            $files = new Stream(
                [
                    'savePath' => '/tmp',
                ]
            );

            $session
                ->setAdapter($files)
                ->start();

            if ( $session->exists('user') ) {
                if ( $this->redis->exists($session->get('user')) ) {
                    $bearerToken = $this->redis->get($session->get('user'));
    
                    $jwtConfig = Configuration::forUnsecuredSigner();
                    $tokenId = $jwtConfig->parser()->parse($bearerToken)->claims()->get('jti');
    
                    if ( $tokenId == '4f1g23a12aa' ) {
                        echo "redireciona para página inicial";
                    } else {
                        echo "não";
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
    
                                if ( $session->has($request->get('email')) ) {
                                    $session->remove($request->get('email'));
                                }
    
                                if ( $this->redis->set($request->get('email'), $token->toString()) ) {
                                    $session->set('user', $request->get('email'));
    
                                    $response
                                        ->setStatusCode(200)
                                        ->setContent($token->toString())
                                        ->send();
                                } else {
                                    $response
                                        ->setStatusCode(400)
                                        ->setContent('Não foi possível registrar token!')
                                        ->send();
                                }
    
                                /*$response
                                    ->setStatusCode(200)
                                    ->setContent('Logado!')
                                    ->send();*/
                            } else {
                                $response
                                    ->setStatusCode(401)
                                    ->setContent('Usuário desativado, favor contatar departamento de RH!')
                                    ->send();
                            }
                        } else {
                            $response
                                ->setStatusCode(400)
                                ->setContent('E-mail e/ou senha incorreto(s)!')
                                ->send();
                        }
                    } else {
                        $response
                            ->setStatusCode(401)
                            ->setContent('Usuário não encontrado no sistema!')
                            ->send();
                    }
                } else {
                    $response
                        ->setStatusCode(401)
                        ->setContent('Dados não preenchidos!')
                        ->send();
                }
            }
        }

        public function logout()
        {

        }
    }

?>