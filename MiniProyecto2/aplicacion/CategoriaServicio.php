<?php
namespace Aplicacion;

use Dominio\Repositorios\CategoriaRepositorio;

    class CategoriaServicio {

        private $categoriaRepositorio;

        public function __construct(CategoriaRepositorio $categoriaRepositorio) {
            $this->categoriaRepositorio = $categoriaRepositorio;
        }

        public function obtenerCategorias() {
            return $this->categoriaRepositorio->obtenerCategorias();
        }

        public function obtenerCategoriaPorId($id) {
            return $this->categoriaRepositorio->obtenerCategoriaPorId($id);
        }

        public function crearCategoria($categoria) {
            return $this->categoriaRepositorio->crearCategoria($categoria);
        }

        public function actualizarCategoria($categoria) {
            return $this->categoriaRepositorio->actualizarCategoria($categoria);
        }

        public function eliminarCategoria($id) {
            return $this->categoriaRepositorio->eliminarCategoria($id);
        }
    }
