<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    protected static ?PDO $connection = null;

    /**
     * Возвращает единственный экземпляр соединения с базой данных (Singleton).
     *
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if (static::$connection === null) {
            static::$connection = static::createConnection();
        }
        return static::$connection;
    }

    /**
     * Создает новое соединение с базой данных.
     *
     * @return PDO
     */
    protected static function createConnection(): PDO
    {
        $connection = Config::get('database.connection');
        $host = Config::get('database.host');
        $database = Config::get('database.database');
        $username = Config::get('database.username');
        $password = Config::get('database.password');

        $dsn = "$connection:host=$host;dbname=$database";

        try {
            return new PDO($dsn, $username, $password);
        } catch (PDOException $e) {
            // Обработка ошибок подключения
            exit('Database connection failed: ' . $e->getMessage());
        }
    }
}
