<?php

namespace Core;

class Config
{
    protected static array $configurations = [];

    // Загрузка конфигурации из файла
    public static function load(string $path): void
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $configurations = require $path;

        static::$configurations[$filename] = isset(static::$configurations[$filename])
            ? array_merge_recursive(static::$configurations[$filename], $configurations)
            : $configurations;
    }

    // Получение значения конфигурации по ключу (по точечной нотации)
    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = static::$configurations;

        foreach ($keys as $subKey) {
            if (isset($value[$subKey])) {
                $value = $value[$subKey];
            } else {
                // Если ключ не найден, возвращаем значение по умолчанию
                return $default;
            }
        }

        return $value;
    }

    // Установка значения конфигурации по ключу (по точечной нотации)
    public static function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $target = &static::$configurations;

        foreach ($keys as $subKey) {
            if (!isset($target[$subKey]) || !is_array($target[$subKey])) {
                // Если ключа нет или это не массив, создаем его
                $target[$subKey] = [];
            }
            $target = &$target[$subKey];
        }

        // Устанавливаем значение конфигурации по ключу
        $target = $value;
    }

    // Автозагрузка всех файлов в директории config
    public static function autoloadConfigurations(): void
    {
        $configDir = __DIR__ . '/../config';
        $configFiles = scandir($configDir);

        foreach ($configFiles as $file) {
            $filePath = $configDir . '/' . $file;
            if (is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
                // Загружаем каждый файл с конфигурацией
                static::load($filePath);
            }
        }
    }
}
