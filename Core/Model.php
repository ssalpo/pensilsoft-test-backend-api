<?php

namespace Core;

use Core\Exceptions\ModelNotFoundException;
use PDO;

class Model
{
    protected static string $table = '';
    protected static array $fillable = [];

    /**
     * Находит запись в таблице по её ID.
     *
     * @param int $id ID записи.
     * @return static|null Объект модели, соответствующий найденной записи, или null, если запись не найдена.
     */
    public static function find(int $id): ?self
    {
        $db = Database::getInstance();

        $query = "SELECT * FROM " . static::$table . " WHERE id = :id LIMIT 1";

        $stmt = $db->prepare($query);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return static::hydrate($result);
        }

        return null;
    }

    /**
     * Находит запись в таблице по её ID иначе выдает эксепшн
     *
     * @param int $id ID записи.
     * @return static Объект модели, соответствующий найденной записи, или null, если запись не найдена.
     * @throws ModelNotFoundException
     */
    public static function findOrFail(int $id): self
    {
        $result = static::find($id);

        if (!$result) {
            throw new ModelNotFoundException();
        }

        return $result;
    }

    /**
     * Получает все записи из таблицы.
     *
     * @return array Массив объектов модели, представляющих все записи в таблице.
     */
    public static function all(): array
    {
        $db = Database::getInstance();

        $query = "SELECT * FROM " . static::$table;

        $stmt = $db->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $models = [];

        foreach ($results as $result) {
            $models[] = static::hydrate($result);
        }

        return $models;
    }

    /**
     * Сохраняет новую модель в базе данных.
     *
     * @param array $data Ассоциативный массив данных модели, которые нужно сохранить.
     * @return bool Возвращает true, если сохранение прошло успешно, иначе false.
     */
    public static function create(array $data): bool
    {
        $fillableData = self::getFillableData($data);

        if (empty($fillableData)) {
            // Если не указаны данные для сохранения, возвращаем false
            return false;
        }

        $db = Database::getInstance();

        $columns = implode(', ', array_keys($fillableData));
        $placeholders = ':' . implode(', :', array_keys($fillableData));

        $query = "INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)";

        $stmt = $db->prepare($query);

        return $stmt->execute($data);
    }

    /**
     * Обновляет данные существующей модели в базе данных.
     *
     * @param int $id Идентификатор модели, которую нужно обновить.
     * @param array $data Ассоциативный массив данных модели, которые нужно обновить.
     * @return bool Возвращает true, если обновление прошло успешно, иначе false.
     */
    public static function update(int $id, array $data): bool
    {
        $fillableData = self::getFillableData($data);

        if (empty($fillableData)) {
            // Если не указаны данные для обновления, возвращаем false
            return false;
        }

        // Подготовим SQL-запрос для обновления данных
        $fields = [];

        foreach ($fillableData as $field => $value) {
            $fields[] = "`$field` = :$field";
        }

        $fieldList = implode(', ', $fields);
        $db = Database::getInstance();


        // Выполняем SQL-запрос для обновления данных
        $query = "UPDATE " . static::$table . " SET $fieldList WHERE `id` = :id";
        $statement = $db->prepare($query);

        $bindValue = ['id' => $id];

        foreach ($fillableData as $field => $value) {
            $bindValue[$field] = $value;
        }

        if ($statement->execute($bindValue)) {
            // Если запрос выполнен успешно, возвращаем true
            return true;
        }

        // Если запрос не выполнен или произошла ошибка, возвращаем false
        return false;
    }

    /**
     * Удаляет текущий объект модели из базы данных.
     *
     * @return bool Возвращает true, если удаление прошло успешно, иначе false.
     */
    public function delete(): bool
    {
        $db = Database::getInstance();

        $query = "DELETE FROM " . static::$table . " WHERE id = :id";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Получает данные, которые можно заполнить (fillable) для модели.
     *
     * @param array $data Ассоциативный массив данных модели.
     * @return array Ассоциативный массив данных, готовых к сохранению или обновлению.
     */
    private static function getFillableData(array $data): array
    {
        $fillableData = [];

        foreach ($data as $key => $value) {
            if (in_array($key, static::$fillable, true)) {
                $fillableData[$key] = $value;
            }
        }

        return $fillableData;
    }

    /**
     * Создает объект модели и заполняет его данными из массива.
     *
     * @param array $data Данные для заполнения объекта модели.
     * @return static Созданный объект модели.
     */
    protected static function hydrate(array $data): self
    {
        $model = new static();

        foreach ($data as $key => $value) {
            if (in_array($key, ['id', ...static::$fillable], true)) {
                $model->$key = $value;
            }
        }

        return $model;
    }
}
