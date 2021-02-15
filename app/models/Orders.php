<?php

    namespace App\Models;

    use Phalcon\Mvc\Model;

    class Orders extends Model
    {
        private $id;
        private $solicitor;
        private $designated;
        private $product;
        private $raw_material;
        private $quantity_product_requested;
        private $quantity_raw_material_limit;
        private $status_order;
        private $situation;
        private $date_initial;
        private $date_final;

        public function getId()
        {
            return $this->id;
        }

        public function getSolicitor()
        {
            return $this->solicitor;
        }

        public function getDesignated()
        {
            return $this->designated;
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

        public function getStatusOrder()
        {
            return $this->status_order;
        }

        public function getSituation() {
            return $this->situation;
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

        public function setSolicitor(int $solicitor)
        {
            $this->solicitor = $solicitor;
        }

        public function setDesignated(int $designated)
        {
            $this->designated = $designated;
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
            $this->quantity_product_requested = $quantity_product_requested;           
        }

        public function setQuantityRawMaterialLimit(int $quantity_raw_material_limit)
        {
            $this->quantity_raw_material_limit = $quantity_raw_material_limit;
        }

        public function setStatusOrder(int $status_order)
        {
            $this->status_order = $status_order;
        }

        public function setSituation(int $situation) {
            $this->situation = $situation;
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