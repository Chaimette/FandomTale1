<?php
namespace Models;
use \PDO;
use \PDOStatement;
use \PDOException;

abstract class AbstractModel {
protected PDO $pdo;
/**
 * Retourne une instance de connexion PDO pour se connecter à la BDD "blog"
 *
 * @return \PDO
 */
function connexionPDO(): \PDO
{
    // Pour plus de détail, voir le cours "00-database.php"
 /*    try
    {
        $config = require __DIR__."/../config/config.php";
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
        $pdo = new \PDO($dsn, $config["user"], $config["password"], $config["options"]);
        return $pdo;
    }
    catch(\PDOException $e)
    {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    } */

     
 

try {
   $dsn = "mysql:host=db;dbname={$_ENV['MYSQL_DATABASE']};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
    $pdo = new \PDO($dsn, $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD'], $options);
    return $pdo;
} catch (\PDOException $e) {
    throw new \PDOException("Erreur de connexion : " . $e->getMessage()."<br>". "Code Exception : ". (int)$e->getCode());
}
    
}


}

