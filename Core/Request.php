<?php

namespace Core;

class Request
{
    private array $data;

    /**
     * Конструктор класса Request.
     * Получает данные запроса из $_GET и $_POST и объединяет их в один массив.
     */
    public function __construct()
    {
        $this->data = array_merge($_GET, $_POST, $this->getPatchData());
    }

    /**
     * Получить значение из запроса по ключу.
     *
     * @param string $key Ключ для получения значения.
     * @param mixed $default Значение по умолчанию, возвращаемое, если ключ не найден.
     * @return mixed Значение из запроса или значение по умолчанию.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Получить все данные запроса в виде ассоциативного массива.
     *
     * @return array Ассоциативный массив с данными запроса.
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Получить значение из запроса по ключу методом POST.
     *
     * @param string $key Ключ для получения значения.
     * @param mixed $default Значение по умолчанию, возвращаемое, если ключ не найден.
     * @return mixed Значение из запроса или значение по умолчанию.
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Получает данные из запроса с методом PATCH в формате raw.
     *
     * @return array Ассоциативный массив данных из запроса с методом PATCH в формате raw.
     * @throws \JsonException
     */
    public function getPatchData(): array
    {
        $rawData = file_get_contents('php://input');

        if(empty($rawData)) {
            return [];
        }

        // Предполагаем, что данные приходят в формате JSON, но можно использовать другой формат
        $data = json_decode($rawData, true, 512, JSON_THROW_ON_ERROR);

        return $data ?? [];
    }

    /**
     * Получить значение из запроса по ключу методом GET.
     *
     * @param string $key Ключ для получения значения.
     * @param mixed $default Значение по умолчанию, возвращаемое, если ключ не найден.
     * @return mixed Значение из запроса или значение по умолчанию.
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Устанавливает необходимые заголовки CORS для ответа.
     */
    public function setCorsHeaders()
    {
        header('Access-Control-Allow-Origin: *'); // Разрешаем запросы с любых доменов (можно указать конкретный домен)
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS'); // Разрешаем различные методы запросов
        header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Разрешаем указанные заголовки
        header('Access-Control-Max-Age: 86400'); // Задаем время кэширования предварительных запросов (preflight) на 24 часа
        header('Access-Control-Allow-Credentials: true'); // Разрешаем передачу куки и авторизационных данных между источниками (если необходимо)

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            // Если это предварительный запрос, возвращаем пустой ответ с кодом 200
            http_response_code(200);
            exit();
        }
    }
}

