<?php
namespace Dominio\Repositorios;

use Dominio\Categoria;

    interface CategoriaRepositorio {

        public function obtenerCategorias(): array;
        public function obtenerCategoriaPorId($id): ?Categoria;
        public function crearCategoria($categoria): int;
        public function actualizarCategoria($categoria): bool;
        public function eliminarCategoria($id): bool;
    }
