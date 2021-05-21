<?php

namespace Logistics;

use Exception;
use Longman\TelegramBot\Exception\TelegramException;
use PDO;
use Longman\TelegramBot\DB;

class WorkerDB extends DB {

    const TB_WORKERS = 'workers';

    public static function initializeWorker(): void
    {
        if (!defined('static::TB_WORKERS')) {
            define('static::TB_WORKERS', self::$table_prefix . 'workers');
        }
    }

/**
     * Select a worker from the DB
     */
    public static function selectWorkerByPhone(string $worker_phone,$limit=0)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              SELECT *
              FROM `' . static::TB_WORKERS . '`
              WHERE `contact_phone` LIKE :phone
            ';

            if ($limit > 0) {
                $sql .= ' LIMIT :limit';
            }

            $sth = self::$pdo->prepare($sql);

            $sth->bindValue(':phone', $worker_phone);

            if ($limit > 0) {
                $sth->bindValue(':limit', $limit, PDO::PARAM_INT);
            }

            $sth->execute();

            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }

    /**
     * Insert worker in the database
     *
     */
    public static function insertWorker(int $id, string $name,int $address=1, bool $statusIsFree=true, string $phone ): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare("INSERT INTO `". static::TB_WORKERS ."`
                (`id`, `name`, `address`, `status_is_free`, `contact_phone`, `registration_date`)
                VALUES
                (`:id`,  `:name`, `:address`, `:status`, `:phone`, `:date`)
            ");

            $date = self::getTimestamp();

            $sth->bindValue(":id", $id);
            $sth->bindValue(":status", $statusIsFree);
            $sth->bindValue(":name", $name);
            $sth->bindValue(":address", $address);
            $sth->bindValue(":phone", $phone);
            $sth->bindValue(":date", $date);


            return $sth->execute();
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }

    /**
     * Update a worker info
     */
    public static function updateWorker(array $fields_values, array $where_fields_values): bool
    {

        return self::update(static::TB_WORKERS, $fields_values, $where_fields_values);
    }
}


