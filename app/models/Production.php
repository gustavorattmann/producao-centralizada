<?php

    namespace App\Models;

    use Phalcon\Mvc\Model;

    class Production extends Model
    {
        private $id;
        private $user;
        private $ordered;
        private $quantity_product_produced;
        private $quantity_product_losted;
        private $quantity_raw_material_used;
        private $quantity_raw_material_losted;
        private $justification;
        private $date;

        public function getId()
        {
            return $this->id;
        }

        public function getUser()
        {
            return $this->user;
        }
        
        public function getOrdered()
        {
            return $this->ordered;
        }

        public function getQuantityProductProduced()
        {
            return $this->quantity_product_produced;
        }

        public function getQuantityProductLosted()
        {
            return $this->quantity_product_losted;
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

        public function setUser(int $user)
        {
            $this->user = $user;
        }
        
        public function setOrdered(int $ordered)
        {
            $this->ordered = $ordered;
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