<?php

    namespace App\Models;

    use Phalcon\Mvc\Model;

    class Products extends Model
    {
        private $id, $name, $category, $situation;

        public function getId()
        {
            return $this->id;
        }

        public function getName()
        {
            return $this->name;
        }

        public function getCategory()
        {
            return $this->category;
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

        public function setCategory($category)
        {
            $this->category = $category;
        }
        
        public function setSituation(int $situation) {
            $this->situation = $situation;
        }
    }

?>