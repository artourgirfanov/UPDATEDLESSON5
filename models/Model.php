<?php
namespace app\models;

use app\services\DB;

/**
 * Class Model
 * @package app\models
 * @property int id
 */
abstract class Model
{
    /**
     * Метод для
     *
     * @return mixed
     */
    abstract protected static function getTableName():string;

    /**
     * @return DB
     */
    protected static function getDB()
    {
        return DB::getInstance();
    }

    public static function getOne($id)
    {
        $tableName = static::getTableName();
        $sql = "SELECT * FROM {$tableName} WHERE id = :id ";
        $params = [':id' => $id];
        return static::getDB()->getObject($sql, static::class, $params);
    }

    public static function getAll()
    {
        $tableName = static::getTableName();
        $sql = "SELECT * FROM {$tableName}";
        return static::getDB()->getAllObjects($sql, static::class);
    }

    protected function insert()
    {
        $fields = [];
        $params = [];
        foreach ($this as $fieldName => $value) {
            if ($fieldName == 'id') {
                continue;
            }
            $fields[] = $fieldName;
            $params[":{$fieldName}"] = $value;
        }

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            static::getTableName(),
            implode(',', $fields),
            implode(',', array_keys($params))
        );

        static::getDB()->execute($sql, $params);
        $this->id = static::getDB()->getLastId();
    }

    protected function update()
    {
        /*
            UPDATE
                `users`
            SET
                `login` = :login,
                `password` = :password,
                `is_admin` = :is_admin
            WHERE
                `id` = :id;
        */
        $fields = [];
        $params = [];
        foreach ($this as $fieldName => $value) {
            $params[":{$fieldName}"] = $value;
            if ($fieldName == 'id') {
                continue;
            }
            $fields[] = "{$fieldName} = :{$fieldName}";
        }

//        $sql = sprintf(
//            "UPDATE %s SET %s WHERE id = :id",
//            static::getTableName(),
//            implode(',', $fields)
//        );

        $sql = "UPDATE " . static::getTableName() . " SET " . implode(',', $fields) . " WHERE id = :id";
        static::getDB()->execute($sql, $params);
    }

    public function save()
    {
        if (empty($this->id)) {
            $this->insert();
            return;
        }
        $this->update();
    }

    public function delete()
    {
        //$this->id
    }
}
