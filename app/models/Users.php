<?php

    namespace App\Models;

    use Phalcon\Mvc\Model;

    class Users extends Model
    {
        protected $id, $name, $email, $password, $level, $situation;

        public function getId()
        {
            return $this->id;
        }

        public function getName()
        {
            return $this->name;
        }

        public function getEmail()
        {
            return $this->email;
        }

        public function getPassword()
        {
            return $this->password;
        }

        public function getLevel()
        {
            return $this->level;
        }

        public function getSituation()
        {
            return $this->situation;
        }

        public function setId(int $id)
        {
            $this->id = $id;
        }

        public function setName($name)
        {
            $this->name = $name;
        }

        public function setEmail($email)
        {
            $this->email = $email;
        }

        public function setPassword($password)
        {
            $this->password = $password;
        }

        public function setLevel(int $level)
        {
            $this->level = $level;
        }

        public function setSituation(int $situation)
        {
            $this->situation = $situation;
        }
    }

?>