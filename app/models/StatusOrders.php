<?php

    namespace App\Models;

    use Phalcon\Mvc\Model;

    class StatusOrders extends Model
    {
        private $id, $name;

        public function getId() {
            return $this->id;
        }

        public function getName() {
            return $this->name;
        }

        public function setId(int $id) {
            $this->id = $id;
        }

        public function setName($name) {
            $this->name = $name;
        }
    }

?>