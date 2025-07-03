<?php 
return
[
    "host"=>$_ENV["DB_HOST"],
    "port"=>3306,
    "dbname"=>$_ENV["DB_NAME"],
    "user"=>$_ENV["DB_USER"],
    "password"=>$_ENV["DB_PASSWORD"],
    "charset"=>"utf8mb4",
    "options"=>[
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
];