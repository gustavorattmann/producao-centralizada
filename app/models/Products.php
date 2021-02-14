<?php

    namespace App\Models;

    use Phalcon\Mvc\Model;

    class Products extends Model
    {
        private $id, $name, $category;

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
    }

?>