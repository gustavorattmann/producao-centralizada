<?php

    namespace App\Models;

    use Phalcon\Mvc\Model;

    class Production extends Model
    {
        private $id;
        private $ordered;
        private $quantity_product_produced;
        private $quantity_product_losted;
        private $quantity_raw_material_used;
        private $quantity_raw_material_losted;
        private $justification;
        private $date_initial;
        private $date_final;

        public function getId()
        {
            return $this->id;
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

        public function getDateInitial()
        {
            return $this->date_initial;
        }

        public function getDateFinal()
        {
            return $this->date_final;
        }

        public function setId(int $id)
        {
            $this->id = $id;
        }
        
        public function setOrdered(int $ordered)
        {
            $this->ordered = $ordered;
        }

        public function setQuantityProductProduced(int $quantity_product_produced)
        {
            $this->quantity_product_produced = $quantity_product_produced;
        }

        public function setQuantityProductLosted(int $quantity_product_losted)
        {
            $this->quantity_product_losted = $quantity_product_losted;
        }

        public function setQuantityRawMaterialUsed(int $quantity_raw_material_used)
        {
            $this->quantity_raw_material_used = $quantity_raw_material_used;
        }

        public function setQuantityRawMaterialLosted(int $quantity_raw_material_losted)
        {
            $this->quantity_raw_material_losted = $quantity_raw_material_losted;
        }

        public function setJustification($justification)
        {
            $this->justification = $justification;
        }

        public function setDateInitial($date_initial)
        {
            $this->date_initial = $date_initial;
        }

        public function setDateFinal($date_final)
        {
            $this->date_final = $date_final;
        }
    }

?>