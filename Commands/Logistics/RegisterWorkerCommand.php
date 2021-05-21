<?php

/**
 * This file is part of the PHP Telegram Bot example-bot package.
 * https://github.com/php-telegram-bot/example-bot/
 *
 * (c) PHP Telegram Bot Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * User "/registerworker" command
 *
 * This command cancels the currently active conversation and
 * returns a message to let the user know which conversation it was.
 *
 * If no conversation is active, the returned message says so.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Commands\UserCommands\Logistics\Worker;
use Longman\TelegramBot\Commands\UserCommands\Logistics\WorkerDB;

class RegisterWorkerCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'registerworker';

    /**
     * @var string
     */
    protected $description = 'Registration as worker';

    /**
     * @var string
     */
    protected $usage = '/registerworker';

    /**
     * @var string
     */
    protected $version = '0.3.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    protected $worker;

    /**
     * Main command execution if no DB connection is available
     *
     * @throws TelegramException
     */
    public function executeNoDb(): ServerResponse
    {
        return $this->replyToChat('Registration temporary unavailable (database connect error).');
    }

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
         $message = $this->getMessage();
         $user_id = $message->getFrom();

         $this->worker = new Worker($user_id,'test',1,1,'800');

         $result = $this->worker->insert();

         if (!$result) {
            $text = 'error saving to database';
         }

         $result = $this->worker->load('800');

         $text = $result;

        if (!$result) {
            $text = 'error fetching from database';
         }

         return $this->replyToChat($text);

        /* $text = 'database connect test';

        $message = $this->getMessage();
        $userText    = $message->getText(true);

        if ($userText === '') {
            return $this->replyToChat('Command usage: ' . $this->getUsage());
        } */

        return $this->replyToChat($text);
    }

}
