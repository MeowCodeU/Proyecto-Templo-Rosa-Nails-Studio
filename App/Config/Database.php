<?php

namespace App\Config;

use PDO;
use PDOException;


/* ===================================================== */
/* CONEXIÓN CON LA BASE DE DATOS */
/* ===================================================== */

abstract class Database
{
    /* Conexión utilizada para acceder a la base de datos */
    private $conexionDB;


    /* ================================================= */
    /* INICIALIZACIÓN DE LA CONEXIÓN */
    /* ================================================= */

    public function __construct()
    {
        $this->getConnection();
    }


    /* ================================================= */
    /* OBTENCIÓN DE LA CONEXIÓN */
    /* ================================================= */

    protected function getConnection(): PDO
    {
        try {

            /* Conectar con la base de datos utilizando la codificación utf8mb4 */
            $this->conexionDB = new PDO(
                "mysql:host=localhost;dbname=templo_rosa_bd;charset=utf8mb4",
                "root",
                ""
            );


            /* Gestionar los errores de PDO mediante excepciones */
            $this->conexionDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {

            /* Finalizar la ejecución cuando no se pueda establecer la conexión */
            die(
                'ERROR DE CONEXIÓN: No se ha podido conectar con la base de datos. '
                . $e->getMessage()
            );
        }

        return $this->conexionDB;
    }
}