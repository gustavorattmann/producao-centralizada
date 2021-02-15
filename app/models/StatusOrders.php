<?php

    namespace App\Models;

    use Phalcon\Mvc\Model;

    class StatusOrders extends Model
    {
        private $id, $name, $situation;

        public function getId() {
            return $this->id;
        }

        public function getName() {
            return $this->name;
        }

        public function getSituation() {
            return $this->situation;
        }

        public function setId(int $id) {
            $this->id = $id;
        }

        public function setName($name) {
            $this->name = $name;
        }

        public function setSituation(int $situation) {
            $this->situation = $situation;
        }
    }

?>