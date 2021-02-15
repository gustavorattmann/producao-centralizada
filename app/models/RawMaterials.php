<?php

    namespace App\Models;

    use Phalcon\Mvc\Model;

    class RawMaterials extends Model
    {
        private $id, $name, $stock, $situation;

        public function getId()
        {
            return $this->id;
        }

        public function getName()
        {
            return $this->name;
        }

        public function getStock()
        {
            return $this->stock;
        }

        public function getSituation() {
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

        public function setStock($stock)
        {
            $this->stock = $stock;
        }

        public function setSituation(int $situation) {
            $this->situation = $situation;
        }
    }

?>