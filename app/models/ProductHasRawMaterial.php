<?php

    namespace App\Models;

    use Phalcon\Mvc\Model;

    class ProductHasRawMaterial extends Model
    {
        private $id;
        private $product;
        private $raw_material;
        private $quantity_product_requested;
        private $quantity_product_produced;
        private $quantity_product_losted;
        private $quantity_raw_material_limit;
        private $quantity_raw_material_used;
        private $quantity_raw_material_losted;
        private $justification;
        private $date;

        public function getId()
        {
            return $this->id;
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

        public function getQuantityProductProduced()
        {
            return $this->quantity_product_produced;
        }

        public function getQuantityProductLosted()
        {
            return $this->quantity_product_losted;
        }

        public function getQuantityRawMaterialLimit()
        {
            return $this->quantity_raw_material_limit;
        }

        public function getQuantityRawMaterialUsed()
        {
            return $this->quantity_raw_material_used;
        }

        public function getQuantityRawMaterialLosted()
        {
            return $this->quantity_raw_material_losted;
        }

        public function getJustification()
        {
            return $this->justification;
        }

        public function getDate()
        {
            return $this->date;
        }

        public function setId(int $id)
        {
            $this->id = $id;
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

        public function setQuantityProductProduced(int $quantity_product_produced)
        {
            if ( $quantity_product_produced > 0 ) {
                $this->quantity_product_produced = $quantity_product_produced;
            } else {
                return 'Insira um valor maior que 0';
            }
        }

        public function setQuantityProductLosted(int $quantity_product_losted)
        {
            if ( $quantity_product_losted > 0 ) {
                $this->quantity_product_losted = $quantity_product_losted;
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

        public function setQuantityRawMaterialUsed(int $quantity_raw_material_used)
        {
            if ( $quantity_raw_material_used > 0 ) {
                $this->quantity_raw_material_used = $quantity_raw_material_used;
            } else {
                return 'Insira um valor maior que 0';
            }
        }

        public function setQuantityRawMaterialLosted(int $quantity_raw_material_losted)
        {
            if ( $quantity_raw_material_losted > 0 ) {
                $this->quantity_raw_material_losted = $quantity_raw_material_losted;
            } else {
                return 'Insira um valor maior que 0';
            }
        }

        public function setJustification($justification)
        {
            $this->justification = $justification;
        }

        public function setDate($date)
        {
            $this->date = $date;
        }
    }

?>