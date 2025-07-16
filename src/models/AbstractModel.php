<?php

namespace App\Models;

use \PDO;
use \PDOStatement;
use \PDOException;

abstract class AbstractModel
{
    protected PDO $pdo;
    /**
     * Retourne une instance de connexion PDO pour se connecter à la BDD "fandom_tales"
     *
     * @return \PDO
     */
    function connexionPDO(): \PDO
    {
        try {
            $dsn = "mysql:host=db;dbname={$_ENV['MYSQL_DATABASE']};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $pdo = new \PDO($dsn, $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD'], $options);
            return $pdo;
        } catch (\PDOException $e) {
            throw new \PDOException("Erreur de connexion : " . $e->getMessage() . "<br>" . "Code Exception : " . (int)$e->getCode());
        }
    }

    public function __construct()
    {
        $this->pdo = $this->connexionPDO();
    }
}
