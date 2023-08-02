<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Container;
use Dotenv\Dotenv;

// Загружаем переменные из .env файла
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Автозагрузка всех конфигураций
Config::autoloadConfigurations();

// Инициализация контейнера
Container::bind(\Core\Request::class);

// Инициализация контейнера и автоматическая регистрация контроллеров
Container::autoloadControllers(__DIR__ . '/../App/Controllers');

// Вызываем метод setCorsHeaders для установки заголовков CORS перед отправкой ответа
Container::make(\Core\Request::class)->setCorsHeaders();

// Создаем экземпляр маршрутизатора FastRoute
$dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    // Добавляем маршруты с параметрами
    $r->addRoute('GET', '/api/test/expenses', 'App\Controllers\ExpenseController@index');
    $r->addRoute('GET', '/api/test/expenses/{id}', 'App\Controllers\ExpenseController@show');
    $r->addRoute('POST', '/api/test/expenses', 'App\Controllers\ExpenseController@store');
    $r->addRoute('PATCH', '/api/test/expenses/{id}', 'App\Controllers\ExpenseController@update');
    $r->addRoute('DELETE', '/api/test/expenses/{id}', 'App\Controllers\ExpenseController@destroy');
});

// Получение текущего метода и URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'], '?');

// Ищем соответствующий обработчик маршрута
$routeInfo = $dispatcher->dispatch($method, $uri);

// Обрабатываем результаты маршрутизации
switch ($routeInfo[0]) {
    case \FastRoute\Dispatcher::NOT_FOUND:
        // Маршрут не найден
        http_response_code(404);
        echo 'Not found';
        break;
    case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        // Метод запроса не разрешен для данного маршрута
        http_response_code(405);
        echo 'Method not allowed';
        break;
    case \FastRoute\Dispatcher::FOUND:
        // Маршрут найден
        [$controllerName, $action] = explode('@', $routeInfo[1]);

        $vars = $routeInfo[2];

        // Создаем экземпляр контроллера и вызываем соответствующий метод с параметрами
        $container = new \Core\Container();
        $controller = $container->make($controllerName);

        try {
            call_user_func_array([$controller, $action], array_values($vars));
        } catch (\Exception $e) {
            http_response_code(is_int($e->getCode()) ? $e->getCode() : 500);

            \Core\Response::json([
                'status' => false,
                'notification' => 'Ошибка выполнения запроса.'
            ]);
        }

        break;
}
