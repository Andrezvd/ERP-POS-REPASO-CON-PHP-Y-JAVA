<?php
namespace Dominio;

    class Categoria {

        private $id;
        private $nombre;
        private $descripcion;
        private $activo;
        private $created_at;
        private $updated_at;

        public function __construct($id, $nombre, $descripcion, $activo, $created_at, $updated_at) {
            $this->id = $id;
            $this->nombre = $nombre;
            $this->descripcion = $descripcion;
            $this->activo = $activo;
            $this->created_at = $created_at;
            $this->updated_at = $updated_at;

        }

        public function getId() {
            return $this->id;
        }
        public function setId($id) {
            $this->id = $id;
        }
        public function getNombre() {
            return $this->nombre;
        }
        public function setNombre($nombre) {
            $this->nombre = $nombre;
        }
        public function getDescripcion() {
            return $this->descripcion;
        }
        public function setDescripcion($descripcion) {
            $this->descripcion = $descripcion;
        }
        public function getActivo() {
            return $this->activo;
        }
        public function setActivo($activo) {
            $this->activo = $activo;
        }
        public function getCreatedAt() {
            return $this->created_at;
        }
        public function setCreatedAt($created_at) {
            $this->created_at = $created_at;
        }
        public function getUpdatedAt() {
            return $this->updated_at;
        }
        public function setUpdatedAt($updated_at) {
            $this->updated_at = $updated_at;
        }

        public function toArray(): array {
            return [
                'id' => $this->id,
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'activo' => $this->activo,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }


