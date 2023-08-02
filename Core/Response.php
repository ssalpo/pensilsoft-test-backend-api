<?php

namespace Core;

class Response
{
    protected static array $headers = [];
    protected static int $statusCode = 200;

    /**
     * Устанавливает заголовок ответа.
     *
     * @param string $name Имя заголовка.
     * @param string $value Значение заголовка.
     * @return void
     */
    public static function setHeader(string $name, string $value): void
    {
        static::$headers[$name] = $value;
    }

    /**
     * Устанавливает код статуса HTTP-ответа.
     *
     * @param int $statusCode Код статуса HTTP.
     * @return void
     */
    public static function setStatusCode(int $statusCode): void
    {
        static::$statusCode = $statusCode;
    }

    /**
     * Возвращает ответ в формате JSON.
     *
     * @param mixed $data Данные для сериализации в JSON.
     * @return void
     */
    public static function json($data): void
    {
        // Устанавливаем заголовок для ответа в формате JSON
        static::setHeader('Content-Type', 'application/json');
        // Устанавливаем код статуса HTTP
        http_response_code(static::$statusCode);
        // Устанавливаем дополнительные заголовки
        foreach (static::$headers as $name => $value) {
            header("$name: $value");
        }
        // Преобразуем данные в формат JSON и выводим ответ
        echo json_encode($data);
    }
}
