<?php

namespace Logistics;

use Exception;
use Longman\TelegramBot\Exception\TelegramException;
use PDO;
use Longman\TelegramBot\DB;

class WorkerDB extends DB {

    public static function initializeWorker(): void
    {
        if (!defined('TB_WORKERS')) {
            define('TB_WORKERS', self::$table_prefix . 'workers');
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
              FROM `' . TB_WORKERS . '`
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
    public static function insertWorker(int $id, string $name,int $address=1, bool $statusIsFree=1, string $phone ): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('INSERT INTO `' . TB_WORKERS . '`
                (`id`, `status_is_free`, `name`, `address`, `phone`, `registration_date`)
                VALUES
                (:id, :status, :user_id, `:name`, `:address`, `:phone`, `date`)
            ');

            $date = self::getTimestamp();

            $sth->bindValue(':id', $id)
            $sth->bindValue(':status', 1);
            $sth->bindValue(':name', $name);
            $sth->bindValue(':address', $address);
            $sth->bindValue(':phone', $phone);
            $sth->bindValue(':date', $date);


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

        return self::update(TB_WORKERS, $fields_values, $where_fields_values);
    }
}


}