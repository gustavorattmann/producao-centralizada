<?php

    namespace App\Models;

    use Phalcon\Mvc\Model;

    class Orders extends Model
    {
        private $id;
        private $user;
        private $product;
        private $raw_material;
        private $quantity_product_requested;
        private $quantity_raw_material_limit;
        private $date;

        public function getId()
        {
            return $this->id;
        }

        public function getUser()
        {
            return $this->user;
        }
        
        public function getProduct()
        {
            return $this->product;
        }

        public function getRawMaterial()
        {
            return $this->raw_material;
        }

        public function getQuantityProductRequested()
        {
            return $this->quantity_product_requested;
        }

        public function getQuantityRawMaterialLimit()
        {
            return $this->quantity_raw_material_limit;
        }

        public function getDate()
        {
            return $this->date;
        }

        public function setId(int $id)
        {
            $this->id = $id;
        }

        public function setUser(int $user)
        {
            $this->user = $user;
        }
        
        public function setProduct(int $product)
        {
            $this->product = $product;
        }

        public function setRawMaterial(int $raw_material)
        {
            $this->raw_material = $raw_material;
        }

        public function setQuantityProductRequested(int $quantity_product_requested)
        {
            if ( $quantity_product_requested > 0 ) {
                $this->quantity_product_requested = $quantity_product_requested;
            } else {
                return 'Insira um valor maior que 0';
            }            
        }

        public function setQuantityRawMaterialLimit(int $quantity_raw_material_limit)
        {
            if ( $quantity_raw_material_limit > 0 ) {
                $this->quantity_raw_material_limit = $quantity_raw_material_limit;
            } else {
                return 'Insira um valor maior que 0';
            }
        }

        public function setDate($date)
        {
            $this->date = $date;
        }
    }

?>