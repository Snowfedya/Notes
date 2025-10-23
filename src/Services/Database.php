<?php

namespace App\Services;

class Database
{
    private static $pdo;

    public static function getConnection()
    {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/../../config/database.php';

            self::$pdo = new \PDO(
                "{$config['driver']}:host={$config['host']};dbname={$config['database']};charset={$config['charset']}",
                $config['username'],
                $config['password']
            );

            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        return self::$pdo;
    }
}
