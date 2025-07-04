<?php

namespace Models;

use Models\ConnectionModel;
use \PDO;


abstract class AbstractModel
{
    protected PDO $pdo;
    public function __construct()
    {
        $this->pdo = ConnectionModel::connexionPDO();
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_CLASS);
    }
}
