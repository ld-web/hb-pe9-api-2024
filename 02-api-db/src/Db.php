<?php

namespace App;

use PDO;
use Symfony\Component\Dotenv\Dotenv;

class Db
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $dotenv = new Dotenv();
            $dotenv->loadEnv(__DIR__ . '/../.env');

            [
                'DB_HOST' => $host,
                'DB_PORT' => $port,
                'DB_NAME' => $name,
                'DB_CHARSET' => $charset,
                'DB_USER' => $user,
                'DB_PASSWORD' => $password,
            ] = $_ENV;

            $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=$charset";

            self::$pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        }

        return self::$pdo;
    }
}