<?php

namespace Core;

class Container
{
    protected static array $bindings = [];

    /**
     * Регистрирует привязку абстракции к конкретной реализации или замыканию (closure).
     */
    public static function bind(string $abstract, mixed $concrete = null): void
    {
        static::$bindings[$abstract] = $concrete ?? $abstract;
    }

    /**
     * Получает экземпляр класса, связанный с абстракцией.
     */
    public static function make(string $abstract): mixed
    {
        if (isset(static::$bindings[$abstract])) {
            $concrete = static::$bindings[$abstract];
            if (is_callable($concrete)) {
                return $concrete();
            } elseif (is_string($concrete)) {
                return static::resolve($concrete);
            }
        }
        return null;
    }

    protected static function resolve(string $concrete): mixed
    {
        $reflection = new \ReflectionClass($concrete);
        $constructor = $reflection->getConstructor();
        if (!$constructor || $constructor->getNumberOfParameters() === 0) {
            return new $concrete();
        } else {
            $parameters = $constructor->getParameters();
            $dependencies = array_map(fn($param) => static::make($param->getType()->getName()), $parameters);
            return $reflection->newInstanceArgs($dependencies);
        }
    }

    /**
     * Автоматически регистрирует контроллеры и их зависимости из указанной директории.
     */
    public static function autoloadControllers(string $directory): void
    {
        $controllerFiles = glob("$directory/*.php");

        foreach ($controllerFiles as $file) {
            $className = 'App\\Controllers\\' . basename($file, '.php');
            static::bindController($className);
        }
    }

    /**
     * Регистрирует контроллер и его зависимости в контейнере.
     */
    protected static function bindController(string $className): void
    {
        $reflection = new \ReflectionClass($className);
        $constructor = $reflection->getConstructor();
        if (!$constructor || $constructor->getNumberOfParameters() === 0) {
            static::bind($className, fn() => new $className());
        } else {
            $parameters = $constructor->getParameters();
            $dependencies = array_map(fn($param) => static::make($param->getType()?->getName()), $parameters);
            static::bind($className, fn() => new $className(...$dependencies));
        }
    }
}
