<?php

namespace Models;

use \PDO;
use \PDOStatement;
use \PDOException;

abstract class ConnectionModel
{
    protected PDO $pdo;
    /**
     * Retourne une instance de connexion PDO pour se connecter à la BDD "fandom_tales"
     *
     * @return \PDO
     */

    public static function connexionPDO(): \PDO
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
    /* Statique pour pouvoir l'utiliser dans les classes filles
    et pour éviter de créer une instance de ConnectionModel à chaque fois
     On utilise "$this->pdo = ConnectionModel::connexionPDO();" dans AbstractModel pour initialiser la connexion, pas besoin de créer une instance car méthode statique
     Si nouvelle instance:
    $connection = new ConnectionModel();
    $this->pdo = $connection->connexionPDO(); */
}
