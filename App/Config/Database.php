<?php

namespace App\Config;

use PDO;
use PDOException;

abstract class Database
{
    private $conexionDB;

    public function __construct()
    {
        $this->getConnection(); 
    }

    protected function getConnection(): PDO
    { 
        try {
            $this->conexionDB = new PDO(
                "mysql:host=localhost;dbname=templo_rosa_bd;charset=utf8",
                "root",
                ""
            );

            $this->conexionDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            die('ERROR DE CONEXIÓN: No se ha podido conectar con la base de datos. ' . $e->getMessage());
        }

        return $this->conexionDB;
    }
}